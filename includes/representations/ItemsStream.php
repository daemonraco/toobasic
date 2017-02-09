<?php

/**
 * @file ItemsStream.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

//
// Class aliases.
use PDO;
use PDOStatement;
use TooBasic\Exception;
use TooBasic\Representations\CoreProps;
use TooBasic\Translate;

/**
 * @class ItemsStream
 * @todo doc
 */
class ItemsStream implements \Iterator {
	//
	// Protected properties.
	/**
	 * @var string @todo doc
	 */
	protected $_corePropsHolder = false;
	/**
	 * @var int @todo doc
	 */
	protected $_currentId = false;
	/**
	 * @var \TooBasic\Representations\ItemRepresentation @todo doc
	 */
	protected $_currentItem = false;
	/**
	 * @var mixed[] @todo doc
	 */
	protected $_currentRow = false;
	/**
	 * @var string @todo doc
	 */
	protected $_idKey = false;
	/**
	 * @var \TooBasic\Representations\ItemsFactory @todo doc
	 */
	protected $_factory = false;
	/**
	 * @var boolean @todo doc
	 */
	protected $_pristine = true;
	/**
	 * @var \PDOStatement @todo doc
	 */
	protected $_statement = false;
	//
	// Magic methods.
	public function __construct(ItemsFactory $factory, $corePropsHolder, PDOStatement $statement) {
		//
		// Storing shortcuts.
		$this->_corePropsHolder = $corePropsHolder;
		$this->_factory = $factory;
		$this->_statement = $statement;
		//
		// Initializing.
		$this->init();
	}
	//
	// Public methods.
	public function current() {
		$this->checkCurrent();
		if($this->_currentId !== false && $this->_currentItem === false) {
			$this->_currentItem = $this->_factory->item($this->_currentId);
		}

		return $this->_currentItem;
	}
	public function key() {
		$this->checkCurrent();
		return $this->_currentId;
	}
	public function currentRow() {
		$this->checkCurrent();
		return $this->_currentRow;
	}
	public function fetch() {
		$this->fetchRow();
		return $this->valid();
	}
	public function length() {
		return $this->_statement->rowCount();
	}
	public function next() {
		$this->fetch();
		return $this->current();
	}
	public function pristine() {
		return $this->_pristine;
	}
	public function rewind() {
		if($this->_pristine) {
			$this->checkCurrent();
		} else {
			throw new Exception(Translate::Instance()->EX_items_stream_no_rewind);
		}
	}
	public function skip($offset) {
		while($offset > 0) {
			$offset--;
			$this->fetch();
		}
		return $this->key();
	}
	public function valid() {
		return boolval($this->_currentRow);
	}
	//
	// Protected methods.
	protected function checkCurrent() {
		if(!boolval($this->_currentRow) && $this->_pristine) {
			$this->fetchRow();
		}
	}
	protected function fetchRow() {
		//
		// Trying to fetch the next row from the statement.
		$this->_currentRow = $this->_statement->fetch();
		//
		// This operation changes the status of the statement, that means
		// it cannot be rewinded or things like that, so it's marked as
		// spoiled.
		$this->_pristine = false;
		//
		// Checking if the end-of-cursor was reached.
		if(boolval($this->_currentRow)) {
			//
			// Id shortcut.
			$this->_currentId = $this->_currentRow[$this->_idKey];
		} else {
			//
			// Cleaning.
			$this->_currentId = false;
		}
		//
		// Leaving current item shortcut as cleaned and assuming it works
		// with lazy load.
		$this->_currentItem = false;

		return $this->_currentRow;
	}
	protected function init() {
		$cp = CoreProps::GetCoreProps($this->_corePropsHolder);
		$this->_idKey = "{$cp->ColumnsPerfix}{$cp->IDColumn}";
	}
}
