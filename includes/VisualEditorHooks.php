<?php

namespace MediaWiki\Extension\MobileApp;

use MediaWiki\Extension\VisualEditor\VisualEditorRegisterChangeTagsHook;

/**
 * Hooks from VisualEditor, which is optional.
 *
 * This class is only loaded when VisualEditor runs its hooks.
 */
class VisualEditorHooks implements VisualEditorRegisterChangeTagsHook {

	/**
	 * @inheritDoc
	 */
	public function onVisualEditorRegisterChangeTags( array &$tags ): void {
		// VE will handle the onListDefinedTags/onChangeTagsListActive for these:
		$tags[] = 'app web edit android';
		$tags[] = 'app web edit ios';
		$tags[] = 'app web edit other';
	}
}
