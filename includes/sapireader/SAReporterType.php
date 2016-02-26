<?php

namespace TooBasic;

abstract class SAReporterType {
	protected $_conf = false;
	public function __construct($conf) {
		$this->_conf = $conf;
	}
	abstract public function render($results);
	protected function extraCssClass($columnConf, $class = []) {
		$out = '';

		if(isset($columnConf->extras)) {
			
		}

		return $out;
	}
	protected function extraAttributes($columnConf) {
		$out = '';

		if(isset($columnConf->extras)) {
			$exceptions = ['class', 'label'];
			foreach(get_object_vars($columnConf->extras) as $name => $value) {
				if(in_array($name, $exceptions)) {
					continue;
				}
				if(is_object($value)) {
					$aux = '';
					foreach(get_object_vars($value) as $k => $v) {
						$aux.= "{$k}:{$v};";
					}
					$out.= " {$name}=\"{$aux}\"";
				} else {
					$out.= " {$name}=\"{$value}\"";
				}
			}
		}

		return $out;
	}
	protected function getPathCleaned($path) {
		return implode('->', explode('/', $path));
	}
	protected function getPathIsset($item, $path) {
		$path = $this->getPathCleaned($path);
		eval("\$out=isset(\$item->{$path});");
		return $out;
	}
	protected function getPathValue($item, $path) {
		$path = $this->getPathCleaned($path);
		eval("\$out=isset(\$item->{$path})?\$item->{$path}:false;");
		return $out;
	}
	protected function isRowExcluded($item) {
		$exclude = false;

		foreach($this->_conf->exceptions as $exception) {
			$path = $this->getPathCleaned($exception->path);
			$isset = $this->getPathIsset($item, $path);
			if($isset) {
				$value = $this->getPathValue($item, $path);
			} else {
				$value = false;
			}

			if(isset($exception->isset) && $isset == $exception->isset) {
				$exclude = true;
				break;
			}
			if(isset($exception->exclude) && $isset && in_array($value, $exception->exclude)) {
				$exclude = true;
				break;
			}
		}
		if(!$exclude) {
			foreach($this->_conf->columns as $column) {
				$value = $this->getPathValue($item, $column->path);

				if(in_array($value, $column->exclude)) {
					$exclude = true;
					break;
				}
			}
		}

		return $exclude;
	}
}
