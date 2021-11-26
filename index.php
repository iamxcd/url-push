<?php
ini_set('date.timezone', 'Asia/Shanghai');

require __DIR__ . "/vendor/autoload.php";

use GuzzleHttp\Client;
use QL\QueryList;
use QL\Ext\Baidu;
use Laravie\Parser\Xml\Reader;
use Laravie\Parser\Xml\Document;

class UrlPush
{
    private $http;
    private $ql;
    private $urls = [];
    private $site;
    private $token;

    public function __construct($site, $token)
    {
        $this->http = new Client([
            'base_uri' => 'http://data.zz.baidu.com',
            'timeout'  => 10.0,
        ]);

        $this->site = $site;
        $this->token = $token;

        $this->ql = QueryList::getInstance();
        $this->ql->use(Baidu::class, 'baidu');
    }

    private function push()
    {
        try {
            $res = $this->http->post('urls', [
                'query' => [
                    'site' => $this->site,
                    'token' => $this->token
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

    public function setUrl($urls = [])
    {
        $file = getcwd() . "/urls.txt";

        /**
         * 通过配置文件获取
         */
        if (!file_exists($file)) {
            $this->log("文件 urls.txt 不存在", false);
        } else {
            $content = file_get_contents($file);
            if ($content == false) {
                $this->log("读取 urls.txt 文件失败", false);
            } else {
                $urls = array_merge($urls, explode("\n", $content));
                $this->log("读取 urls.txt 文件成功");
            }
        }

        if (count($urls) == 0) {
            $this->log('url 数量为空，停止执行');
            die;
        }
        $this->log('一共 ' . count($urls) .  " 条记录");

        foreach ($urls as $url) {
            if (!$this->isIncluded($url)) {
                $this->urls[] = $url;
            }
        }
    }

    public function isIncluded($url): bool
    {
        $url =  preg_replace("(^https?://)", "", $url);
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

    public function parseWpSitemap($sitemapUrl)
    {
        $xml = (new Reader(new Document()))->remote($sitemapUrl);
        $sitemap = $xml->parse([
            'sitemap' => ['uses' => 'sitemap[loc>url]'],
        ]);

        $urls = [];
        foreach ($sitemap['sitemap'] as $sitemap) {
            $urls = array_merge($urls, $this->parseSitemapUrl($sitemap['url']));
        }
        return $urls;
    }
    public function parseSitemapUrl($sitemapUrl)
    {
        $xml = (new Reader(new Document()))->remote($sitemapUrl);
        $url = $xml->parse([
            'url' => ['uses' => 'url[loc>url]'],
        ]);
        $urls = array_column($url['url'], 'url');
        return $urls;
    }
}

// 导入配置
$config = require getcwd() . '/config.php';
$push = new UrlPush($config['site'], $config['token']);
$urls = [];

if (isset($config['sitemap']['wordpress']) &&  !!$config['sitemap']['wordpress']) {
    $urls = $push->parseWpSitemap($config['sitemap']['wordpress']);
}

$push->setUrl($urls);
$push->run();
