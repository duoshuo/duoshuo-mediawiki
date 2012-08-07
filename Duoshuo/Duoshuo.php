<?php

// This plugin is inspired by AvbDisqus and MpDisqus 
// @see http://devwiki.beloblotskiy.com/index.php5/AvbDisqus_%28MediaWiki_extension%29
// @see http://www.mediawiki.org/wiki/Extension:MpDisqus

if( !defined( 'MEDIAWIKI' ) ) die( -1 );

// Credits
$wgExtensionCredits['specialpage'][] = array(
		'path'              => __FILE__,
		'name'              => 'Duoshuo',
		'version'           => '0.1',
		'author'            => 'shen2',
		'description'       => 'Integrates Duoshuo commenting service',
		'descriptionmsg'    => 'duoshuo-desc',
		'url'               => 'http://dev.duoshuo.com/mediawiki'
);

// Duoshuo settings.
// "ShortName" field in Duoshuo basic settings for your site.
//$wgDuoshuoShortName = strtolower("你的多说域名");

// Register duoshuo tag.
$wgExtensionFunctions[] = "DuoshuoExtension";
$wgExtensionMessagesFiles['Duoshuo'] = dirname( __FILE__ ) . '/Duoshuo.i18n.php';

// Add hooks
$wgHooks['SkinAfterContent'][] = 'onSkinAfterContent_AddDuoshuo';
$wgHooks['SkinAfterBottomScripts'][] = 'onSkinAfterBottomScripts_AddDuoshuoScript';

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

// Event 'SkinAfterContent': Allows extensions to add text after the page content and article metadata.
// &$data: (string) Text to be printed out directly (without parsing)
// This hook should work in all skins. Just set the &$data variable to the text you're going to add.
// Documentation: \mediawiki-1.16.0\docs\hooks.txt
function onSkinAfterContent_AddDuoshuo(&$data, $skin)
{
	global $wgDuoshuoShortName, $wgTitle, $wgRequest, $wgOut;

	if($wgTitle->isSpecialPage()
		|| $wgTitle->getArticleID() == 0
		|| !$wgTitle->canTalk()
		|| $wgTitle->isTalkPage()
		|| $wgTitle->isMainPage()
		|| in_array($wgTitle->getNamespace(), array(NS_MEDIAWIKI, NS_TEMPLATE, NS_CATEGORY))
		|| $wgOut->isPrintable()
		|| $wgRequest->getVal('action', 'view') != "view")
		return true;
	
	if(empty($wgDuoshuoShortName))
	{
		echo('Please, set $wgDuoshuoShortName in LocalSettings.php');
		die(1);
	}

	$data = wfMsgForContent('duoshuo-before')
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
function onSkinAfterBottomScripts_AddDuoshuoScript($skin, &$text)
{
	global $wgDuoshuoShortName;
	if ($wgDuoshuoShortName == "") {
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
