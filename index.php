<?php
ini_set('date.timezone', 'Asia/Shanghai');

require __DIR__ . "/vendor/autoload.php";

// 导入配置
require getcwd() . '/config.php';

use GuzzleHttp\Client;
use QL\QueryList;
use QL\Ext\Baidu;

class UrlPush
{
    private $http;
    private $ql;
    private $urls = [];

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => 'http://data.zz.baidu.com',
            'timeout'  => 10.0,
        ]);

        $this->ql = QueryList::getInstance();
        $this->ql->use(Baidu::class, 'baidu');
    }

    private function push()
    {
        try {
            $res = $this->http->post('urls', [
                'query' => [
                    'site' => SITE,
                    'token' => TOKEN
                ],
                'body' => implode("\n", $this->urls),
            ]);
            $json = json_decode($res->getBody()->getContents(), true);
            $this->log("发送请求成功");
        } catch (\Throwable $th) {
            $this->log("发送请求失败" . $th->getMessage(), false);
        }
        return $json;
    }

    public function run()
    {
        $json = $this->push();

        $this->log("剩余次数: " . $json['remain']);
        $this->log("成功数量: " . $json['success']);
    }

    public function setUrl()
    {
        $file = getcwd() . "/urls.txt";
        $urls = [];

        /**
         * 通过配置文件获取
         */
        if (!file_exists($file)) {
            $this->log("文件 urls.txt 不存在", false);
        } else {
            $content = file_get_contents($file);
            if ($content == false) {
                $this->log("读取 urls.txt 文件失败", false);
                die;
            }
            $urls = explode("\n", $content);
            $this->log("读取 urls.txt 文件成功");
            $this->log(count($urls) .  "条记录");
        }

        /**
         * TODO 通过sitemap获取
         */
        foreach ($urls as $url) {
            if (!$this->isIncluded($url)) {
                $this->urls[] = $url;
            }
        }
    }

    public function isIncluded($url): bool
    {
        $baidu = $this->ql->baidu(10); // 设置每页搜索15条结果
        $searcher = $baidu->search('site:' . $url);
        $data = $searcher->page(1);
        $count = $data->count();

        $this->log($url);
        $this->log("找到 " . $count . " 条记录");
        return $count > 0;
    }

    public function log($msg, $type = true)
    {
        $date = date("Y/m/d H:i:s") . ' ';
        $tag = '[' . ($type ? '正常' : '异常') . ' '  .  $date  . '] ';
        echo $tag . $msg . PHP_EOL;
    }
}



$push = new UrlPush();
$push->setUrl();
$push->run();
