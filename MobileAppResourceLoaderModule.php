<?php
/**
 * Custom ResourceLoader module that loads a Mobile.css page per-wiki.
 */
class MobileAppResourceLoaderModule extends ResourceLoaderWikiModule {
	/**
	 * @param $context ResourceLoaderContext
	 * @return array
	 */
	protected function getPages( ResourceLoaderContext $context ) {
		return [
			'MediaWiki:Mobile.css' => [ 'type' => 'style' ],
		];
	}
}
