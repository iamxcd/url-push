# 百度收录推送脚本

本项目利用百度的收录API，实现了全自动的提交网站外链，并且自动查重，对于已收录的链接不再重复提交。

## 使用说明

不会php普通用户可以只下载 url-push.phar 文件，复制到你的服务器，按照下面配置好就能执行。

### 创建配置文件

在脚本同目录下创建 config.php 文件，复制以下内容，调整成自己的配置信息。

```
<?php

return [
    // 你的域名
    'site' => "www.xxx.com",
    // 百度API给你分配的TOKEN
    'token' => "xxxxx",
    'sitemap' => [
        // 这里替换成你的wordpress的sitemap地址
        'wordpress' => "{你的域名}/wp-sitemap.xml"
    ]
];


```

### 通过txt文件配置推送网站

在脚本通目录下，创建 urls.txt 文件，每行一个地址，不需要http(https)前缀。
有前缀会导致查重失败。


### 通过配置 sitemap 链接(建议的方式)，自动解析并推送

目前支持 wordpress ，在config.php中，将"{你的域名}/wp-sitemap.xml" 换成你自己的域名。


## 添加crontab定时任务

```bash
// path 是你放脚本文件的目录，url-push.phar是本项目打包后的文件

0 0 * * * cd /path && php url-push.phar
```


## 宝塔面板添加定时任务
