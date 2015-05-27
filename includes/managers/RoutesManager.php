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
	const ValueTypeEnumerative = 'enum';
	const ValueTypeInteger = 'integer';
	const ValueTypeString = 'string';
	//
	// Protected properties.
	protected $_routes = false;
	//
	// Public methods.
	public function enroute($path) {
		$out = $path;

		global $Defaults;

		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			$this->parseConfigs();

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
			if(!isset($url["host"]) && isset($url['query']['action'])) {
				foreach($this->routes() as $route) {
					if($route->action == $url['query']['action']) {
						$matchingRoute = $route;
						break;
					}
				}
			}

			if($matchingRoute) {
				unset($url['query']['action']);

				$wrong = false;
				foreach($matchingRoute->pattern as $piece) {
					if($piece->type == self::PatternTypeLiteral) {
						$newPath[] = $piece->name;
					} elseif($piece->type == self::PatternTypeParameter) {
						if(isset($url['query'][$piece->name])) {
							$newPath[] = $url['query'][$piece->name];

							switch($piece->valueType) {
								case self::ValueTypeInteger:
									$wrong = !is_numeric($url['query'][$piece->name]);
									break;
								case self::ValueTypeString:
									$wrong = !is_string($url['query'][$piece->name]);
									break;
								case self::ValueTypeEnumerative:
									$wrong = !in_array($url['query'][$piece->name], $piece->values);
									break;
							}

							unset($url['query'][$piece->name]);
						} else {
							$wrong = true;
						}
					}

					if($wrong) {
						break;
					}
				}

				if($wrong) {
					$matchingRoute = false;
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
			$this->parseConfigs();

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
								$matches = is_numeric($settings[$route->pattern[$i]->name]);
								break;
							case self::ValueTypeString:
								$matches = is_string($settings[$route->pattern[$i]->name]);
								break;
							case self::ValueTypeEnumerative:
								$matches = in_array($path[$i], $route->pattern[$i]->values);
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
				$_SERVER['TOOBASIC_ROUTE'] = $matchingRoute->route;
			} else {
				$_GET['action'] = $_REQUEST['action'] = HTTPERROR_NOT_FOUND;
			}
		}
	}
	public function routes() {
		$this->parseConfigs();
		return $this->_routes;
	}
	//
	// Protected methods.
	protected function buildPattern(&$route) {
		$pattern = array();

		foreach(explode('/', $route->route) as $piece) {
			$patPiece = new \stdClass();
			if(preg_match('/:(?<pname>[^:]*):(?<vtype>[^:]*)(:(?<vtypedata>.*)|)/', $piece, $matches)) {
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
					case 'enum':
						$vdata = isset($matches['vtypedata']) ? explode(',', $matches['vtypedata']) : array();
						if($vdata) {
							$patPiece->valueType = self::ValueTypeEnumerative;
							$patPiece->values = $vdata;
						}
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
	protected function debugRoutes() {
		echo "<pre style=\"border:dashed gray 1px;width:100%;padding:5px;\">\nRoutes:\n";
		foreach($this->routes() as $route) {
			echo "- '{$route->route}':\n";
			echo "\tAction: '{$route->action}'\n";

			echo "\tPieces:\n";
			$i = 0;
			foreach($route->pattern as $pat) {
				$i++;
				echo "\t\t[{$i}] '{$pat->name}'";
				switch($pat->type) {
					case self::PatternTypeParameter:
						echo "[parameter]";
						switch($pat->valueType) {
							case self::ValueTypeInteger:
								echo ' (must be numeric)';
								break;
							case self::ValueTypeString:
								echo ' (must be a string)';
								break;
							case self::ValueTypeEnumerative:
								echo "\n\t\t\tMust be one of these:";
								foreach($pat->values as $val) {
									echo "\n\t\t\t\t- '{$val}'";
								}
								break;
						}
						break;
					case self::PatternTypeLiteral:
					default:
						echo "[literal] (must be an exact match)";
						break;
				}
				echo "\n";
			}

			if($route->params) {
				echo "\tForced Url Parameters:\n";
				foreach($route->params as $key => $value) {
					echo "\t\t'{$key}': '{$value}'\n";
				}
			}

			echo "\n";
		}
		echo "</pre>\n";
		die;
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
		if($this->_routes === false) {
			$this->_routes = array();

			global $Defaults;

			if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
				foreach(Paths::Instance()->routesPaths() as $path) {
					$this->parseConfig($path);
				}

				if(isset($_REQUEST['debugroutes'])) {
					$this->debugRoutes();
				}
			} else {
				if(isset($_REQUEST['debugroutes'])) {
					debugit("Routes are disabled", true);
				}
			}
		}
	}
}
