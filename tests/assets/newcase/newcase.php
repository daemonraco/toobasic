<?php

require_once __DIR__.'/autoload.php';

class NewCase {
	//
	// Protected properties.
	/**
	 * @var string[]
	 */
	protected $_assignment = [];
	protected $_issue = false;
	protected $_issueTitle = false;
	/**
	 * @var string[]
	 */
	protected $_params = [];
	/**
	 * @var \Smarty
	 */
	protected $_smarty = false;
	/**
	 * @var string
	 */
	protected $_suite = false;
	//
	// Magic methods.
	public function __construct($argv) {
		$this->_params = $argv;
	}
	//
	// Public methods.
	public function run() {
		$whatToDo = false;

		if(isset($this->_params[1])) {
			if($this->_params[1] == 'help') {
				$whatToDo = 'showHelp';
			} elseif($this->_params[1] == 'add-asset') {
				$whatToDo = 'addAssetFile';
			} elseif($this->_params[1] == 'add-test-ctrl') {
				$whatToDo = 'addTestCtrl';
			} elseif(isset($this->_params[2])) {
				$matches = false;
				if(preg_match('/^(?P<suite>cases|cases-on-selenium)$/', $this->_params[1], $matches)) {
					$this->_suite = $matches['suite'];
					$whatToDo = 'createBasic';
				} elseif(preg_match('/^(?P<suite>cases-by-issue)$/', $this->_params[1], $matches)) {
					$this->_suite = $matches['suite'];

					ob_start();
					passthru("wget -nv 'https://api.github.com/repos/daemonraco/toobasic/issues/{$this->_params[2]}' -O - 2>/dev/null");
					$output = ob_get_contents();
					ob_end_clean();
					$json = json_decode($output);
					if($json && isset($json->title)) {
						$this->_issue = $this->_params[2];
						$this->_issueTitle = $json->title;
						$this->_params[2] = sprintf('iss %03d %s', $this->_issue, $this->_issueTitle);

						$whatToDo = 'createWithoutIndex';
					} else {
						throw new Exception("Unable to obtain information for issue '{$this->_params[2]}'.");
					}
				}
			}
		}

		if($whatToDo) {
			$this->{$whatToDo}();
		} else {
			$this->showHelp();
			echo "\n";
			throw new Exception('Nothing to do.');
		}
	}
	//
	// Protected methods.
	protected function addAssetFile() {
		$suites = [
			'cases',
			'cases-by-class',
			'cases-by-issue',
			'cases-on-selenium'
		];
		$types = [
			'controller',
			'empty',
			'json',
			'php',
			'service',
			'view'
		];
		if(!isset($this->_params[2]) || !in_array($this->_params[2], $suites)) {
			echo "\e[1;31mYou must specify a valid suite.\e[0m\n";
			echo "Available options are:\n";
			foreach($suites as $suite) {
				echo"\t- '{$suite}'\n";
			}
		} elseif(!isset($this->_params[3]) || !preg_match('~([i]?)([0-9]+)~i', $this->_params[3])) {
			echo "bananas\n";
		} elseif(!isset($this->_params[4]) || !preg_match('~^\/(.+)$~i', $this->_params[4])) {
			echo "\e[1;31mYou must specify an asset path (for example '/site/my_config.php').\e[0m\n";
		} else {
			file_put_contents(TESTS_LAST_GENERATION, '');

			$suite = $this->_params[2];
			$index = strtoupper($this->_params[3]);
			$assetPath = $this->_params[4];

			$paths = glob(TESTS_TESTS_DIR."/{$suite}/{$index}*");
			$pathsCount = count($paths);
			if($pathsCount == 1) {
				$path = $paths[0];
				$pathGuessing = TooBasic_AssetsManager::GuessAssetsPaths($path);
				$assetFullPath = "{$pathGuessing[TEST_AFIELD_ASSETS_PATH]}{$assetPath}";

				$assetPathInfo = pathinfo($assetFullPath);
				//
				// Guessing type.
				$type = false;
				if(isset($this->_params[5])) {
					$type = in_array($this->_params[5], $types) ? $this->_params[5] : 'empty';
				} else {
					switch(strtolower($assetPathInfo['extension'])) {
						case 'json':
							$type = 'json';
							break;
						case 'php':
							$type = 'php';
							break;
						default:
							$type = 'empty';
					}
				}
				//
				// Loading contents.
				$assignments = [];
				switch($type) {
					case 'controller':
						$info = pathinfo(preg_replace('~\.pre$~', '', $assetPath));
						$className = ucwords(str_replace(['_', '-'], ' ', $info['filename'])).'Controller';
						$className = str_replace(' ', '', preg_replace('~((Controller)+)$~', 'Controller', $className));
						$assignments['controllerClass'] = $className;
						break;
					case 'service':
						$info = pathinfo(preg_replace('~\.pre$~', '', $assetPath));
						$className = ucwords(str_replace(['_', '-'], ' ', $info['filename'])).'Service';
						$className = str_replace(' ', '', preg_replace('~((Service)+)$~', 'Service', $className));
						$assignments['controllerClass'] = $className;
						break;
				}
				$contents = $this->render("atype_{$type}.tpl", $assignments);

				$possibleDirectory = '';
				$possibleDirectories = [];
				foreach(array_filter(explode('/', $assetPath)) as $pieces) {
					$possibleDirectory = "{$possibleDirectory}/{$pieces}";
					if($possibleDirectory != $assetPath) {
						$possibleDirectories[] = $possibleDirectory;
					}
				}
				//
				// Creating directory.
				if(!is_dir($assetPathInfo['dirname'])) {
					echo str_replace(TESTS_ROOTDIR.'/', '', "Creating asset's directory '\e[1;36m{$assetPathInfo['dirname']}\e[0m': ");
					mkdir($assetPathInfo['dirname'], 0777, true);
					file_put_contents(TESTS_LAST_GENERATION, "D:{$assetPathInfo['dirname']}\n", FILE_APPEND);
					echo "\e[1;32mDone\e[0m\n";
				}
				//
				// Creating asset.
				if(!is_file($assetPath)) {
					echo str_replace(TESTS_ROOTDIR.'/', '', "Creating asset '\e[1;36m{$assetFullPath}\e[0m': ");
					file_put_contents($assetFullPath, $contents);
					file_put_contents(TESTS_LAST_GENERATION, "F:{$assetFullPath}\n", FILE_APPEND);
					echo "\e[1;32mDone\e[0m\n";
				}
				//
				// Updating manifest.
				if(!is_file($assetPath)) {
					echo str_replace(TESTS_ROOTDIR.'/', '', "Updating manifest '\e[1;36m{$pathGuessing[TEST_AFIELD_MANIFEST_PATH]}\e[0m': ");
					$json = json_decode(file_get_contents($pathGuessing[TEST_AFIELD_MANIFEST_PATH]));
					//
					// Inserting asset path.
					$aux = isset($json->assets) ? $json->assets : [];
					$aux[] = $assetPath;
					$aux = array_unique($aux);
					sort($aux);
					$json->assets = $aux;
					//
					// Inserting possible sub-directories
					// paths.

					$aux = isset($json->assetDirectories) ? $json->assetDirectories : [];
					$aux = array_merge($aux, $possibleDirectories);
					$aux = array_unique($aux);
					sort($aux);
					$json->assetDirectories = $aux;

					file_put_contents($pathGuessing[TEST_AFIELD_MANIFEST_PATH], json_encode($json, JSON_PRETTY_PRINT));
					file_put_contents(TESTS_LAST_GENERATION, "F:{$pathGuessing[TEST_AFIELD_MANIFEST_PATH]}\n", FILE_APPEND);
					echo "\e[1;32mDone\e[0m\n";
				}
			} elseif($pathsCount > 1) {
				echo "\e[1;31mTo many cases found for index '{$index}' inside suite '{$suite}'\e[0m\n";
			} else {
				echo "\e[1;31mNo case found for index '{$index}' inside suite '{$suite}'\e[0m\n";
			}
		}
	}
	protected function addTestCtrl() {
		file_put_contents(TESTS_LAST_GENERATION, '');

		if(is_dir($this->_params[2])) {
			$ctrlPath = "/site/controllers/test.php";
			$ctrlFullPath = "{$this->_params[2]}{$ctrlPath}";
			$ctrlFullDir = dirname($ctrlFullPath);
			if(!is_dir($ctrlFullDir)) {
				echo str_replace(TESTS_ROOTDIR.'/', '', "Creating site directory '\e[1;36m{$ctrlFullDir}\e[0m': ");
				mkdir($ctrlFullDir, 0777, true);
				file_put_contents(TESTS_LAST_GENERATION, "D:{$ctrlFullDir}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			}


			echo str_replace(TESTS_ROOTDIR.'/', '', "Creating class file '\e[1;36m{$ctrlFullPath}\e[0m': ");
			if(!is_file($ctrlFullPath)) {
				file_put_contents($ctrlFullPath, $this->render('test_ctrl.tpl'));
				file_put_contents(TESTS_LAST_GENERATION, "F:{$ctrlFullPath}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			} else {
				echo "\e[1;31mFailed\e[0m (already exists)\n";
			}

			$viewPath = "/site/templates/action/test.html";
			$viewFullPath = "{$this->_params[2]}{$viewPath}";
			$viewFullDir = dirname($viewFullPath);
			if(!is_dir($viewFullDir)) {
				echo str_replace(TESTS_ROOTDIR.'/', '', "Creating site directory '\e[1;36m{$viewFullDir}\e[0m': ");
				mkdir($viewFullDir, 0777, true);
				file_put_contents(TESTS_LAST_GENERATION, "D:{$viewFullDir}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			}
			echo str_replace(TESTS_ROOTDIR.'/', '', "Creating class file '\e[1;36m{$viewFullPath}\e[0m': ");
			if(!is_file($viewFullPath)) {
				file_put_contents($viewFullPath, $this->render('test_view.tpl'));
				file_put_contents(TESTS_LAST_GENERATION, "F:{$viewFullPath}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			} else {
				echo "\e[1;31mFailed\e[0m (already exists)\n";
			}

			$manifestPath = "{$this->_params[2]}/manifest.json";
			echo str_replace(TESTS_ROOTDIR.'/', '', "Updating manifest file '\e[1;36m{$manifestPath}\e[0m': ");
			$this->updateManifest($manifestPath, [
				"%%->assets[] = '{$ctrlPath}';",
				"%%->assets[] = '{$viewPath}';",
				"sort(%%->assets);"
			]);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			throw new Exception("'{$this->_params[2]}' is not a directory.");
		}
	}
	protected function assign($name, $value) {
		$this->_assignment[$name] = $value;
	}
	protected function createBasic($useIndex = true) {
		$idx = false;
		$parentClassName = false;
		$classPath = false;

		file_put_contents(TESTS_LAST_GENERATION, '');

		if($useIndex) {
			$knownCases = array_filter(glob(TESTS_TESTS_DIR."/{$this->_suite}/*"), function($path) {
				return !preg_match('~^/(.*)/XXX(.*)\.php$~', $path);
			});
			$max = 0;
			foreach($knownCases as $pos => $path) {
				$match = false;
				if(preg_match('~^/(.*)/(?P<idx>[0-9]{3,3})(.*)\.php$~', $path, $match)) {
					$value = $match['idx'] + 0;
					$max = $value > $max ? $value : $max;
				}
			}
			$idx = sprintf('%03d', $max + 1);
		} else {
			$idx = '';
		}

		$matches = false;
		$className = trim(str_replace(' ', '', ucwords(strtolower(preg_replace('/([A-Z])/', ' $1', preg_replace('/([ \'\\._-])/', ' ', $this->_params[2]))))));
		$className = preg_match('/^Iss([0-9]+)(.*)/', $className, $matches) ? 'I'.$matches[1].'_'.$matches[2] : $className;

		switch($this->_suite) {
			case 'cases':
			case 'cases-by-issue':
				$parentClassName = 'TooBasic_TestCase';
				break;
			case 'cases-on-selenium':
				$parentClassName = 'TooBasic_SeleniumTestCase';
				break;
		}

		$classPath = TESTS_TESTS_DIR."/{$this->_suite}/${idx}${className}.php";
		$pathGuessing = TooBasic_AssetsManager::GuessAssetsPaths($classPath);

		$assetsDirectory = $pathGuessing[TEST_AFIELD_ASSETS_PATH];
		$manifestPath = $pathGuessing[TEST_AFIELD_MANIFEST_PATH];
		$configPath = "{$assetsDirectory}/site/case_config.php";

		$this->_assignment['className'] = $className;
		$this->_assignment['parentClassName'] = $parentClassName;
		$this->_assignment['issueId'] = $this->_issue;
		$this->_assignment['issueTitle'] = $this->_issueTitle;
		#
		# Assets directory.
		echo str_replace(TESTS_ROOTDIR.'/', '', "Creating directory '\e[1;36m{$assetsDirectory}\e[0m': ");
		if(!is_dir($assetsDirectory)) {
			mkdir($assetsDirectory, 0777, true);
			file_put_contents(TESTS_LAST_GENERATION, "D:{$assetsDirectory}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			echo "\e[1;31mFailed\e[0m (already exists)\n";
		}
		#
		# Manifest.
		echo str_replace(TESTS_ROOTDIR.'/', '', "Updating manifest file '\e[1;36m{$manifestPath}\e[0m': ");
		$this->updateManifest($manifestPath);
		echo "\e[1;32mDone\e[0m\n";
		#
		# Basic config file.
		$configPathDir = dirname($configPath);
		if(!is_dir($configPathDir)) {
			echo str_replace(TESTS_ROOTDIR.'/', '', "Creating site directory '\e[1;36m{$configPathDir}\e[0m': ");
			mkdir($configPathDir, 0777, true);
			file_put_contents(TESTS_LAST_GENERATION, "D:{$configPathDir}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		}
		echo str_replace(TESTS_ROOTDIR.'/', '', "Creating config file '\e[1;36m{$configPath}\e[0m': ");
		if(!is_file($configPath)) {
			file_put_contents($configPath, $this->render('config.tpl'));
			file_put_contents(TESTS_LAST_GENERATION, "F:{$configPath}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			echo "\e[1;31mFailed\e[0m (already exists)\n";
		}
		#
		# Case class.
		echo str_replace(TESTS_ROOTDIR.'/', '', "Creating class file '\e[1;36m{$classPath}\e[0m': ");
		if(!is_file($classPath)) {
			file_put_contents($classPath, $this->render('class.tpl'));
			file_put_contents(TESTS_LAST_GENERATION, "F:{$classPath}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			echo "\e[1;31mFailed\e[0m (already exists)\n";
		}
	}
	protected function createWithoutIndex() {
		$this->createBasic(false);
	}
	protected function showHelp() {
		$this->assign('program', $this->_params[0]);
		echo $this->render('help.tpl');
	}
	protected function render($template, $assignments = false) {
		$this->startSmarty();

		if($assignments === false) {
			$assignments = $this->_assignment;
		}

		foreach($assignments as $name => $value) {
			$this->_smarty->assign($name, $value);
		}
		return $this->_smarty->fetch($template);
	}
	protected function startSmarty() {
		if($this->_smarty === false) {
			$this->_smarty = new Smarty();

			$smartyDirs = [
				TESTS_SMARTY_DIR,
				TESTS_SMARTY_DIR.'/compile',
				TESTS_SMARTY_DIR.'/cache',
				TESTS_SMARTY_DIR.'/configs'
			];
			foreach($smartyDirs as $dir) {
				if(!is_dir($dir)) {
					mkdir($dir, 0777, true);
				}
			}

			$this->_smarty->setTemplateDir(TESTS_NEWCASE_TEMPLATES);
			$this->_smarty->setCompileDir(TESTS_SMARTY_DIR.'/compile');
			$this->_smarty->setConfigDir(TESTS_SMARTY_DIR.'/configs');
			$this->_smarty->setCacheDir(TESTS_SMARTY_DIR.'/cache');

			$this->_smarty->left_delimiter = '<%';
			$this->_smarty->right_delimiter = '%>';
			$this->_smarty->force_compile = true;
		}
	}
	protected function updateManifest($path, $updateCommands = false) {
		if(!is_array($updateCommands)) {
			$updateCommands = [];
		}

		$json = false;
		if(is_file($path)) {
			$json = json_decode(file_get_contents($path));
		}
		if(!$json) {
			$json = json_decode(file_get_contents(TESTS_NEWCASE_TEMPLATES.'/manifest.json'));
		}

		foreach($updateCommands as $command) {
			eval(str_replace('%%', '$json', $command));
		}

		file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT));
		file_put_contents(TESTS_LAST_GENERATION, "F:{$path}\n", FILE_APPEND);
	}
}

try {
	$runner = new NewCase($argv);
	$runner->run();
} catch(Exception $e) {
	echo "\e[1;31m{$e->getMessage()}\e[0m\n";
}
