<?php

namespace MediaWiki\Extension\MobileApp;

use ChangeTags;
use MediaWiki\ChangeTags\Hook\ChangeTagsListActiveHook;
use MediaWiki\ChangeTags\Hook\ListDefinedTagsHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Hook\RecentChange_saveHook;
use RecentChange;
use User;

class Hooks implements
	ListDefinedTagsHook,
	ChangeTagsListActiveHook,
	RecentChange_saveHook
{
	/**
	 * ListDefinedTags hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ListDefinedTags
	 *
	 * @param array &$tags
	 */
	public function onListDefinedTags( &$tags ) {
		$this->addChangeTags( $tags );
	}

	/**
	 * ChangeTagsListActive hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangeTagsListActive
	 *
	 * @param array &$tags
	 */
	public function onChangeTagsListActive( &$tags ) {
		$this->addChangeTags( $tags );
	}

	/**
	 * @param array &$tags
	 */
	private function addChangeTags( &$tags ) {
		$tags[] = 'mobile edit';
		$tags[] = 'mobile app edit';
		$tags[] = 'android app edit';
		$tags[] = 'ios app edit';
	}

	/**
	 * RecentChange_save hook handler that tags mobile changes
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RecentChange_save
	 * @param RecentChange $rc
	 */
	public function onRecentChange_save( $rc ) {
		global $wgRequest;
		$userAgent = $wgRequest->getHeader( "User-agent" );
		$isWikipediaApp = strpos( $userAgent, "WikipediaApp/" ) === 0;
		$isCommonsApp = strpos( $userAgent, "Commons/" ) === 0;
		$logType = $rc->getAttribute( 'rc_log_type' );

		// Apply tag for edits done with the Wikipedia app, and
		// edits and uploads done with the Commons app
		if (
			( $isWikipediaApp && $logType === null )
			|| ( $isCommonsApp && ( $logType === null || $logType === 'upload' ) )
		) {
			// Although MobileFrontend applies the "mobile edit" tag to any edit
			// that is made through the mobile domain, the Android app actually
			// makes its API requests through the desktop domain, meaning that we
			// must apply the "mobile edit" tag explicitly ourselves, in addition
			// to the "mobile app edit" tag.
			$tags = [ 'mobile edit', 'mobile app edit' ];

			$isAndroid = strpos( $userAgent, "Android" ) > 0;
			$isIOS = strpos( $userAgent, "iOS" ) > 0;

			if ( $isAndroid ) {
				$tags[] = 'android app edit';
			} elseif ( $isIOS ) {
				$tags[] = 'ios app edit';
			}

			$rc->addTags( $tags );
		}
	}

	/**
	 * AbuseFilter-generateUserVars hook handler that adds the user_app variable.
	 *
	 * @see hooks.txt in AbuseFilter extension
	 * @param VariableHolder $vars object to add vars to
	 * @param User $user
	 * @param RecentChange|null $rc If the variables should be generated for an RC entry, this
	 *  is the entry. Null if it's for the current action being filtered.
	 * @return bool
	 */
	public static function onAbuseFilterGenerateUserVars( $vars, $user, RecentChange $rc = null ) {
		global $wgRequest;
		if ( !$rc ) {
			$userAgent = $wgRequest->getHeader( "User-agent" );
			$isWikipediaApp = strpos( $userAgent, "WikipediaApp/" ) === 0;
			$vars->setVar( 'user_app', $isWikipediaApp );
		} else {
			$dbr = wfGetDB( DB_REPLICA );
			$tags = ChangeTags::getTags( $dbr, $rc->getAttribute( 'rc_id' ) );
			$vars->setVar( 'user_app', in_array( 'mobile app edit', $tags, true ) );
		}
		return true;
	}

	/**
	 * AbuseFilter-builder hook handler that adds user_app variable to list
	 *  of valid vars
	 *
	 * @param array &$builder Array in AbuseFilter::getBuilderValues to add to.
	 * @return bool
	 */
	public static function onAbuseFilterBuilder( &$builder ) {
		$builder['vars']['user_app'] = 'user-app';
		return true;
	}
}
