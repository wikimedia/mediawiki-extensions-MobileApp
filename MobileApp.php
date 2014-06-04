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
$wgAutoloadClasses['MobileAppHooks'] = __DIR__ . '/MobileApp.hooks.php';

$wgHooks['ListDefinedTags'][] = 'MobileAppHooks::onListDefinedTags';
$wgHooks['RecentChange_save'][] = 'MobileAppHooks::onRecentChange_save';

$wgResourceModules['mobile.app.site'] = array( 'class' => 'MobileAppResourceLoaderModule' );

$wgCommonMobileAppModuleDef = array(
	'localBasePath' => $localBasePath,
	'remoteExtPath' => $remoteExtPath
);

$wgCommonMobileAppLESSFiles = array(
	'../MobileFrontend/less/common/reset.less',
	'../MobileFrontend/less/content/main.less',
	'../MobileFrontend/less/content/hacks.less',
	'less/links.less'
);

$wgResourceModules['mobile.app.pagestyles.android'] = array(
		'styles' => array_merge( $wgCommonMobileAppLESSFiles,
			array(
				'less/android.less',
				'less/enwiki.less'
			) )
	) + $wgCommonMobileAppModuleDef;

$wgResourceModules['mobile.app.pagestyles.ios'] = array(
		'styles' => $wgCommonMobileAppLESSFiles
) + $wgCommonMobileAppModuleDef;

$wgResourceModules['mobile.app.preview'] = array(
	'styles' => array_merge( $wgCommonMobileAppLESSFiles,
		array(
			'less/preview.less',
			'less/enwiki.less'
		) )
) + $wgCommonMobileAppModuleDef;
