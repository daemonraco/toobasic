<?php

namespace TestSpace;

class MyConfig extends \TooBasic\ComplexConfig {
	protected $_CP_RequiredPaths = array(
		'property->value' => \TooBasic\ComplexConfig::PathTypeString,
		'property->location' => \TooBasic\ComplexConfig::PathTypeString,
		'property->any' => \TooBasic\ComplexConfig::PathTypeAny,
		'property->list' => \TooBasic\ComplexConfig::PathTypeList,
		'property->numeric->int' => \TooBasic\ComplexConfig::PathTypeNumeric,
		'property->numeric->float' => \TooBasic\ComplexConfig::PathTypeNumeric,
		'property->object' => \TooBasic\ComplexConfig::PathTypeObject,
		'property->string' => \TooBasic\ComplexConfig::PathTypeString
	);
}
