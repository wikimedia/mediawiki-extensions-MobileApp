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

	private function execute(
		string $userAgent,
		string $language = 'qqx',
		array $params = []
	): array {
		$request = new FauxRequest( $params );
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

	public static function provideSupportedPlatforms(): iterable {
		yield 'platform=ios with desktop user agent' => [
			'ios',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
			self::IOS_URL,
		];
		yield 'platform=ios with Android user agent' => [
			'ios',
			'Mozilla/5.0 (Linux; Android 14; Pixel 8)',
			self::IOS_URL,
		];
		yield 'platform=ios with empty user agent' => [
			'ios',
			'',
			self::IOS_URL,
		];
		yield 'platform=ios with Wikipedia app user agent' => [
			'ios',
			'WikipediaApp/2.7.50411-r-2024-05-29 (Android 14; Pixel 8)',
			self::IOS_URL,
		];
		yield 'platform=iOS case insensitive' => [
			'iOS',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
			self::IOS_URL,
		];
		yield 'platform=android with desktop user agent' => [
			'android',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
			self::ANDROID_URL,
		];
		yield 'platform=android with iPhone user agent' => [
			'android',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
			self::ANDROID_URL,
		];
		yield 'platform=android with empty user agent' => [
			'android',
			'',
			self::ANDROID_URL,
		];
		yield 'platform=android with Wikipedia app user agent' => [
			'android',
			'WikipediaApp/2.7.50411-r-2024-05-29 (Android 14; Pixel 8)',
			self::ANDROID_URL,
		];
		yield 'platform=ANDROID case insensitive' => [
			'ANDROID',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
			self::ANDROID_URL,
		];
	}

	/** @dataProvider provideSupportedPlatforms */
	public function testRedirectsBasedOnPlatformParam(
		string $platform,
		string $userAgent,
		string $expectedUrl
	): void {
		[ , $response ] = $this->execute( $userAgent, 'qqx', [ 'platform' => $platform ] );

		$this->assertSame( $expectedUrl, $response->getHeader( 'location' ) );
	}

	public static function provideUnsupportedPlatforms(): iterable {
		yield 'unsupported platform with iPhone user agent' => [
			'windows',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
		];
		yield 'unsupported platform with Android user agent' => [
			'windows',
			'Mozilla/5.0 (Linux; Android 14; Pixel 8)',
		];
		yield 'unsupported platform with desktop user agent' => [
			'windows',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
		];
		yield 'unsupported platform with Wikipedia app user agent' => [
			'invalid',
			'WikipediaApp/2.7.50411-r-2024-05-29 (Android 14; Pixel 8)',
		];
		yield 'unsupported platform with empty user agent' => [
			'invalid',
			'',
		];
	}

	/** @dataProvider provideUnsupportedPlatforms */
	public function testReturns404WhenPlatformIsUnsupported(
		string $platform,
		string $userAgent
	): void {
		[ , $response ] = $this->execute( $userAgent, 'qqx', [ 'platform' => $platform ] );

		$this->assertSame( 404, $response->getStatusCode() );
		$this->assertNull( $response->getHeader( 'location' ) );
	}

	public function testReturns404WhenPlatformIsNotConfigured(): void {
		$this->overrideConfigValues( [
			'MobileAppRedirectUrls' => [
				'android' => self::ANDROID_URL,
				'portal' => self::WIKIPEDIA_PORTAL_URL,
			],
		] );

		[ , $response ] = $this->execute(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
			'qqx',
			[ 'platform' => 'ios' ]
		);

		$this->assertSame( 404, $response->getStatusCode() );
		$this->assertNull( $response->getHeader( 'location' ) );
	}

	public function testEmptyPlatformParamFallsBackToUserAgent(): void {
		[ , $response ] = $this->execute(
			'Mozilla/5.0 (Linux; Android 14; Pixel 8)',
			'qqx',
			[ 'platform' => '' ]
		);

		$this->assertSame( self::ANDROID_URL, $response->getHeader( 'location' ) );

		[ , $desktopResponse ] = $this->execute(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
			'qqx',
			[ 'platform' => '' ]
		);

		$this->assertSame( self::WIKIPEDIA_PORTAL_URL, $desktopResponse->getHeader( 'location' ) );
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
