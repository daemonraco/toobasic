<?php

/**
 * @file Params.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\Shell\OptionsStack;

/**
 * @class Params
 * This class is an abstract way to represent all major global arrays like
 * '$_GET', '$_POST', etc.
 */
class Params extends Singleton {
	//
	// Constants.
	const TypeCOOKIE = 'cookie';
	const TypeENV = 'env';
	const TypeGET = 'get';
	const TypeHEADERS = 'headers';
	const TypeOPTIONS = 'opt';
	const TypePOST = 'post';
	const TypeSERVER = 'server';
	const TypeINTERNAL = 'internal';
	//
	// Protected properties.
	/**
	 * @var string[] List of detected debug parameters.
	 */
	protected $_debugs = false;
	/**
	 * @var boolean This flag inidicates that a debug parameter has been
	 * detected.
	 */
	protected $_hasDebugs = false;
	/**
	 * @var \TooBasic\ParamsStack[string] List of parameters stacks managed by this
	 * singleton.
	 */
	protected $_paramsStacks = array();
	//
	// Magic methods.
	/**
	 * This magic method provides a simple way to access values. Supposing you
	 * need to access a property called 'myprop' in the url, you may use one
	 * of these:
	 * 	- Params::Instance()->get->myprop
	 * 	- Params::Instance()->myprop
	 * The second one may not return what's expected because it search in all
	 * stacks and only return the first one found.
	 *
	 * @param string $name Method or property to look for.
	 * @return mixed Retuns a \TooBasic\ParamsStack when a method is required
	 * or a property value.
	 */
	public function __get($name) {
		//
		// Default values.
		$out = null;
		//
		// Cleaning possible method name.
		$methodName = strtolower($name);
		//
		// If there's a params stack for the method, the stack is
		// retruned.
		if(isset($this->_paramsStacks[$methodName])) {
			$out = $this->_paramsStacks[$methodName];
		} else {
			//
			// If there's no params stack for the given name, every
			// stack is search for this name.
			// The first to be found is returned.
			foreach($this->_paramsStacks as $stack) {
				$out = $stack->{$name};
				if($out !== null) {
					break;
				}
			}
		}
		//
		// Returning what was found.
		return $out;
	}
	/**
	 * This method provides a way to know if a parameter was given or not. It
	 * can be used in this two ways:
	 * 	- isset(Params::Instance()->get->myprop)
	 * 	- isset(Params::Instance()->myprop)
	 * It's behavior is similar to '__get()'.
	 *
	 * @param string $name Method or property to look for and check.
	 * @return mixed Retuns a \TooBasic\ParamsStack when a method is required
	 * or a property status.
	 */
	public function __isset($name) {
		//
		// Default values.
		$out = false;
		//
		// Cleaning possible method name.
		$methodName = strtolower($name);
		//
		// If there's a params stack for the method, the stack is
		// retruned for further checks.
		if(isset($this->_paramsStacks[$methodName])) {
			$out = $this->_paramsStacks[$methodName];
		} else {
			//
			// If there's no params stack for the given name, every
			// stack is search for this name.
			// The first to be found implies existence.
			foreach($this->_paramsStacks as $stack) {
				//
				// Checking status inside the stack
				$out = isset($stack->{$name});
				//
				// When it's found, not more checks are required.
				if($out) {
					break;
				}
			}
		}
		//
		// Returning what was found.
		return $out;
	}
	//
	// Public methods.
	/**
	 * This method allows to intentionaly set values on some params stack. It
	 * won't affect the real super global.
	 *
	 * @param string $type Params stack identifier.
	 * @param string[string] $values Associative list of values to set. It
	 * overrides existing values on identical keys.
	 */
	public function addValues($type, $values) {
		//
		// Checking if it's a valid params stack.
		if(isset($this->_paramsStacks[$type])) {
			//
			// Adding values.
			$this->_paramsStacks[$type]->addValues($values);
			//
			// Debugs may have changed.
			$this->_debugs = false;
		} else {
			throw new Exception("Unknown parameters stack called '{$type}'");
		}
	}
	/**
	 * This method allows to access a complete list of values on some params
	 * stack.
	 *
	 * @param string $type Params stack identifier.
	 * @return string[string] Associative list of found values.
	 */
	public function allOf($type) {
		//
		// Default values.
		$out = array();
		//
		// Checking if it's a valid params stack.
		if(isset($this->_paramsStacks[$type])) {
			//
			// Requesting all values from the stack,
			$out = $this->_paramsStacks[$type]->all();
		} else {
			throw new Exception("Unknown parameters stack called '{$type}'");
		}
		//
		// Returning what was found.
		return $out;
	}
	/**
	 * This method returns the list of found debug parameters across all
	 * params stacks.
	 *
	 * @return string[] Returns a list of found debug parameters.
	 */
	public function debugs() {
		//
		// Checking if it's already calculated.
		if($this->_debugs === false) {
			//
			// Default values.
			$this->_debugs = array();
			//
			// Checking every stack.
			foreach($this->_paramsStacks as $stack) {
				//
				// Checkin if it has debug parameters.
				if($stack->hasDebugs()) {
					//
					// At this point, we do have debugs.
					$this->_hasDebugs = true;
					//
					// Adding debugs to the list.
					$this->_debugs = array_merge($this->_debugs, $stack->debugs());
				}
			}
		}
		//
		// Returning what was found.
		return $this->_debugs;
	}
	/**
	 * This method allows to know if a debug parameter was given.
	 *
	 * @return boolean Returns true when at least one debug parameter was
	 * given.
	 */
	public function hasDebugs() {
		//
		// Enforcing debugs check and listing.
		$this->debugs();
		return $this->_hasDebugs;
	}
	//
	// Protected methods.
	/**
	 * Singletons initializtion.
	 */
	protected function init() {
		$this->loadParams();
	}
	/**
	 * This method loads all params stacks.
	 */
	protected function loadParams() {
		$this->_paramsStacks[self::TypeOPTIONS] = defined('__SHELL__') ? new OptionsStack(array()) : new ParamsStack(array());
		$this->_paramsStacks[self::TypePOST] = new ParamsStack($_POST);
		$this->_paramsStacks[self::TypeGET] = new ParamsStack($_GET);
		$this->_paramsStacks[self::TypeENV] = new ParamsStack($_ENV);
		$this->_paramsStacks[self::TypeCOOKIE] = new ParamsStack($_COOKIE);
		$this->_paramsStacks[self::TypeSERVER] = new ParamsStack($_SERVER);
		$this->_paramsStacks[self::TypeHEADERS] = !defined('__SHELL__') ? new ParamsStack(\getallheaders()) : new ParamsStack(array());
		//
		// Internal stack for custom purposes.
		$this->_paramsStacks[self::TypeINTERNAL] = new ParamsStack(array());
	}
}
