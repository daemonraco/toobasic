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
use TooBasic\Params;
use TooBasic\Representations\CoreProps;
use TooBasic\Translate;

/**
 * @class ItemsStream
 * This class provides a middleware between an items factory and a SQL statement
 * provinding some logics required by TooBasic core logic.
 */
class ItemsStream implements \Iterator {
	//
	// Protected properties.
	/**
	 * @var string Name of the class or JSON specs where core properties are
	 * held.
	 */
	protected $_corePropsHolder = false;
	/**
	 * @var int Shortcut to the last fetched ID.
	 */
	protected $_currentId = false;
	/**
	 * @var \TooBasic\Representations\ItemRepresentation Shortcut to the last
	 * loaded item.
	 */
	protected $_currentItem = false;
	/**
	 * @var mixed[] Shortcut to the last fetched row set.
	 */
	protected $_currentRow = false;
	/**
	 * @var string ID column name shortcut.
	 */
	protected $_idKey = false;
	/**
	 * @var \TooBasic\Representations\ItemsFactory Shortcut to the factory
	 * where this stream was created.
	 */
	protected $_factory = false;
	/**
	 * @var int Size of the current statement.
	 */
	protected $_length = false;
	/**
	 * @var boolean Indicates if the internal SQL statement hasn't been used.
	 */
	protected $_pristine = true;
	/**
	 * @var mixed[string] Information about the current used query.
	 */
	protected $_queryInfo = false;
	/**
	 * @var \PDOStatement SQL statement shortcut.
	 */
	protected $_statement = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param \TooBasic\Representations\ItemsFactory $factory Factory that is
	 * creating this stream.
	 * @param string $corePropsHolder Core properties name associated with
	 * represented item in this stream.
	 * @param PDOStatement $statement Already executed statement from which
	 * obtain each entry.
	 * @param mixed[string] $queryInfo Information about the used query.
	 */
	public function __construct(ItemsFactory $factory, $corePropsHolder, PDOStatement $statement, $queryInfo = false) {
		//
		// Storing shortcuts.
		$this->_corePropsHolder = $corePropsHolder;
		$this->_factory = $factory;
		$this->_statement = $statement;
		$this->_queryInfo = $queryInfo;
		//
		// Initializing.
		$this->init();
	}
	//
	// Public methods.
	/**
	 * Provides access to the current item representation.
	 *
	 * @note required by interface \Iterator.
	 *
	 * @return \TooBasic\Representations\ItemRepresentation Returns an item or
	 * FALSE if the end of the statement has been reached.
	 */
	public function current() {
		//
		// Checking if at least one fetch has been attempted.
		$this->checkCurrent();
		if($this->_currentId !== false && $this->_currentItem === false) {
			$this->_currentItem = $this->_factory->item($this->_currentId);
		}

		return $this->_currentItem;
	}
	/**
	 * Provides access to the current row set.
	 *
	 * @return mixed[] Returns a row set or FALSE if the end of the statement
	 * has been reached.
	 */
	public function currentRow() {
		//
		// Checking if at least one fetch has been attempted.
		$this->checkCurrent();
		return $this->_currentRow;
	}
	/**
	 * This method fetches another row set from the internal statement and
	 * tells if it obtained a valid entry.
	 *
	 * @return boolean Returns when the fetched row set is valid.
	 */
	public function fetch() {
		$this->fetchRow();
		return $this->valid();
	}
	/**
	 * Provides access to the current id.
	 *
	 * @note required by interface \Iterator.
	 *
	 * @return int Returns an ID or FALSE if the end of the statement has been
	 * reached.
	 */
	public function key() {
		//
		// Checking if at least one fetch has been attempted.
		$this->checkCurrent();
		return $this->_currentId;
	}
	/**
	 * Provides access to the amount of entries retrieve by the internal SQL
	 * statement.
	 *
	 * @warning PDOStatement::rowCount() may not work as expected with some
	 * database engines, for example SQLite.
	 *
	 * @return int Returns a row count.
	 */
	public function length() {
		if($this->_length === false) {
			//
			// SQLite does not fully support PDOStatement::rowCount()
			// in its current version, therefore this workaround
			// provides a way to get the right amount of retrieved
			// items.
			// Downside: It actually runs a second query to count.
			// Issue: https://github.com/daemonraco/toobasic/issues/222
			if($this->_queryInfo[GC_AFIELD_DB]->engine() == 'sqlite') {
				$query = "select count(*) as entries from ({$this->_queryInfo[GC_AFIELD_QUERY]}) temp";
				$stmt = $this->_queryInfo[GC_AFIELD_DB]->prepare($query);

				if($stmt->execute($this->_queryInfo[GC_AFIELD_PARAMS])) {
					$row = $stmt->fetch();
					$this->_length = $row['entries'];
				} elseif(isset(Params::Instance()->debugdberrors)) {
					debugit($stmt);
				}
			} else {
				$this->_length = $this->_statement->rowCount();
			}
		}
		return $this->_length;
	}
	/**
	 * This method fetches another row set from the internal statement and
	 * returns it as un item.
	 *
	 * @note required by interface \Iterator.
	 *
	 * @return \TooBasic\Representations\ItemRepresentation Returns an item or
	 * FALSE if the end of the statement has been reached.
	 */
	public function next() {
		$this->fetch();
		return $this->current();
	}
	/**
	 * Let's you know if the internal statement has been used.
	 *
	 * @return boolean Returns TRUE when the internal SQL statement hasn't
	 * been used yet.
	 */
	public function pristine() {
		return $this->_pristine;
	}
	/**
	 * Forces this stream to start from the beginning. Since this stream
	 * depends on database logics, this method can only be called once which
	 * is enough to be used in a foreach statement.
	 *
	 * @note required by interface \Iterator.
	 *
	 * @throws \TooBasic\Exception
	 */
	public function rewind() {
		if($this->_pristine) {
			//
			// Checking if at least one fetch has been attempted.
			$this->checkCurrent();
		} else {
			throw new Exception(Translate::Instance()->EX_items_stream_no_rewind);
		}
	}
	/**
	 * This methods provides a way to skip a certain amount of fetch
	 * operations.
	 *
	 * @param int $offset Number of fetches to avoid.
	 * @return int Returns the current id after the skipping.
	 */
	public function skip($offset) {
		while($offset >= 0) {
			$offset--;
			$this->fetch();
		}

		return $this->key();
	}
	/**
	 * This method check if this stream is currently pointing to a valid row
	 * set.
	 *
	 * @note required by interface \Iterator.
	 *
	 * @return boolean Returns TRUE when it's pointing to a row set.
	 */
	public function valid() {
		return boolval($this->_currentRow);
	}
	//
	// Protected methods.
	/**
	 * Ensures that at least one fetch has been attempted.
	 */
	protected function checkCurrent() {
		if(!boolval($this->_currentRow) && $this->_pristine) {
			$this->fetchRow();
		}
	}
	/**
	 * This method is the one that actually retrieves a new entry from the
	 * database and runs all the necessary checks.
	 *
	 * @return mixed[] Returns a row set or FALSE if the end of the statement
	 * has been reached.
	 */
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
	/**
	 * Class initializer
	 */
	protected function init() {
		//
		// Shortcuts.
		$cp = CoreProps::GetCoreProps($this->_corePropsHolder);
		$this->_idKey = "{$cp->ColumnsPerfix}{$cp->IDColumn}";
	}
}
