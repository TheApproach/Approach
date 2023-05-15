<?php
namespace Approach\Service\MySQL{

use \Approach\nullstate;
use \Approach\paths;
use \Approach\Scope;
use \Approach\Render;
use \Fiber;
use \Exception;
use \Approach\Service\Service;
use \Approach\Aspect;

enum opt:int{
	case SELECT = 1;
	case FROM = 2;
	case WHERE = 3;
	case ORDER_BY = 4;
	case GROUP_BY = 5;
	case LIMIT = 6;
	case OFFSET = 7;
	case CALL = 8;
}

class DataType
{
	const INTEGER = 0;
	const FLOAT = 1;
	const BOOLEAN = 2;
	const STRING = 3;
	const DATETIME = 4;
	const TIMESTAMP = 4;
	const ENUMERATION = 5;

	const TYPES = [
		self::INTEGER => 'integer',
		self::FLOAT => 'float',
		self::BOOLEAN => 'boolean',
		self::STRING => 'string',
		self::DATETIME => 'datetime',
		self::TIMESTAMP => 'timestamp',
		self::ENUMERATION => 'enumeration',
	];
}

class ComparisonOperation
{
	const RANGE = 0;
	const COMPARE = 1;
	const WILDCARD = 2;
	const RELATIVE = 3;
}

class TimeUnit
{
	const DAY = 0;
	const MONTH = 1;
	const YEAR = 2;
	const HOUR = 3;
	const MINUTE = 4;
	const SECOND = 5;
	const MILLISECOND = 6;
	const MICROSECOND = 7;
	const NANOSECOND = 8;
}

class Expression
{
	const OPERATION = 0;
	const MIN = 1;
	const MAX = 2;
	const VALUE = 3;
	const PATTERN = 4;
	const INTERVAL = 5;
	const UNIT = 6;
}



trait connectability
{
	protected $state  = nullstate::undefined;
	protected $status = nullstate::undefined;
	protected static $connection = [];

	protected $host;
	protected $username;
	protected $password;
	protected $source;

	// method to establish the connection
	public static function connect($host, $username, $password, $source): nullstate
	{
		// create a new fiber
		$fiber = new Fiber(
			function ($host, $username, $password, $source): nullstate {
				try {
					// create a new MySQLi connection
					$connection = new \mysqli($host, $username, $password, $source);

					// if there was an error connecting, throw an exception
					if ($connection->connect_error) {
						throw new Exception('Error connecting to MySQL server: ' . $connection->connect_error);
					}

					// set the connection to use UTF-8
					$connection->set_charset('utf8');

					// store the connection in the fiber
					self::class::$connection[] = $connection;

				} catch (Exception $e) {
					// store the exception in the fiber
					self::class::$status = $e;
					Fiber::suspend(nullstate::ambiguous);
				}
				return nullstate::nolongernull;
			}
		);
		if ($fiber->isSuspended() ) {
			$fiber->resume(nullstate::ambiguous);
		}
		// start the fiber
		$state = $fiber->start($host, $username, $password, $source);

		// return the fiber
		return $state;
	}
}
class Source extends Service // implements connectable, sourceable
{
	use connectability;

	
	

	// constructor to set up the connection parameters
	public function __construct($host, $username, $password, $source)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->source = $source;

		$this->state  = nullstate::ambiguous;
		$this->status = self::connect($host, $username, $password, $source);
	}

}



class URI extends Render\Node{

	// 'mysql', 'mariadb', 'mssql', 'pgsql', 'sqlite', 'oracle', 'dblib', 'odbc', 'ifmx', 'fbd', 'ibm', 'informix', 'sqlsrv', '4D'];
	// the MySQL connection
	protected static $connection;
	protected static $namespace = '\Approach\Service\MySQL';
	protected Render\Stream $ancestor;
	protected Render\Stream $parent;
	protected Render\Stream $links;

	// the query string
	protected $query;
	
	// the list of operations and their corresponding syntax
	public const SELECT = 0;
	public const FROM = 1;
	public const WHERE = 2;
	public const ORDER_BY = 3;
	public const LIMIT = 4;
	public const CALL = 5;
	
	const OPERATIONS = [
	  self::SELECT => 'SELECT',
	  self::FROM => 'FROM',
	  self::WHERE => 'WHERE',
	  self::ORDER_BY => 'ORDER BY',
	  self::LIMIT => 'LIMIT',
	  self::CALL => 'CALL',
	];
	
	// method to add a clause to the query
	public function clause($operation, ...$values) {
		static $meta = static::$namespace . '\\meta';

		$syntax = self::OPERATIONS[$operation];
		$this->query .= $syntax . ' ';
	
		// bind the values to the query
		if (!empty($values)) {
			// create a string of types for bind_param()
			$types = '';
			foreach ($values as $value) {
					$types .= getType($value);
			}

			// bind the values to the query
			self::$connection->bind_param($types, ...$values);
		}
	  
		return $this;
	}

	// method to translate the path-style string into clause nodes
	public function translate(?\Stringable $path=null)
	{
		$path = $path ?? $this->path;

		// split the path string into its parts after trimming leading and trailing characters
		// Trimmed: slashes, colons, spaces, tabs, newlines, carriage returns, and null bytes
		$parts = explode('/', trim($path,':/\\ \r\n\t\v\0'));
		
		
		// iterate over the parts and match them to the operations
		foreach ($parts as $part){
			$expressions = self::parse_path_segment($part);
			self::clause( ...$expressions);
		}
	}

	public function infer_type($field)
	{
		if (is_int($field)) {
			return DataType::INTEGER;
		} else if (is_float($field)) {
			return DataType::FLOAT;
		} else if (is_bool($field)) {
			return DataType::BOOLEAN;
		} else if (is_string($field)) {
			return DataType::STRING;
		} else if (is_a($field, 'DateTime')) {
			return DataType::DATETIME;
		} else {
			// assume the field is an enumeration label
			return DataType::ENUMERATION;
		}
	}


	// method to parse the URI path components into expressions
	function parse_path_segment($part)
	{
		$results = array();

		$remainder = '';
		$parts = explode('[', $part);
		$part = $parts[0];
		$remainder = $parts[1] ?? $remainder;

		// if there are multiple parts, the first part is the resource name
		if (count($parts) > 1) {
			$results['resource'] = $parts[0];

			// parse the selection criteria
			$criteria = $parts[1];
			$criteria = explode(']', $criteria)[0]; // remove the closing ']'
			$remainder = $criteria[1] ?? $remainder;
			$criteria = explode(',', $criteria); // split into individual criteria
			foreach ($criteria as $criterion) {

				// split the criterion into field and value
				$fieldValue = explode(':', $criterion);
				$field = trim($fieldValue[0]);
				$value = trim($fieldValue[1]);
				// add the field and value to the results array
				$results[$field] = $value;
			}
		} else {
			// if there is only one part, it is the resource name
			$results['resource'] = $part;
		}

		// return the results array
		return $results;
	}


	protected function profile_criterion($field, $value) {
		// define an array to hold the results
		$results = array();
		
		// determine the data type of the field
		$dataType = self::infer_type($field);
		
		// determine the comparison operation based on the data type
		switch ($dataType) {
			case DataType::INTEGER:
			case DataType::FLOAT:
				// check for range comparisons
				if (preg_match('/^(\d+\.\.)(\d+)$/', $value, $matches)) {
					$results[Expression::OPERATION] = ComparisonOperation::RANGE;
					$results[Expression::MIN] = $matches[1];
					$results[Expression::MAX] = $matches[2];
				} else {
					// assume a simple comparison
					$results[Expression::OPERATION] = ComparisonOperation::COMPARE;
					$results[Expression::VALUE] = $value;
				}
				break;
			case DataType::BOOLEAN:
				// assume a simple comparison
				$results[Expression::OPERATION] = ComparisonOperation::COMPARE;
				$results[Expression::VALUE] = $value;
				break;
			case DataType::STRING:
				// check for wildcard comparisons
				if (strpos($value, '*') !== false) {
					$results[Expression::OPERATION] = ComparisonOperation::WILDCARD;
					$results[Expression::PATTERN] = $value;
				} else {
					// assume a simple comparison
					$results[Expression::OPERATION] = ComparisonOperation::COMPARE;
					$results[Expression::VALUE] = $value;
				}
				break;
			case DataType::DATETIME:
				// check for relative comparisons
				if (preg_match('/^(-?\d+)([dmy])$/', $value, $matches)) {
					$results[Expression::OPERATION] = ComparisonOperation::RELATIVE;
					$results[Expression::INTERVAL] = $matches[1];
					$results[Expression::UNIT] = $matches[2];
				} else {
					// assume a simple comparison
					$results[Expression::OPERATION] = ComparisonOperation::COMPARE;
					$results[Expression::VALUE] = $value;
				}
				break;
			case DataType::ENUMERATION:
				// assume a simple comparison
				$results[Expression::OPERATION] = ComparisonOperation::COMPARE;
				$results[Expression::VALUE] = $value;
				break;
		}
		
		return $results;
	}


	
	
	// method to generate the final query URL
	public function getUrl()
	{
		// get the table name from the meta class
		$meta = get_called_class()::meta;
		$tableName = $meta::name;

		// return the final URL
		return $this->baseUrl . $tableName . '?query=' . urlencode($this->query);
	}

	// method to execute the query and return the result
	public function getResult()
	{
		// create a new fiber
		go(function () {
			// create a new query object
			$query = new Query(self::$connection, $this->query);

			// execute the query and return the result
			$result = yield $query->execute();
			return $result;
		});
	}
}

// meta class
abstract class meta
{

	// the table name
	const name = 0;
	const type = 1;
	const length = 2;
	const nullable = 3;
	const default = 4;


	// the table fields
	const field = [
		'id' => 'int',
		'name' => 'varchar(255)',
		'age' => 'int',
	];

	// the table indexes
	const INDEXES = [
		'PRIMARY KEY' => ['id'],
		'INDEX' => ['name'],
	];


	// helper method to get the correct type for a value
	public static function getType($value)
	{
		$field = self::field[$value][DataType::class];
		if ($field['type'] === DataType::INTEGER) {
			return 'i';
		} elseif ($field['type'] === 'double') {
			return 'd';
		} elseif ($field['type'] === 'string') {
			return 's';
		} else {
			return 'b';
		}
	}

	// helper method to get the field for a value
	public static function getField($value)
	{
		$meta = get_called_class()::meta;
		foreach ($meta::FIELDS as $fieldName => $fieldType) {
			if ($fieldName === $value) {
				return ['name' => $fieldName, 'type' => $fieldType];
			}
		}
	}


}

// example usage
use MySQL\myHost\myDatabase\myTable\QB;
$connection = new Source('localhost', 'username', 'password', 'source');
$connection->connect();

$builder = new QB2('http://myHost/myDatabase/myTable', $connection);
$builder
	->clause(QB::SELECT, 'id', 'name', 'age')
	->clause(QB::FROM, 'myTable')
	->clause(QB::WHERE, 'name', '=', 'John Doe')
	->clause(QB::ORDER_BY, 'name')
	->clause(QB::LIMIT, 10);
$url = $builder->getUrl();
$result = $builder->getResult();


}

?>


