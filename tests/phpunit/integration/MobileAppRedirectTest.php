<?php

namespace MediaWiki\Extension\MobileApp\Tests\Integration;

use MediaWiki\Request\FauxRequest;
use MediaWiki\Tests\Specials\SpecialPageTestBase;
use MediaWiki\Title\Title;

/**
 * @covers \MediaWiki\Extension\MobileApp\MobileAppRedirect
 * @group Database
 */
class MobileAppRedirectTest extends SpecialPageTestBase {

	private const IOS_URL = 'https://apps.apple.com/app/wikipedia/id324715238';
	private const ANDROID_URL = 'https://play.google.com/store/apps/details?id=org.wikipedia';
	private const WIKIPEDIA_PORTAL_URL = 'https://www.wikipedia.org';

	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValues( [
			'MobileAppRedirectUrls' => [
				'ios' => self::IOS_URL,
				'android' => self::ANDROID_URL,
				'portal' => self::WIKIPEDIA_PORTAL_URL,
			],
		] );
	}

	protected function newSpecialPage() {
		return new \MediaWiki\Extension\MobileApp\MobileAppRedirect(
			$this->getServiceContainer()->getMainConfig()
		);
	}

	private function execute( string $userAgent, string $language = 'qqx' ): array {
		$request = new FauxRequest();
		$request->setHeader( 'User-Agent', $userAgent );

		return $this->executeSpecialPage( '', $request, $language, null, true );
	}

	public static function provideUserAgents(): iterable {
		yield 'Android Wikipedia app' => [
			'WikipediaApp/2.7.50411-r-2024-05-29 (Android 14; Pixel 8)',
			Title::newMainPage()->getFullURL(),
		];
		yield 'iOS Wikipedia app' => [
			'Wikipedia/7.5.1 (iOS 17.5; iPhone)',
			Title::newMainPage()->getFullURL(),
		];
		yield 'iPhone' => [
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)', self::IOS_URL,
		];
		yield 'Android' => [
			'Mozilla/5.0 (Linux; Android 14; Pixel 8)', self::ANDROID_URL,
		];
		yield 'unrecognized platform' => [
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', self::WIKIPEDIA_PORTAL_URL,
		];
		yield 'empty user agent' => [ '', self::WIKIPEDIA_PORTAL_URL ];
	}

	/** @dataProvider provideUserAgents */
	public function testRedirectsBasedOnUserAgent( string $userAgent, string $expectedUrl ): void {
		[ , $response ] = $this->execute( $userAgent );

		$this->assertSame( $expectedUrl, $response->getHeader( 'location' ) );
	}

	public function testReturns404WhenRedirectUrlsAreNotConfigured(): void {
		$this->overrideConfigValue( 'MobileAppRedirectUrls', false );

		[ , $response ] = $this->execute( '', 'en' );

		$this->assertSame( 404, $response->getStatusCode() );
		$this->assertNull( $response->getHeader( 'location' ) );
	}

	public function testReturns404WhenPortalIsNotConfigured(): void {
		$this->overrideConfigValue( 'MobileAppRedirectUrls', [] );

		[ , $response ] = $this->execute( '' );

		$this->assertSame( 404, $response->getStatusCode() );
		$this->assertNull( $response->getHeader( 'location' ) );
	}

	public function testHeadersPreventUserAgentSpecificRedirectCaching(): void {
		[ , $response ] = $this->execute( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' );

		$this->assertStringContainsString( 'User-Agent', $response->getHeader( 'vary' ) );
		$this->assertStringContainsString( 'no-store', $response->getHeader( 'cache-control' ) );
	}

	public function testUsesPrefixedTitleMessage(): void {
		$page = $this->getServiceContainer()->getSpecialPageFactory()->getPage( 'MobileAppRedirect' );

		$this->assertSame(
			'mobileapp-mobileappredirect-special-title',
			$page->getDescription()->getKey()
		);
	}
}
