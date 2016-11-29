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
 */
class RestManagerException extends Exception {
	
}

/**
 * @class RestManager
 * This manager interprets and solve rest calls.
 * Allowed calls:
 * 	| URL                           | GET | PUT | POST | DELETE |
 * 	|:------------------------------|:---:|:---:|:----:|:------:|
 * 	| resource/<resource-name>      |  Y  |  N  |  Y   |   N    |
 * 	| resource/<resource-name>/<id> |  Y  |  Y  |  N   |   Y    |
 * 	| stats/<resource-name>         |  Y  |  N  |  N   |   N    |
 * 	| search/<resource-name>        |  Y  |  N  |  N   |   N    |
 *
 * Known actions:
 * 	| URL                           | Method | Name    |
 * 	|:------------------------------|:------:|:-------:|
 * 	| resource/<resource-name>      | GET    | index   |
 * 	| resource/<resource-name>      | POST   | create  |
 * 	| resource/<resource-name>/<id> | GET    | show    |
 * 	| resource/<resource-name>/<id> | PUT    | update  |
 * 	| resource/<resource-name>/<id> | DELETE | destroy |
 * 	| search/<resource-name>        | GET    | search  |
 * 	| stats/<resource-name>         | GET    | stats   |
 *
 */
class RestManager extends Manager {
	//
	// Constants.
	/**
	 * Default amount of items on action 'search'.
	 */
	const ListsLimit = 10;
	/**
	 * Maximum limit on action 'search', it will never return more than this
	 * amount.
	 */
	const ListsMaxLimit = 100;
	//
	// Protected properties.
	/**
	 * @var \TooBasic\RestConfig Current RESTful configuration.
	 */
	protected $_config = false;
	/**
	 * @var mixed[] Full list of errors.
	 */
	protected $_errors = [];
	/**
	 * @var boolean This flags indicates the presence of at least one error.
	 */
	protected $_hasErrors = false;
	/**
	 * @var mixed[string] This structure holds the inforamtion retrieve for
	 * the current path.
	 */
	protected $_restPath = [];
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	protected function __construct() {
		parent::__construct();
		//
		// Global dependencies.
		global $RestPath;
		//
		// Loading current path and configuration.
		if(!empty($RestPath)) {
			$this->loadConfiguration();
			$this->expandRestPath();
		}
	}
	//
	// Public methods.
	/**
	 * This method provides access to the current authorization key.
	 *
	 * @return string Returns a hash string or FALSE if it's not set.
	 */
	public function authorizationKey() {
		$sKey = $this->sessionKey();
		return isset($this->params->session->{$sKey}) ? $this->params->session->{$sKey}[GC_AFIELD_HASH] : false;
	}
	/**
	 * This method creates an authorization key and associates certain level
	 * to it. If the key is already created, it just makes the association.
	 *
	 * @param string $key Authorization key/level to set.
	 * @return mixed[string] Returns the current autorization status.
	 */
	public function authorize($key = GC_REST_DEFAULT_KEY) {
		//
		// Default values.
		$sKey = $this->sessionKey();
		//
		// Loading current authentication setting, it it's not present,
		// it's generated.
		$wallet = [];
		if(!isset($this->params->session->{$sKey})) {
			//
			// Generating an emtpy authentication setting with a new
			// hash-key.
			$wallet = [
				GC_AFIELD_HASH => self::GenHash(),
				GC_AFIELD_KEYS => []
			];
		} else {
			$wallet = $this->params->session->{$sKey};
		}
		//
		// Appending the new key.
		if(!in_array($key, $wallet[GC_AFIELD_KEYS])) {
			$wallet[GC_AFIELD_KEYS][] = $key;
		}
		//
		// Saving new settings on session.
		$this->params->session->{$sKey} = $wallet;
		//
		// Returning current settings.
		return $this->params->session->{$sKey};
	}
	/**
	 * This method provides access to every found error.
	 *
	 * @return mixed[] List of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method tells if there was at least one error, either when loading
	 * or in execution.
	 *
	 * @return boolean Returns TRUE when there's at least one error.
	 */
	public function hasErrors() {
		return $this->_hasErrors;
	}
	/**
	 * This method provides access to the last found error.
	 *
	 * @return mixed[string] Returns the last error or FALSE if there was
	 * none.
	 */
	public function lastErrors() {
		$pos = count($this->_errors) - 1;
		return $pos > -1 ? $this->_errors[$pos] : false;
	}
	/**
	 * This is the main method of this manager and it tries to analyze and
	 * execute a REST call.
	 *
	 * @param boolean $autoDisplay When TRUE, results are prompted, otherwise
	 * is just returned.
	 * @return \stdClass Returns a JSON containing the execution results.
	 */
	public function run($autoDisplay = true) {
		//
		// Check security.
		if(!$this->hasErrors()) {
			$this->checkPermissions();
		}
		//
		// Forwarding calls.
		if(!$this->hasErrors()) {
			switch($this->_restPath[GC_AFIELD_TYPE]) {
				case GC_REST_TYPE_RESOURCE:
					$response = $this->runResource();
					break;
				case GC_REST_TYPE_STATS:
					$response = $this->runStats();
					break;
				case GC_REST_TYPE_SEARCH:
					$response = $this->runSearch();
					break;
			}
		}
		//
		// If there's at least one error, the response is replace by an
		// error response.
		if($this->hasErrors()) {
			$response = new \stdClass();
			$response->{GC_AFIELD_LASTERROR} = $this->lastErrors();
			$response->{GC_AFIELD_ERRORS} = $this->errors();
		}
		//
		// Should it be prompted?
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
	/**
	 * This method drop current authentication settings.
	 *
	 * @return boolean Returns TRUE if it was successfully dropped.
	 */
	public function unauthorize() {
		//
		// Default values.
		$sKey = $this->sessionKey();
		//
		// Removing keys.
		if(isset($this->params->session->{$sKey})) {
			unset($this->params->session->{$sKey});
		}
		//
		// Returning current status.
		return !isset($this->params->session->{$sKey});
	}
	//
	// Protected methods.
	/**
	 * This method analyzes current REST request against policies.
	 */
	protected function checkPermissions() {
		//
		// Default values.
		$policy = GC_REST_POLICY_BLOCKED;
		//
		// Checking known policies types.
		if(in_array($this->_restPath[GC_AFIELD_TYPE], [GC_REST_TYPE_RESOURCE, GC_REST_TYPE_SEARCH, GC_REST_TYPE_STATS])) {
			if(isset($this->_config->resources->{$this->_restPath[GC_AFIELD_RESOURCE]})) {
				$policy = $this->_config->resources->{$this->_restPath[GC_AFIELD_RESOURCE]};
			}
		} else {
			$this->setError($this->tr->EX_no_policies_for_type([
					'type' => $this->_restPath[GC_AFIELD_TYPE]
				]), HTTPERROR_FORBIDDEN);
		}
		//
		// Analysing policy when specified by action.
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
			$policyParams = explode(':', $policy);
			$policy = array_shift($policyParams);
			switch($policy) {
				case GC_REST_POLICY_BLOCKED:
					$this->setError($this->tr->EX_rest_action_not_allowed([
							'action' => $this->_restPath[GC_AFIELD_ACTION],
							'resource' => $this->_restPath[GC_AFIELD_RESOURCE]
						]), HTTPERROR_FORBIDDEN);
					break;
				case GC_REST_POLICY_AUTH:
					if(!$this->isAuthorized($policyParams)) {
						$this->setError($this->tr->EX_rest_action_not_authorized([
								'action' => $this->_restPath[GC_AFIELD_ACTION],
								'resource' => $this->_restPath[GC_AFIELD_RESOURCE]
							]), HTTPERROR_UNAUTHORIZED);
					}
					break;
				case GC_REST_POLICY_ACTIVE:
					//
					// Nothing to do here, becuase it's alright.
					break;
				default:
					$this->setError($this->tr->EX_unknown_rest_policy([
							'policy' => $policy
						]), HTTPERROR_NOT_IMPLEMENTED);
					break;
			}
		}
	}
	/**
	 * This method parses the REST given URL and extracts from it all
	 * parameters.
	 */
	protected function expandRestPath() {
		//
		// Global dependencies.
		global $RestPath;
		//
		// Expanding the url and checking the first value (there should be
		// at leas one).
		$path = explode('/', $RestPath);
		if(empty($path[0])) {
			$this->setError($this->tr->EX_wrong_rest_path, HTTPERROR_BAD_REQUEST);
		}
		//
		// Alalizing all pieces.
		if(!$this->hasErrors()) {
			//
			// Extracting method and type of request.
			$this->_restPath[GC_AFIELD_TYPE] = array_shift($path);
			$this->_restPath[GC_AFIELD_METHOD] = $this->params->server->REQUEST_METHOD;
			//
			// Validating type and extrating further information.
			switch($this->_restPath[GC_AFIELD_TYPE]) {
				case GC_REST_TYPE_RESOURCE:
					//
					// There must be a resource name.
					if(!empty($path[0])) {
						//
						// Extracting resource name and
						// parameters.
						$this->_restPath[GC_AFIELD_RESOURCE] = array_shift($path);
						$this->_restPath[GC_AFIELD_PARAMS] = $path;
					} else {
						$this->setError($this->tr->EX_rest_resource_no_resource, HTTPERROR_BAD_REQUEST);
					}
					break;
				case GC_REST_TYPE_STATS:
					//
					// There must be a resource name.
					if(!empty($path[0])) {
						//
						// Extracting resource name.
						$this->_restPath[GC_AFIELD_RESOURCE] = array_shift($path);
					} else {
						$this->setError($this->tr->EX_rest_stats_no_resource, HTTPERROR_BAD_REQUEST);
					}
					break;
				case GC_REST_TYPE_SEARCH:
					//
					// There must be a resource name.
					if(!empty($path[0])) {
						//
						// Extracting resource name and
						// conditions.
						$this->_restPath[GC_AFIELD_RESOURCE] = array_shift($path);
						$this->_restPath[GC_AFIELD_PARAMS] = $path;
					} else {
						$this->setError($this->tr->EX_rest_search_no_resource, HTTPERROR_BAD_REQUEST);
					}
					break;
				default:
					$this->setError($this->tr->EX_unknown_rest_type([
							'type' => $this->_restPath[GC_AFIELD_TYPE]
						]), HTTPERROR_BAD_REQUEST);
			}
		}
		//
		// Gessing the right action.
		if(!$this->hasErrors()) {
			$this->guessActionName();
		}
	}
	/**
	 * This method tries to guess the current action based on request type,
	 * method and parameters.
	 */
	protected function guessActionName() {
		//
		// Known actions.
		static $names = [
			'resource-GET' => 'index',
			'resource-POST' => 'create',
			'resource-GET-PARAM' => 'show',
			'resource-PUT-PARAM' => 'update',
			'resource-DELETE-PARAM' => 'destroy',
			'search-GET' => 'search',
			'search-GET-PARAM' => 'search',
			'stats-GET' => 'stats'
		];
		//
		// Building the action matcher.
		$query = [$this->_restPath[GC_AFIELD_TYPE], $this->_restPath[GC_AFIELD_METHOD]];
		if(isset($this->_restPath[GC_AFIELD_PARAMS]) && isset($this->_restPath[GC_AFIELD_PARAMS][0])) {
			$query[] = 'PARAM';
		}
		$query = implode('-', $query);
		//
		// Is it known?
		if(isset($names[$query])) {
			$this->_restPath[GC_AFIELD_ACTION] = $names[$query];
		} else {
			$this->setError($this->tr->EX_unknown_rest_action([
					'type' => $this->_restPath[GC_AFIELD_TYPE],
					'method' => $this->_restPath[GC_AFIELD_METHOD]
				]), HTTPERROR_BAD_REQUEST);
		}
	}
	/**
	 * This method checks the URL parameter 'authorize' against some session
	 * stored hashes.
	 *
	 * @param string[] $checkKeys List of hash identifiers to check.
	 * @return boolean Returns TRUE when it's authorized.
	 */
	protected function isAuthorized($checkKeys = false) {
		//
		// Default values.
		$authorized = false;
		$sKey = $this->sessionKey();
		$authorize = $this->params->get->authorize;
		//
		// Ensuring parameter structure.
		if(!$checkKeys || !is_array($checkKeys)) {
			$checkKeys = [GC_REST_DEFAULT_KEY];
		}
		//
		// Retrieving knwon keys from session.
		$wallet = isset($this->params->session->{$sKey}) ? $this->params->session->{$sKey} : [GC_AFIELD_HASH => false];
		//
		// Checking hash.
		if($wallet[GC_AFIELD_HASH] == $authorize) {
			//
			// Checking each key against those known until one matches.
			foreach($checkKeys as $key) {
				if(in_array($key, $wallet[GC_AFIELD_KEYS])) {
					$authorized = true;
					break;
				}
			}
		}
		//
		// Returning authorization results.
		return $authorized;
	}
	/**
	 * This methods loads current RESTful configuration files.
	 */
	protected function loadConfiguration() {
		$this->_config = $this->config->rest(GC_CONFIG_MODE_MERGE, 'TooBasic');
	}
	/**
	 * This method analyzes and triggers the execution of actions 'index',
	 * 'show', 'update', 'create' and 'destroy'.
	 *
	 * @return mixed Returns the proper response for the request method.
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
	 * This removes an element from a representation factory using the third
	 * parameter of the REST as item ID.
	 *
	 * @param ItemsFactory $factory Representation factory to affect.
	 * @return \stdClass Returns a simple JSON with a field called 'status'
	 * that is TRUE when the operation was successful.
	 */
	protected function runResourceMethodDelete(ItemsFactory $factory) {
		//
		// Default values.
		$response = new \stdClass();
		//
		// Checking third parameter presence.
		if(empty($this->_restPath[GC_AFIELD_PARAMS][0])) {
			$this->setError($this->tr->EX_item_not_specified, HTTPERROR_BAD_REQUEST);
		}
		//
		// Retrieveing and removing the requested element.
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
	 * This method attends the action 'index' and 'show' on a REST call.
	 *
	 * @param ItemsFactory $factory Representation factory to affect.
	 * @return mixed Returns a found item or list of them in a way that can be
	 * used as REST response.
	 */
	protected function runResourceMethodGet(ItemsFactory $factory) {
		//
		// Dafault values.
		$response = [];
		$expand = isset($this->params->get->expand);
		//
		// Is it a list request or a specific item request?
		if(empty($this->_restPath[GC_AFIELD_PARAMS][0])) {
			//
			// Loading parameters
			$offset = isset($this->params->get->offset) ? $this->params->get->offset : 0;
			$limit = isset($this->params->get->limit) ? $this->params->get->limit : self::ListsLimit;
			//
			// Safe-guarding limits.
			if($limit > self::ListsMaxLimit) {
				$limit = self::ListsMaxLimit;
			}
			//
			// Loading and trimming.
			$ids = $factory->ids();
			$ids = array_splice($ids, $offset, $limit);
			//
			// Loading each element.
			foreach($ids as $id) {
				$item = $factory->item($id);
				if($expand) {
					$item->expandExtendedColumns();
				}
				$response[] = $item->toArray();
			}
		} else {
			//
			// Loading the requested item.
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
	 * This method attends the action 'create' on a REST call.
	 *
	 * @param ItemsFactory $factory Representation factory to affect.
	 * @return mixed Returns an item in a way that can be used as REST
	 * response.
	 */
	protected function runResourceMethodPost(ItemsFactory $factory) {
		//
		// Default values.
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
			//
			// Checking creation.
			if($newId) {
				//
				// Loading item.
				$item = $factory->item($newId);
				//
				// Setting its values based on the posted body.
				foreach($data as $field => $value) {
					$item->{$field} = $value;
				}
				//
				// Persisting changes.
				$item->persist();
				//
				// Preparing it for response.
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
	 * This method attends the action 'update' on a REST call.
	 *
	 * @param ItemsFactory $factory Representation factory to affect.
	 * @return mixed Returns an item in a way that can be used as REST
	 * response.
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
			//
			// Loading item.
			$item = $factory->item($this->_restPath[GC_AFIELD_PARAMS][0]);
			if($item) {
				//
				// Setting its values based on the posted body.
				foreach($data as $field => $value) {
					$item->{$field} = $value;
				}
				//
				// Persisting changes.
				$item->persist();
				//
				// Preparing it for response.
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
	 * This method attends the action 'search' on a REST call.
	 *
	 * @return mixed Returns a list of found items in a way that can be used
	 * as REST response.
	 */
	protected function runSearch() {
		//
		// Default values.
		$response = [];
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
			$query = [];
			while(!empty($this->_restPath[GC_AFIELD_PARAMS])) {
				$key = array_shift($this->_restPath[GC_AFIELD_PARAMS]);
				$value = array_shift($this->_restPath[GC_AFIELD_PARAMS]);
				$query[$key] = $value;
			}
			//
			// Loading parameters.
			$offset = isset($this->params->get->offset) ? $this->params->get->offset : 0;
			$limit = isset($this->params->get->limit) ? $this->params->get->limit : self::ListsLimit;
			$expand = isset($this->params->get->expand);
			//
			// Safe-guarding limits.
			if($limit > self::ListsMaxLimit) {
				$limit = self::ListsMaxLimit;
			}
			//
			// Searching and trimming.
			$ids = $factory->idsBy($query);
			$ids = array_splice($ids, $offset, $limit);
			//
			// Loading each element.
			foreach($ids as $id) {
				$item = $factory->item($id);
				if($expand) {
					$item->expandExtendedColumns();
				}
				$response[] = $item->toArray();
			}
		}
		//
		// Returning results.
		return $response;
	}
	/**
	 * This method attends the action 'search' on a REST call.
	 *
	 * @return \stdClass Returns a object that can be used as REST response
	 * with status values regarding certain representation.
	 */
	protected function runStats() {
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
	 * This method builds the right key where current authorizations are
	 * stored inside $_SESSION.
	 *
	 * @return string Returns an array key.
	 */
	protected function sessionKey() {
		static $key = false;

		if($key === false) {
			$key = 'K'.md5(strtoupper('REST-KEY-'.ROOTURI));
		}

		return $key;
	}
	/**
	 * This method adds an error to the internal errors queue.
	 *
	 * @param string $message Message to attach to an error.
	 * @param int $code Numeric error code.
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
		$this->_hasErrors = true;
	}
	//
	// Protected class methods.
	/**
	 * This class method is a random hash generator.
	 *
	 * @param int $length How long the hash must be.
	 * @return string Returns an alphanumeric hash.
	 */
	protected static function GenHash($length = 40) {
		static $keyCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return substr(str_shuffle(str_repeat($keyCharacters, ceil($length / strlen($keyCharacters)))), 1, $length);
	}
}
