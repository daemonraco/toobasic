<?php

/**
 * @file RoutesManager.php
 * @author Alejandro Dario Simi
 */

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
	protected $_lastErrorMessage = false;
	protected $_params = false;
	protected $_routes = false;
	//
	// Public methods.
	/**
	 * This method cleans a url and returns it as a friendly url capable of
	 * going through routes analysis.
	 *
	 * @param string $path Url to clean.
	 * @return string Cleaned url-
	 */
	public function enroute($path) {
		//
		// Default values.
		$out = $path;
		//
		// Global dependencies.
		global $Defaults;
		//
		// This method makes sence only when routes are active.
		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			//
			// Loading routes configurations.
			$this->parseConfigs();
			//
			// Variable to store the path pieces.
			$newPath = array();
			//
			// Loading required information of the URL
			$url = array(
				GC_AFIELD_QUERY => explode('&', parse_url($path, PHP_URL_QUERY)),
				GC_AFIELD_HOST => parse_url($path, PHP_URL_HOST),
				GC_AFIELD_PATH => parse_url($path, PHP_URL_PATH)
			);
			//
			// Exploding each query parameter.
			foreach($url[GC_AFIELD_QUERY] as $key => $value) {
				unset($url[GC_AFIELD_QUERY][$key]);

				$aux = explode('=', $value);
				if(isset($aux[1])) {
					$url[GC_AFIELD_QUERY][$aux[0]] = $aux[1];
				} else {
					$url[GC_AFIELD_QUERY][$aux[0]] = "true";
				}
			}
			//
			// Tring to match a suitable route.
			$matchingRoutes = array();
			//
			// This works if there's not host set and if there's an
			// action name given in the parameters.
			if(!isset($url[GC_AFIELD_HOST]) && isset($url[GC_AFIELD_QUERY][GC_REQUEST_ACTION])) {
				//
				// Checking each route.
				foreach($this->routes() as $route) {
					//
					// If the action matches is a route to
					// consider.
					if($route->action == $url[GC_AFIELD_QUERY][GC_REQUEST_ACTION]) {
						$matchingRoutes[] = $route;
					}
				}
			}
			//
			// Checking matching routes for the right one.
			$matchingRoute = false;
			if($matchingRoutes) {
				//
				// Action is no longer needed at this point.
				unset($url[GC_AFIELD_QUERY][GC_REQUEST_ACTION]);
				//
				// Checking each matching route.
				foreach($matchingRoutes as $matchingRoute) {
					$wrong = false;
					$newPath = array();
					//
					// Checking routes pattern.
					foreach($matchingRoute->pattern as $piece) {
						//
						// Checking what type of
						// parameters is the current
						// piece.
						if($piece->type == self::PatternTypeLiteral) {
							$newPath[] = $piece->name;
						} elseif($piece->type == self::PatternTypeParameter) {
							//
							// Is there a parameter
							// with the same name of
							// this piece.
							if(isset($url[GC_AFIELD_QUERY][$piece->name])) {
								$newPath[] = $url[GC_AFIELD_QUERY][$piece->name];
								//
								// Checking found piece type.
								switch($piece->valueType) {
									case self::ValueTypeInteger:
										$wrong = !is_numeric($url[GC_AFIELD_QUERY][$piece->name]);
										break;
									case self::ValueTypeString:
										$wrong = !is_string($url[GC_AFIELD_QUERY][$piece->name]);
										break;
									case self::ValueTypeEnumerative:
										$wrong = !in_array($url[GC_AFIELD_QUERY][$piece->name], $piece->values);
										break;
								}
								//
								// The parameter is no longer needed.
								unset($url[GC_AFIELD_QUERY][$piece->name]);
							} else {
								$wrong = true;
							}
						}
						//
						// If something went wrong, this
						// route doesn't really match.
						if($wrong) {
							break;
						}
					}
					//
					// If there were no problems, it is the
					// right route.
					if(!$wrong) {
						break;
					}
				}
			}
			//
			// Did we find a route?
			if($matchingRoute) {
				//
				// Build the basic part of the new URL.
				$out = \TooBasic\Sanitizer::UriPath("{$url[GC_AFIELD_PATH]}/".implode('/', $newPath));
				//
				// If there are some unkwon parameters, they are
				// given as query parameters.
				if($url[GC_AFIELD_QUERY]) {
					$aux = array();
					foreach($url[GC_AFIELD_QUERY] as $key => $value) {
						$aux[] = "{$key}={$value}";
					}
					$out.= '?'.implode('&', $aux);
				}
			}
		}
		//
		// Returning a processed and cleaned url.
		return $out;
	}
	public function lastErrorMessage() {
		return $this->_lastErrorMessage;
	}
	public function load() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// This method works only when routes are active and there is a
		// route in the url query.
		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES] && isset($this->_params->route)) {
			//
			// Loading route configuration files.
			$this->parseConfigs();

			$path = explode('/', $this->_params->route);

			$matches = false;
			$matchingRoute = false;
			$extraRoute = "";
			$settings = false;
			foreach($this->routes() as $route) {
				$matches = true;
				$settings = array();

				$len = count($route->pattern);
				for($i = 0; $i < $len && $matches; $i++) {
					if(!isset($path[$i]) || !strlen($path[$i])) {
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
					$this->_params->addValues(Params::TypeGET, array($key => $value));
				}
				foreach($settings as $key => $value) {
					$this->_params->addValues(Params::TypeGET, array($key => $value));
				}
				if($extraRoute) {
					$this->_params->addValues(Params::TypeGET, array('_route' => $extraRoute));
				}

				$this->_params->addValues(Params::TypeGET, array(GC_REQUEST_ACTION => $matchingRoute->action));
				$this->_params->addValues(Params::TypeSERVER, array('TOOBASIC_ROUTE' => $matchingRoute->route));
			} else {
				$this->_params->addValues(Params::TypeGET, array(GC_REQUEST_ACTION => HTTPERROR_NOT_FOUND));
				$this->_lastErrorMessage = "Unable to find a matching route for '".Sanitizer::UriPath(ROOTURI."/{$this->_params->route}")."'.";
			}
		}
	}
	public function routes() {
		//
		// Forcing routes to be loaded.
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
		$out = '';

		foreach($this->routes() as $route) {
			$out.= "- '{$route->route}':\n";
			$out.= "\tAction: '{$route->action}'\n";

			$out.= "\tPieces:\n";
			$i = 0;
			foreach($route->pattern as $pat) {
				$i++;
				$out.= "\t\t[{$i}] '{$pat->name}'";
				switch($pat->type) {
					case self::PatternTypeParameter:
						$out.= "[parameter]";
						switch($pat->valueType) {
							case self::ValueTypeInteger:
								$out.= ' (must be numeric)';
								break;
							case self::ValueTypeString:
								$out.= ' (must be a string)';
								break;
							case self::ValueTypeEnumerative:
								$out.= "\n\t\t\tMust be one of these:";
								foreach($pat->values as $val) {
									$out.= "\n\t\t\t\t- '{$val}'";
								}
								break;
						}
						break;
					case self::PatternTypeLiteral:
					default:
						$out.= "[literal] (must be an exact match)";
						break;
				}
				$out.= "\n";
			}

			if($route->params) {
				$out.= "\tForced Url Parameters:\n";
				foreach($route->params as $key => $value) {
					$out.= "\t\t'{$key}': '{$value}'\n";
				}
			}

			$out.= "\n";
		}

		\TooBasic\debugThing($out);
		die;
	}
	protected function init() {
		$this->_params = Params::Instance();
	}
	protected function parseConfig($path) {
		$json = json_decode(file_get_contents($path));
		if(json_last_error() != JSON_ERROR_NONE) {
			throw new Exception("Unable to parse file '{$path}'. [".json_last_error().'] '.json_last_error_msg());
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

				if(isset($this->_params->debugroutes)) {
					$this->debugRoutes();
				}
			} else {
				if(isset($this->_params->debugroutes)) {
					debugit('Routes are disabled', true);
				}
			}
		}
	}
}
