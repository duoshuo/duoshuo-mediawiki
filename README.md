duoshuo-mediawiki
=================

多说 extension for mediawiki，官方主页：[dev.duoshuo.com](http://dev.duoshuo.com/)

## 安装步骤
1. 在[多说网](http://duoshuo.com/)注册一个站点，记录下域名(ShortName)和密钥(Secret)
1. 将Duoshuo目录复制到你的MediaWiki的extensions目录下
1. 在MediaWiki的LocalSettings.php文件末尾增加三行代码：

请将下面的"你的多说域名"替换为你的实际多说域名(不包括.duoshuo.com部分，必须为小写)，“你的多说密钥”替换成你实际的多说密钥。

    require_once("$IP/extensions/Duoshuo/Duoshuo.php");
    $wgDuoshuoShortName = '你的多说域名';
    $wgDuoshuoSecret = '你的多说密钥';

## 注意事项
你可以在编辑页面时，使用&lt;duoshuo&gt;&lt;/duoshuo&gt;手动插入评论框

安装完成之后，所有内容页面底部都会出现多说评论框，以下页面不会出现多说评论框：
* 首页
* 讨论页面
* 特殊页面
* 新创建尚未保存的页面
* 页面的打印版本
* MediaWiki, Template和Category命名空间下的页面

## Contact
本插件由[多说网](http://duoshuo.com/)维护，如果你有什么疑问或者建议，欢迎发邮件给zhenyu (at) duoshuo.com，或者在新浪微博上私信[@多说网](http://weibo.com/duoshuo)。

## Showcases
* [旅法师百科](http://wiki.iplaymtg.com/)
* [萌娘百科](http://wiki.moegirl.org/)
