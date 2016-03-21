<?php

class FilterRepresentation extends \TooBasic\Representations\ItemRepresentation {
	//
	// Protected core properties.
	protected $_CP_ColumnsPerfix = 'fil_';
	protected $_CP_ColumnFilters = array(
		'indexed' => GC_DATABASE_FIELD_FILTER_BOOLEAN,
		'props' => GC_DATABASE_FIELD_FILTER_JSON,
		'status' => GC_DATABASE_FIELD_FILTER_BOOLEAN
	);
	protected $_CP_IDColumn = 'id';
	protected $_CP_Table = 'filters';
}
