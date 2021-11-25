# 百度收录推送脚本

本项目利用百度的收录API，实现了全自动的提交网站外链，并且自动查重，对于已收录的链接不再重复提交。

## 使用说明

### 系统配置

在脚本同目录下创建 config.php 文件，复制以下内容，调整成自己的配置信息。

```
<?php

// 你的域名
const SITE = "www.xxx.com";
// 百度API给你分配的TOKEN
const TOKEN = "xxxxxxxx";

```

### 配置推送路径

在脚本通目录下，创建 urls.txt 文件，每行一个地址，不需要http(https)前缀。
有前缀会导致查重失败。


### 配置 sitemap 链接，自动解析并推送

wordpress sitemap

这部分未完成


## 添加定时任务


