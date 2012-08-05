duoshuo-mediawiki
=================

多说 extension for mediawiki

== 安装步骤==
1. 将Duoshuo目录复制到你的MediaWiki的extensions目录中
2. 在你的LocalSettings文件末尾增加两行：
>require_once("$IP/extensions/Duoshuo/Duoshuo.php" );
>$wgDuoshuoShortName = '你的多说域名';

== 注意事项 ==
你可以在编辑文章时，使用<duoshuo></duoshuo>手动插入评论框

安装完成之后，所有内容页面底部都会出现多说评论框，以下页面不会出现多说评论框：
1.首页
2.讨论页面
3.特殊页面
4.新创建尚未保存的页面
5.页面的打印版本