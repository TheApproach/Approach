<?php

namespace Approach\Resource\MariaDB;

use Approach\path;
use Approach\Resource\Resource;

/**
 * Represent a source of data within a MariaDB\Server. For MariaDB, a source is a database.
 * MariaDB://Server/Schema/Table|View|Procedure
 * 
 * This class is responsible for discovering the tables, views and procedures within a database.
 * This class will be instantiated by the Approach\Resource\MariaDB\Server class.
 * This class will be generate class definitions for:
 * - MyProject\Resource\Server\Schema\MyTable extends Approach\Resource\MariaDB\Table
 * - MyProject\Resource\Server\Schema\MyView extends Approach\Resource\MariaDB\View
 * - MyProject\Resource\Server\Schema\MyProcedure extends Approach\Resource\MariaDB\Procedure
 * 
 * Scope::GetPath(path::resource) will return the project's \Resource\ path.
 * 
 * @package Approach\Resource\MariaDB
 * @version 1.0.0
 * @see Approach\Resource\Source
 * @see Approach\Resource\MariaDB\Server
 * @license Apache License Version 2.0, January 2004 http://www.apache.org/licenses
 * 
 */

class Source extends Resource{
	
	/**
	 * Discover the tables, views and procedures within a database.
	 * 
	 * @param string $path
	 * @return void
	 * @throws Exception
	 */
	public function __construct($path){
		
		parent::__construct($path);
		
		$this->discoverTables();
		$this->discoverViews();
		$this->discoverProcedures();
	}
	
	/**
	 * Discover the tables within a database.
	 * 
	 * @return void
	 * @throws Exception
	 */
	protected function discoverTables(){
		
		$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?";
		$this->database->prepare($sql);
		$this->database->execute([$this->name]);
		
		while($row = $this->database->fetch()){
			
			$this->addTable($row['TABLE_NAME']);
		}
	}
	
	/**
	 * Discover the views within a database.
	 * 
	 * @return void
	 * @throws Exception
	 */
	protected function discoverViews(){
		
		$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = ?";
		$this->database->prepare($sql);
		$this->database->execute([$this->name]);
		
		while($row = $this->database->fetch()){
			
			$this->addView($row['TABLE_NAME']);
		}
	}
	
	/**
	 * Discover the procedures within a database.
	 * 
	 * @return void
	 * @throws Exception
	 */
	protected function discoverProcedures(){
		
		$sql = "SELECT ROUTINE_NAME FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_TYPE = 'PROCEDURE'";
		$this->database->prepare($sql);
		$this->database->execute([$this->name]);
		
		while($row = $this->database->fetch()){
			
			$this->addProcedure($row['ROUTINE_NAME']);
		}
	}
	
	/**
	 * Add a table to the resource.
	 * 
	 * @param string $name
	 * @return void
	 * @throws Exception
	 */
	protected function addTable($name){
		
		$this->addResource('Table', $name);
	}
	
	/**
	 * Add a view to the resource.
	 * 
	 * @param string $name
	 *	
}