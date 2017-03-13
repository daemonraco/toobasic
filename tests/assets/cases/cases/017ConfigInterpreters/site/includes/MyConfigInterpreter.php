<?php

namespace TestSpace;

class MyConfig extends \TooBasic\ComplexConfig {
	protected $_CP_RequiredPaths = array(
		'property->value' => \TooBasic\ComplexConfig::PATH_TYPE_STRING,
		'property->location' => \TooBasic\ComplexConfig::PATH_TYPE_STRING,
		'property->any' => \TooBasic\ComplexConfig::PATH_TYPE_ANY,
		'property->list' => \TooBasic\ComplexConfig::PATH_TYPE_LIST,
		'property->numeric->int' => \TooBasic\ComplexConfig::PATH_TYPE_NUMERIC,
		'property->numeric->float' => \TooBasic\ComplexConfig::PATH_TYPE_NUMERIC,
		'property->object' => \TooBasic\ComplexConfig::PATH_TYPE_OBJECT,
		'property->string' => \TooBasic\ComplexConfig::PATH_TYPE_STRING
	);
}
