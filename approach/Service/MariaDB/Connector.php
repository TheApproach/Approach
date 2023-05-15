<?php

namespace Approach\Service\MariaDB;

use \Approach\Resource\Resource;
use \Approach\Resource\Aspect\aspects;
use \Approach\nullstate;
use \Approach\Service\Service;
use \Approach\Service\flow;
use \Approach\Service\format;
use \Approach\Service\target;

use \Approach\Render\PHP\Concepts;
use \Approach\Resource\Aspect;
use \Approach\Resource\MariaDB\Server;
use \MySQLi;
use \Stringable;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function SavePHP(object $dataObject)
{
    $filename = $dataObject->filename;
    $php = $dataObject->php;
    $php = '<?php' . PHP_EOL . $php;

    foreach ($dataObject->namespace as $namespace)
    {
        $php .= 'namespace ' . $namespace . ';' . PHP_EOL;
    }
    $php .= PHP_EOL;
    foreach ($dataObject->use as $use)
    {
        $php .= 'use ' . $use . ';' . PHP_EOL;
    }
    $php .= PHP_EOL;
    foreach (aspects::cases() as $enum)
    {
        $php .= 'enum ' . $enum->name . PHP_EOL;
        $php .= '{' . PHP_EOL;
        foreach ($dataObject[$enum->name]->cases as $case)
        {
            $php .= "\t" . 'case ' . $case . ';' . PHP_EOL;
        }
        $php .= '}' . PHP_EOL;
        $php .= PHP_EOL;
    }
}



class Connector extends Service
{
    use connectivity;

    public function __construct(
        public flow $flow = flow::in,
        public bool $auto_dispatch = false,
        public ?format $format_in = format::json,
        public ?format $format_out = format::json,
        public ?target $target_in = target::file,
        public ?target $target_out = target::file,
        public $input = null,
        public $output = null,
        public mixed $metadata = [],
        public bool $register_connection = true
    )
    {
        parent::__construct($flow, $auto_dispatch, $format_in, $format_out, $target_in, $target_out, $input, $output, $metadata);
    }

    /**
     * Create or update a Resource definition from a MariaDB database source
     * 
     * @param Resource $which	Updates all tables in the database if null
     * 
     */
    public function discover(null|Resource $which): nullstate
    {
		foreach( $this->nodes as $server){
			$server->discover();
		}
        $schemas = $this->inventory();
        $this->manifest_fields($schemas);
        return nullstate::defined;
    }

    private function fetchSchemaNames()
    {
        $conn = false;
    }
    public function override($sql): ?array
    {
        $result = [];
        try
        {
            $result = $this->connection->query($sql);
        }
        catch (\Exception $e)
        {
            return null;
        }
        return $result;
    }

	/**
	 * Fetches a list of all available database names on the server
	 */
    private function inventory(): array
    {
		$sql = 'SHOW DATABASES;';
		$result = [];
		try
		{
			$result = $this->connection->query($sql);
		}
		catch (\Exception $e)
		{
			return [];
		}        
        return $result;
    }

    private function get_indices($table)
    {
        $sql = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "' . $table . '";';
        $indices = 0; // TODO: YOU STOPPED HERE (with the generator/manifest)

        return [];
    }

    public function manifest_fields(array $schemas)
    {
        foreach ($schemas as $current_db => $spread)
            foreach ($spread as $table => $columns)
            {
                //Cross-Database Discrepency : MySQL uses quotes, MSSQL uses N
                $sql = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "' . $table . '";';
                $indices = self::get_indices($table);

                $sql = 'SELECT * FROM INFORMATION_SCHEMA.VIEW_COLUMN_USAGE WHERE `VIEW_NAME` = "' . $table . '";';
                $keyProperties = []; //self::load('mariadb://db-00.system-00.suitespace.corp/INFORMATION_SCHEMA.VIEW_COLUMN_USAGE');

                $dObj = new \stdClass();

                //	var_dump($keyProperties);
                foreach ($indices as $row)
                {
                    $str = explode('_', $row->data['CONSTRAINT_NAME']);
                    //		if($table == 'compositions'){ var_export($row); }
                    if ($str[0] == 'PRIMARY')
                        $dObj->PrimaryKey = $row->data['COLUMN_NAME'];
                    else
                        $dObj->ForeignKey[$row->data['COLUMN_NAME']] = [$row->data['REFERENCED_TABLE_SCHEMA'], $row->data['REFERENCED_TABLE_NAME'], $row->data['REFERENCED_COLUMN_NAME']];
                }

                $t = array();
                foreach ($keyProperties as $View)
                {
                    if ($View === reset($keyProperties))
                    {
                        $t = $spread[$table];
                        $spread[$table] = array();
                    }
                    $spread[$table][$View->data['TABLE_NAME']][$View->data['COLUMN_NAME']] = array_merge($spread[$View->data['TABLE_NAME']][$View->data['COLUMN_NAME']], $View->data);
                }

                $dObj->Columns = $spread[$table];
                $dObj->table = $table;

                $classpath = '';
                foreach ($spread[$table] as $column)
                {
                    $classpath = 'schema/' . $current_db;   //wth?
                    break;
                }

                SavePHP($dObj);//,  $classpath);
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
