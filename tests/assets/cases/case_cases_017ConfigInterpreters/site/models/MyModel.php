<?php

class MyModel extends \TooBasic\Model {
	public function cleanJSON($json) {
		return json_encode($json);
	}
	public function multiConfig() {
		return $this->config->my_config(GC_CONFIG_MODE_MULTI, 'TestSpace');
	}
	protected function init() {
		
	}
}
