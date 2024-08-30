<?php

namespace Approach\Service\MariaDB;

use \Approach\Render\Node;
use \Approach\Service\Service;
use \Approach\Resource\MariaDB\Server;
use \Approach\nullstate;
use \MySQLi;
use \Stringable;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


trait connectivity
{
	public bool $connected = false;
	public mixed $connection;
	protected $alias=null;
	protected $current_statement;
	protected static $connection_limit=null;
	protected static $num_connected = 0;

	public function connect($register_connection = true, ?Server $server = null): nullstate
	{
		if( !$server ){
			throw new \Exception('Default MariaDB Server configuration not provided');
		}
		
		// foreach ($this->nodes as &$server)
		// {
			$state = nullstate::ambiguous;

			try
			{
				$p = $server->persistent ? '' : '';
				$this->connection = new MySQLi(
					$p . $server->host,
					$server->user,
					$server->pass,
					$server->database,
					$server->port
					// $server->socket
				);
				$state = $this->connection->connect_errno ? nullstate::undefined : nullstate::defined;
			}
			catch (\Exception $e)
			{
				$state = nullstate::ambiguous;
				throw $e;
			}
			finally
			{
				$server->state = $state;
				if ($server->state == nullstate::defined)
				{
					if ($server->ssl_key || $server->ssl_cert || $server->ssl_ca || $server->ssl_capath || $server->ssl_cipher){
						$this->connection->ssl_set($server->ssl_key, $server->ssl_cert, $server->ssl_ca, $server->ssl_capath, $server->ssl_cipher);
					}
					if ($server->charset){
						$this->connection->set_charset($server->charset);
					}
					if ($server->collation){
						$this->connection->query('SET collation_connection = \'' . $server->collation . '\';');
					}
					if ($server->timeout){
						$this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $server->timeout);
					}
				}
			}
		// }

		if( $state === nullstate::defined && $register_connection ){
			$this->alias = $this->alias ?? $server->host;
			$this->register_connection(server: $server);
		}

		return $state;
	}

	/**
	 * Register a connection as a Service protocol handler
	 * - Allows for multiple connections to the same server
	 * 		* Limits the number of connections to the same server
	 * 		* Accepts a connection alias
	 * 		* Helps share connections between downstream Services and Resources
	 * - Defines a root connection for the Service type
	 * 		* Creates a node tree which cuts through types and instances
	 * 		* For example, a Service type can have a root connection to a database and a child connection to a table
	 * 		* Each protocol root is filled recursively with container types that drill-down and connect to the same server
	 * 
	 * 			* MariaDB://localhost/MyDatabase/MyTable becomes Service::$protocol[MariaDB][localhost][MyDatabase][MyTable]
	 * 					Service::$protocol --> Render\Node
	 * 					Service::$protocol[MariaDB] --> Service\Service
	 * 					Service::$protocol[MariaDB][localhost] --> Resource\MariaDB\Server
	 * 					Service::$protocol[MariaDB][localhost][MyDatabase] --> Resource\MariaDB\Database
	 * 					Service::$protocol[MariaDB][localhost][MyDatabase][MyTable] --> Resource\MariaDB\Table
	 * 
	 */
	public function register_connection($server=null)
	{
		$proto = static::getProtocol();

		Service::$protocols[$proto] = Service::$protocols[$proto] ?? new Node();

		$num_connected = count(Service::$protocols[$proto][$server->alias] ?? []);
		if( $num_connected < static::$connection_limit ){
			if(isset(Service::$protocols[$proto][$server->alias])){
				if( Service::$protocols[$proto][$server->alias] !== $this )
					Service::$protocols[$proto][$server->alias]->connections[] = $this;
				elseif( Service::$protocols[$proto][$server->alias] === $this )
					$this->ServiceException('already_connected', static::class . '::connect()', '');
			}
			else{
				if( isset(Service::$protocols[$proto][$this->_render_id]) )
					unset(Service::$protocols[$proto][$this->_render_id]);
				Service::$protocols[$proto][$this->alias ?? $this->_render_id] = $this;
			}
		}
		else{
			if( $num_connected === 0 )
				$this->ServiceException('connection_limit_exceeded', static::class . '::connect()', '');
			
			if($this->connection && $this->connection->ping())
				$this->disconnect();
			$this->ServiceException('already_connected', static::class . '::connect()', '');
		}
	}

	/**
	 * Close open connections to the database
	 * 
	 * @return bool|null
	 */
	public function disconnect($which = null, $index = null): nullstate
	{
		$state = nullstate::ambiguous;
		try
		{
			// User selected a specific connection to close

			if( $which ){
				// If the connection exists
				if( isset($this->connections[$which]) ){
					// Close the connection
					$this->connections[$which]->close();
					// Remove the connection from the list
					unset($this->connections[$which]);
					// Set the state to defined
					$state = nullstate::defined;
				}
				// If the connection does not exist
				else{
					// Set the state to undefined
					$state = nullstate::undefined;
				}
			}
		}
		catch (\Exception $e)
		{
			// TODO: Emit error message renderable
			$state = nullstate::stalled;
		}
		return $state;
	}

	public function send($query, $which = null, $index = null)
	{
		$state = nullstate::ambiguous;
		try{
			$result = static::$current_statement->execute($query);
		}
		catch (\Exception $e) {
			$state = nullstate::stalled;
			throw $e;
		}
		finally
		{
			// TODO: Reset query and connection state if stalled
			$state = $result ? nullstate::defined : nullstate::undefined;
		}
		return $state;
	}

	public function recieve()
	{
	}
}
