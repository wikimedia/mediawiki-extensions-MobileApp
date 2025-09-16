<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\MobileApp;

use MediaWiki\ChangeTags\ChangeTagsStore;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterBuilderHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterGenerateUserVarsHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\RecentChanges\RecentChange;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IConnectionProvider;

class AbuseFilterHooks implements
	AbuseFilterBuilderHook,
	AbuseFilterGenerateUserVarsHook
{
	public function __construct(
		private readonly IConnectionProvider $dbProvider,
		private readonly ChangeTagsStore $changeTagsStore,
	) {
	}

	/**
	 * AbuseFilter-generateUserVars hook handler that adds the user_app variable.
	 *
	 * @param VariableHolder $vars object to add vars to
	 * @param User $user
	 * @param RecentChange|null $rc If the variables should be generated for an RC entry, this
	 *  is the entry. Null if it's for the current action being filtered.
	 */
	public function onAbuseFilter_generateUserVars( VariableHolder $vars, User $user, ?RecentChange $rc ) {
		global $wgRequest;
		if ( !$rc ) {
			$userAgent = $wgRequest->getHeader( "User-agent" );
			$isWikipediaApp = strpos( $userAgent, "WikipediaApp/" ) === 0;
			$vars->setVar( 'user_app', $isWikipediaApp );
		} else {
			$dbr = $this->dbProvider->getReplicaDatabase();
			$tags = $this->changeTagsStore->getTags( $dbr, $rc->getAttribute( 'rc_id' ) );
			$vars->setVar( 'user_app', in_array( 'mobile app edit', $tags, true ) );
		}
	}

	/**
	 * AbuseFilter-builder hook handler that adds user_app variable to list
	 *  of valid vars
	 *
	 * @param array &$realValues Array in AbuseFilter::getBuilderValues to add to.
	 */
	public function onAbuseFilter_builder( array &$realValues ) {
		$realValues['vars']['user_app'] = 'user-app';
	}
}
