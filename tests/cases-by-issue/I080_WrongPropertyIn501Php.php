<?php

/**
 * @issue 80
 * @description Wrong property in 501.php
 * @url https://github.com/daemonraco/toobasic/issues/80
 */
class I080_WrongPropertyIn501PhpTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCheckingBug() {
		$content = $this->getUrl('?action=test');
	}
	// @}
}
