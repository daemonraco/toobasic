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
			$matches = false;
			if(isset($this->_params[2])) {
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
				} elseif($this->_params[1] == 'add-test-ctrl') {
					$whatToDo = 'addTestCtrl';
				}
			} elseif($this->_params[1] == 'help') {
				$whatToDo = 'showHelp';
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
	protected function addTestCtrl() {
		file_put_contents(TOOBASIC_LAST_GENERATION, '');

		if(is_dir($this->_params[2])) {
			$ctrlPath = "/site/controllers/test.php";
			$ctrlFullPath = "{$this->_params[2]}{$ctrlPath}";
			$ctrlFullDir = dirname($ctrlFullPath);
			if(!is_dir($ctrlFullDir)) {
				echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating site directory '\e[1;36m{$ctrlFullDir}\e[0m': ");
				mkdir($ctrlFullDir, 0777, true);
				file_put_contents(TOOBASIC_LAST_GENERATION, "D:{$ctrlFullDir}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			}


			echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating class file '\e[1;36m{$ctrlFullPath}\e[0m': ");
			if(!is_file($ctrlFullPath)) {
				file_put_contents($ctrlFullPath, $this->render('test_ctrl.tpl'));
				file_put_contents(TOOBASIC_LAST_GENERATION, "F:{$ctrlFullPath}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			} else {
				echo "\e[1;31mFailed\e[0m (already exists)\n";
			}

			$viewPath = "/site/templates/action/test.html";
			$viewFullPath = "{$this->_params[2]}{$viewPath}";
			$viewFullDir = dirname($viewFullPath);
			if(!is_dir($viewFullDir)) {
				echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating site directory '\e[1;36m{$viewFullDir}\e[0m': ");
				mkdir($viewFullDir, 0777, true);
				file_put_contents(TOOBASIC_LAST_GENERATION, "D:{$viewFullDir}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			}
			echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating class file '\e[1;36m{$viewFullPath}\e[0m': ");
			if(!is_file($viewFullPath)) {
				file_put_contents($viewFullPath, $this->render('test_view.tpl'));
				file_put_contents(TOOBASIC_LAST_GENERATION, "F:{$viewFullPath}\n", FILE_APPEND);
				echo "\e[1;32mDone\e[0m\n";
			} else {
				echo "\e[1;31mFailed\e[0m (already exists)\n";
			}

			$manifestPath = "{$this->_params[2]}/manifest.json";
			echo str_replace(TOOBASIC_ROOTDIR, '...', "Updating manifest file '\e[1;36m{$manifestPath}\e[0m': ");
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
		$className = false;
		$parentClassName = false;
		$assetsDirectory = false;
		$classPath = false;
		$manifestPath = false;

		file_put_contents(TOOBASIC_LAST_GENERATION, '');

		if($useIndex) {
			$knownCases = array_filter(glob(TOOBASIC_TESTS_DIR."/{$this->_suite}/*"), function($path) {
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

		$assetsDirectory = TOOBASIC_TESTS_ASSETS_DIR."/cases/case_".str_replace('-', '', $this->_suite)."_{$idx}{$className}";
		$classPath = TOOBASIC_TESTS_DIR."/{$this->_suite}/${idx}${className}.php";
		$manifestPath = "{$assetsDirectory}/manifest.json";
		$configPath = "{$assetsDirectory}/site/config.php";

		$this->_assignment['className'] = $className;
		$this->_assignment['parentClassName'] = $parentClassName;
		$this->_assignment['issueId'] = $this->_issue;
		$this->_assignment['issueTitle'] = $this->_issueTitle;
		#
		# Assets directory.
		echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating directory '\e[1;36m{$assetsDirectory}\e[0m': ");
		if(!is_dir($assetsDirectory)) {
			mkdir($assetsDirectory, 0777, true);
			file_put_contents(TOOBASIC_LAST_GENERATION, "D:{$assetsDirectory}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			echo "\e[1;31mFailed\e[0m (already exists)\n";
		}
		#
		# Manifest.
		echo str_replace(TOOBASIC_ROOTDIR, '...', "Updating manifest file '\e[1;36m{$manifestPath}\e[0m': ");
		$this->updateManifest($manifestPath);
		echo "\e[1;32mDone\e[0m\n";
		#
		# Basic config file.
		$configPathDir = dirname($configPath);
		if(!is_dir($configPathDir)) {
			echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating site directory '\e[1;36m{$configPathDir}\e[0m': ");
			mkdir($configPathDir, 0777, true);
			file_put_contents(TOOBASIC_LAST_GENERATION, "D:{$configPathDir}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		}
		echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating config file '\e[1;36m{$configPath}\e[0m': ");
		if(!is_file($configPath)) {
			file_put_contents($configPath, $this->render('config.tpl'));
			file_put_contents(TOOBASIC_LAST_GENERATION, "F:{$configPath}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			echo "\e[1;31mFailed\e[0m (already exists)\n";
		}
		#
		# Case class.
		echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating class file '\e[1;36m{$classPath}\e[0m': ");
		if(!is_file($classPath)) {
			file_put_contents($classPath, $this->render('class.tpl'));
			file_put_contents(TOOBASIC_LAST_GENERATION, "F:{$classPath}\n", FILE_APPEND);
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
				TOOBASIC_SMARTY_DIR,
				TOOBASIC_SMARTY_DIR.'/compile',
				TOOBASIC_SMARTY_DIR.'/cache',
				TOOBASIC_SMARTY_DIR.'/configs'
			];
			foreach($smartyDirs as $dir) {
				if(!is_dir($dir)) {
					mkdir($dir, 0777, true);
				}
			}

			$this->_smarty->setTemplateDir(TOOBASIC_NEWCASE_TEMPLATES);
			$this->_smarty->setCompileDir(TOOBASIC_SMARTY_DIR.'/compile');
			$this->_smarty->setConfigDir(TOOBASIC_SMARTY_DIR.'/configs');
			$this->_smarty->setCacheDir(TOOBASIC_SMARTY_DIR.'/cache');

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
			$json = json_decode(file_get_contents(TOOBASIC_NEWCASE_TEMPLATES.'/manifest.json'));
		}

		foreach($updateCommands as $command) {
			eval(str_replace('%%', '$json', $command));
		}

		file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT));
		file_put_contents(TOOBASIC_LAST_GENERATION, "F:{$path}\n", FILE_APPEND);
	}
}

try {
	$runner = new NewCase($argv);
	$runner->run();
} catch(Exception $e) {
	echo "\e[1;31m{$e->getMessage()}\e[0m\n";
}
