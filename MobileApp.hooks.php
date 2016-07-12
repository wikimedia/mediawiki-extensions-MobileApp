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
		$tags[] = 'mobile app edit';
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
		$isCommonsApp = strpos( $userAgent, "Commons/" ) === 0;
		$logType = $rc->getAttribute( 'rc_log_type' );

		// Apply tag for edits done with the Wikipedia app, and
		// edits and uploads done with the Commons app
		if (
			( $isWikipediaApp && is_null( $logType ) )
			|| ( $isCommonsApp && ( is_null( $logType ) || $logType == 'upload' ) )
		) {
			$rcId = $rc->getAttribute( 'rc_id' );
			$revId = $rc->getAttribute( 'rc_this_oldid' );
			$logId = $rc->getAttribute( 'rc_logid' );
			ChangeTags::addTags( 'mobile app edit', $rcId, $revId, $logId );
		}
		return true;
	}
}
