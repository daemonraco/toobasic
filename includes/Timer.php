<?php

/**
 * @file Timer.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\MagicProp;

/**
 * @class Timer
 * This singleton class holds the logic to manage timers and impact
 * them into a database table.
 */
class Timer extends Singleton {
	/**
	 * @var int This is a stop-watch for global timing.
	 */
	protected $_globalTimer = 0;
	/**
	 * @var boolean This flag indicates if global stop-watch is running.
	 */
	protected $_globalTimerRunning = false;
	/**
	 * @var \TooBasic\MagicProp MagicProp singleton shortcut.
	 */
	protected $_magic = false;
	/**
	 * @var boolean This flag indicates if current execution is being done
	 * from shell or from web.
	 */
	protected $_onShell = false;
	/**
	 * @var int[string] This is a list of stop-watches associated with an
	 * identifier.
	 */
	protected $_timers = [];
	/**
	 * @var int[string] This is a list of flags associated with an identifier
	 * used to know if a stop-watch is running.
	 */
	protected $_timersRunning = [];
	/**
	 * Class constructor.
	 */
	protected function __construct() {
		//
		// Shortcuts.
		$this->_magic = MagicProp::Instance();
		//
		// Is it a shell execution?
		$this->_onShell = defined('__SHELL__');
	}
	//
	// Public methods.
	/**
	 * This method allows to know global stop-watch start position when it's
	 * running or its elapsed time when it's stopped.
	 *
	 * @warning if it never was started it triggers an exception.
	 *
	 * @param boolean $running When given it will be used to return global
	 * stop-watch status.
	 * @return int Returns an amount of milliseconds.
	 */
	public function globalTimer(&$running = null) {
		//
		// Setting a default value to be returned.
		$out = -1;
		//
		// Checking if global timer was used at least once.
		if($this->_globalTimer) {
			//
			// Getting start position.
			$out = ceil($this->_globalTimer * 1000);
			//
			// Returning current status.
			$running = $this->_globalTimerRunning;
		} else {
			//
			// Triggering exception.
			throw new Exception($this->_magic->tr->EX_global_timer_never_used);
		}
		//
		// Returning current position.
		return $out;
	}
	/**
	 * This method starts a specific stop-watch.
	 *
	 * @warning if a stop watch is already running, it will trigger an
	 * exception.
	 *
	 * @param string $id Stop-watch identifier.
	 */
	public function start($id) {
		//
		// Checking timer is not running.
		if(!isset($this->_timersRunning[$id]) || !$this->_timersRunning[$id]) {
			//
			// Setting stop-watch as running
			$this->_timersRunning[$id] = true;
			//
			// Setting start position.
			$this->_timers[$id] = microtime(true);
		} else {
			//
			// Triggering exception.
			throw new Exception($this->_magic->tr->EX_timer_running(['id' => $id]));
		}
	}
	/**
	 * This method starts global stop-watch.
	 *
	 * @warning if a stop watch is already running, it will trigger an
	 * exception.
	 */
	public function startGlobal() {
		//
		// Checking global timer is not running.
		if(!$this->_globalTimerRunning) {
			//
			// Setting stop-watch as running
			$this->_globalTimerRunning = true;
			//
			// Setting start position.
			$this->_globalTimer = microtime(true);
		} else {
			//
			// Triggering exception.
			throw new Exception($this->_magic->tr->EX_global_timer_already_started);
		}
	}
	/**
	 * This method allows to stop and know a specific stop-watch count.
	 *
	 * @warning if it's not running ot it doesn't exists, it triggers an
	 * exception.
	 *
	 * @param string $id Stop-watch identifier.
	 * @return int Returns an amount of milliseconds.
	 */
	public function stop($id) {
		//
		// Checking stop-watch exists and it's running.
		if(isset($this->_timersRunning[$id]) && $this->_timersRunning[$id]) {
			//
			// Setting stop-watch as not running.
			$this->_timersRunning[$id] = false;
			//
			// Calculating and setting elapsed time.
			$this->_timers[$id] = microtime(true) - $this->_timers[$id];
		} elseif(!isset($this->_timersRunning[$id])) {
			//
			// Triggering exception if it doesn't exists.
			throw new Exception($this->_magic->tr->EX_timer_not_started(['id' => $id]));
		} else {
			//
			// Triggering exception if it's not running.
			throw new Exception($this->_magic->tr->EX_timer_not_running(['id' => $id]));
		}
		//
		// Returning final count.
		return $this->timer($id);
	}
	/**
	 * This method allows to stop and know global stop-watch count.
	 *
	 * @warning if it's not running it triggers an exception.
	 *
	 * @return int Returns an amount of milliseconds.
	 */
	public function stopGlobal() {
		//
		// Checking global stop-watch is running.
		if($this->_globalTimerRunning) {
			//
			// Setting stop-watch as not running.
			$this->_globalTimerRunning = false;
			//
			// Calculating and setting elapsed time.
			$this->_globalTimer = microtime(true) - $this->_globalTimer;
		} else {
			//
			// Triggering exception.
			throw new Exception($this->_magic->tr->EX_global_timer_is_not_started);
		}
		//
		// Returning final count.
		return $this->globalTimer($id);
	}
	/**
	 * This method allows to know a specific stop-watch start position when
	 * it's running or its elapsed time when it's stopped.
	 *
	 * @param string $id Stop-watch identifier.
	 * @param boolean $running When given it will be used to return global
	 * stop-watch status.
	 * @return int Returns an amount of milliseconds.
	 */
	public function timer($id, &$running = null) {
		//
		// Setting default values to be returned.
		// @{
		$out = 0;
		$running = null;
		// @}
		//
		// Checking if timer exists.
		if(isset($this->_timersRunning[$id])) {
			//
			// Getting start position.
			$out = ceil($this->_timers[$id] * 1000);
			//
			// Returning current status.
			$running = $this->_timersRunning[$id];
		}
		//
		// Returning current position.
		return $out;
	}
}
