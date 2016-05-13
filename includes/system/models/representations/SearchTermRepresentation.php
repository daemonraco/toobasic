<?php

namespace TooBasic\Search;

class SearchTermRepresentation extends \TooBasic\Representations\ItemRepresentation {
	//
	// Protected core properties.
	protected $_CP_ColumnsPerfix = 'ste_';
	protected $_CP_IDColumn = 'id';
	protected $_CP_NameColumn = 'term';
	protected $_CP_Table = 'tb_search_terms';
}
