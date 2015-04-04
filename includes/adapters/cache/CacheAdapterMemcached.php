<?php

namespace TooBasic;

class CacheAdapterMemcached extends CacheAdapter {
	//
	// Constants.
	//
	// Protected properties.
	protected $_conn = false;
	// 
	// Magic methods.
	public function __construct() {
		parent::__construct();
		global $Defaults;

		if(!isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER]) || !isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT])) {
			trigger_error("Memcached is not properly set. Check constants \$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER] and \$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT].", E_USER_ERROR);
		}

		$this->_conn = new \Memcached();
		$this->_conn->addServer($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER], $Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT]);
	}
	//
	// Public methods.
	public function delete($prefix, $key) {
		$fullKey = $this->fullKey($prefix, $key);
		$data = $this->_conn->delete($fullKey);

		if(isset(Params::Instance()->get->debugmemcached)) {
			echo "<!-- memcached delete: $fullKey [NOT FOUND]-->\n";
		}
	}
	public function get($prefix, $key) {
		$data = null;
		$fullKey = $this->fullKey($prefix, $key);

		$data = $this->_conn->get($fullKey);
		if($data) {
			$data = unserialize($data);

			if(isset(Params::Instance()->get->debugmemcached)) {
				echo "<!-- memcached get: $fullKey [FOUND]-->\n";
			}
		} else {
			$data = null;

			if(isset(Params::Instance()->get->debugmemcached)) {
				echo "<!-- memcached get: $fullKey [NOT FOUND]-->\n";
			}
		}

		return $data;
	}
	public function save($prefix, $key, $data) {
		$fullKey = $this->fullKey($prefix, $key);
		$this->_conn->delete($fullKey);
		$this->_conn->set($fullKey, serialize($data), time() + $this->_expirationLength);

		if(isset(Params::Instance()->get->debugmemcached)) {
			echo "<!-- memcached set: {$fullKey} -->\n";
		}
	}
	//
	// Protected methods.
	protected function fullKey($prefix, $key) {
		$key = sha1($key);
		$prefix.= ($prefix ? "_" : "");
		$out = "{$prefix}{$key}";

		return $out;
	}
}
