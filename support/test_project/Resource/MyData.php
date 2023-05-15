<?php

namespace MyProject\Resource;

class MyData extends \Approach\Resource\MariaDB\Server
{
	
	public const HOST = 'database-01.system-00.suitespace.corp';
	public const USER = 'tom';
	public const DATABASE = 'MyHome';
	public const PORT = '3306';
	public const PERSISTENT = '1';
	
	
}

/*
// use MyProject\Resource\MyData;
// use MyProject\Resource\MyData\MyTable\fields;                // enum

$MyData->nodes[ 'TableName' ];  //  MyProject\Resource\MyData\TableName
$MyData->nodes[ 'TableName' ]->nodes['ColumnName'];             //  MyProject\Resource\MyData\TableName\ColumnName

$MyData['TableName']['ColumnName'];  //  MyProject\Resource\MyData\TableName\ColumnName
$MyData[ MyData_tables::TableName->value ][ fields::id->value ] // list of selected id(s)

*/
