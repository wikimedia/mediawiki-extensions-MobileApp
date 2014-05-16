<?php
/**
 * Extension MobileApp
 *
 * @file
 * @ingroup Extensions
 * @author Yuvi Panda
 * @copyright © 2014 Yuvi Panda
 * @licence GNU General Public Licence 2.0 or later
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}

$localBasePath = __DIR__;
$remoteExtPath = 'MobileApp';

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'MobileApp',
	'author' => array( 'Yuvi Panda' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:MobileApp',
	'descriptionmsg' => 'mobileapp-desc',
);

$wgMessagesDirs['MobileApp'] = __DIR__ . "/i18n";

$wgAutoloadClasses['MobileAppResourceLoaderModule'] = __DIR__ . '/MobileAppResourceLoaderModule.php';
$wgResourceModules['mobile.app.site'] = array( 'class' => 'MobileAppResourceLoaderModule' );

$wgCommonMobileAppModuleDef = array(
	'localBasePath' => $localBasePath,
	'remoteExtPath' => $remoteExtPath
);

$wgCommonMobileAppLESSFiles = array(
	'less/pagestyles.less',
	'less/wikihacks.less'
);

$wgResourceModules['mobile.app.pagestyle'] = array(
	'styles' => array_merge( array(
		'less/ui.less',
	), $wgCommonMobileAppLESSFiles )
) + $wgCommonMobileAppModuleDef;

$wgResourceModules['mobile.app.preview'] = array(
	'styles' => array_merge( array(
		'less/preview.less',
	), $wgCommonMobileAppLESSFiles )
) + $wgCommonMobileAppModuleDef;
