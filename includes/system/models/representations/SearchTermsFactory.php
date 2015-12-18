<?php

namespace TooBasic\Search;

class SearchTermsFactory extends \TooBasic\Representations\ItemsFactory {
	//
	// Protected core properties.
	protected $_CP_ColumnsPerfix = 'ste_';
	protected $_CP_IDColumn = 'id';
	protected $_CP_NameColumn = 'term';
	protected $_CP_RepresentationClass = 'TooBasic\\Search\\SearchTerm';
	protected $_CP_Table = 'tb_search_terms';
}
