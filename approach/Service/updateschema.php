<?php

namespace Approach\Resource\MySQL;

use mysqli;

function UpdateSchema()
{
	//need switch() case: for database type [MySQL, MSSQL, Mongo, Redis, Parsyph, Hadoop, Cassandra]
	$InfoSchemaDatabaseColumn = 'TABLE_SCHEMA';

	$sql = 'SELECT TABLE_CATALOG, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS';

	$spread = array();
	$DataObjects = array();
	$schemainfo = LoadDirect($sql, 'INFORMATION_SCHEMA.COLUMNS');

	foreach ($schemainfo as $SchemaRow)
	{
		$schemas[$SchemaRow->data['TABLE_SCHEMA']][$SchemaRow->data['TABLE_NAME']][$SchemaRow->data['COLUMN_NAME']] = $SchemaRow->data;
	}

	foreach ($schemas as $current_db => $spread)
		foreach ($spread as $table => $columns)
		{
			//Cross-Database Discrepency : MySQL uses quotes, MSSQL uses N
			$sql = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE `TABLE_NAME` = "' . $table . '";';
			$findKeys = LoadDirect($sql, 'INFORMATION_SCHEMA.KEY_COLUMN_USAGE');

			$sql = 'SELECT * FROM INFORMATION_SCHEMA.VIEW_COLUMN_USAGE WHERE `VIEW_NAME` = "' . $table . '";';
			$keyProperties = LoadDirect($sql, 'INFORMATION_SCHEMA.VIEW_COLUMN_USAGE');

			$dObj = new stdClass();

			//	var_dump($keyProperties);
			foreach ($findKeys as $row)
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

			SavePHP($dObj,  $classpath);
		}
}

