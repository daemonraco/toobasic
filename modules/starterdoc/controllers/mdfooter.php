<?php

class MdfooterController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\CacheAdapter::ExpirationSizeLarge;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		global $Defaults;
		global $SkinName;

		$this->assign('allowSkins', $Defaults['starterdoc-allow-skins']);

		$skins = array();
#		$skins[] = array(
#			'value' => 'default',
#			'title' => 'Default',
#			'current' => false
#		);
		$skins[] = array(
			'value' => 'cerulean',
			'title' => 'Cerulean',
			'current' => false
		);
#		$skins[] = array(
#			'value' => 'cosmo',
#			'title' => 'Cosmo',
#			'current' => false
#		);
		$skins[] = array(
			'value' => 'cyborg',
			'title' => 'Cyborg',
			'current' => false
		);
#		$skins[] = array(
#			'value' => 'darkly',
#			'title' => 'Darkly',
#			'current' => false
#		);
#		$skins[] = array(
#			'value' => 'flatly',
#			'title' => 'Flatly',
#			'current' => false
#		);
		$skins[] = array(
			'value' => 'journal',
			'title' => 'Journal',
			'current' => false
		);
#		$skins[] = array(
#			'value' => 'lumen',
#			'title' => 'Lumen',
#			'current' => false
#		);
#		$skins[] = array(
#			'value' => 'paper',
#			'title' => 'Paper',
#			'current' => false
#		);
		$skins[] = array(
			'value' => 'readable',
			'title' => 'Readable',
			'current' => false
		);
#		$skins[] = array(
#			'value' => 'sandstone',
#			'title' => 'Sandstone',
#			'current' => false
#		);
		$skins[] = array(
			'value' => 'simplex',
			'title' => 'Simplex',
			'current' => false
		);
		$skins[] = array(
			'value' => 'slate',
			'title' => 'Slate',
			'current' => false
		);
		$skins[] = array(
			'value' => 'spacelab',
			'title' => 'Spacelab',
			'current' => false
		);
		$skins[] = array(
			'value' => 'superhero',
			'title' => 'Superhero',
			'current' => false
		);
		$skins[] = array(
			'value' => 'united',
			'title' => 'United',
			'current' => false
		);
#		$skins[] = array(
#			'value' => 'yeti',
#			'title' => 'Yeti',
#			'current' => false
#		);
		foreach($skins as &$skin) {
			if($skin['value'] == $SkinName) {
				$skin['current'] = true;
				break;
			}
		}
		$this->assign('skins', $skins);

		return true;
	}
}
