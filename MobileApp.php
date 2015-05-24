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
	'license-name' => 'GPL-2.0+',
);

$wgMessagesDirs['MobileApp'] = __DIR__ . "/i18n";

$wgAutoloadClasses['MobileAppResourceLoaderModule'] = __DIR__ . '/MobileAppResourceLoaderModule.php';
$wgAutoloadClasses['MobileAppHooks'] = __DIR__ . '/MobileApp.hooks.php';

$wgHooks['ListDefinedTags'][] = 'MobileAppHooks::onListDefinedTags';
$wgHooks['ChangeTagsListActive'][] = 'MobileAppHooks::onListDefinedTags';
$wgHooks['RecentChange_save'][] = 'MobileAppHooks::onRecentChange_save';

$wgResourceModules['mobile.app.site'] = array( 'class' => 'MobileAppResourceLoaderModule' );

$wgCommonMobileAppModuleDef = array(
	'localBasePath' => $localBasePath,
	'remoteExtPath' => $remoteExtPath
);

$wgResourceModules['mobile.app.pagestyles.android'] = array(
		'styles' => array(
				'styles/android.less',
				'styles/editlinks.less',
				'styles/issues.less',
				'styles/disambig.less',
				'styles/tables.less',
				'styles/ipa.less',
				'styles/enwiki.less',
				'styles/thumbnails-android.less',
				'styles/galleries-android.less',
			)
) + $wgCommonMobileAppModuleDef;

$wgResourceModules['mobile.app.pagestyles.android.night'] = array(
		'styles' => array(
				'styles/night.less',
			)
) + $wgCommonMobileAppModuleDef;

$wgResourceModules['mobile.app.pagestyles.ios'] = array(
		'styles' => array(
				'styles/editlinks.less',
				'styles/enwiki.less',
				'styles/issues.less',
				'styles/disambig.less'
			)
) + $wgCommonMobileAppModuleDef;

$wgResourceModules['mobile.app.preview'] = array(
	'styles' => array(
			'styles/preview.less',
			'styles/enwiki.less'
		)
) + $wgCommonMobileAppModuleDef;
