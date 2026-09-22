<?php
/**
 * @license GPL-2.0-or-later
 * @file
 */
namespace MediaWiki\Extension\MobileApp;

use MediaWiki\Config\Config;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Title\Title;

/**
 * Redirects users based on the configured URLs and the User-Agent header. The
 * Wikipedia app is sent to the main page, while browsers are sent to the
 * configured app store for their platform. If that URL is not configured, the
 * portal URL is used; if the portal URL is also missing, the page returns 404.
 *
 * @ingroup SpecialPage
 * @since 1.47
 */
class MobileAppRedirect extends UnlistedSpecialPage {

	private const APP_USER_AGENT_REGEX = '/Wikipedia/i';
	private const IOS_USER_AGENT_REGEX = '/ipad|iphone|ipod/i';
	private const ANDROID_USER_AGENT_REGEX = '/android/i';

	public function __construct(
		private readonly Config $config,
	) {
		parent::__construct( 'MobileAppRedirect' );
	}

	/** @inheritDoc */
	public function getDescription() {
		return $this->msg( 'mobileapp-mobileappredirect-special-title' );
	}

	/** @inheritDoc */
	public function execute( $subPage ) {
		$this->getOutput()->addVaryHeader( 'User-Agent' );
		$this->getOutput()->disableClientCache();
		$targetUrl = $this->getTargetUrl();

		if ( $targetUrl === null ) {
			$this->getOutput()->setStatusCode( 404 );
			$this->getOutput()->showErrorPage( 'nosuchspecialpage', 'nospecialpagetext' );
			return;
		}

		$this->getOutput()->redirect( $targetUrl );
	}

	private function getTargetUrl(): ?string {
		$redirectUrls = $this->config->get( 'MobileAppRedirectUrls' );
		if ( !is_array( $redirectUrls ) ) {
			return null;
		}

		$userAgent = $this->getRequest()->getHeader( 'User-Agent' ) ?: '';
		if ( $this->isWikipediaApp( $userAgent ) ) {
			return Title::newMainPage()->getFullURL();
		}

		$platform = $this->getBrowserPlatform( $userAgent );
		if ( $platform !== null && array_key_exists( $platform, $redirectUrls ) ) {
			return $redirectUrls[$platform];
		}

		return array_key_exists( 'portal', $redirectUrls ) ? $redirectUrls['portal'] : null;
	}

	private function isWikipediaApp( string $userAgent ): bool {
		return (bool)preg_match( self::APP_USER_AGENT_REGEX, $userAgent );
	}

	private function getBrowserPlatform( string $userAgent ): ?string {
		if ( preg_match( self::IOS_USER_AGENT_REGEX, $userAgent ) ) {
			return 'ios';
		}

		if ( preg_match( self::ANDROID_USER_AGENT_REGEX, $userAgent ) ) {
			return 'android';
		}

		return null;
	}
}
