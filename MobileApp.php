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

$localBasePath = dirname( __DIR__ );
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

// List of less files included for both iOS and Android
$commonStyles = array(
	'MobileApp/less/common.less'
);

$wgResourceModules = array_merge( $wgResourceModules, array(
	'mobile.app' => array(
		'styles' => $commonStyles,
		'localBasePath' => $localBasePath,
		'remoteExtPath' => $remoteExtPath
	)
) );
