<?php

/**
 * @issue 115
 * @brief Scaffolds with default values in routes
 * @url https://github.com/daemonraco/toobasic/issues/115
 */
class I115_ScaffoldsWithDefaultValuesInRoutesTest extends TooBasic_TestCase {
	//
	// Internal properties.
	protected $_routesPath = '';
	//
	// Set up
	public function setUp() {
		parent::setUp();

		$this->_routesPath = TESTS_ROOTDIR.'/site/configs/routes.json';
	}
	//
	// Test cases @{
	public function testCreateController() {
		$cmd = 'php shell.php sys controller new hello_world';
		$cmd.= ' --param p1';
		$cmd.= ' --param p2:v2';
		$cmd.= ' --param p3';
		$cmd.= ' --param p4:v4';
		$cmd.= ' --param p5:v5';

		$this->runCommand($cmd);

		$this->assertTrue(is_file(TESTS_ROOTDIR.'/site/controllers/hello_world.php'), 'Controller file was not created.');
		$this->assertTrue(is_file(TESTS_ROOTDIR.'/site/templates/action/hello_world.html'), 'View file was not created.');

		$this->checkRoutes('action', '');

		$this->runCommand(str_replace(' new ', ' remove ', $cmd));
		$this->checkRemovedRoutes('action', '');
	}
	public function testCreateService() {
		$cmd = 'php shell.php sys service new hello_world';
		$cmd.= ' --param p1';
		$cmd.= ' --param p2:v2';
		$cmd.= ' --param p3';
		$cmd.= ' --param p4:v4';
		$cmd.= ' --param p5:v5';

		$this->runCommand($cmd);

		$this->assertTrue(is_file(TESTS_ROOTDIR.'/site/services/hello_world.php'), 'Service file was not created.');

		$this->checkRoutes('service', 'srv/');

		$this->runCommand(str_replace(' new ', ' remove ', $cmd));
		$this->checkRemovedRoutes('service', 'srv/');
	}
	// @}
	//
	// Internal methods @{
	protected function checkRemovedRoutes($type, $prefix) {
		$routes = json_decode(file_get_contents($this->_routesPath));
		$this->assertTrue(boolval($routes), 'Routes configuration file is not a valid JSON.');

		$this->assertEquals(0, count($routes->routes), 'The amount of routes is not as expected.');
	}
	protected function checkRoutes($type, $prefix) {
		$this->assertTrue(is_file($this->_routesPath), 'Routes configuration file was not created.');

		$routes = json_decode(file_get_contents($this->_routesPath));
		$this->assertTrue(boolval($routes), 'Routes configuration file is not a valid JSON.');

		$route = $routes->routes[0];
		$this->assertEquals('hello_world', $route->{$type}, "Field '{$type}' is not as expected.");
		$this->assertEquals("{$prefix}hello_world/:p1:/:p2:/:p3:/:p4:/:p5:", $route->route, "Field 'route' is not as expected.");
		$this->assertFalse(isset($route->params), "Field 'params' should not be present.");

		$route = $routes->routes[1];
		$this->assertEquals('hello_world', $route->{$type}, "Field '{$type}' is not as expected.");
		$this->assertEquals("{$prefix}hello_world/:p1:/:p2:/:p3:/:p4:", $route->route, "Field 'route' is not as expected.");
		$this->assertTrue(isset($route->params), "Field 'params' should be present.");
		$this->assertTrue(isset($route->params->p5), "Field 'params' doesn't have an entry for 'p5'.");
		$this->assertEquals('v5', $route->params->p5, "Entry 'p5' on field 'params' has an unexpected value.");

		$route = $routes->routes[2];
		$this->assertEquals('hello_world', $route->{$type}, "Field '{$type}' is not as expected.");
		$this->assertEquals("{$prefix}hello_world/:p1:/:p2:/:p3:", $route->route, "Field 'route' is not as expected.");
		$this->assertTrue(isset($route->params), "Field 'params' should be present.");
		$this->assertTrue(isset($route->params->p4), "Field 'params' doesn't have an entry for 'p4'.");
		$this->assertEquals('v4', $route->params->p4, "Entry 'p4' on field 'params' has an unexpected value.");
		$this->assertTrue(isset($route->params->p5), "Field 'params' doesn't have an entry for 'p5'.");
		$this->assertEquals('v5', $route->params->p5, "Entry 'p5' on field 'params' has an unexpected value.");

		$this->assertEquals(3, count($routes->routes), 'The amount of routes is not as expected.');
	}
	// @}
}
