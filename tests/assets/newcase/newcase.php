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
		$whatToDo = 'showHelp';

		if(isset($this->_params[2])) {
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

		$this->{$whatToDo}();
	}
	//
	// Protected methods.
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
		$className = trim(str_replace(' ', '', ucwords(strtolower(preg_replace('/([A-Z])/', ' $1', preg_replace('/([ \._-])/', ' ', $this->_params[2]))))));
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
		echo str_replace(TOOBASIC_ROOTDIR, '...', "Creating manifest file '\e[1;36m{$manifestPath}\e[0m': ");
		if(!is_file($manifestPath)) {
			file_put_contents($manifestPath, $this->render('manifest.tpl'));
			file_put_contents(TOOBASIC_LAST_GENERATION, "F:{$manifestPath}\n", FILE_APPEND);
			echo "\e[1;32mDone\e[0m\n";
		} else {
			echo "\e[1;31mFailed\e[0m (already exists)\n";
		}
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
}

$runner = new NewCase($argv);
$runner->run();
