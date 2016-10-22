<?php

/**
 * @file RestManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\Representations\ItemsFactory;
use TooBasic\Representations\ItemRepresentation;

/**
 * @class RestManagerException
 * @todo doc
 */
class RestManagerException extends Exception {
	
}

/**
 * @class RestManager
 * @todo doc
 *
 * 	| URL                           | GET | PUT | POST | DELETE |
 * 	|:------------------------------|:---:|:---:|:----:|:------:|
 * 	| resource/<resource-name>      |  Y  |  N  |  Y   |   N    |
 * 	| resource/<resource-name>/<id> |  Y  |  Y  |  N   |   Y    |
 * 	| stats/<resource-name>         |  Y  |  N  |  N   |   N    |
 *
 * 	| URL                           | Method | Name    |
 * 	|:------------------------------|:------:|:-------:|
 * 	| resource/<resource-name>      | GET    | index   |
 * 	| resource/<resource-name>      | POST   | create  |
 * 	| resource/<resource-name>/<id> | GET    | show    |
 * 	| resource/<resource-name>/<id> | PUT    | update  |
 * 	| resource/<resource-name>/<id> | DELETE | destroy |
 * 	| stats/<resource-name>         | GET    | stat    |
 *
 */
class RestManager extends Manager {
	//
	// Constants.
	/**
	 * @todo doc 
	 */
	const ListsLimit = 10;
	/**
	 * @todo doc 
	 */
	const ListsMaxLimit = 100;
	//
	// Protected properties.
	/**
	 * @var \TooBasic\RestConfig @todo doc
	 */
	protected $_config = false;
	/**
	 * @var mixed[] @todo doc 
	 */
	protected $_errors = [];
	/**
	 * @var mixed[string] @todo doc
	 */
	protected $_restPath = [];
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @throws \TooBasic\Managers\RestManagerException
	 */
	protected function __construct() {
		parent::__construct();
		//
		// Global dependencies.
		global $RestPath;
		if(!empty($RestPath)) {
			$this->loadConfiguration();
			$this->expandRestPath();
		}
	}
	//
	// Public methods.
	public function authorize($set = true) {
		$key = $this->authorizationKey();

		if(!isset($this->params->session->{$key}) && $set) {
			$length = 40;
			$keyCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$this->params->session->{$key} = substr(str_shuffle(str_repeat($keyCharacters, ceil($length / strlen($keyCharacters)))), 1, $length);
		}

		return $this->params->session->{$key};
	}
	/**
	 * @todo doc
	 *
	 * @return type @todo doc
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * @todo doc
	 *
	 * @return type @todo doc
	 */
	public function hasErrors() {
		return !empty($this->_errors);
	}
	/**
	 * @todo doc
	 *
	 * @return type @todo doc
	 */
	public function lastErrors() {
		$pos = count($this->_errors) - 1;
		return $pos > -1 ? $this->_errors[$pos] : false;
	}
	/**
	 * @todo doc
	 *
	 * @param boolean $autoDisplay @todo doc
	 * @throws \TooBasic\Managers\RestManagerException
	 */
	public function run($autoDisplay = true) {
		//
		// Check security.
		$this->checkPermissions();
		//
		// Forwarding calls.
		if(!$this->hasErrors()) {
			switch($this->_restPath[GC_AFIELD_TYPE]) {
				case GC_REST_TYPE_RESOURCE:
					$response = $this->runResource();
					break;
				case GC_REST_TYPE_STAT:
					$response = $this->runStat();
					break;
			}
		}

		if($this->hasErrors()) {
			$response = new \stdClass();
			$response->{GC_AFIELD_LASTERROR} = $this->lastErrors();
			$response->{GC_AFIELD_ERRORS} = $this->errors();
		}

		if($autoDisplay) {
			//
			// Every service response is a JSON object.
			header('Content-Type: application/json');
			//
			// Displaying.
			echo json_encode($response);
		}

		return $response;
	}
	public function unauthorize($set = true) {
		$key = $this->authorizationKey();

		if(isset($this->params->session->{$key})) {
			unset($this->params->session->{$key});
		}

		return !isset($this->params->session->{$key});
	}
	//
	// Protected methods.
	public function authorizationKey() {
		static $key = false;

		if($key === false) {
			$key = 'K'.md5(strtoupper('REST-KEY-'.ROOTURI));
		}

		return $key;
	}
	protected function checkPermissions() {
		//
		// Default values.
		$policy = GC_REST_POLICY_BLOCKED;
		//
		// Checking known policies.
		if(in_array($this->_restPath[GC_AFIELD_TYPE], [GC_REST_TYPE_RESOURCE, GC_REST_TYPE_STAT])) {
			if(isset($this->_config->resources->{$this->_restPath[GC_AFIELD_RESOURCE]})) {
				$policy = $this->_config->resources->{$this->_restPath[GC_AFIELD_RESOURCE]};
			}
		} else {
			$this->setError($this->tr->EX_no_policies_for_type([
					'type' => $this->_restPath[GC_AFIELD_TYPE]
					], HTTPERROR_FORBIDDEN));
		}
		//
		// Analysing policies.
		if(!$this->hasErrors() && is_object($policy)) {
			if(isset($policy->{$this->_restPath[GC_AFIELD_ACTION]})) {
				$policy = $policy->{$this->_restPath[GC_AFIELD_ACTION]};
			} else {
				$policy = GC_REST_POLICY_BLOCKED;
			}
		}
		//
		// Analysing policies.
		if(!$this->hasErrors()) {
			switch($policy) {
				case GC_REST_POLICY_BLOCKED:
					$this->setError($this->tr->EX_rest_action_not_allowed([
							'action' => $this->_restPath[GC_AFIELD_ACTION],
							'resource' => $this->_restPath[GC_AFIELD_RESOURCE]
							], HTTPERROR_FORBIDDEN));
					break;
				case GC_REST_POLICY_AUTH:
					if(!$this->isAuthorized()) {
						$this->setError($this->tr->EX_rest_action_not_authorized([
								'action' => $this->_restPath[GC_AFIELD_ACTION],
								'resource' => $this->_restPath[GC_AFIELD_RESOURCE]
								], HTTPERROR_UNAUTHORIZED));
					}
					break;
				case GC_REST_POLICY_ACTIVE:
					//
					// Nothing to do here, becuase it's alright.
					break;
				default:
					$this->setError($this->tr->EX_unknown_rest_policy([
							'policy' => $policy
							], HTTPERROR_NOT_IMPLEMENTED));
					break;
			}
		}
	}
	/**
	 * @todo doc
	 *
	 * @throws \TooBasic\Managers\RestManagerException
	 */
	protected function expandRestPath() {
		//
		// Global dependencies.
		global $RestPath;

		$path = explode('/', $RestPath);
		if(empty($path[0])) {
			throw new RestManagerException($this->tr->EX_wrong_rest_path);
		}
		$this->_restPath[GC_AFIELD_TYPE] = array_shift($path);
		$this->_restPath[GC_AFIELD_METHOD] = $this->params->server->REQUEST_METHOD;

		switch($this->_restPath[GC_AFIELD_TYPE]) {
			case GC_REST_TYPE_RESOURCE:
				if(!empty($path[0])) {
					$this->_restPath[GC_AFIELD_RESOURCE] = array_shift($path);
					$this->_restPath[GC_AFIELD_PARAMS] = $path;
				} else {
					throw new RestManagerException($this->tr->EX_rest_resource_no_resource);
				}
				break;
			case GC_REST_TYPE_STAT:
				if(!empty($path[0])) {
					$this->_restPath[GC_AFIELD_RESOURCE] = array_shift($path);
				} else {
					throw new RestManagerException($this->tr->EX_rest_stats_no_resource);
				}
				break;
			default:
				throw new RestManagerException($this->tr->EX_unknown_rest_type([
					'type' => $this->_restPath[GC_AFIELD_TYPE]
				]));
		}

		$this->guessActionName();
	}
	protected function guessActionName() {
		$names = [
			'resource-GET' => 'index',
			'resource-POST' => 'create',
			'resource-GET-PARAM' => 'show',
			'resource-PUT-PARAM' => 'update',
			'resource-DELETE-PARAM' => 'destroy',
			'stats-GET' => 'stat'
		];

		$query = [$this->_restPath[GC_AFIELD_TYPE], $this->_restPath[GC_AFIELD_METHOD]];
		if(isset($this->_restPath[GC_AFIELD_PARAMS]) && isset($this->_restPath[GC_AFIELD_PARAMS][0])) {
			$query[] = 'PARAM';
		}
		$query = implode('-', $query);

		if(isset($names[$query])) {
			$this->_restPath[GC_AFIELD_ACTION] = $names[$query];
		} else {
			throw new RestManagerException($this->tr->EX_unknown_rest_action([
				'type' => $this->_restPath[GC_AFIELD_TYPE],
				'method' => $this->_restPath[GC_AFIELD_METHOD]
			]));
		}
	}
	protected function isAuthorized() {
		$key = $this->authorizationKey();
		$authorize = $this->params->get->authorize;

		return isset($this->params->session->{$key}) && $this->params->session->{$key} == $authorize;
	}
	/**
	 * @todo doc
	 *
	 * @throws \TooBasic\Managers\RestManagerException
	 */
	protected function loadConfiguration() {
		$this->_config = $this->config->rest(GC_CONFIG_MODE_MERGE, 'TooBasic');
	}
	/**
	 * @todo doc
	 *
	 * @return type @todo doc
	 */
	protected function runResource() {
		//
		// Default values.
		$response = false;
		$factory = false;
		$methods = [
			'DELETE' => 'runResourceMethodDelete',
			'GET' => 'runResourceMethodGet',
			'POST' => 'runResourceMethodPost',
			'PUT' => 'runResourceMethodPut'
		];
		//
		// Trying to load the right factory.
		try {
			$factory = $this->representation->{$this->_restPath[GC_AFIELD_RESOURCE]};
		} catch(Exception $e) {
			$this->setError($this->tr->EX_unknown_resource([
					'resource' => $this->_restPath[GC_AFIELD_RESOURCE]
				]), HTTPERROR_BAD_REQUEST, $e->getMessage());
		}
		//
		// Checking request method.
		if(!$this->hasErrors()) {
			if(isset($methods[$this->_restPath[GC_AFIELD_METHOD]])) {
				$method = $methods[$this->_restPath[GC_AFIELD_METHOD]];
				$response = $this->{$method}($factory);
			} else {
				$this->setError($this->tr->EX_unhandled_request_method([
						'method' => $this->_restPath[GC_AFIELD_METHOD]
					]), HTTPERROR_NOT_IMPLEMENTED);
			}
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * @todo doc
	 *
	 * @param ItemsFactory $factory @todo doc
	 * @return \stdClass @todo doc
	 */
	protected function runResourceMethodDelete(ItemsFactory $factory) {
		$response = new \stdClass();

		if(empty($this->_restPath[GC_AFIELD_PARAMS][0])) {
			$this->setError($this->tr->EX_item_not_specified, HTTPERROR_BAD_REQUEST);
		}

		if(!$this->hasErrors()) {
			$item = $factory->item($this->_restPath[GC_AFIELD_PARAMS][0]);
			if($item) {
				$response->{GC_AFIELD_STATUS} = $item->remove();
			} else {
				$this->setError($this->tr->EX_item_not_found([
						'id' => $this->_restPath[GC_AFIELD_PARAMS][0]
					]), HTTPERROR_NOT_FOUND);
			}
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * @todo doc
	 *
	 * @param ItemsFactory $factory @todo doc
	 * @return type @todo doc
	 */
	protected function runResourceMethodGet(ItemsFactory $factory) {
		$response = [];

		$expand = isset($this->params->get->expand);
		//
		// Is it a list request or a specific item request?
		if(empty($this->_restPath[GC_AFIELD_PARAMS][0])) {
			$offset = isset($this->params->get->offset) ? $this->params->get->offset : 0;
			$limit = isset($this->params->get->limit) ? $this->params->get->limit : self::ListsLimit;

			if($limit > self::ListsMaxLimit) {
				$limit = self::ListsMaxLimit;
			}

			$ids = $factory->ids();
			$ids = array_splice($ids, $offset, $limit);

			$items = [];
			foreach($ids as $id) {
				$item = $factory->item($id);
				if($expand) {
					$item->expandExtendedColumns();
				}
				$items[] = $item->toArray();
			}

			$response = $items;
		} else {
			$item = $factory->item($this->_restPath[GC_AFIELD_PARAMS][0]);
			if($item) {
				if($expand) {
					$item->expandExtendedColumns(true);
				}
				$response = $item->toArray();
			} else {
				$this->setError($this->tr->EX_item_not_found([
						'id' => $this->_restPath[GC_AFIELD_PARAMS][0]
					]), HTTPERROR_NOT_FOUND);
			}
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * @todo doc
	 *
	 * @param ItemsFactory $factory @todo doc
	 * @return type @todo doc
	 */
	protected function runResourceMethodPost(ItemsFactory $factory) {
		$response = false;
		//
		// Checking POST body.
		$data = json_decode(file_get_contents('php://input'), true);
		if(!$data) {
			$this->setError($this->tr->EX_bad_request_json_body, HTTPERROR_BAD_REQUEST);
		}
		//
		// Creating...
		if(!$this->hasErrors()) {
			$newId = $factory->create();
			if($newId) {
				$item = $factory->item($newId);

				foreach($data as $field => $value) {
					$item->{$field} = $value;
				}

				$item->persist();

				$item->expandExtendedColumns();
				$response = $item->toArray();
			} else {
				$this->setError($this->tr->EX_unable_create_item, HTTPERROR_INTERNAL_SERVER_ERROR, [
					GC_AFIELD_DB => $factory->lastDBError(),
					GC_AFIELD_DATA => $data
				]);
			}
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * @todo doc
	 *
	 * @param ItemsFactory $factory @todo doc
	 * @return type @todo doc
	 */
	protected function runResourceMethodPut(ItemsFactory $factory) {
		//
		// Default values.
		$response = new \stdClass();
		//
		// Checking parameters.
		if(empty($this->_restPath[GC_AFIELD_PARAMS][0])) {
			$this->setError($this->tr->EX_item_not_specified, HTTPERROR_BAD_REQUEST);
		}
		//
		// Checking PUT body.
		$data = json_decode(file_get_contents('php://input'), true);
		if(!$data) {
			$this->setError($this->tr->EX_bad_request_json_body, HTTPERROR_BAD_REQUEST);
		}
		//
		// Updating...
		if(!$this->hasErrors()) {
			$item = $factory->item($this->_restPath[GC_AFIELD_PARAMS][0]);
			if($item) {
				foreach($data as $field => $value) {
					$item->{$field} = $value;
				}

				$item->persist();

				$item->expandExtendedColumns();
				$response = $item->toArray();
			} else {
				$this->setError($this->tr->EX_item_not_found([
						'id' => $this->_restPath[GC_AFIELD_PARAMS][0]
					]), HTTPERROR_NOT_FOUND);
			}
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * @todo doc
	 *
	 * @return \stdClass @todo doc
	 */
	protected function runStat() {
		//
		// Default values.
		$response = new \stdClass();
		$factory = false;
		//
		// Checking method.
		if($this->_restPath[GC_AFIELD_METHOD] != 'GET') {
			$this->setError($this->tr->EX_unhandled_request_method([
					'method' => $this->_restPath[GC_AFIELD_METHOD]
				]), HTTPERROR_BAD_REQUEST);
		}
		//
		// Trying to load the right factory.
		if(!$this->hasErrors()) {
			try {
				$factory = $this->representation->{$this->_restPath[GC_AFIELD_RESOURCE]};
			} catch(Exception $e) {
				$this->setError($this->tr->EX_unknown_resource([
						'resource' => $this->_restPath[GC_AFIELD_RESOURCE]
					]), HTTPERROR_BAD_REQUEST, $e->getMessage());
			}
		}
		//
		// Building stats information.
		if(!$this->hasErrors()) {
			$response->count = count($factory->ids());
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * This method adds an error to the internal errors queue.
	 *
	 * @param type $message @todo doc
	 * @param type $code @todo doc
	 * @param mixed $info Extra information to attach.
	 */
	protected function setError($message, $code = HTTPERROR_INTERNAL_SERVER_ERROR, $info = false) {
		$aux = [
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message
		];
		if($info) {
			$aux[GC_AFIELD_INFO] = $info;
		}
		$this->_errors[] = $aux;
	}
}

// https://en.wikipedia.org/wiki/Representational_state_transfer#Relationship_between_URL_and_HTTP_methods
