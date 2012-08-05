duoshuo-mediawiki
=================

多说 extension for mediawiki

## 安装步骤
1. 将Duoshuo目录复制到你的MediaWiki的extensions目录中
2. 在你的LocalSettings文件末尾增加两行：

请将下面的"你的多说域名"替换为你的实际多说域名(不包括.duoshuo.com部分)，必须为小写

    require_once("$IP/extensions/Duoshuo/Duoshuo.php");
    $wgDuoshuoShortName = '你的多说域名';

## 注意事项
你可以在编辑文章时，使用<duoshuo></duoshuo>手动插入评论框

安装完成之后，所有内容页面底部都会出现多说评论框，以下页面不会出现多说评论框：
* 首页
* 讨论页面
* 特殊页面
* 新创建尚未保存的页面
* 页面的打印版本

## Contact
本插件由[多说网](http://duoshuo.com/)维护，如果你有什么疑问或者建议，欢迎发邮件给zhenyu (at) duoshuo.com，或者在新浪微博上私信[@多说网](http://weibo.com/duoshuo)。

## Showcases
* [旅法师百科](http://wiki.iplaymtg.com/)
