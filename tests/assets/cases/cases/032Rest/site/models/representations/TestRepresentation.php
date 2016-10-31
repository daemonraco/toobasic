<?php

class TestRepresentation extends \TooBasic\Representations\ItemRepresentation {
	//
	// Protected core properties.
	protected $_CP_ColumnsPerfix = 'tes_';
	protected $_CP_ColumnFilters = array(
		'indexed' => GC_DATABASE_FIELD_FILTER_BOOLEAN,
		'conf' => GC_DATABASE_FIELD_FILTER_JSON,
		'info' => GC_DATABASE_FIELD_FILTER_JSON
	);
	protected $_CP_IDColumn = 'id';
	protected $_CP_Table = 'tests';
}
