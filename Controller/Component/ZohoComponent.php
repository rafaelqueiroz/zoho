<?php
App::uses('Component', 'Controller');
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');
/**
 * Zoho Component
 *
 * @author        Rafael F Queiroz <rafaelfqf@gmail.com>
 * @package       Controller.Component
 * @since         v 0.0.1
 */
class ZohoComponent extends Component {

	/**
	 * API Urls
	 *
	 * @var array
	 */
	protected $apiUrls = array(
		'auth' => 'https://accounts.zoho.com/apiauthtoken/nb/create',
		'crm'  => 'https://crm.zoho.com/crm/private/xml/'
	);

	/**
	 * Auth Token
	 *
	 * @var string
	 */
	protected $authToken;

	/**
	 * Auto Token Session Key
	 *
	 * @var string
	 */
	protected $authTokenSessionKey = 'Zoho.authToken';

	/**
	 * HTTP Socket
	 *
	 * @var HttpSocket
	 */
	protected $Http;

	/**
	 * Scopes available
	 *
	 * @var array
	 */
	protected $scopes = array(
		'Leads', 'Contacts', 'Accounts', 'Potentials', 'Campaigns',
		'Cases', 'Products', 'Vendors', 'Quotes', 'SalesOrders',
		'PurchaseOrders', 'Invoices'
	);

	/**
	 * Settings
	 *
	 * @var array
	 */
	public $settings = array();	

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = array('Session');

	/**
	 * Constructor
	 *
	 * @param ComponentCollection $collection
	 * @param array $settings
	 * @return ZohoComponent
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->settings = array_merge($this->settings, $settings);
	}

	/**
	 * Initialize callback
	 *
	 * @param Controller $controller
	 * @param array $settings
	 * @return void
	 */
	public function initialize(Controller $controller, $settings = array()) {
		if (!isset($this->settings['email'])) {
			$this->settings['email'] = Configure::read('Zoho.email');
		}
		if (!isset($this->settings['password'])) {
			$this->settings['password'] = Configure::read('Zoho.password');
		}

		if (in_array(null, $this->settings)) {
			throw new CakeException ("You must set Zoho credentials.");
		}

		$this->authToken = $this->Session->read($this->authTokenSessionKey);
		$this->Http = new HttpSocket();
	}

	/**
	 * Getter of my Records.
	 * You can use the getMyRecords method to fetch data by the owner of the Authentication token specified in the API request.
	 *
	 * @param  	  string $scope
	 * @param 	  array  $params
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/getmyrecords.html
	 * @return 	  array
	 */
	public function getMyRecords($scope, $params = array()) {
		if (!$this->authToken) {
			$this->_generateAuthToken();
		}
		if (!$this->_validateScopeInsertRecords($scope)) {
			throw new CakeException('Invalid scope.');
		}		

		$url = $this->_makeUrl($scope, __FUNCTION__);
		$_params = array('authtoken' => $this->authToken, 'scope' => 'crmapi');
		$params  = array_merge($params, $_params);
		$request = $this->_makeRequest($url, $params);

		if (!$request->isOk()) {
			throw new CakeException('Invalid request.');
		}

		$response = $this->_parseRequest($request->body);
		if (!empty ($response['response']['error'])) {
			throw new CakeException($response['response']['error']['message']);
		}

		return $response['response'];
	}

	/**
	 * Getter of Records.
	 * You can use the getRecords method to fetch all users data specified in the API request.
	 *
	 * @param  	  string $scope
	 * @param 	  array  $params
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/getrecords.html
	 * @return 	  array
	 */
	public function getRecords($scope, $params = array()) {
		if (!$this->authToken) {
			$this->_generateAuthToken();
		}
		if (!$this->_validateScopeInsertRecords($scope)) {
			throw new CakeException('Invalid scope.');
		}		

		$url = $this->_makeUrl($scope, __FUNCTION__);
		$_params = array('authtoken' => $this->authToken, 'scope' => 'crmapi');
		$params  = array_merge($params, $_params);
		$request = $this->_makeRequest($url, $params);

		if (!$request->isOk()) {
			throw new CakeException('Invalid request.');
		}

		$response = $this->_parseRequest($request->body);
		if (!empty ($response['response']['error'])) {
			throw new CakeException($response['response']['error']['message']);
		}

		return $response['response'];
	}	

	/**
	 * Getter of Record By ID.
	 * You can use this method to retrieve individual records by record ID.
	 *
	 * @param  	  int    $id
	 * @param 	  string $scope
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/getrecordbyid.html
	 * @return 	  array
	 */
	public function getRecordById($id, $scope = 'Leads') {
		if (!$this->authToken) {
			$this->_generateAuthToken();
		}
		if (!$this->_validateScopeInsertRecords($scope)) {
			throw new CakeException('Invalid scope.');
		}		

		$url = $this->_makeUrl($scope, __FUNCTION__);
		$params = array(
			'authtoken' => $this->authToken, 
			'id' => $id,
			'scope' => 'crmapi'
		);
		$request = $this->_makeRequest($url, $params);

		if (!$request->isOk()) {
			throw new CakeException('Invalid request.');
		}

		$response = $this->_parseRequest($request->body);
		if (!empty ($response['response']['error'])) {
			throw new CakeException($response['response']['error']['message']);
		}

		return $response['response'];
	}	


	/**
	 * Insert a records.
	 * You can use the insertRecords method to insert records into the required Zoho CRM module.
	 *
	 * @param  	  string $scope
	 * @param 	  array  $data
	 * @param 	  array  $params
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/insertrecords.html
	 * @return 	  array
	 */
	public function insertRecords($scope, $data, $params = array()) {
		if (!$this->authToken) {
			$this->_generateAuthToken();
		}
		if (!$this->_validateScopeInsertRecords($scope)) {
			throw new CakeException('Invalid scope.');
		}

		$url = $this->_makeUrl($scope, __FUNCTION__);
		$xmlData = $this->_makeXMLDataToInsertRecords($scope, $data);

		$_params = array('authtoken' => $this->authToken, 'xmlData' => $xmlData);
		$params  = array_merge($params, $_params);

		$request = $this->_makeRequest($url, $params, 'post');
		if (!$request->isOk()) {
			throw new CakeException('Invalid request.');
		}

		$response = $this->_parseRequest($request->body);
		if (!empty ($response['response']['error'])) {
			throw new CakeException($response['response']['error']['message']);
		}

		return $response['response'];
	}

	/**
	 * Update a records.
	 * You can use the updateRecords method to update or modify the records in Zoho CRM.
	 *
	 * @param  	  string $scope
	 * @param 	  array  $data
	 * @param 	  array  $params
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/updaterecords.html
	 * @return 	  array
	 */
	public function updateRecords($scope, $data, $params = array()) {
		if (!$this->authToken) {
			$this->_generateAuthToken();
		}
		if (!$this->_validateScopeInsertRecords($scope)) {
			throw new CakeException('Invalid scope.');
		}

		$url = $this->_makeUrl($scope, __FUNCTION__);
		$xmlData = $this->_makeXMLDataToInsertRecords($scope, $data);

		$_params = array('authtoken' => $this->authToken, 'xmlData' => $xmlData);
		$params  = array_merge($params, $_params);

		$request = $this->_makeRequest($url, $params, 'post');
		if (!$request->isOk()) {
			throw new CakeException('Invalid request.');
		}

		$response = $this->_parseRequest($request->body);
		if (!empty ($response['response']['error'])) {
			throw new CakeException($response['response']['error']['message']);
		}

		return $response['response'];		
	}

	/**
	 * Validate scope to insertRecords method
	 *
	 * @param string $scope
	 * @return bool
	 */
	protected function _validateScopeInsertRecords($scope) {
		return in_array($scope, $this->scopes);
	}

	/**
	 * Make XML to insertRecords method
	 *
	 * @param string $scope
	 * @param array  $data
	 * @return string
	 */
	protected function _makeXMLDataToInsertRecords($scope, $data) {
		$xml = Xml::build("<{$scope}></{$scope}>", array('return' => 'domdocument'));

		if (!is_array(current($data))) {
			$data = array($data);
		}
		
		foreach ($data as $no => $row) {
			$el = $xml->createElement('row');
			$el->setAttribute('no', ++$no);
			foreach ($row as $key => $value) {
				$child = $xml->createElement('FL', $value);	
				$child->setAttribute('val', $key);			

				$el->appendChild($child);
			}
			$xml->firstChild->appendChild($el);
		}

		return $xml->saveHTML();
	}

	/**
	 * Method of Generate Auth Token
	 * To use the API require the Zoho CRM Authentication Token.
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/using-authentication-token.html
	 * @return 	  void
	 */
	protected function _generateAuthToken() {

		$params = array(
			'SCOPE' => 'ZohoCRM/crmapi',
			'EMAIL_ID' => $this->settings['email'],
			'PASSWORD' => $this->settings['password']
		);
		$url = $this->apiUrls['auth'];

		$request = $this->_makeRequest($url, $params);
		if (!$request->isOk()) {
			throw new CakeException('Invalid Request.');
		}

		$authToken = $this->_parseAuthTokenResponse($request->body);
		if (!$authToken) {
			throw new CakeException('The Auth Token invalid.');	
		}

		$this->authToken = $authToken;
		$this->Session->write($this->authTokenSessionKey, $this->authToken);
	}

	/**
	 * Parse Auth Response.
	 *
	 * @param 	  string $response
	 * @return 	  array
	 */
	protected function _parseAuthTokenResponse($response) {
		preg_match("/(\w+)=([^\s]+)/", $response, $math);
		if ($math[1] == 'AUTHTOKEN') {
			return $math[2];
		}

		return false;
	}


	/**
	 * Make a Request.
	 *
	 * @var string $name
	 * @var array  $params
	 * @var string $method
	 * @return HttpSocket
	 */
	protected function _makeRequest($url, $params = array(), $method='get') {
		if (!in_array($method, array('get', 'post', 'put', 'delete'))) {
			return false;
		}

		return $this->Http->$method($url, http_build_query($params));
	}

	/**
	 * Make a URL.
	 *
	 * @var string $scope
	 * @var string $function
	 * @return string
	 */
	protected function _makeUrl($scope, $function) {
		return "{$this->apiUrls['crm']}{$scope}/{$function}";
	}

	/**
	 * Parse a request.
	 *
	 * @var string $body
	 * @return array
	 */
	protected function _parseRequest($body) {
		return Xml::toArray(Xml::build($body));
	}

}