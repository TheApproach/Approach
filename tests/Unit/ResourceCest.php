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
	public function checkResourceParse(UnitTester $I): void
	{
		$url = "MariaDB://db.host/instances[rate gt 1000]/myDatabase/myTable[! price le 250 $ 5 AND id eq 1, status: active, updated: 12-31-2022][id, name].getFile()?hello=world";
		$r = Resource::parseUri($url);
		// var_export($r);
		$I->assertEquals($r['result']['scheme'], "MariaDB");
	}

	public function emptyFind(UnitTester $I)
	{
		$result = (new Resource(''))->find('');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof Resource
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
			$result instanceof Resource
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
			$result instanceof Resource
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
			$result instanceof Resource
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]
				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]
				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					2 => [
						'type' => 'c',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]

				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]
				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]
				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					2 => [
						'type' => 'c',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]

				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]
				],
				'query_string' => []
			) 
		);
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
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]
				],
				'query_string' => []
			) 
		);
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

	public function FindQueryString1(UnitTester $I)
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

	public function FindQueryString2(UnitTester $I)
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

	public function FindQueryString3(UnitTester $I)
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

	public function FindCriteriaCheck1(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[0]/b[100]/c[1000][0]/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'int',
								'token' => 0
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [
							0 => [
								'type' => 'int',
								'token' => 100
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					2 => [
						'type' => 'c',
						'criterias' => [
							0 => [
								'type' => 'int',
								'token' => 1000
							],
							1 => [
								'type' => 'next_block',
								'token' => ']['
							],
							2 => [
								'type' => 'int',
								'token' => 0
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]

				],
				'query_string' => []
			) 
		);
	}

	public function FindCriteriaCheck2(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[x]/b[x,y]/c[x,y,z]/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'comma',
								'token' => ','
							],
							2 => [
								'type' => 'identifier',
								'token' => 'y'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					2 => [
						'type' => 'c',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'comma',
								'token' => ','
							],
							2 => [
								'type' => 'identifier',
								'token' => 'y'
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => 'z'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]

				],
				'query_string' => []
			) 
		);
	}

	public function FindCriteriaCheck3(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[x: 1..2]/b[x>1,y < 2]/c[x == 01-01-1970][y != 0][z  <=5]/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'colon',
								'token' => ':'
							],
							2 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							3 => [
								'type' => 'range',
								'token' => '1..2'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					1 => [
						'type' => 'b',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'greater_than',
								'token' => '>'
							],
							2 => [
								'type' => 'int',
								'token' => 1
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => 'y'
							],
							5 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							6 => [
								'type' => 'less_than',
								'token' => '<'
							],
							7 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							8 => [
								'type' => 'int',
								'token' => 2
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					],
					2 => [
						'type' => 'c',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							2 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							3 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							4 => [
								'type' => 'date',
								'token' => '01-01-1970'
							],
							5 => [
								'type' => 'next_block',
								'token' => ']['
							],
							6 => [
								'type' => 'identifier',
								'token' => 'y'
							],
							7 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							8 => [
								'type' => 'not_equal_to',
								'token' => '!='
							],
							9 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							10 => [
								'type' => 'int',
								'token' => 0
							],
							11 => [
								'type' => 'next_block',
								'token' => ']['
							],
							12 => [
								'type' => 'identifier',
								'token' => 'z'
							],
							13 => [
								'type' => 'whitespace',
								'token' => '  '
							],
							14 => [
								'type' => 'less_equal_to',
								'token' => '<='
							],
							15 => [
								'type' => 'int',
								'token' => 5
							]

						],
						'parsed_csv' => null,
						'sub_delim_part' => null
					]

				],
				'query_string' => []
			) 
		);
	}


	public function FindCriteriaAndSubDelimCheck(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[x];a/b[x,y];whatever/c[x,y,z];/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => 'a'
					],
					1 => [
						'type' => 'b',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'comma',
								'token' => ','
							],
							2 => [
								'type' => 'identifier',
								'token' => 'y'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => 'whatever'
					],
					2 => [
						'type' => 'c',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'comma',
								'token' => ','
							],
							2 => [
								'type' => 'identifier',
								'token' => 'y'
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => 'z'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => ''
					]

				],
				'query_string' => []
			) 
		);
	}


	public function FindStringLiteral(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[x == "abc"];a/b[x=="abc",y == \'cba\'];whatever/c[x,y,z];/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							2 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							3 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							4 => [
								'type' => 'string',
								'token' => '"abc"'
							]

						],
						'parsed_csv' => null,
						'sub_delim_part' => 'a'
					],
					1 => [
						'type' => 'b',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							2 => [
								'type' => 'string',
								'token' => '"abc"'
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => 'y'
							],
							5 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							6 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							7 => [
								'type' => 'whitespace',
								'token' => ' '
							],
							8 => [
								'type' => 'string',
								'token' => "'cba'"
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => 'whatever'
					],
					2 => [
						'type' => 'c',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'x'
							],
							1 => [
								'type' => 'comma',
								'token' => ','
							],
							2 => [
								'type' => 'identifier',
								'token' => 'y'
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => 'z'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => ''
					]

				],
				'query_string' => []
			) 
		);
	}

	public function FindCheckComparisonOperators(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[a==0,b!=1,c<2,d>3,e<=4,f>=5]/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => 'a'
							],
							1 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							2 => [
								'type' => 'int',
								'token' => '0'
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => 'b'
							],
							5 => [
								'type' => 'not_equal_to',
								'token' => '!='
							],
							6 => [
								'type' => 'int',
								'token' => '1'
							],
							7 => [
								'type' => 'comma',
								'token' => ','
							],
							8 => [
								'type' => 'identifier',
								'token' => 'c'
							],
							9 => [
								'type' => 'less_than',
								'token' => '<'
							],
							10 => [
								'type' => 'int',
								'token' => '2'
							],
							11 => [
								'type' => 'comma',
								'token' => ','
							],
							12 => [
								'type' => 'identifier',
								'token' => 'd'
							],
							13 => [
								'type' => 'greater_than',
								'token' => '>'
							],
							14 => [
								'type' => 'int',
								'token' => '3'
							],
							15 => [
								'type' => 'comma',
								'token' => ','
							],
							16 => [
								'type' => 'identifier',
								'token' => 'e'
							],
							17 => [
								'type' => 'less_equal_to',
								'token' => '<='
							],
							18 => [
								'type' => 'int',
								'token' => '4'
							],
							19 => [
								'type' => 'comma',
								'token' => ','
							],
							20 => [
								'type' => 'identifier',
								'token' => 'f'
							],
							21 => [
								'type' => 'greater_equal_to',
								'token' => '>='
							],
							22 => [
								'type' => 'int',
								'token' => '5'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null 
					]
				],
				'query_string' => []
			) 
		);
	}

	public function FindCheckNumberIdentifiers(UnitTester $I)
	{
		$result = (new Resource(''))->find('MariaDB://MyServer/a[1==1,2==1]/');

		$I->assertTrue(
			$result instanceof \Approach\nullstate ||
			$result instanceof \Approach\Resource\Resource
		);

		if($result instanceof \Approach\nullstate){
			$I->outputError( 'Parsing empty URL was successful, should have failed' );
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals(
			$result->tmp_parsed_url,
			array(
				'protocol' => 'MariaDB',
				'host' => 'MyServer',
				'parts' => [
					0 => [
						'type' => 'a',
						'criterias' => [
							0 => [
								'type' => 'identifier',
								'token' => '1'
							],
							1 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							2 => [
								'type' => 'int',
								'token' => '1'
							],
							3 => [
								'type' => 'comma',
								'token' => ','
							],
							4 => [
								'type' => 'identifier',
								'token' => '2'
							],
							5 => [
								'type' => 'equal_to',
								'token' => '=='
							],
							6 => [
								'type' => 'int',
								'token' => '1'
							]
						],
						'parsed_csv' => null,
						'sub_delim_part' => null 
					]
				],
				'query_string' => []
			) 
		);
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
