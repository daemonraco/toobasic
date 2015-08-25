<?php

/**
 * @file RoutesManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use \TooBasic\Params;
use \TooBasic\Paths;
use \TooBasic\Sanitizer;

/**
 * @class RoutesManager
 * This class holds the logic to manage routes and perform different operations
 * with them.
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
	/**
	 * @var string Error message.
	 */
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
	 * @return string Cleaned url.
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
				$out = Sanitizer::UriPath("{$url[GC_AFIELD_PATH]}/".implode('/', $newPath));
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
	/**
	 * This method provides access to the last error message.
	 *
	 * @return string Returns an error message.
	 */
	public function lastErrorMessage() {
		return $this->_lastErrorMessage;
	}
	/**
	 * This method analyse current url route. This means it take the parameter
	 * 'route' in the URL and checks it against routes configuration.
	 * 
	 * The parameter 'route' is an automatic URL parameter handled by the file
	 * '.htaccess' at the root directory of TooBasic.
	 */
	public function load() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// This method works only when routes are active and there is a
		// route in the URL query.
		if($Defaults[GC_DEFAULTS_ALLOW_ROUTES] && isset($this->_params->{GC_REQUEST_ROUTE})) {
			//
			// Loading route configuration files.
			$this->parseConfigs();
			//
			// Expanding given route for analysis.
			$path = explode('/', $this->_params->{GC_REQUEST_ROUTE});

			$matches = false;
			$matchingRoute = false;
			$extraRoute = '';
			$settings = false;
			//
			// Checking each configured route a matching one.
			foreach($this->routes() as $route) {
				$matches = true;
				$settings = array();
				//
				// Checking each piece.
				$len = count($route->pattern);
				for($i = 0; $i < $len && $matches; $i++) {
					if(!isset($path[$i]) || !strlen($path[$i])) {
						//
						// At this point there's a
						// required piece that was not
						// given in the request.
						// Not matching
						$matches = false;
					} elseif($route->pattern[$i]->type == self::PatternTypeLiteral) {
						//
						// At this point, matching depends
						// on a exact match between the
						// given piece and the one
						// requiered by the pattern.
						$matches = $route->pattern[$i]->name == $path[$i];
					} elseif($route->pattern[$i]->type == self::PatternTypeParameter) {
						//
						// At this point, the piece of URL
						// may be considered as a value
						// for some parameter.
						$settings[$route->pattern[$i]->name] = $path[$i];
						//
						// Checking if there's a format
						// conditions and enforcing it.
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
						//
						// At this point, it simply
						// doesn't match.
						$matches = false;
					}
				}
				//
				// Checking if current route role matches or not.
				if($matches) {
					//
					// Saving the matching route.
					$matchingRoute = $route;
					//
					// Saving piece of route.
					if(count($path) > $len) {
						$extraRoute = implode('/', array_slice($path, $len));
					}
					//
					// No more analysis is required.
					break;
				}
			}
			//
			// Checking if there's a matching route.
			if($matchingRoute) {
				//
				// Setting parameters required by the route
				// configuration.
				foreach($matchingRoute->params as $key => $value) {
					$this->_params->addValues(Params::TypeGET, array($key => $value));
				}
				//
				// Setting parameters found in the URL associated
				// with the route.
				foreach($settings as $key => $value) {
					$this->_params->addValues(Params::TypeGET, array($key => $value));
				}
				//
				// If there's an extra piece that was not
				// consumed, it is readed as '_route'.
				if($extraRoute) {
					$this->_params->addValues(Params::TypeGET, array(GC_REQUEST_EXTRA_ROUTE => $extraRoute));
				}
				//
				// Setting the action/controller to exectute.
				$this->_params->addValues(Params::TypeGET, array(GC_REQUEST_ACTION => $matchingRoute->action));
				//
				// Adding route specs as a '$_SERVER' value.
				$this->_params->addValues(Params::TypeSERVER, array(GC_SERVER_TOOBASIC_ROUTE => $matchingRoute->route));
			} else {
				$this->_params->addValues(Params::TypeGET, array(GC_REQUEST_ACTION => HTTPERROR_NOT_FOUND));
				$this->_lastErrorMessage = "Unable to find a matching route for '".Sanitizer::UriPath(ROOTURI."/{$this->_params->route}")."'.";
			}
		}
	}
	/**
	 * This method provides access to the full list of routes.
	 *
	 * @return mixed[] Returns a list of parsed and enriched route objects.
	 */
	public function routes() {
		//
		// Forcing routes to be loaded.
		$this->parseConfigs();
		return $this->_routes;
	}
	//
	// Protected methods.
	/**
	 * THis method thakes a basic route specification object and extends it
	 * adding a field called pattern with more useful information about its
	 * behavior.
	 *
	 * @param \stdClass $route Route to be analysed.
	 */
	protected function buildPattern(&$route) {
		//
		// Default values.
		$pattern = array();
		//
		// Expanding an checking each piece on the route.
		foreach(explode('/', $route->route) as $piece) {
			//
			// Creating an ancillary object to hold the generated
			// pattern.
			$patPiece = new \stdClass();
			//
			// Checking if current piece is a parameter specification.
			if(preg_match('/:(?<pname>[^:]*):(?<vtype>[^:]*)(:(?<vtypedata>.*)|)/', $piece, $matches)) {
				//
				// Setting the right type for this piece.
				$patPiece->type = self::PatternTypeParameter;
				//
				// Saving the name to associate with a parameter.
				$patPiece->name = $matches['pname'];
				//
				// Checking if there's a type control to be
				// applied and generating the proper structure.
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
				//
				// At this point, the piece of route has to be an
				// exact match.
				$patPiece->type = self::PatternTypeLiteral;
				$patPiece->name = $piece;
			}
			//
			// Enqueuing a new piece.
			$pattern[] = $patPiece;
		}
		//
		// Route enrichment.
		$route->pattern = $pattern;
	}
	/**
	 * This method is used to generate debugging information about current
	 * routes settings.
	 */
	protected function debugRoutes() {
		//
		// Default values.
		$out = '';
		//
		// Generating information about each route.
		foreach($this->routes() as $route) {
			$out.= "- '{$route->route}':\n";
			$out.= "\tAction: '{$route->action}'\n";
			//
			// Describing how the route is analysed.
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
			//
			// Listing parameters automatically set by current route.
			if($route->params) {
				$out.= "\tForced Url Parameters:\n";
				foreach($route->params as $key => $value) {
					$out.= "\t\t'{$key}': '{$value}'\n";
				}
			}

			$out.= "\n";
		}
		//
		// Displaying debug information and stopping the execution.
		\TooBasic\debugThing($out);
		die;
	}
	/**
	 * Manager's initilization.
	 */
	protected function init() {
		$this->_params = Params::Instance();
	}
	/**
	 * This method loads a single routes specification file and reads all
	 * routes in it.
	 *
	 * @param string $path Absolute file path to load.
	 * @throws \TooBasic\Exception
	 */
	protected function parseConfig($path) {
		//
		// Loading and parsing.
		$json = json_decode(file_get_contents($path));
		//
		// Checking for parsing errors.
		if(json_last_error() != JSON_ERROR_NONE) {
			throw new Exception("Unable to parse file '{$path}'. [".json_last_error().'] '.json_last_error_msg());
		}
		//
		// Checking if there are routes specified.
		if(isset($json->routes)) {
			//
			// Loading each route.
			foreach($json->routes as $route) {
				//
				// Copying only useful field and enforcing those
				// that are required.
				$auxRoute = \TooBasic\objectCopyAndEnforce(array('route', 'action', 'params'), $route, new \stdClass(), array('params' => array()));
				//
				// Expanding route's pattern.
				$this->buildPattern($auxRoute);
				$this->_routes[] = $auxRoute;
			}
		}
	}
	/**
	 * This method loads all routes specification files and trigger their
	 * analysis.
	 */
	protected function parseConfigs() {
		//
		// Avoiding multiple loads.
		if($this->_routes === false) {
			//
			// Default values.
			$this->_routes = array();
			//
			// Global dependencies.
			global $Defaults;
			//
			// Checking if routes are enabled.
			if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
				//
				// Searching and loading each file.
				foreach(Paths::Instance()->routesPaths() as $path) {
					$this->parseConfig($path);
				}
				//
				// If requested, showing debug information
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
