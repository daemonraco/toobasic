<?php

/**
 * @file EmailsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

/**
 * @class EmailsManager
 */
class EmailsManager extends Manager {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\EmailPayload @todo doc
	 */
	protected $_emailPayload = false;
	protected $_lastRender = false;
	//
	// Public methods.
	public function lastRender() {
		return $this->_lastRender;
	}
	public function run($autoDisplay = false) {
		//
		// Checking simulation status.
		$isSimulation = isset($this->params->get->debugemail);

		if($isSimulation) {
			$this->_emailPayload = new \TooBasic\EmailPayload();
			$this->_emailPayload->setName($this->params->get->debugemail);
		} elseif($this->_emailPayload === false) {
			throw new \TooBasic\Exception("No email payload set, use 'EmailsManager::Instance()->setEmailPayload()'");
		} elseif(!$this->_emailPayload->isValid()) {
			throw new \TooBasic\Exception("Email payload is not valid, check email name, recipients and subject");
		}
		//
		// Default values.
		$layoutName = false;
		//
		// Current email execution.
		$emailLastRun = self::ExecuteAction($this->_emailPayload->name(), $this->_emailPayload, $isSimulation, null, $layoutName);
		//
		// Layout execution (if any).
		$layoutLastRun = false;
		//
		// Running layout's controller.
		if($layoutName) {
			$this->_emailPayload->setLayout($layoutName);
			$layoutLastRun = self::ExecuteAction($layoutName, $this->_emailPayload, $isSimulation, $emailLastRun);
		}
		//
		// If there's a layout present, controller's result must
		// be shown inside a layout's result.
		if($layoutLastRun) {
			$emailLastRun[GC_AFIELD_FULL_RENDER] = str_replace('%TOO_BASIC_EMAIL_CONTENT%', $emailLastRun[GC_AFIELD_RENDER], $layoutLastRun[GC_AFIELD_RENDER]);
		} else {
			$emailLastRun[GC_AFIELD_FULL_RENDER] = $emailLastRun[GC_AFIELD_RENDER];
		}
		//
		// Stripping tags that may be a problem for some email clients.
		if($this->_emailPayload->stripTags()) {
			self::StripContentTags($emailLastRun[GC_AFIELD_FULL_RENDER]);
		}
		//
		// Autodisplay works only on simulations.
		if($autoDisplay && $isSimulation) {
			echo $emailLastRun[GC_AFIELD_FULL_RENDER];
		}

		$this->_lastRender = $emailLastRun[GC_AFIELD_FULL_RENDER];

		return $emailLastRun;
	}
	public function send() {
		$ok = true;

		if($this->_emailPayload->isValid()) {
			//
			// Global dependencies.
			global $Defaults;
			//
			// Email headers.
			$headers = 'From: '.strip_tags($Defaults[GC_DEFAULTS_EMAIL_FROM])."\r\n";
			$headers .= 'Reply-To: '.strip_tags($Defaults[GC_DEFAULTS_EMAIL_REPLAYTO])."\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=utf-8\r\n";
			$headers .= 'X-PowerdBy: TooBasic '.TOOBASIC_VERSION."\r\n";
			//
			// Sending mail.
			$ok = mail($this->_emailPayload->emails(), $this->_emailPayload->subject(), $this->_lastRender, $headers);
		} else {
			throw new \TooBasic\Exception('Email payload structure is not valid');
		}

		return $ok;
	}
	public function setEmailPayload($payload) {
		$this->_emailPayload = $payload;
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();
	}
	//
	// Public class methods.
	public static function ExecuteAction($emailName, $emailPayload, $isSimulation, $previousActionRun = null, &$layoutName = false) {
		//
		// Default values.
		$status = true;
		//
		// Loading controller based on current email name.
		$controllerClass = self::FetchController($emailName, $emailPayload);
		if($controllerClass !== false) {
			$layoutName = $controllerClass->layout();
			//
			// If there's a previous run, this must be the layout and
			// the previous run comes from the controller, so
			// assignments from that controller should be available
			// for the layout for different reason like setting the
			// page title.
			if(is_array($previousActionRun)) {
				$controllerClass->massiveAssign($previousActionRun['assignments']);
			}

			$controllerClass->setSimulation($isSimulation);
			$status = $controllerClass->run();
		} else {
			$status = false;
		}

		$lastRun = false;
		if($status) {
			$lastRun = $controllerClass->lastRun();
		} else {
			$whatIsIt = (is_array($previousActionRun) ? 'email layout' : 'email');
			$errorCode = HTTPERROR_NOT_FOUND;
			$errorMessage = "Unable to find {$whatIsIt} '{$emailName}'";
			if($controllerClass instanceof \TooBasic\Email) {
				$lastError = $controllerClass->lastError();
				if($lastError) {
					$errorCode = $lastError[GC_AFIELD_CODE];
					$errorMessage = $lastError[GC_AFIELD_MESSAGE];
				} else {
					$errorCode = HTTPERROR_INTERNAL_SERVER_ERROR;
					$errorMessage = "Something went wrong with email '{$emailName}'";
				}
			}

			throw new \TooBasic\Exception($errorMessage, $errorCode);
		}

		return $lastRun;
	}
	/**
	 * This class method looks for a controller based on an email name.
	 *
	 * @param string $emailName Action name from which guess a controller's
	 * name.
	 * @return \TooBasic\Controller Returns a controllers object or false on
	 * failure.
	 */
	public static function FetchController($emailName, $emailPayload, $recursive = false) {
		//
		// Default values.
		$out = false;
		//
		// Looking for a controller with the given email name as a file
		// name.
		$controllerPath = \TooBasic\Paths::Instance()->emailControllerPath($emailName);
		//
		// Checking the obtained path.
		if($controllerPath) {
			//
			// Loading physical file with the controllers definition.
			require_once $controllerPath;
			//
			// Guessing the right class name.
			$controllerClassName = \TooBasic\Names::EmailControllerClass($emailName);
			//
			// Creating the controllers class.
			if(class_exists($controllerClassName)) {
				$out = new $controllerClassName($emailPayload);
			} else {
				throw new \TooBasic\Exception("Class '{$controllerClassName}' is not defined. File '{$controllerPath}' doesn't seem to load the right object.");
			}
		} elseif(!$recursive) {
			//
			// If there's no controller file, but there's a template,
			// a virtual controller is used.
			//
			// Searching for a template.
			$template = \TooBasic\Paths::Instance()->templatePath($emailName, 'email');
			//
			// Checking if there's a template for the given name.
			if($template) {
				//
				// Loading a virtual controller.
				$out = self::FetchController('too_basic_virtual', $emailPayload, true);
				//
				// Setting view name to the virtual controller.
				if($out) {
					$out->setViewName($emailName);
				}
			}
		}
		//
		// Returning fetched controller.
		return $out;
	}
	public static function StripContentTags(&$content) {
		$content = preg_replace('%<style( |>)(.*)</style>%s', '<!--CSS removed by TooBasic-->', $content);
		$content = preg_replace('%<script( |>)(.*)</script>%s', '<!--JS removed by TooBasic-->', $content);
	}
}
