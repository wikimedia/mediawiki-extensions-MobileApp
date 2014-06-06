<?php

class MobileAppHooks {
	/**
	 * ListDefinedTags hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ListDefinedTags
	 * @param array &$tags
	 *
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
		if ( strpos( $userAgent, "WikipediaApp/" ) === 0 ) {
			// This is from the app!
			$logType = $rc->getAttribute( 'rc_log_type' );
			// Only apply tag for edits, nothing else
			if ( is_null( $logType ) ) {
				$rcId = $rc->getAttribute( 'rc_id' );
				$revId = $rc->getAttribute( 'rc_this_oldid' );
				$logId = $rc->getAttribute( 'rc_logid' );
				ChangeTags::addTags( 'mobile app edit', $rcId, $revId, $logId );
			}
		}
		return true;
	}
}
