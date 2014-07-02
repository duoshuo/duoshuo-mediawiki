<?php

/**
 * Duoshuo for MediaWiki
 * @version 0.2
 * @license Apache License 2.0
 * @see http://dev.duoshuo.com/mediawiki/
 * @see https://github.com/duoshuo/duoshuo-mediawiki
 */

// This plugin is inspired by AvbDisqus and MpDisqus 
// @see http://devwiki.beloblotskiy.com/index.php5/AvbDisqus_%28MediaWiki_extension%29
// @see http://www.mediawiki.org/wiki/Extension:MpDisqus

// 安装方法
// 请将多说站点管理后台的基本设置中的多说域名和密钥填入下面三行代码，并复制到LocalSettings.php尾部
// require_once("$IP/extensions/Duoshuo/Duoshuo.php");
// $wgDuoshuoShortName = strtolower('你的多说域名');
// $wgDuoshuoSecret = '你的多说密钥';

if( !defined( 'MEDIAWIKI' ) ) die( -1 );

// Credits
$wgExtensionCredits['specialpage'][] = array(
		'path'              => __FILE__,
		'name'              => 'Duoshuo',
		'version'           => '0.2',
		'author'            => 'shen2',
		'description'       => 'Integrates Duoshuo commenting service',
		'descriptionmsg'    => 'duoshuo-desc',
		'url'               => 'http://dev.duoshuo.com/mediawiki'
);

// Register duoshuo tag.
$wgExtensionFunctions[] = "DuoshuoExtension";
$wgExtensionMessagesFiles['Duoshuo'] = dirname( __FILE__ ) . '/Duoshuo.i18n.php';

// Add hooks
$wgHooks['SkinAfterContent'][] = 'Duoshuo::onSkinAfterContent';
$wgHooks['SkinAfterBottomScripts'][] = 'Duoshuo::onSkinAfterBottomScripts';

if (function_exists('hash_hmac')){
	$wgHooks['UserLoginComplete'][] = 'Duoshuo::onUserLoginComplete';
	$wgHooks['UserLogoutComplete'][] = 'Duoshuo::onUserLogoutComplete';
}

// Duoshuo tag
function DuoshuoExtension() {
	global $wgParser;
	# register the extension with the WikiText parser
	# the first parameter is the name of the new tag.
	# In this case it defines the tag <example> ... </example>
	# the second parameter is the callback function for
	# processing the text between the tags
	$wgParser->setHook( "duoshuo", "render_Duoshuo" );
}

// Renders Duoshuo embed code
function render_Duoshuo($input, $argv, $parser = null) {
	global $wgDuoshuoShortName, $wgTitle, $wgRequest;
	if ($wgDuoshuoShortName == "") {
		echo('Please, set $wgDuoshuoShortName in LocalSettings.php');
		die(1);
	}

	if (!$parser) $parser =& $GLOBALS['wgParser'];
	$output = '<div class="ds-thread" data-thread-key="' . $wgTitle->getArticleID() . '" data-title="' . $wgTitle->getPrefixedText() . '" data-url="' . $wgRequest->getFullRequestURL() . '"></div>';
	return $output;
}

class Duoshuo{
	// Event 'SkinAfterContent': Allows extensions to add text after the page content and article metadata.
	// &$data: (string) Text to be printed out directly (without parsing)
	// This hook should work in all skins. Just set the &$data variable to the text you're going to add.
	// Documentation: \mediawiki-1.16.0\docs\hooks.txt
	public static function onSkinAfterContent(&$data, $skin = null)
	{
		global $wgDuoshuoShortName, $wgTitle, $wgRequest, $wgOut;
	
		if($wgTitle->isSpecialPage()
			|| $wgTitle->getArticleID() == 0
			|| !$wgTitle->canTalk()
			|| $wgTitle->isTalkPage()
			|| method_exists($wgTitle, 'isMainPage') && $wgTitle->isMainPage()
			|| in_array($wgTitle->getNamespace(), array(NS_MEDIAWIKI, NS_TEMPLATE, NS_CATEGORY))
			|| $wgOut->isPrintable()
			|| $wgRequest->getVal('action', 'view') != "view")
			return true;
		
		if(empty($wgDuoshuoShortName))
		{
			echo('Please, set $wgDuoshuoShortName in LocalSettings.php');
			die(1);
		}
	
		$data .= wfMsgForContent('duoshuo-before')
			. '<div class="ds-thread" data-thread-key="' . $wgTitle->getArticleID() . '" data-title="' . $wgTitle->getPrefixedText() . '" data-url="' . $wgTitle->getFullURL() . '"></div>'
			. wfMsgForContent('duoshuo-after');
		return true;
	}
	
	// --------------------- Duoshuo bottom script -------------------------
	
	// Event 'SkinAfterBottomScripts': At the end of Skin::bottomScripts()
	// $skin: Skin object
	// &$text: bottomScripts Text
	// Append to $text to add additional text/scripts after the stock bottom scripts.
	// Documentation: \mediawiki-1.13.0\docs\hooks.txt
	public static function onSkinAfterBottomScripts($skin, &$text)
	{
		global $wgDuoshuoShortName;
		if (empty($wgDuoshuoShortName)) {
			echo('Please, set $wgDuoshuoShortName in LocalSettings.php');
			die(1);
		}
	
		$text .= <<<eot
<!-- Duoshuo bottom script -->
<script type="text/javascript">
	var duoshuoQuery = {short_name: "$wgDuoshuoShortName"};
	(function() {
		var ds = document.createElement('script');
		ds.type = 'text/javascript';ds.async = true;
		ds.src = 'http://static.duoshuo.com/embed.js';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ds);
	})();
</script>
eot;
		return true;
	}
	
	public static function onUserLoginComplete(&$user, &$injected_html){
		global $wgDuoshuoShortName, $wgDuoshuoSecret, $wgCookiePath, $wgCookieDomain, $wgCookieSecure, $wgCookieExpiration;
		if (empty($wgDuoshuoShortName) || empty($wgDuoshuoSecret))
			return true;
		
		$token = array(
			'short_name'=>	$wgDuoshuoShortName,
			'user_key'	=>	$user->mId,
			'name'		=>	$user->getName(),
		);
		$jwt = self::encodeJWT($token, $wgDuoshuoSecret);

		setcookie('duoshuo_token', $jwt, time() + $wgCookieExpiration, $wgCookiePath, $wgCookieDomain, $wgCookieSecure, false);
		return true;
	}
	
	public static function onUserLogoutComplete(&$user, &$inject_html, $old_name){
		global $wgCookiePath, $wgCookieDomain, $wgCookieSecure;
		setcookie('duoshuo_token', '', time() - 86400, $wgCookiePath, $wgCookieDomain, $wgCookieSecure, false);
		return true;
	}
	
	public static function encodeJWT($payload, $key){
		$header = array('typ' => 'JWT', 'alg' => 'HS256');
	
		$segments = array(
			str_replace('=', '', strtr(base64_encode(json_encode($header)), '+/', '-_')),
			str_replace('=', '', strtr(base64_encode(json_encode($payload)), '+/', '-_')),
		);
		$signing_input = implode('.', $segments);
	
		$signature = hash_hmac('sha256', $signing_input, $key, true);
	
		$segments[] = str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));
	
		return implode('.', $segments);
	}
}
