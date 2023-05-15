<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;

use Approach\Scope;
use Approach\path;
use Approach\Resource;
use Approach\Service;


class ResourceCest
{
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
