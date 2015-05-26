<?php

namespace TooBasic;

/**
 * @class RoutesManager
 */
class RoutesManager extends Manager {
	//
	// Constants.
	const PatternTypeLiteral = 'L';
	const PatternTypeParameter = 'P';
	const ValueTypeInteger = 'integer';
	const ValueTypeString = 'string';
	//
	// Protected properties.
	protected $_routes = array();
	//
	// Public methods.
	public function load() {
		if(isset($_REQUEST['route'])) {
			$path = explode('/', $_REQUEST['route']);

			$matches = false;
			$matchingRoute = false;
			$extraRoute = "";
			$settings = false;
			foreach($this->routes() as $route) {
				$matches = true;
				$settings = array();

				$len = count($route->pattern);
				for($i = 0; $i < $len && $matches; $i++) {
					if(!strlen($path[$i])) {
						$matches = false;
					} elseif($route->pattern[$i]->type == self::PatternTypeLiteral) {
						$matches = $route->pattern[$i]->name == $path[$i];
					} elseif($route->pattern[$i]->type == self::PatternTypeParameter) {
						$settings[$route->pattern[$i]->name] = $path[$i];

						switch($route->pattern[$i]->valueType) {
							case self::ValueTypeInteger:
								$settings[$route->pattern[$i]->name] = $settings[$route->pattern[$i]->name] + 0;
								$matches = is_integer($settings[$route->pattern[$i]->name]);
								break;
							case self::ValueTypeString:
								$matches = is_string($settings[$route->pattern[$i]->name]);
								break;
						}
					} else {
						$matches = false;
					}
				}

				if($matches) {
					$matchingRoute = $route;

					if(count($path) > $len) {
						$extraRoute = implode('/', array_slice($path, $len));
					}

					break;
				}
			}

			if($matchingRoute) {
				foreach($matchingRoute->params as $key => $value) {
					$_GET[$key] = $_REQUEST[$key] = $value;
				}
				foreach($settings as $key => $value) {
					$_GET[$key] = $_REQUEST[$key] = $value;
				}
				if($extraRoute) {
					$_GET['_route'] = $_REQUEST['_route'] = $extraRoute;
				}

				$_GET['action'] = $_REQUEST['action'] = $matchingRoute->action;
			} else {
				$_GET['action'] = $_REQUEST['action'] = '404';
			}

//			debugit([$path, '$matches' => $matches, $extraRoute, $matchingRoute, $settings, $_REQUEST, $_GET], true);
		}
	}
	public function routes() {
		return $this->_routes;
	}
	//
	// Protected methods.
	protected function buildPattern(&$route) {
		$pattern = array();

		foreach(explode('/', $route->route) as $piece) {
			$patPiece = new \stdClass();
			if(preg_match('/:(?<pname>.+):(?<vtype>.*)/', $piece, $matches)) {
				$patPiece->type = self::PatternTypeParameter;
				$patPiece->name = $matches['pname'];
				switch($matches['vtype']) {
					case 'int':
					case 'integer':
						$patPiece->valueType = self::ValueTypeInteger;
						break;
					case 'str':
					case 'string':
						$patPiece->valueType = self::ValueTypeString;
						break;
					default:
						$patPiece->valueType = false;
				}
			} else {
				$patPiece->type = self::PatternTypeLiteral;
				$patPiece->name = $piece;
			}
			$pattern[] = $patPiece;
		}

		$route->pattern = $pattern;
	}
	protected function init() {
		$this->parseConfigs();
	}
	protected function parseConfig($path) {
		$json = json_decode(file_get_contents($path));
		if(json_last_error() != JSON_ERROR_NONE) {
			trigger_error("Unable to parse file '{$path}'. [".json_last_error().'] '.json_last_error_msg(), E_USER_ERROR);
		}

		if(isset($json->routes)) {
			foreach($json->routes as $route) {
				$auxRoute = \TooBasic\objectCopyAndEnforce(array('route', 'action', 'params'), $route, new \stdClass(), array('params' => array()));
				$this->buildPattern($auxRoute);
				$this->_routes[] = $auxRoute;
			}
		}
	}
	protected function parseConfigs() {
		foreach(Paths::Instance()->routesPaths() as $path) {
			$this->parseConfig($path);
		}
	}
}
