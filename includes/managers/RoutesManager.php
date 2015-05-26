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
	public function enroute($path) {
		$out = $path;

		global $Defaults;

		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			$newPath = array();
			$url = parse_url($path);
			$url['query'] = isset($url['query']) ? explode('&', $url['query']) : array();
			foreach($url['query'] as $key => $value) {
				unset($url['query'][$key]);

				$aux = explode('=', $value);
				if(isset($aux[1])) {
					$url['query'][$aux[0]] = $aux[1];
				} else {
					$url['query'][$aux[0]] = "true";
				}
			}

			$matchingRoute = false;
			if(isset($url['query']['action'])) {
				foreach($this->routes() as $route) {
					if($route->action == $url['query']['action']) {
						$matchingRoute = $route;
						break;
					}
				}
			}

			if($matchingRoute) {
				unset($url['query']['action']);

				foreach($matchingRoute->pattern as $piece) {
					if($piece->type == self::PatternTypeLiteral) {
						$newPath[] = $piece->name;
					} elseif($piece->type == self::PatternTypeParameter) {
						if(isset($url['query'][$piece->name])) {
							$newPath[] = $url['query'][$piece->name];
//@TODO
//						switch($piece->valueType) {
//							case self::ValueTypeInteger:
//								$settings[$piece->name] = $settings[$piece->name] + 0;
//								$matches = is_integer($settings[$piece->name]);
//								break;
//							case self::ValueTypeString:
//								$matches = is_string($settings[$piece->name]);
//								break;
//						}
							unset($url['query'][$piece->name]);
						} else {
							$matchingRoute = false;
							break;
						}
					}
				}
			}

			if($matchingRoute) {
				$out = "{$url['path']}/".implode('/', $newPath);
				if($url['query']) {
					$aux = array();
					foreach($url['query'] as $key => $value) {
						$aux[] = "{$key}={$value}";
					}
					$out.= '?'.implode('&', $aux);
				}
			}
		}

		return $out;
	}
	public function load() {
		global $Defaults;

		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES] && isset($_REQUEST["route"])) {
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
		global $Defaults;

		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			foreach(Paths::Instance()->routesPaths() as $path) {
				$this->parseConfig($path);
			}
		}
	}
}
