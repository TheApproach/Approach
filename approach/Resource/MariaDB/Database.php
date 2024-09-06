<?php
// Path: approach\Resource\MariaDB\Database.php

/*
 * The Database class is intended to represent the scope of a MariaDB connection
 * having selected a particular database with the USE statement.
 *
 * Just as the Server class represents a MariaDB server, and is a child node of
 * the MariaDB service -- the Database class represents a MariaDB database, and is
 * a child node of the MariaDB server.
 *
 * This allows Resource::find() to resolve URLs of the following variety:
 * - MariaDB://MyServer/MyDatabase
 * - MariaDB://MyServer/MyDatabase/MyTable
 * - MariaDB://MyServer/MyDatabase/MyView
 * - MariaDB://MyServer/MyDatabase/MyProcedure
 *
 * Which all map to your project's Resource classes, depending on your prefixing settings:
 * - Resource\MyServer 						extends Approach\Resource\MariaDB\Server
 * - Resource\MyServer\MyDatabase 				extends Approach\Resource\MariaDB\Database
 * - Resource\MyServer\MyDatabase\MyTable 		extends Approach\Resource\MariaDB\Table
 * - Resource\MyServer\MyDatabase\MyView 		extends Approach\Resource\MariaDB\View
 * - Resource\MyServer\MyDatabase\MyProcedure 	extends Approach\Resource\MariaDB\Procedure
 *
 * Service::$protocols['MariaDB'] is type Service\MariaDB\Connector
 * Service::$protocols['MariaDB']->nodes[] are all type Resource\MariaDB\Server
 * Service::$protocols['MariaDB'][MyServerLabel] is an alias for Service::$protocols['MariaDB']->nodes[MyServerLabel]
 * Service::$protocols['MariaDB'][MyServerLabel]->nodes[] are all type Resource\MariaDB\Database
 * Service::$protocols['MariaDB'][MyServerLabel][MyDatabaseLabel] is an alias for Service::$protocols['MariaDB'][MyServerLabel]->nodes[MyDatabaseLabel]
 * Service::$protocols['MariaDB'][MyServerLabel][MyDatabaseLabel]->nodes[] are all type Resource\MariaDB\Table|View|Procedure
 * Service::$protocols['MariaDB'][MyServerLabel][MyDatabaseLabel][MyLabel] is an alias for Service::$protocols['MariaDB'][MyServerLabel][MyDatabaseLabel]->nodes[MyLabel]
 *
 * These classes are generated into a projects Scope::GetPath(path::resource) directory, which is the default value of Scope::path::resource
 * This can be changed with Scope::SetPath(path::resource, $path), at Scope initialization or soon by a configuration service.
 *
 * Database->discover() uses the following methods to discover the database:
 * - Database->GetTableList($database)
 * - Database->GetViewList($database)
 * - Database->GetProcedureList($database)
 * - Database->GetFunctionList($database)
 *
 * Which all return a list of tables, views, procedures, and functions respectively.
 * By default Database will profile the INFORMATION_SCHEMA to retrieve a list of these items and
 * their metadata such as accessors, types, etc.
 *
 * These methods are generated and intended not to be overridden by the developer.
 * However, we ensure a [MyClass]Traits.php trait exists and is used by the generated class.
 *
 * This trait will be blank by default, but will allow you to override the methods in the
 * Database class. This allows you to customize the default behavior without having to modify
 * the generated class, which would be overwritten if you regenerate the class.
 */

namespace Approach\Resource\MariaDB;

use Approach\Render\Node;
use Approach\Resource\Aspect\aspects;
use Approach\Resource\Aspect\operation;
use Approach\Resource\MariaDB\Server;
use Approach\Resource\Resource;
use Approach\Resource\MariaDB\Aspect\profile;

use Approach\Resource\MariaDB\Aspect\Table as discoverable;
use Approach\path;
use Approach\runtime;
use Approach\Scope;

class Database extends Resource
{
    // use discoverability;
    // The table list. Aggregates all tables in the database and their metadata
    public array $tables = [];

    // The view list. Aggregates all views in the database and their metadata
    public array $views = [];

    // The procedure list. Aggregates all procedures/functions in the database and their metadata
    public array $procedures = [];

    // The functions. Aggregates all functions in the database and their metadata
    public array $functions = [];

    // The triggers. Aggregates all triggers in the database and their metadata
    public array $triggers = [];

    // The events. Aggregates all events in the database and their metadata
    public array $events = [];

    public function __construct(
        public Server $server,
        public string $database
    ) {
        $this->set_render_id();
        foreach (get_class_vars(static::class) as $key => $value) {
            if (!isset($this->{$key}) && defined(static::class . '::' . $key)) {
                $this->{$key} = constant(static::class . '::' . $key);
            }
        }

        // Get an instance of the server
        if ($server instanceof \Approach\Resource\MariaDB\Server) {
            $this->server = $server;
        } else {
            try {
                $this->server = new $this->server_name;
            } catch (\Throwable $e) {
                try {
                    $this->server = Resource::find('MariaDB://' . $this->server_name);
                } catch (\Throwable $e) {
                    throw new \Exception('Unable to create server instance');
                }
            }
        }
    }

    /**
     * Discover the database
     * - Query a list of all tables|views
     * - Query a list of all procedures|functions
     * - Query a list of all triggers|events
     * - Use Scope::GetPath(path::resource) to get the project's resource class root and append the server's safe label
     * - Generate class file for resource_root/ThisLabel.php  (class extends Approach\Resource\MariaDB\Database)
     * - Generate class file for resource_root/ThisLabel/[each table|view name].php  (class extends Approach\Resource\MariaDB\Table|View)
     * - Generate class file for resource_root/ThisLabel/operations/[each procedure|function name].php  (class extends Approach\Resource\Aspect\operation)
     * - Generate class file for resource_root/ThisLabel/operations/[each trigger name]Trigger|Event.php  (class extends Approach\Resource\Aspect\operation)
     */

    /**
     * Get a list of tables from $this->database
     * - Query a list of all tables from $this->database from the information_schema
     *
     * @return array A list of tables
     */
    public function GetTableList()
    {
        if ($this->server->connection instanceof \Approach\nullstate) {
            throw new \Exception('Server is not connected, nullstate::' . $this->server->connection->name);
        }
        // escape input
        $database = $this->server->connection->real_escape_string($this->database);

        // query for table names
        $result = $this->server->connection->query("SELECT * FROM information_schema.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = '" . $database . "'");
        $tables = [];
        while ($row = $result->fetch_assoc()) {
            $tables[] = $row;
        }
        return $tables;
    }

    /**
     * Get a list of views from $this->database
     * - Query a list of all views from $this->database from the information_schema
     *
     * @return array A list of views
     */
    public function GetViewList()
    {
        // escape input
        $database = $this->server->connection->real_escape_string($this->database);

        // query for view names
        $result = $this->server->connection->query("SELECT * FROM information_schema.TABLES WHERE TABLE_TYPE = 'VIEW' AND TABLE_SCHEMA = '" . $database . "'");
        $views = [];
        while ($row = $result->fetch_assoc()) {
            $views[] = $row;
        }
        return $views;
    }

    /**
     * Get a list of procedures and functions from $this->database
     * - Query a list of all procedures from $this->database from the information_schema
     * - Associated profile data is also included
     * @return array A list of procedures
     */
    public function GetProcedureList()
    {
        // escape input
        $database = $this->server->connection->real_escape_string($this->database);

        // query for procedure names including procedure signature and profile data
        $result = $this->server->connection->query("SELECT * FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '" . $database . "'");
        $procedures = [];
        while ($row = $result->fetch_assoc()) {
            $procedures[] = $row;
        }
        return $procedures;
    }

    /**
     * Determine if an entity exists in $this->database immediate scope (not the table scope)
     * - Query a list of all tables|views|procedures|functions|triggers|events from $this->database from the information_schema
     *
     * @param string $name The name of the entity to search for
     * @param string $type The type of entity to search for, * for any type
     * @return bool Whether the entity exists or not
     */
    public function HasEntity($name, $type = '*')
    {
        $q = '';
        $has = false;

        // escape input
        $database = $this->server->connection->real_escape_string($this->database);
        $name = $this->server->connection->real_escape_string($name);
        $type = $this->server->connection->real_escape_string($type);

        // prepare values for switch/case statement
        $table_query = "SELECT TRUE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $database . "' AND TABLE_NAME = '" . $name . "'";
        $procedure_query = "SELECT TRUE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '" . $database . "' AND ROUTINE_NAME = '" . $name . "'";
        $trigger_query = "SELECT TRUE FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = '" . $database . "' AND TRIGGER_NAME = '" . $name . "'";
        $event_query = "SELECT TRUE FROM information_schema.EVENTS WHERE EVENT_SCHEMA = '" . $database . "' AND EVENT_NAME = '" . $name . "'";

        // switch/case statement for entity type
        switch ($type) {
            case '*':
                $q = '('
                    . $table_query . ') OR ('
                    . $procedure_query . ') OR ('
                    . $trigger_query . ') OR ('
                    . $event_query
                    . ')';
                break;
            case 'table':
            case 'view':
                $q = $table_query;
                break;
            case 'procedure':
                $q = $procedure_query;
                break;
            case 'trigger':
                $q = $trigger_query;
                break;
            case 'event':
                $q = $event_query;
                break;
            default:
                $q = 'SELECT FALSE;';
                break;
        }

        $result = $this->server->connection->query($q);
        if ($result) {
            $has = $result->fetch_assoc();
        }

        return $has;
    }

    /**
     * Get a list of triggers from $this->database
     * - Query a list of all triggers from $this->database from the information_schema
     *
     * @return array A list of triggers
     */
    public function GetTriggerList()
    {
        // escape input
        $database = $this->server->connection->real_escape_string($this->database);

        // query for trigger names
        $result = $this->server->connection->query("SELECT * FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = '" . $database . "'");
        $triggers = [];
        while ($row = $result->fetch_assoc()) {
            $triggers[] = $row;
        }
        return $triggers;
    }

    /**
     * Get a list of events from $this->database
     * - Query a list of all events from $this->database from the information_schema
     *
     * @return array A list of events
     */
    public function GetEventList()
    {
        // escape input
        $database = $this->server->connection->real_escape_string($this->database);

        // query for event names
        $result = $this->server->connection->query("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = '" . $database . "'");
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        return $events;
    }

    public static function get_safe_table($name)
    {
        $safe_name = $name;

        // Check for reserved words and characters unsafe for PHP class names
        $reserved_words = ['', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor'];
        $unsafe_chars = [' ', '-', '.', ',', ';', ':', '*', '/', '\\', '|', '&', '^', '%', '$', '#', '@', '!', '?', '<', '>', '(', ')'];

        if (in_array($name, $reserved_words)) {
            $safe_name = '_' . $name;
        }
        if (in_array(substr($name, 0, 1), $unsafe_chars)) {
            $safe_name = '_' . $name;
        }

        if (in_array($name, $reserved_words)) {
            $safe_name = '_' . $name;
        }
        return $safe_name;
    }

    /**
     * Sanitizes a class name by removing any characters that are not a-z, A-Z, 0-9, or _.
     * If the first character is a number, it is also removed.
     *
     * @param string $className The class name to sanitize.
     * @return string The sanitized class name.
     */
    function sanitize_class_name(string $className): string
    {
        $className = str_split($className);
        foreach ($className as $i => $char) {
            $ascii = ord($char);
            // If character is not a-z, A-Z, 0-9, or _, or if it's a number at the start of the string
            if (!(($ascii >= 48 && $ascii <= 57 && $i != 0) ||  // 0-9 (not at start)
                ($ascii >= 65 && $ascii <= 90) ||  // A-Z
                ($ascii >= 97 && $ascii <= 122) ||  // a-z
                ($ascii == 95))) {  // _
                $className[$i] = '';
            }
        }
        return implode('', $className);
    }

    // No FunctionList() method required as functions are included in ProcedureList()

    /**
     * Discover the database
     */
    public function discover()
    {
        $resource_root = Scope::GetPath(path::resource) . 'MariaDB' . DIRECTORY_SEPARATOR;
        $resource_ns = Scope::$Active->project . '\Resource\MariaDB';

        $safe_database_name = '';  // We will use this to hold a safe version of $this->label

        // Check if $this->label starts with 'p:' (persistent)
        // If so, then remove it and set that result to $safe_database_name

        // Remove characters that are invalid for class names for this->label
        $safe_database_name = $this->sanitize_class_name($this->database);

        $server_label = substr($this->server->label, 0, 2) == 'p:'
            ? substr($this->server->label, 2)
            : $this->server->label;

        $server_safe = $this->sanitize_class_name($server_label);

        $classname = $resource_ns . '\\' . $server_safe . '\\' . $safe_database_name;
        if(!class_exists($classname)){
            $this->MintResourceClass(
                path: $resource_root . DIRECTORY_SEPARATOR . $server_safe . DIRECTORY_SEPARATOR . $safe_database_name . '.php',
                class: $resource_ns . '\\' . $server_safe . '\\' . $safe_database_name,
                extends: 'MariaDB\Database',
                namespace: $resource_ns . '\\' . $server_safe,
                uses: ['\Approach\Resource\MariaDB'],
                constants: [
                    "NAME = '" . $this->database . "'",
                    "DATABASE = '" . $this->database . "'",
                    "SERVER_NAME = '" . $this->server->label . "'",
                    "RESOURCE_PROTO = 'MariaDB'",
                    "SERVER_CLASS = '" . $resource_ns . '\\' . $server_safe . "'",
                    "RESOURCE_CLASS = '" . $resource_ns . "'",
                    "CONNECTOR_CLASS = '\Approach\Service\MariaDB\Connector" . "'",
                ],
                // properties: 	[],
                // methods: 	[],
            );
        }

        // Discover the tables
        $tables = $this->GetTableList();
        foreach ($tables as $table) {
            // Get Classname for table
            $table_safe = static::get_safe_table($table['TABLE_NAME']);
            $classname = $resource_ns . '\\' . $server_safe . '\\' . $safe_database_name . '\\' . $table_safe;

            // Check if class exists
            if (!class_exists($classname)) {
                // Create class
                $this->MintResourceClass(
                    path: $resource_root . DIRECTORY_SEPARATOR . $server_safe . DIRECTORY_SEPARATOR . $safe_database_name . DIRECTORY_SEPARATOR . $table_safe . '.php',
                    class: $resource_ns . '\\' . $server_safe . '\\' . $safe_database_name . '\\' . $table_safe,
                    extends: 'MariaDB\Table',
                    namespace: $resource_ns . '\\' . $server_safe . '\\' . $safe_database_name,
                    uses: ['\Approach\Resource\MariaDB'],
                    constants: [
                        "DATABASE_CLASS = '\\" . $resource_ns . '\\' . $server_safe . '\\' . $safe_database_name . "'",
                        "SERVER_CLASS = '\\" . $resource_ns . '\\' . $server_safe . "'",
                        "CONNECTOR_CLASS = '\Approach\Service\MariaDB\Connector" . "'",
                        "RESOURCE_CLASS = '\\" . $resource_ns . "'",
                        "DATABASE_NAME = '" . $this->database . "'",
                        "SERVER_NAME = '" . $this->server->label . "'",
                        "RESOURCE_PROTO = '" . 'MariaDB' . "'",
                        "NAME = '" . $table['TABLE_NAME'] . "'",
                        "COMMENT = '" . $table['TABLE_COMMENT'] . "'",
                        "ENGINE = '" . $table['ENGINE'] . "'",
                        "ROW_FORMAT = '" . $table['ROW_FORMAT'] . "'",
                        "TABLE_COLLATION = '" . $table['TABLE_COLLATION'] . "'",
                        "CREATE_OPTIONS = '" . $table['CREATE_OPTIONS'] . "'",
                        "TABLE_ROWS = '" . $table['TABLE_ROWS'] . "'",
                        "AVG_ROW_LENGTH = '" . $table['AVG_ROW_LENGTH'] . "'",
                        "DATA_LENGTH = '" . $table['DATA_LENGTH'] . "'",
                        "MAX_DATA_LENGTH = '" . $table['MAX_DATA_LENGTH'] . "'",
                        "INDEX_LENGTH = '" . $table['INDEX_LENGTH'] . "'",
                        "DATA_FREE = '" . $table['DATA_FREE'] . "'",
                        "AUTO_INCREMENT = '" . $table['AUTO_INCREMENT'] . "'",
                        "CREATE_TIME = '" . $table['CREATE_TIME'] . "'",
                        "UPDATE_TIME = '" . $table['UPDATE_TIME'] . "'",
                        "CHECK_TIME = '" . $table['CHECK_TIME'] . "'",
                        "CHECKSUM = '" . $table['CHECKSUM'] . "'",
                        "TABLE_COMMENT = '" . $table['TABLE_COMMENT'] . "'",
                    ],
                    // properties: 	[],
                    // methods: 	[],
                );
            }

            // Composer's autoloader does not know about the new class yet, so we will include it here
            static::__update_composer_autoloader(
                resource_root: NULL,
                resource_class: 'MariaDB' . '\\' . $server_safe . '\\' . $safe_database_name . '\\' . $table_safe,
            );

 

            require_once $resource_root . DIRECTORY_SEPARATOR . $server_safe . DIRECTORY_SEPARATOR . $safe_database_name . DIRECTORY_SEPARATOR . $table_safe . '.php';

            $this->nodes[$table_safe] = new $classname(name: $table['TABLE_NAME'], database: $this);
            $this->nodes[$table_safe]->discover();
            
            discoverable::define(caller: $this->nodes[$table_safe]);

            // static::__update_composer_autoloader(
            //     resource_root: NULL,
            //     resource_class: 'MariaDB' . '\\' . $server_safe . '\\' . $safe_database_name . '\\' . 'user_trait',
            // );
            // static::__update_composer_autoloader(
            //     resource_root: NULL,
            //     resource_class: 'MariaDB' . '\\' . $server_safe . '\\' . $safe_database_name . '\\' . 'profile',
            // );
        }

        /*
         * // Under here is not required for AvenuePad or SuiteUX so far
         * // Discover the views
         * $views = $this->GetViewList();
         * foreach ($views as $view)
         * {
         * 	$this->nodes[$view['TABLE_NAME']] = new Table($this, $view['TABLE_NAME']);
         * 	$this->nodes[$view['TABLE_NAME']]->discover();
         * }
         *
         * // Discover the triggers
         * $triggers = $this->GetTriggerList();
         * foreach ($triggers as $trigger)
         * {
         * 	// Available Keys:
         * 	// TRIGGER_CATALOG, TRIGGER_SCHEMA, TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_CATALOG,
         * 	// EVENT_OBJECT_SCHEMA, EVENT_OBJECT_TABLE, ACTION_ORDER, ACTION_CONDITION, ACTION_STATEMENT,
         * 	// ACTION_ORIENTATION, ACTION_TIMING, ACTION_REFERENCE_OLD_TABLE, ACTION_REFERENCE_NEW_TABLE,
         * 	// ACTION_REFERENCE_OLD_ROW, ACTION_REFERENCE_NEW_ROW, CREATED, SQL_MODE, DEFINER,
         *
         * 	//Describe Trigger as an operation
         * 	$operation = new operation(parent: $this);
         * 	$operation->name = $trigger['TRIGGER_NAME'];
         * 	$operation->description = $trigger['ACTION_STATEMENT'];
         * 	$operation->signature = [];
         * 	$operation->signature['condition'] = $trigger['ACTION_CONDITION'];
         * 	$operation->signature['from'] = $trigger['ACTION_REFERENCE_OLD_TABLE'];
         * 	$operation->signature['then'] = $trigger['ACTION_REFERENCE_OLD_ROW'];
         * 	$operation->signature['to'] = $trigger['ACTION_REFERENCE_NEW_TABLE'];
         * 	$operation->signature['now'] = $trigger['ACTION_REFERENCE_NEW_ROW'];
         * 	$operation->signature['name'] = $trigger['TRIGGER_NAME'];
         * 	$operation->signature['type'] = 'trigger';
         * 	$operation->is_action 	= true;
         *
         * 	$this->triggers[$trigger['TRIGGER_NAME']] = $operation;
         * }
         */
    }
}
