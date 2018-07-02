<?php

class MobileAppHooks {
	/**
	 * ListDefinedTags and ChangeTagsListActive hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ListDefinedTags
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangeTagsListActive
	 *
	 * @param array &$tags
	 * @return bool
	 */
	public static function onListDefinedTags( array &$tags ) {
		$tags[] = 'mobile edit';
		$tags[] = 'mobile app edit';
		$tags[] = 'android app edit';
		$tags[] = 'ios app edit';
		return true;
	}

	/**
	 * RecentChange_save hook handler that tags mobile changes
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RecentChange_save
	 * @param RecentChange $rc
	 *
	 * @return bool
	 */
	public static function onRecentChange_save( RecentChange $rc ) {
		global $wgRequest;
		$userAgent = $wgRequest->getHeader( "User-agent" );
		$isWikipediaApp = strpos( $userAgent, "WikipediaApp/" ) === 0;
		$isAndroid = strpos( $userAgent, "Android" ) > 0;
		$isIOS = strpos( $userAgent, "iOS" ) > 0;
		$isCommonsApp = strpos( $userAgent, "Commons/" ) === 0;
		$logType = $rc->getAttribute( 'rc_log_type' );

		// Apply tag for edits done with the Wikipedia app, and
		// edits and uploads done with the Commons app
		if (
			( $isWikipediaApp && is_null( $logType ) )
			|| ( $isCommonsApp && ( is_null( $logType ) || $logType == 'upload' ) )
		) {
			// Although MobileFrontend applies the "mobile edit" tag to any edit
			// that is made through the mobile domain, the Android app actually
			// makes its API requests through the desktop domain, meaning that we
			// must apply the "mobile edit" tag explicitly ourselves, in addition
			// to the "mobile app edit" tag.
			$tags = [ 'mobile edit', 'mobile app edit' ];

			if ( $isAndroid ) {
				$tags[] = 'android app edit';
			} elseif ( $isIOS ) {
				$tags[] = 'ios app edit';
			}
		}
		if ( isset( $tags ) ) {
			$rc->addTags( $tags );
		}
		return true;
	}
}
