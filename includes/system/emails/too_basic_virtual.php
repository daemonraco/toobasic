<?php

/**
 * @file too_basic_virtual.php
 * @author Alejandro Dario Simi
 */

/**
 * @class TooBasicVirtualEmail
 */
class TooBasicVirtualEmail extends \TooBasic\Email {
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
	protected function simulation() {
		return true;
	}
}
