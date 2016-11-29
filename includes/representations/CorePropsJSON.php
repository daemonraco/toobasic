<?php

/**
 * @file ItemRepresentation.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\SpecsValidator;
use TooBasic\Translate;

/**
 * @class CorePropsJSON
 * This class can provided access to core properties defined inside a JSON file.
 */
class CorePropsJSON extends CoreProps {
	//
	// Public methods.
	public function load($path) {
		$jsonString = file_get_contents($path);
		if(!SpecsValidator::ValidateJsonString('representation', $jsonString, $info)) {
			throw new Exception(Translate::Instance()->EX_json_path_fail_specs(['path' => $path])." {$info[JV_FIELD_ERROR][JV_FIELD_MESSAGE]}");
		}
		$json = json_decode($jsonString, true);

		$this->_ColumnsPerfix = isset($json['columns_perfix']) ? $json['columns_perfix'] : '';
		$this->_ColumnFilters = isset($json['column_filters']) ? $json['column_filters'] : [];
		$this->_DisableCreate = isset($json['disable_create']) ? $json['disable_create'] : false;
		$this->_ExtendedColumns = isset($json['extended_columns']) ? $json['extended_columns'] : [];
		$this->_IDColumn = isset($json['columns']['id']) ? $json['columns']['id'] : '';
		$this->_NameColumn = isset($json['columns']['name']) ? $json['columns']['name'] : 'name';
		$this->_OrderBy = isset($json['order_by']) ? $json['order_by'] : false;
		$this->_ReadOnlyColumns = isset($json['read_only_columns']) ? $json['read_only_columns'] : [];
		$this->_RepresentationClass = isset($json['representation_class']) ? $json['representation_class'] : '';
		$this->_SubLists = isset($json['sub_lists']) ? $json['sub_lists'] : [];
		$this->_Table = isset($json['table']) ? $json['table'] : '';
	}
}
