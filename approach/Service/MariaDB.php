<?php
namespace Approach\Service;
use \Approach\Resource\Resource;
use \Approach\Render\PHP\Concepts;
use \Approach\Resource\aspects;
use \Approach\nullstate;
use \mysqli;
use \Stringable;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function SavePHP(object $dataObject){
	$filename = $dataObject->filename;
	$php = $dataObject->php;
	$php = '<?php' . PHP_EOL . $php;

	foreach($dataObject->namespace as $namespace){
		$php .= 'namespace ' . $namespace . ';' . PHP_EOL;
	}
	$php .= PHP_EOL;
	foreach($dataObject->use as $use){
		$php .= 'use ' . $use . ';' . PHP_EOL;
	}
	$php .= PHP_EOL;
	foreach(aspects::cases() as $enum){
		$php .= 'enum ' . $enum->name . PHP_EOL;
		$php .= '{' . PHP_EOL;
		foreach($dataObject[$enum->name]->cases as $case){
			$php .= "\t" . 'case ' . $case . ';' . PHP_EOL;
		}
		$php .= '}' . PHP_EOL;
		$php .= PHP_EOL;
	}
	

}

trait Maria_connectivity
{
	public $connection;
	static $current_statement;

	public function connect($host, $user, $password, $schema, $port = null, $socket = null, ...$args): nullstate
	{
		$state = nullstate::ambiguous;
		try
		{
			$this->connection = new mysqli($host, $user, $password, $schema, $port, $socket);
			$state = $this->connection->connect_errno ? nullstate::undefined : nullstate::defined;
		}
		catch (\Exception $e)
		{
			// TODO: Emit error message renderable
			$this->connection->close();
			$state = nullstate::ambiguous;
		}
		return $state;
	}

	/**
	 * Close open connections to the database
	 * 
	 * @return bool|null
	 */
	public function disconnect($which=null, $index=null): nullstate
	{
		$state = nullstate::ambiguous;
		try
		{
			// User selected a specific connection to close
			$this->connection->close();
			$state = nullstate::empty;
		}
		catch (\Exception $e)
		{
			// TODO: Emit error message renderable
			$state = nullstate::stalled;
		}
		return $state;
	}

	public function send( $query, $which=null, $index=null )
	{
		$state = nullstate::ambiguous;
		try
		{
			$result = static::$current_statement->execute($query);
		}
		catch (\Exception $e)
		{

		}
		finally{
			$state = $result ? nullstate::defined : nullstate::undefined;
		}
		return $state;
	}

	public function recieve()
	{
	}
}

/**
 * 
 * 
 * 
 * 
Scope::Services['data']= new Resource\Galera(...);

$id_list = Resource::find(
  'data:\\MLS_DB\listings[ListPrice: 100000..200000]\ListingId'
);

*********************************
          Equivalent
*********************************


$data = Scope::Services['data']['MLS_DB'];
$id_list = $data['listings']['ListPrice: 100000..20000'];


/*********************************
          Equivalent
/*********************************


$rows = $data['MLS_DB']['listings'];
$id_list = $rows['ListPrice: 100000..20000, Beds: 2..5']['ListingId'];


*********************************
          Equivalent
*********************************
use \SuiteUX\Data\MLS_DB\listings;
use \Approach\Resource\Aspects;

$id_list = 
  Resource::find( 'data' )
    ->find( 'MLS_DB' )
    ->find( 'listings' )
    ->sift([
      Aspects::field,
      listings\field::ListPrice,
      [
        $min, // null for unlimited
        $max  // optional, null for unlimited
      ]
    ) 
    ->sort( [
       listings\field::ListPrice,
       Resource\mode::descending
    ]
	->load()
;


********************************
          Equivalent
*********************************


$myAspect = listings\Aspect::By( 
	type:  Aspects::field, 
	pick: 'ListingId',
	sift: [
		Aspects::field,
		listings\field::ListPrice,
		[
			$min, // null for unlimited
			$max  // optional, null for unlimited
		]
	],
	sort: [
		listings\field::ListPrice,
		Resource\mode::descending
	]
	weigh:     Aspect, 
	divide: Aspect,
	filter: function(self),
);

$id_list = Resource::find( $myAspect );


*/



trait MariaDB_sourceability 
{

	public static $acquired = [];

	public static function aquire(Stringable|string $where, ...$options): ?Resource
	{
		return new Resource('/');
	}

	public static function pull(Stringable|string $where, ...$options): ?Resource
	{
		return self::aquire($where, ...$options);
	}

	public static function load(Stringable|string  $where, ...$options): ?Resource
	{
		$type = self::acquire($where)->find($where);
		$type::$cache[$where] =
			$type::$cache[$where]
			??
			self::pull($where, ...$options);

		return $type::$cache[$where];
	}

	public function save(Resource $where): ?bool
	{
		if ($where) return true;
		return null;
	}

	public function push(Resource $where): ?bool
	{
		if ($where) return true;
		return null;
	}

	public function release(Resource $where): ?bool
	{
		/*
			Rollback active transactions

			Close and drop temporary tables

			Unlock tables

			Reset session variables

			Close prepared statements (always happens with PHP)

			Close handler
		*/
		if (!in_array($where,self::$acquired)) return true;
		return null;
	}
}

class MariaDB extends Service
{
	use Maria_connectivity;

	public function __construct($host, $user, $password, $world, $port = null, $socket = null, ...$args)
	{
		$this->connect($host, $user, $password, $world, $port = null, $socket = null, ...$args);
	}

	/**
	 * Create or update a Resource definition from a MariaDB database source
	 * 
	 * @param Resource $which	Updates all tables in the database if null
	 * 
	 */
	public function discover(null|Resource $which):nullstate
	{
		$schemas = $this->inventory();		
		$this->manifest_fields($schemas);
		return nullstate::defined;
	}

	private function fetchSchemaNames()
	{
		$conn=false;
	}
	public function override($sql) :?array
	{
		$result = [];
		try
		{
			$result = $this->connection::query($sql);
		}
		catch (\Exception $e)
		{
			return null;
		}
		return $result;
	}
	private function inventory():array
	{
		$sql = 'SELECT DISTINCT TABLE_SCHEMA FROM INFORMATION_SCHEMA.COLUMNS';
		$schemas = $this->connection->query($sql);
		
		$result = [];
		foreach ($schemas as $schema) {
			$this->connection->prepare('SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ?');
			$this->connection->bind_param('s', $schema);
			$result[$schema] = $this->connection->get_results();

		}
		// $schemas[$SchemaRow->data['TABLE_SCHEMA']][$SchemaRow->data['TABLE_NAME']][$SchemaRow->data['COLUMN_NAME']] = $SchemaRow->data;
		return $result;
	}

	private function manifest_fields(array $schemas)
	{
		foreach ($schemas as $current_db => $spread)
			foreach ($spread as $table => $columns) {
				//Cross-Database Discrepency : MySQL uses quotes, MSSQL uses N
				$sql = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "' . $table . '";';
				$findKeys = self::load('mariadb://db-00.system-00.suitespace.corp/INFORMATION_SCHEMA.KEY_COLUMN_USAGE');

				$sql = 'SELECT * FROM INFORMATION_SCHEMA.VIEW_COLUMN_USAGE WHERE `VIEW_NAME` = "' . $table . '";';
				$keyProperties = self::load('mariadb://db-00.system-00.suitespace.corp/INFORMATION_SCHEMA.VIEW_COLUMN_USAGE');

				$dObj = new \stdClass();

				//	var_dump($keyProperties);
				foreach ($findKeys as $row) {
					$str = explode('_', $row->data['CONSTRAINT_NAME']);
					//		if($table == 'compositions'){ var_export($row); }
					if ($str[0] == 'PRIMARY')
					$dObj->PrimaryKey = $row->data['COLUMN_NAME'];
					else
						$dObj->ForeignKey[$row->data['COLUMN_NAME']] = [$row->data['REFERENCED_TABLE_SCHEMA'], $row->data['REFERENCED_TABLE_NAME'], $row->data['REFERENCED_COLUMN_NAME']];
				}

				$t = array();
				foreach ($keyProperties as $View) {
					if ($View === reset($keyProperties)) {
						$t = $spread[$table];
						$spread[$table] = array();
					}
					$spread[$table][$View->data['TABLE_NAME']][$View->data['COLUMN_NAME']] = array_merge($spread[$View->data['TABLE_NAME']][$View->data['COLUMN_NAME']], $View->data);
				}

				$dObj->Columns = $spread[$table];
				$dObj->table = $table;

				$classpath = '';
				foreach ($spread[$table] as $column) {
					$classpath = 'schema/' . $current_db;   //wth?
					break;
				}

				SavePHP($dObj,  $classpath);
			}
	}

/*	public function find(
		string $where 		= '/',
		?Aspect $sort		= null,
		?Aspect $weigh		= null,
		?Aspect $pick		= null,
		?Aspect $sift		= null,
		?Aspect $divide		= null,
		?callable $filter	= null,
		?string $as			= null
	): Resource|accessible|nullstate {

		return nullstate::ambiguous;
	}
*/
}