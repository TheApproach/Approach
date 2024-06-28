<?php

namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Resource\Aspect\discover;
use \Approach\Resource\discoverability as resource_discoverability;
use \Approach\nullstate;

// trait table_discoverability
// {

/**
 * aspects enum - defines the types of aspects Resource classes can have
 *				- defines the define_[aspect]() method for generating Aspect classes
 *
 * @package		Approach\Resource
 * @subpackage	MariaDB
 * @version		2.0.-1
 * @category	Aspect
 *
 */

class Table extends discover
{
	use resource_discoverability;

	public static function get_resource_directory()
	{
		// Get the directory child class is in
		$reflector = new \ReflectionClass(static::class);
		$directory = dirname($reflector->getFileName());

		// Remove the last directory from the path
		$directory = dirname($directory);

		return $directory;
	}

	private static function get_accessors($table, $connection)
	{
		// Get all accessors and keys for the table
		$sql = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "' . $table . '";';
		$result = $connection->query($sql);

		// Use fetch_assoc() to get an array of the results
		$accessors = [];
		while ($row = $result->fetch_assoc()) {
			$accessors[] = $row;
		}

		return [];
	}

	/**
	 * Get the list of fields for a MariaDB table (using MySQLi Object)
	 *
	 * @param string $name			The name of the table
	 * @param \MySQLi $connection	The MySQLi connection object
	 *
	 * @return array
	 */
	public static function get_field_list($table, $connection)
	{
		$fields = array();
		$sql = 'SHOW COLUMNS FROM ' . $table . ';';
		$result = $connection->query($sql);
		while ($row = $result->fetch_assoc()) {
			$fields[] = $row['Field'];
		}
		return $fields;
	}

	public static function get_table_definition($table, $connection)
	{
		$fields = array();
		$sql = 'SHOW FULL COLUMNS FROM ' . $table . ';';
		$result = $connection->query($sql);
		while ($row = $result->fetch_assoc()) {
			$which = $row['Field'];
			$fields[$which] = [
				'label' => $which,
				'type' => static::map_data_types($row['Type']),
				'default' => $row['Default'],
				'source_type' => $row['Type'],
				'source_default' => $row['Default'],
				'description' => $row['Comment'],
				'constraint' => !empty($row['Key']) ? $row['Key'] : '',
				'accessor' => false,
				'nullable' => strtolower($row['Null']) == 'yes' ? true : false,
			];

			$constraints = $fields[$which]['constraint'];
			if ($constraints == 'PRI') {
				$fields[$which]['primary_accessor'] = true;
			}
			if (!empty($constraints)) {
				$fields[$which]['accessor'] = true;
			}
			if (!empty($constraints) && !empty($row['Extra'])) {
				$fields[$which]['constraint'] = ', ' . $row['Extra'];
			} elseif (empty($constraints) && !empty($row['Extra'])) {
				$fields[$which]['constraint'] = $row['Extra'];
			}
		}
		return $fields;
	}

	/**
	 * Map MariaDB data types to PHP data types
	 *
	 * MariaDB/MySQL has many, very specific data types.
	 * We primarily care about the following internal generic types:
	 *
	 * - int
	 * - float
	 * - string
	 * - bool
	 * - date
	 * - time
	 * - datetime
	 * - timestamp
	 * - blob (expects a Render\Type in the description marked by <Render\Type>)
	 * - enum (string representing the chosen enum value)
	 * - set (relies on Render\Node's underlying __labeled_nodes / __node_labels by setting $node[$key] = true])
	 * - json (expects a Render\Type in the description marked by <Render\Type>, or defaults to a Render\Node with ->content set to the decoded json)
	 *
	 * @param string $type	The MariaDB data type
	 *
	 * @return string
	 */

	public static function map_data_types($type)
	{

		// remove any size or precision from the type
		$pos = strpos($type, '(');
		if ($pos !== false) {
			$type = substr($type, 0, $pos);
		}

		$r = '';
		$type = trim(strtolower($type));

		switch ( true ) {
			case (	is_int(strpos($type, 'tinyint') ) ):
			case (	is_int(strpos($type, 'smallint') ) ):
			case (	is_int(strpos($type, 'mediumint') ) ):
			case (	is_int(strpos($type, 'int') ) ):
			case (	is_int(strpos($type, 'bigint') ) ):		$r = 'int';				break;

			case (	is_int(strpos($type, 'decimal') ) ):
			case (	is_int(strpos($type, 'float') ) ):
			case (	is_int(strpos($type, 'double') ) ):		$r = 'float';			break;

			case (	is_int(strpos($type, 'binary') ) ):							 			# accounts for binary, varbinary
			case (	is_int(strpos($type, 'char') ) ):								 		# accounts for varchar, nchar, nvarchar
			case (	is_int(strpos($type, 'text') ) ):		$r = 'string'; 			break;	# accounts for tinytext, mediumtext, longtext..

			case (	is_int(strpos($type, 'blob') ) ):		$r =  'blob'; 			break;	# handed off to Service\Decoder::decode, accounts for all size of blob
			case (	is_int(strpos($type, 'datetime') ) ):	$r =  'datetime'; 		break;
			case (	is_int(strpos($type, 'timestamp') ) ):	$r =  'timestamp'; 		break;
			case (	is_int(strpos($type, 'date') ) ):		$r =  'date'; 			break;	# must occur after datetime
			case (	is_int(strpos($type, 'time') ) ):		$r =  'time'; 			break;	# must occur after datetime & timestamp
			case (	is_int(strpos($type, 'enum') ) ):		$r =  'enum'; 			break;
			case (	is_int(strpos($type, 'set') ) ):		$r =  'set'; 			break;
			case (	is_int(strpos($type, 'json') ) ):		$r =  'json'; 			break;
			case (	is_int(strpos($type, 'xml') ) ):		$r =  'xml'; 			break;
			default:										$r =  'string'; 		break;
		}

		return $r;
	}

	/**
	 * Add the approach_get_field_details() procedure to the database
	 * This procedure can be used to get the details of a field in a table
	 */

	public static function add_profiling_proceure($connection)
	{
		$procedure = <<<SQL
			DELIMITER //

			CREATE PROCEDURE IF NOT EXISTS `get_approach_field_profile` (
				IN `db_name` VARCHAR(255),
				IN `table_name` VARCHAR(255),
				IN `column_name` VARCHAR(255)
			)
			BEGIN
				-- First, get the column details
				SELECT
					TABLE_NAME,
					COLUMN_NAME,
					COLUMN_TYPE,
					IS_NULLABLE,
					COLUMN_DEFAULT,
					COLUMN_COMMENT,
					COLUMN_KEY,
					EXTRA,
					CHARACTER_SET_NAME,
					COLLATION_NAME,
					COLUMN_CATALOG,
					COLUMN_SCHEMA,
					TABLE_CATALOG,
					TABLE_SCHEMA,
					TABLE_ROWS
				FROM
					INFORMATION_SCHEMA.COLUMNS
				WHERE
					TABLE_SCHEMA = db_name AND
					TABLE_NAME = table_name AND
					COLUMN_NAME = column_name;

				-- Then, get the foreign key constraints that reference the column
				SELECT
					TABLE_NAME,
					COLUMN_NAME,
					REFERENCED_TABLE_NAME,
					REFERENCED_COLUMN_NAME,
					CONSTRAINT_NAME
				FROM
					INFORMATION_SCHEMA.KEY_COLUMN_USAGE
				WHERE
					REFERENCED_TABLE_SCHEMA = db_name AND
					REFERENCED_TABLE_NAME = table_name AND
					REFERENCED_COLUMN_NAME = column_name;

			END //

			DELIMITER ;
		SQL;

		$result = $connection->query($procedure);

		// Check for errors
		if (!$result) {
			throw new \Exception('Unable to add approach profiling procedure: "approach_get_field_details(db,table,field)"');
		}

		return $result;
	}



	/**
	 * Get the list of references to each field in a table
	 *
	 * @param string $name			The name of the table
	 * @param array $fields			The list of fields in the table
	 * @param \MySQLi $connection	The MySQLi connection object
	 *
	 * @return array
	 */
	public static function get_reference_list($table, $fields, $connection)
	{

		$references = array();
		foreach ($fields as $field) {
			$sql = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "' . $table . '" AND `COLUMN_NAME` = "' . $field . '";';
			$result = $connection->query($sql);
			while ($row = $result->fetch_assoc()) {
				$references[$field][] = $row;
			}
		}

		// // Also checks views

		// $sql = 'SELECT * FROM INFORMATION_SCHEMA.VIEW_COLUMN_USAGE WHERE `VIEW_NAME` = "' . $table . '";';
		// $result = $connection->query($sql);
		// while ($row = $result->fetch_assoc()) {
		// 	$references[$row['COLUMN_NAME']][] = $row;
		// }

		return $references;
	}



	public static function define_fields($caller): false|array
    {
		$table = $caller->name;
		echo 'Defining fields for MariaDB://' . $caller::SERVER_NAME . '/' . $caller::DATABASE_NAME . '/' . $table . PHP_EOL;
		$aspect_ns = $caller::class;
		$aspect_ns_root = $aspect_ns::get_aspect_directory();
		$connection = $caller->database->server->connection;

		// Make sure we're using the right database
		$sql = 'USE ' . $caller::DATABASE_NAME . ';';
		$result = $connection->query($sql);

		// Check for errors
		if (!$result) {
			return false;
		}
		// exit();
		// $connection = $caller->database->connection;

		$columns = static::get_field_list($table, $connection);
		$fields = static::get_table_definition($table, $connection);
		$accessors = static::get_accessors($table, $connection);
		$keyProperties = static::get_reference_list($table, $columns, $connection);

		$metadata = array();
		$dObj = new \stdClass();
		$dObj->custom_aspects = [];
		$dObj = static::equipFieldPropertyMetadata($dObj, $columns, $fields, $accessors, $keyProperties);
		$dObj = static::equipReferenceToAccessors($dObj, $columns, $accessors);

		$dObj->location = $caller::class;
		$classfile = static::get_table_classfile($caller);
		// remove ".php" from classfile name, add /field.php

		$aspect_root = substr($classfile, 0, -4);

		/**
		 * Transform $classfile into a new path by
		 * - finding the FIRST instance of /Resource/
		 * - replacing it with /Resource/MariaDB/Aspect/
		 * - We can imply MariaDB because we're in the MariaDB extension and know its namespace
		 * - We can imply Aspect because we're generating an aspect class
		 * - The result should match the generated Resource, except prefixed with MariaDB\Aspect\ instead of MariaDB\\
		 */

		// $aspect_root = substr($classfile, 0, strpos($aspect_root, '/Resource/MariaDB')) . 'Resource/MariaDB/Aspect';
		// $aspect_branch = substr($classfile, strpos($aspect_root, '/Resource/MariaDB'));
		$length = strlen('/Resource/MariaDB');
		$aspect_root = substr($classfile, 0, strpos($classfile, '/Resource/MariaDB') + $length);
		$aspect_branch = substr($classfile, strpos($classfile, '/Resource/MariaDB') + $length );
		$aspect_path = $aspect_root . '/Aspect' . $aspect_branch;
		$aspect_path = substr($aspect_path, 0, -4);

//         replace $aspect_ns from MyProject\Resource\MariaDB\MyData\test\names to MyProject\Resource\MariaDB\Aspect\MyData\test\names
        // remove p: from $caller::SERVER_NAME
        $servername = $caller::SERVER_NAME;
        $servername = substr($servername, 2);
        // split at Server_Name
        $aspect_ns = substr($aspect_ns, 0, strpos($aspect_ns, $servername));
        // replace / with \ in $aspect_branch
        $aspect_branch = str_replace('/', '\\', $aspect_branch);
        $aspect_ns .= 'Aspect' . $aspect_branch;
        $aspect_ns = substr($aspect_ns, 0, -4);

        $dObj->location = $aspect_ns;

        // echo $aspect_ns . PHP_EOL;
        // echo $aspect_path . PHP_EOL;

        // exit($aspect_branch . ' | ' . $aspect_path . '|' . $aspect_path);

        $dObj->filename = $aspect_path . '/field.php';
        static::MintAspect($dObj, $columns, 'field'); //,  $classpath);
//
		//FIXME: Noob's Linux sucks: Replace it with 0660 when pushing
		// Make sure the directory exists and is RW but NOT executable, for user and group only
		if (!is_dir($aspect_path)) {
			mkdir($aspect_path, 0777, true);
		} else {	// Directory already exists, make sure it's writable
			chmod($aspect_path, 0777);
			// recursive chmod
			$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($aspect_path), \RecursiveIteratorIterator::SELF_FIRST);
			foreach ($objects as $name => $object) {
				chmod($name, 0777);
			}
		}

		// exit($aspect_branch . ' | ' . $aspect_path . '|' . $aspect_path);

		$dObj->filename = $aspect_path . '/field.php';
		static::MintAspect($dObj, $columns, 'field'); //,  $classpath);

//        echo 'Defining: ';
//        foreach($columns as $col){
//            echo $col . ', ';
//        }
//
//        echo PHP_EOL;

		return ['columns' => $columns, 'data' => $dObj, 'path' => $aspect_path];
	}

    public static function define_profile($caller, $fields_info): void
    {
        $table = $caller->name;
        echo 'Defining profile for MariaDB://' . $caller::SERVER_NAME . '/' . $caller::DATABASE_NAME . '/' . $table . PHP_EOL;

        $aspect_path = $fields_info['path'];
        $dObj = new \stdClass();
        $dObj->filename = $aspect_path . '/profile.php';
        $dObj->location = $fields_info['data']->location;
        static::MintProfile($dObj, $fields_info['columns']); //,  $classpath);
    }



	public static function equipReferenceToAccessors($dObj, $columns, $accessors)
	{
		foreach ($accessors as $row) {
			$str = explode('_', $row['CONSTRAINT_NAME']);
			//		if($table == 'compositions'){ var_export($row); }
			$index = array_search($row->data['COLUMN_NAME'], $columns);
			if ($str[0] == 'PRIMARY') {
				$dObj->primary_accessor = $index;
			} else
				$dObj->reference_to[$index] =
					[
						'schema' => $row->data['REFERENCED_TABLE_SCHEMA'],
						'resource' => $row->data['REFERENCED_TABLE_NAME'],
						'field' => $row->data['REFERENCED_COLUMN_NAME']
					];
		}
		return $dObj;
	}

	/**
	 * Equip a temporary data object with the metadata for each field in a table
	 * These will be mapped to \Approach\Resource\MariaDB\field::_approach_metadata_[ field::$constant ]
	 * in the generated code
	 *
	 */

	public static function equipFieldPropertyMetadata($dObj, $columns, $fields, $accessors, $keyProperties)
	{
		/*

		// Template for $dObj
		{
			$label				=	ordered list, indexed by constants
			$type				=	ordered list, indexed by constants
			$default			=	ordered list, indexed by constants
			$source_type		=	ordered list, indexed by constants
			$source_default		=	ordered list, indexed by constants
			$nullable			=	ordered list, indexed by constants
			$description		=	ordered list, indexed by constants
			$constraint			=	ordered list, indexed by constants
			$accessor			=	ordered list, indexed by constants
			$reference_to		=	dictionary[field_name]
			$primary_accessor	=	constant (static::[field_name])
		}

		*/
		$dObj->_case_map = [];
		$dObj->_label_map = [];
		$dObj->label = [];
		$dObj->type = [];
		$dObj->default = [];
		$dObj->source_type = [];
		$dObj->source_default = [];
		$dObj->nullable = [];
		$dObj->description = [];
		$dObj->constraint = [];
		$dObj->accessor = [];

		foreach ($columns as $index => $column) {
			$index = array_search($column, $columns);
			$dObj->_case_map[] = $column;
			$dObj->_index_map[] = $index;
			$dObj->label[$index] = $column;
			$dObj->type[$index] = $fields[$column]['type'];
			$dObj->default[$index] = $fields[$column]['default'];
			$dObj->source_type[$index] = $fields[$column]['source_type'];
			$dObj->source_default[$index] = $fields[$column]['source_default'];
			$dObj->nullable[$index] = $fields[$column]['nullable'];
			$dObj->description[$index] = $fields[$column]['description'];
			$dObj->constraint[$index] = $fields[$column]['constraint'];
			$dObj->accessor[$index] = $fields[$column]['accessor'];

			if (isset($fields[$column]['primary_accessor']) && $fields[$column]['primary_accessor'] === true) {
				$dObj->primary_accessor = $column;
				$dObj->primary_accessor_symbol = $index;
			}
		}
		return $dObj;
	}

	public static function createMetadataBlock($obj, $columns): string
    {
		$php = '';

		$i = 0;
		$php .= PHP_EOL . '// Discovered Fields' . PHP_EOL;
		$indices = [];
		foreach ($columns as $col) {
			$php .= "\t" . 'const ' . $col . ' = ' . $i . ';' . PHP_EOL;
			$i++;
			$indices[$col] = $i;
		}

		$php .= PHP_EOL . PHP_EOL . '// Discovered Field Metadata' . PHP_EOL;
		$php .= "\t" . 'const _approach_field_profile_ = [' . PHP_EOL;

		$metadata = [
			'_case_map', '_index_map',
			'label', 'type', 'default', 'source_type', 'source_default',
			'nullable', 'description', 'constraint', 'accessor'
		];

		foreach ($metadata as $key) {
			$php .= "\t\t" . 'MariaDB_field::' . $key . ' => [' . PHP_EOL;
			foreach ($obj->{$key} as $i => $value) {
				$prefix = '';
				if ($key != '_case_map') {
					$prefix = 'self::' . $columns[$i] . ' => ';
				}
				$php .= "\t\t\t" . $prefix . var_export($value, true) . ', ' . PHP_EOL;
			}
			$php .= "\t\t" . '],' . PHP_EOL;
		}

		$php .= "\t" . '];' . PHP_EOL;

		return $php;
	}

	public static function get_safe_table_name($table)
	{
		$fqcn = $table::class;
		$prefix = $table->database_class . '\\';
		$safe_name = substr($fqcn, strlen($prefix));
		$source_name = $table->name;
	}

	/**
	 * Given a table, or any class with matching aspect directory, return the classfile
	 *
	 * @param \Approach\Resource\MariaDB\Table $table
	 * @return string
	 */

	public static function get_table_classfile($table)
	{
		return
			rtrim(									// Remove characters from the end of a string
				$table::get_aspect_directory(),		// The string to remove from is the directory that shares path with the classfile
				'\\/'								// The characters to remove are the directory separator \ or /,  \ has to be escaped \\
			)
			.										// "/some/path/Resource/MariaDB/some.host/somedb/sometable"  +  ".php"
			'.php';
	}

    public static function MintProfile(object $dataObject, $columns): void
    {
        $filename = $dataObject->filename;

        $uses = 'use \\Approach\\Resource\\Aspect\\Aspect;' . PHP_EOL;
        $uses .= 'use \\Approach\\Resource\\MariaDB\\Aspect\\field as field_meta;' . PHP_EOL;
        // foreach ($dataObject->use as $use) {
        // 	$uses .= 'use ' . $use . ';' . PHP_EOL;
        // }

        // The namespace is practically the same as the caller's class name
        $ns = $dataObject->location;
        $uses .= 'use ' . $ns . '\\field as SelfField;' . PHP_EOL;

        $php =
            '<?php' . PHP_EOL .
            'namespace ' . $ns . ';'
            . PHP_EOL . PHP_EOL .
            $uses
            . PHP_EOL . PHP_EOL;

        $php .= 'trait profile' . PHP_EOL;
        $php .=  '{' . PHP_EOL;

        $php .= 'static array $profile = [' . PHP_EOL;
        $php .= "\t" . 'Aspect::field => [' . PHP_EOL;

        $metadata = [
            'label', 'type', 'default', 'source_type', 'source_default',
            'nullable', 'description', 'constraint', 'accessor'
        ];

        foreach ($columns as $col) {
            $php .= "\t\t" . 'SelfField::' . $col . ' => [' . PHP_EOL;
            foreach ($metadata as $key) {
                $prefix = '';
                if ($key != '_case_map') {
                    $prefix = 'field_meta::' . $key . ' => SelfField::' . $key . '[SelfField::' . $col . '],';
                }
                $php .= "\t\t\t" . $prefix . PHP_EOL;
            }
            $php .= "\t\t" . '],' . PHP_EOL;
        }

        $php .= "\t" . '],' . PHP_EOL;
        $php .= '];' . PHP_EOL;

        $php .= PHP_EOL . '}' . PHP_EOL;

        // Write the file
        file_put_contents($filename, $php);
    }

	public static function MintAspect(object $dataObject, $columns, $aspect = 'field')
	{
		$filename = $dataObject->filename;

		$uses = 'use \\Approach\\Resource\\MariaDB\\Aspect\\' . $aspect . ' as MariaDB_' . $aspect . ';';
		// foreach ($dataObject->use as $use) {
		// 	$uses .= 'use ' . $use . ';' . PHP_EOL;
		// }

		// The namespace is practically the same as the caller's class name
		$ns = $dataObject->location;

		$php =
			'<?php' . PHP_EOL .
			'namespace ' . $ns . ';'
			. PHP_EOL . PHP_EOL .
			$uses
			. PHP_EOL . PHP_EOL;

        $php .= 'class ' . $aspect . ' extends MariaDB_' . $aspect . PHP_EOL;
        // Allman vs K&R, anyone? A debate for the ages
		// For generated code especially: prefer more vertical AND horizontal space AND alignment where possible
		// Also, we use hard tabs 'round these parts
		$php .= PHP_EOL . '{' . PHP_EOL;

		$php .= static::createMetadataBlock($dataObject, $columns);

		$php .= PHP_EOL . '}' . PHP_EOL;

		// Write the file
		file_put_contents($filename, $php);
	}


	/*
		Deprecated here, but useful elsewhere:

		$baseclass_cases = SomeThing::cases();
		$i= count( $baseclass_cases );
		$additional_cases = false;

		foreach ($something['custom_aspects'] as $case)
		{
			if( !in_array( $case, $baseclass_cases ) )
			{
				$php .= "\t" . 'public const' . $case . ' = '.$i.';' . PHP_EOL;
				$i++;
				$baseclass_cases[] = $case;
				$additional_cases = true;
			}
		}
		if( $additional_cases )
		{
			$php.= static::generate_index_map( $baseclass_cases );
			$php.= static::generate_case_map( $baseclass_cases );
		}
	*/


	/**
	 * Generate a codeblock defining a particular aspect property
	 *
	 * const {$property} = [
	 * 		'{$property}' => var_export( {$value[{$property}]} ),
	 *			...
	 *
	 * 			or
	 *
	 * 		{$value[$i]},
	 * 			...
	 * ];
	 *
	 *
	 * @param string $property
	 * @param array $values
	 * @return string
	 *
	 */

	public static function generate_const_aspect_property($property, $values)
	{
		$php =  PHP_EOL . "\t" . 'const ' . $property . ' = [';

		foreach ($values as $index_or_key => $value) {
			if (is_int($index_or_key)) {
				$php .= PHP_EOL . "\t" . "\t" . var_export($value, true)  . ',';
			} else {
				$php .= PHP_EOL . "\t" . "\t" . $index_or_key . ' => ' . var_export($value, true) . ',';
			}
		}
		$php .= (empty($values) ? '' : PHP_EOL) . "\t" . '];' . PHP_EOL;
	}

	/**
	 * Generate a codeblock defining the index map for aspect categories
	 * Roughly equivalent to a backed enum, the index map is a string to int map
	 *
	 * @param array $cases
	 * @return string
	 *
	 */
	public static function generate_index_map($cases)
	{
		$php =  PHP_EOL . "\t" . 'const _index_map = [' . PHP_EOL;
		foreach ($cases as $case) {
			$php .= "\t" . "\t'" . $case . '\' => static::' . $case . ',' . PHP_EOL;
		}
		$php .= "\t" . '];' . PHP_EOL;
		return $php;
	}

	/**
	 * Generate a codeblock defining the case map for aspect categories
	 * Roughly the inverse of the index map, the case map is an int to string map
	 *
	 * @param array $cases
	 * @return string
	 *
	 */
	public static function generate_case_map($cases, $symbol_name = '_case_map')
	{
		$php =  PHP_EOL . "\t" . 'const ' . $symbol_name . ' = [' . PHP_EOL;
		foreach ($cases as $case) {
			$php .=  "\t" . "\t" . 'static::' . $case . ' => ' . var_export($case, true) . ',' . PHP_EOL;
		}
		$php .= "\t" . '];' . PHP_EOL;
		return $php;
	}

	/**
	 * Together, these maps allow for the use of strings or ints to reference the aspect categories
	 *
	 * Unfortunately, PHP's enums are full of anti-patterns so we have to do this ourselves
	 * In C/C++/C#/Java, we may use enums to avoid type-name wrangling and enforce type safety instead
	 * In PHP, the claim to "better" typed enums comes at the cost of type safe inference.
	 *
	 * However, under the hood, Render\Node's label-and-index methodology is closely equivalent to a C/C++/C#/Java enum
	 * while retaining a type. We will simply have to add some constraint checks against Node types later or
	 * find some way to use docblocks to enforce type safety
	 */
}

/*	use table_discoverability;
	use table_discoverability {
		table_discoverability::define insteadof resource_discoverability;
		table_discoverability::define_fields insteadof resource_discoverability;
	}


}
*/