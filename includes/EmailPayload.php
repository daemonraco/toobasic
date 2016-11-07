<?php

/**
 * @file EmailPayload.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

class EmailPayload {
	//
	// Protected properties.
	protected $_data = [];
	protected $_emails = false;
	protected $_layout = null;
	protected $_name = false;
	protected $_server = false;
	protected $_stripTags = false;
	protected $_subject = false;
	//
	// Magic methods.
	public function __construct() {
		$auxServer = '';
		$serverParams = \TooBasic\Params::Instance()->server;

		$auxServer = "http://{$serverParams->SERVER_NAME}";
		if($serverParams->SERVER_PORT) {
			$auxServer.= ":{$serverParams->SERVER_PORT}";
		}
		$this->setServer($auxServer);
	}
	public function __get($name) {
		return isset($this->_data[$name]) ? $this->_data[$name] : false;
	}
	public function __isset($name) {
		return isset($this->_data[$name]);
	}
	public function __set($name, $value) {
		$this->_data[$name] = $value;
		return $this->_data[$name];
	}
	//
	// Public methods.
	public function data() {
		return $this->_data;
	}
	public function emails() {
		return $this->_emails;
	}
	public function layout() {
		return $this->_layout;
	}
	public function name() {
		return $this->_name;
	}
	public function isValid() {
		return $this->_name && $this->_emails && $this->_subject;
	}
	public function setEmails($emails) {
		$this->_emails = $emails;
	}
	public function setLayout($layout) {
		$this->_layout = $layout;
	}
	public function setName($name) {
		$this->_name = $name;
	}
	public function setServer($server) {
		$this->_server = $server;
	}
	public function setStripTags($stripTags = true) {
		$this->_stripTags = $stripTags;
	}
	public function setSubject($subject) {
		$this->_subject = $subject;
	}
	public function server() {
		return $this->_server;
	}
	public function stripTags() {
		return $this->_stripTags || isset(Params::Instance()->debugemailstriptags);
	}
	public function subject() {
		return $this->_subject;
	}
}
