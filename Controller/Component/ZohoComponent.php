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

		$this->Http = new HttpSocket();
		$this->authToken = $this->Session->read($this->authTokenSessionKey);
	}

	/**
	 * Insert a records.
	 * You can use the insertRecords method to insert records into the required Zoho CRM module.
	 *
	 * @see 	  https://www.zoho.com/crm/help/api/insertrecords.html
	 * @return 	  array
	 */
	public function insertRecords($scope, $data) {
		if (!$this->authToken) {
			$this->_generateAuthToken();
		}
		if (!$this->_validateScopeInsertRecords($scope)) {
			throw new CakeException('Invalid scope.');
		}

		$url = "{$this->apiUrls['crm']}{$scope}/insertRecords";
		$xml = $this->_makeXMLDataToInsertRecords($scope, $data);
		$params = array(
			'authtoken' => $this->authToken, 
			'xmlData' 	=> $xml->saveHTML()
		);
		
		$request = $this->_makeRequest($url, $params, 'post');
		if (!$request->isOk()) {
			throw new CakeException('Invalid request.');
		}

		$response = Xml::toArray(Xml::build($request->body));
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
	 * @return DOMDocument
	 */
	protected function _makeXMLDataToInsertRecords($scope, $data) {
		$xml = Xml::build("<{$scope}></{$scope}>", array('return' => 'domdocument'));
		
		$row = $xml->createElement('row');
		$row->setAttribute('no', 1);
		
		foreach ($data as $key => $value) {
			$child = $xml->createElement('FL', $value);	
			$child->setAttribute('val', $key);			

			$row->appendChild($child);
		}
		$xml->firstChild->appendChild($row);
		return $xml;
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

}