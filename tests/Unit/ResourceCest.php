<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;

use Approach\Scope;
use Approach\path;
use Approach\Resource\Resource;
use Approach\Service;

/**
 * 	This is a Codeception Unit Test for the Approach\Resource class.
 * 
 * 	@package    Approach\Tests\Unit
 * 	@subpackage Approach\Tests\Unit\Resource
 * 	@object     Approach\Tests\Unit\Resource\Resouce
 * 
 * 	@internal	
 *		[ ] This class may be tested for functionality in the future.
 * 		[ ] This class may be tested for security in the future.
 * 		[ ] This class may be tested for documentation in the future.
 * 		[ ] This class may be tested for performance in the future.
 * 
 * 
 * 	@dependencies
 * 		[ ] \Approach\Resource
 * 		[ ] \Approach\Service
 * 		[ ] \Approach\Scope
 * 		[ ] \Approach\path
 * 
 * 	@license    Apache 2.0
 * 	@version    0.0.1-alpha
 * 	@since      2024-01-26
 * 	@see        \Approach\Resource
 * 
 */

class ResourceCest
{
	/**
	 * @var \Approach\Resource
	 * @var \Approach\Service
	 * @var \Approach\Scope
	 */

    private Scope $scope;

    public function _before(UnitTester $I)
    {
        $path_to_project = __DIR__ . '/../..';
        $path_to_approach = __DIR__ . '/../../approach/';
        $path_to_support = __DIR__ . '/../../support/';

        $this->scope = new Scope(
            path: [
                path::project->value        =>  $path_to_project,
                path::installed->value      =>  $path_to_approach,
                path::support->value        =>  $path_to_support,
            ],
        );
    }

	// tests

	public function emptyFind(UnitTester $I)
	{
		$result = (new Resource(''))->find('');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource
		);

		if(!($result instanceof \Approach\nullstate)){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result, \Approach\nullstate::ambiguous );
	}

	public function FindRelativePath(UnitTester $I)
	{
		$result = (new Resource(''))->find('/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource
		);

		if(!($result instanceof \Approach\nullstate)){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result, \Approach\nullstate::ambiguous );
	}

	public function FindNoProtocol(UnitTester $I)
	{
		$result = (new Resource(''))->find('://');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource
		);

		if(!($result instanceof \Approach\nullstate)){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result, \Approach\nullstate::ambiguous );
	}

	public function FindProtocolOnly(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource
		);

		if(!($result instanceof \Approach\nullstate)){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result, \Approach\nullstate::ambiguous );
	}

	public function FindMinimumPass(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => [], 'query_string' => []) );
	}

	public function FindA(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a'], 'query_string' => []) );
	}

	public function FindAB(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a/b');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a', 'b'], 'query_string' => []) );
	}

	public function FindABC(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a/b/c');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a', 'b', 'c'], 'query_string' => []) );
	}

	public function FindATrailingSlash(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a'], 'query_string' => []) );
	}

	public function FindABTrailingSlash(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a/b/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a', 'b'], 'query_string' => []) );
	}

	public function FindABCTrailingSlash(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a/b/c/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a', 'b', 'c'], 'query_string' => []) );
	}

	public function FindLotsOfSlashes1(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a///////');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a'], 'query_string' => []) );
	}

	public function FindLotsOfSlashes2(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer///////////////////a///////');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => ['a'], 'query_string' => []) );
	}

	public function FindLotsOfSlashes3(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://////////////////MyServer///////////////////a///////');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if(!($result instanceof \Approach\nullstate)){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result, \Approach\nullstate::ambiguous );
	}

	public function FindLotsOfSlashes4(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer//////////////////////////');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => [], 'query_string' => []) );
	}

	public function FindQyeryString1(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/?');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => [], 'query_string' => []) );
	}

	public function FindQyeryString2(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/?a=b');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => [], 'query_string' => ['a' => 'b']) );
	}

	public function FindQyeryString3(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/?a=b%20c');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals( $result->tmp_parsed_url, array('protocol' => 'MariaDB', 'host' => 'MyServer',  'parts' => [], 'query_string' => ['a' => 'b c']) );
	}

}



/**
 * 
 * 
 * 
 * 
Scope::Services['data']= new Service\MariaDB(...);

$id_list = Resource::find(
  'MariaDB://MyData/products[ListPrice: 100000..200000]/ListingId'
);

 *********************************
          Equivalent
 *********************************


$data = Service::$protocols['MariaDB']['MyServer'];
$id_list = $data['MyData']['products']['ListPrice: 100000..20000'];


/*********************************
          Equivalent
/*********************************


$rows = $data['MyData']['products'];
$id_list = $rows['ListPrice: 100000..20000, Beds: 2..5']['ListingId'];


 *********************************
          Equivalent
 *********************************
use \MyProject\Resource\MyServer\MyData\products;
use \Approach\Resource\Aspects;

$id_list = 
  Resource::find( 'MariaDB://MyServer' )
    ->find( 'MyData' )
    ->find( 'products' )
    ->sift(
      products\field::ListPrice,		// Interpretation of second parameter depends on the first parameter
      [									// For a field, this is type dependent. A container may be a JSON decode of some more complex query
        $min, // null for unlimited
        $max  // optional, null for unlimited
      ]
    ) 
    ->sort(
       products\field::ListPrice,
       Resource\mode::descending
    )
	->load()
;


 ********************************
          Equivalent
 *********************************


$myAspect = products\Aspect::By( 
	type:  Aspects::field, 								// equivalent to LoadObject($type, [])
	pick: 'ListingId',									// equivalent to LoadObject('',[ 'select'=> 'ListingId'])
	sift: [												// For MySQL, sift is equivalent to the terms of the WHERE clause
		Aspects::field,
		products\field::ListPrice,
		[
			$min, // null for unlimited
			$max  // optional, null for unlimited
		]
	],
	sort: [												// For MySQL, sort is equivalent to the terms of the ORDER BY
		products\field::ListPrice,
		Resource\mode::descending
	]
	weigh:  Aspect, 									// For MySQL, weigh is equivalent to augmenting the ORDER BY clause with a secondary sort
	divide: Aspect,										// For MySQL, divide is equivalent to GROUP BY
	filter: function(self),								// Post processing filter PHP function
	operations: NULL,									// Optionally bind stored procedures (later iteration)
);

$id_list = Resource::find( $myAspect );


 */
