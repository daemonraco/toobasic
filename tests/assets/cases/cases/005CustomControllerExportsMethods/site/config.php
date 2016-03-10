<?php

function customFunction($value, $secondValue) {
	return "VALUE:{$value}:{$secondValue}:";
}
$Defaults[GC_DEFAULTS_CTRLEXPORTS_EXTENSIONS]['customMethod'] = 'customFunction';
