<?php

/**
 * @file ExporterScaffold.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use TooBasic\Shell\Scaffold;

/**
 * @class ExporterScaffold
 */
class ExporterScaffold extends Scaffold {
	//
	// Constants.
	const OptionParam = 'Param';
	//
	// Protected methods.
	protected function genRoutes() {
		if($this->_routes === false) {
			parent::genRoutes();
			//
			// Controller's route.
			$opt = $this->_options->option(self::OptionParam);
			//
			// Expanding parameters.
			$params = [];
			$options = $opt->activated() ? $opt->value() : [];
			foreach($options as $param) {
				$pieces = explode(':', $param);
				$params[] = [
					GC_AFIELD_NAME => $pieces[0],
					GC_AFIELD_DEFAULT => isset($pieces[1]) ? $pieces[1] : false
				];
			}
			$paramsCount = count($params);
			//
			// Generating mutiple routes with decreasing sizes.
			for($currentCount = $paramsCount; $currentCount >= 0; $currentCount--) {
				//
				// If the next parameter has no default value this
				// and next routes with shorter sizes won't be
				// valid.
				if(isset($params[$currentCount]) && $params[$currentCount][GC_AFIELD_DEFAULT] === false) {
					break;
				}
				//
				// Building a route @{
				$route = new \stdClass();
				$route->route = "{$this->_genRoutePrefix}{$this->_names[GC_AFIELD_NAME]}";
				for($i = 0; $i < $currentCount; $i++) {
					$route->route .= "/:{$params[$i][GC_AFIELD_NAME]}:";
				}
				$route->{$this->_genRouteType} = $this->_names[GC_AFIELD_NAME];
				//
				//Building route parameters.
				$route->params = new \stdClass();
				$hasDefaults = false;
				for($i = $paramsCount - 1; $i >= $currentCount; $i--) {
					$hasDefaults = true;
					$route->params->{$params[$i][GC_AFIELD_NAME]} = $params[$i][GC_AFIELD_DEFAULT];
				}
				if(!$hasDefaults) {
					unset($route->params);
				}
				// @}
				//
				// Adding the new route.
				$this->_routes[] = $route;
			}
		}
	}
}
