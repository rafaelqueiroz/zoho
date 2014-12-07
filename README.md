Zoho Plugin for CakePHP
========

Zoho Plugin provides access to Zoho CRM API. With the Plugin, you can extract CRM data and develop new applications or integrate with your existing business applications.

Requirements
------------

* CakePHP 2.x
* PHP 5

Installation
------------

To install the plugin, place the files in a directory labelled "Zoho/" in your "app/Plugin/" directory.

Setup
-----

To use the Zoho Plugin its required to include the following two lines in your `/app/Config/bootstrap.php` file.

```php
Configure::write('Zoho.email', 'your-email');
Configure::write('Zoho.password', 'your-password');
```

Usage
-----

Controllers that will be using Zoho require the Zoho Component to be included.

```php
public $components = array('Zoho.Zoho');
```

Example #1

```php
$data = array(
	'Lead Source' => 'Web Download',
	'Company' => 'Your Company',
	'First Name' => 'Hannah',
	'Last Name' => 'Smith',
	'Email' => 'testing@testing.com',
	'Title' => 'Manager',
	'Phone' => '1234567890',
	'Home Phone' => '0987654321',
	'Other Phone' => '1212211212',
	'Fax' => '02927272626',
	'Mobile' => '292827622',
	'Description' => 'Sample Description.'
);
$scope = 'Leads';

$this->Zoho->insertRecords($scope, $data);

````

The above example will output:

```php
array(
	'@uri' => '/crm/private/xml/Leads/insertRecords',
	'result' => array(
		'message' => 'Record(s) added successfully',
		'recorddetail' => array(
			'FL' => array(
				(int) 0 => array(
					'@val' => 'Id',
					'@' => '1351804000000077011'
				),
				(int) 1 => array(
					'@val' => 'Created Time',
					'@' => '2014-12-07 14:41:07'
				),
				(int) 2 => array(
					'@val' => 'Modified Time',
					'@' => '2014-12-07 14:41:07'
				),
				(int) 3 => array(
					'@val' => 'Created By',
					'@' => 'rafaelfqf'
				),
				(int) 4 => array(
					'@val' => 'Modified By',
					'@' => 'rafaelfqf'
				)
			)
		)
	)
)
````

See also for more details: <a href="https://www.zoho.com/crm/help/api/">Zoho CRM API</a>.

License
-------

Zoho Plugin is offered under an [MIT license](http://www.opensource.org/licenses/mit-license.php).

Copyright
---------

2014 Rafael Queiroz
