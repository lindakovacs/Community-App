<?php

require '../api/php_curl.php';
require '../api/connection.php';

class PL_HTTP_Connection_Test extends PHPUnit_Framework_TestCase {

	public function test_endpoints() {
		$connection = new PL_HTTP_Connection('xxxinvalidxxx');
		$response = $connection->WHOAMI();
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->LOCATIONS();
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->ATTRIBUTES();
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->SEARCH_LISTINGS();
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->SEARCH_AGGREGATE('xxxfieldxxx');
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->GET_LISTING('xxxlistingxxx');
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->CREATE_LISTING('xxxdataxxx');
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->UPDATE_LISTING('xxxlistingxxx', 'xxxdataxxx');
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);

		$response = $connection->DELETE_LISTING('xxxlistingxxx');
		$this->assertInstanceOf('stdClass', $response);
		$this->assertObjectHasAttribute('code', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertEquals($response->code, 100);
	}

	/** @depends test_endpoints */
	public function test_GET_WHOAMI_returns_account_info() {
		$connection = new PL_HTTP_Connection('nhuRyCXHfB2ccbE1A97X1U2pHwwWKghFPt1cClehV7rrixTKmZdECxNxWn1bQt9d1lahudsFPj2JPzE1fK00GQaa');
		$whoami = $connection->GET_WHOAMI();
		$this->assertInstanceOf('stdClass', $whoami);
		$this->assertObjectHasAttribute('id', $whoami);
		$this->assertObjectHasAttribute('email', $whoami);
		$this->assertEquals($whoami->email, 'developer@placester.com');
	}

	/** @depends test_endpoints */
	public function test_GET_LOCATIONS_returns_locations() {
		$connection = new PL_HTTP_Connection('nhuRyCXHfB2ccbE1A97X1U2pHwwWKghFPt1cClehV7rrixTKmZdECxNxWn1bQt9d1lahudsFPj2JPzE1fK00GQaa');
		$locations = $connection->GET_LOCATIONS();
		$this->assertInstanceOf('stdClass', $locations);
		$this->assertObjectHasAttribute('id', $locations);
		$this->assertObjectHasAttribute('email', $locations);
		$this->assertEquals($locations->email, 'developer@placester.com');
	}
}
