<?php


/*************************************************************************

APPROACH
Organic, human driven software.


COPYRIGHT NOTICE
__________________

(C) Copyright 2020 - Garet Claborn
All Rights Reserved.

Notice: All information contained herein is, and remains
the property of Approach Foundation LLC and the original author, Garet Claborn,
herein referred to as "original author".

The intellectual and technical concepts contained herein are
proprietary to Approach Foundation LLC and the original author
and may be covered by U.S. and Foreign Patents, patents in process,
and are protected by trade secret or copyright law.

/*************************************************************************
*
*
* Approach by Garet Claborn is licensed under a
* Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.
*
* Based on a work at https://github.com/stealthpaladin .
*
* Permissions beyond the scope of this license may be available at
* garet.claborn@gmail.com
*
*
*
*************************************************************************/

namespace Approach\Resource;

use Exception;

abstract class FilterOperators
{
    const ASSIGN = 0;
    const EQUAL_TO = 1;
    const NOT_EQUAL_TO = 2;
    const LESS_THAN = 3;
    const GREATER_THAN = 4;
    const LESS_THAN_EQUAL_TO = 5;
    const GREATER_THAN_EQUAL_TO = 6;

    const _AND_ = 7;
    const _OR_ = 8;
    const _HAS_ = 9;

    const OPEN_DIRECTIVE = 10;
    const CLOSE_DIRECTIVE = 11;
    const OPEN_GROUP = 12;
    const CLOSE_GROUP = 13;
    const OPEN_INDEX = 14;
    const CLOSE_INDEX = 15;
    const OPEN_WEIGHT = 16;
    const CLOSE_WEIGHT = 17;

    const NEED_PREFIX = 18;
    const REJECT_PREFIX = 19;
    const WANT_PREFIX = 20;
    const DELIMITER = 21;
    const RANGE = 22;
    // etc.
};
// All operations are as such: NameExpression Operator ValueExpression
// Comparison List is a list of [ [NameExpression, Operator,ValueExpression], ...]
// Value Expression May Represent Multiple Or Single Values, only some operators allow multiple values

//listing(${1.75}hasPool, ~{0.50}price <= 1000, garage HAS (door,lamp) )

// {}  directive
// () parsed and resolved inner-to-outer, except for an outermost set which is interpretted as the comparison list
// [] indexing

class FilterParser extends FilterOperators
{
    public static $Operations = [
        self::ASSIGN => ':',
        self::EQUAL_TO => '=',
        self::NOT_EQUAL_TO => '!=',
        self::LESS_THAN => '<',
        self::GREATER_THAN => '>',
        self::_AND_ => ' AND ',
        self::_OR_ => ' OR ',
        self::_HAS_ => ' HAS ',
        self::LESS_THAN_EQUAL_TO => '<=',
        self::GREATER_THAN_EQUAL_TO => '>=',
        self::RANGE => '..',
        self::OPEN_DIRECTIVE => '{',
        self::CLOSE_DIRECTIVE => '}',
        self::OPEN_GROUP => '[',
        self::CLOSE_GROUP => ']',
        self::OPEN_INDEX => '[',
        self::CLOSE_INDEX => ']',
        self::OPEN_WEIGHT => '{',
        self::CLOSE_WEIGHT => '}',
        self::NEED_PREFIX => '$',
        self::REJECT_PREFIX => '!',
        self::WANT_PREFIX => '~',
        self::DELIMITER => ','
    ];

    public array $parsed=[];
    public array $scopes=[];
    public int $scope_cursor=0;
	public $dataset_type=NULL;

    public function __construct($uri)
    {
		$this->scopes = explode('/', $uri);
		// remove leading '/' empty element
		if(empty($this->scopes[0]))
			array_shift($this->scopes);

		while ($this->scope_cursor < count($this->scopes))
		{
			try
			{
				$this->parsed[$this->scope_cursor] = $this->ResolveCurrentScope();
				$this->scope_cursor++;
			}
			catch(Exception $e)
			{
				exit(PHP_EOL.$e->getMessage());
			}
		}
    }

    public function ResolveCurrentScope()
    {
		$current_scope = $this->scopes[ $this->scope_cursor ];
		$cursor=0;
		$comparison_list = [];
		$query_modifier_list = [];

		$dataset_type = $this->detectDatasetClass($current_scope, $cursor);
		if(empty($this->dataset_type))
			$this->dataset_type = $dataset_type;

		if($cursor < strlen($current_scope) )
			$comparison_list = $this->detectComparisonList($current_scope, $cursor);
		if($cursor < strlen($current_scope) )
			$query_modifier_list = $this->detectQueryModifierList($current_scope, $cursor);

        return [ $dataset_type, $comparison_list, $query_modifier_list ];
	}

	// returns $String dataset type
	public function detectDatasetClass($current_scope, &$cursor): string
    {
		$dataset_type = '';
		$control_chars['dataset'] = [
			self::$Operations[self::OPEN_GROUP],
			self::$Operations[self::OPEN_DIRECTIVE],
			self::$Operations[self::OPEN_INDEX]
		];

		for ($L=strlen($current_scope); $cursor<$L; $cursor++)
		{
			if( !in_array($current_scope[$cursor], $control_chars['dataset']) )
			{
				$dataset_type .= $current_scope[$cursor];
            }
			else break;
		}

		// if(!class_exists($dataset_type) && $dataset_type != '')
		// {
		// 	throw new Exception('Class "'.$dataset_type.'" does not exist.');
		// }

        return $dataset_type;
	}

	private function detectComparisonList($current_scope, &$cursor): array
    {
		// (${1.75}hasPool, ~{0.50}price=1000..2000, garage HAS (door,lamp) )

		$comparison_list=[];
		// not an "("
        if ($current_scope[$cursor] != self::$Operations[self::OPEN_GROUP]) {
            return $comparison_list;
		}

		//Inside Comparison List ()s
		$cursor++;
		$open_group_count = 1;
		$close_group_count = 0;

		$L=strlen($current_scope);
		$found_operator = false;
		$sync = false;
		$field_text = '';
		$value_expression=[];
		$value_text = '';
		$value_expression=[];
		$current_operator=NULL;

        // MariaDB://db.host/myDatabase/myTable

		$comparison_operators=[
			self::$Operations[self::EQUAL_TO],
			self::$Operations[self::NOT_EQUAL_TO],
			self::$Operations[self::LESS_THAN],
			self::$Operations[self::GREATER_THAN],
			self::$Operations[self::_AND_],
			self::$Operations[self::_OR_],
			self::$Operations[self::_HAS_],
			self::$Operations[self::LESS_THAN_EQUAL_TO],
			self::$Operations[self::GREATER_THAN_EQUAL_TO],
			self::$Operations[self::RANGE]
		];
		/*
			/type_to_filter_by(THIS PART IS BEING LOOPED){not this part}
		*/

		//https://service.osc.com/filter/filter.php?json={%22context%22:{%22filter%22:%22/bridge_listings(${1.75}PoolPrivateYN=1,ListPrice=%20100..2000000){LIMIT:1000,SORT:DESC}%22}}
		$field_expression='';
		while( $cursor < $L )
		{
			$char = $current_scope[$cursor];

			if( $char == self::$Operations[self::CLOSE_GROUP] )
			{

				$value_group = false;
				$close_group_count++;
				// If parser catches up to the first opening paranthesis group
				if($close_group_count == $open_group_count)
				{
					$value_expression = $this->detectValueExpression($value_text);
					$comparison_list[] = [$field_expression, $current_operator, $value_expression];
					$cursor++;
					break;
				}
			}
			if( $char == self::$Operations[self::OPEN_GROUP] )
			{
				$open_group_count++;
				$value_group = true;
				if($close_group_count == $open_group_count)
				{
					$cursor++;
					break;
				}
			}

			$next_chars =[
				$current_scope[$cursor],
				($cursor+1 < $L ? $current_scope[$cursor+1] : ''),
				($cursor+2 < $L ? $current_scope[$cursor+2] : ''),
				($cursor+3 < $L ? $current_scope[$cursor+3] : ''),
				($cursor+4 < $L ? $current_scope[$cursor+4] : ''),
			];
            // This matches the length of the longest operator Ex: ' HAS '
			$multi_cursor = [
				$next_chars[0],
				$next_chars[0].$next_chars[1],
				$next_chars[0].$next_chars[1].$next_chars[2],
				$next_chars[0].$next_chars[1].$next_chars[2].$next_chars[3],
				$next_chars[0].$next_chars[1].$next_chars[2].$next_chars[3].$next_chars[4]
			];

			$t=array_intersect($multi_cursor, $comparison_operators);
			// FIELD SIDE EVALUATION UNTIL OPERATOR
            if (empty($t) && !$found_operator) {
				$field_text .= $current_scope[$cursor];
            }
			elseif(!$found_operator)
			{
				$found_operator = true;
				$current_operator = array_shift($t);

				$cursor+= strlen($current_operator);
				$field_expression = $this->detectFieldExpression($field_text);

				$sync = true;
			}

			$char = $current_scope[$cursor];
			// VALUE SIDE EVALUATION
			if($sync && $found_operator)
			{
				if(	($char != self::$Operations[self::CLOSE_GROUP] && $char != self::$Operations[self::DELIMITER])
					||
					($char == self::$Operations[self::DELIMITER] && (($open_group_count - $close_group_count) > 1))
				)
				{
					switch($char)
					{
						case self::$Operations[self::OPEN_GROUP] : $open_group_count++; break;
						default : $value_text .= $current_scope[$cursor]; break;
					}
				}
				else
				{
					$value_expression = $this->detectValueExpression($value_text);
					$comparison_list[] = [$field_expression, $current_operator, $value_expression];

					$found_operator = false;
					$sync = false;
					$field_text = '';
					$value_expression=[];
					$value_text = '';
					$value_expression=[];
					$current_operator=NULL;
				}
			}

			$cursor++;
		}

		if($open_group_count != $close_group_count)
		{
			throw new Exception('Mismatched Parenthesis ()s');
		}

		return $comparison_list;
	}

	private function detectFieldExpression($field_text)
	{
		$field_expression = [];

		$prefix = self::$Operations[self::WANT_PREFIX];
		$unprefixed = false;
		switch($field_text[0] )
		{
			case self::$Operations[self::NEED_PREFIX] : $prefix = self::$Operations[self::NEED_PREFIX]; break;
			case self::$Operations[self::REJECT_PREFIX] : $prefix = self::$Operations[self::REJECT_PREFIX]; break;
			case self::$Operations[self::WANT_PREFIX] : $prefix = self::$Operations[self::WANT_PREFIX]; break;
			default: $unprefixed=true; break;
		}
		$tmp_cursor = $unprefixed ? 0 : 1;

		$weight = 0.50.'';

		$weight_start = strpos($field_text,'{',$tmp_cursor);
		$weight_end = strpos($field_text,'}',$weight_start);
		if($weight_start !== false && $weight_end !== false)
		{
			$weight = substr($field_text,$weight_start+1,($weight_end - $weight_start)-1);
		}

		$field = $weight_end !== false ? 			// If curly braces detected at all
			substr($field_text,$weight_end+1) : 	// Then substr starting after }
			substr($field_text,$tmp_cursor);		// Else Start Based on prefix at 0 or 1

		return [$prefix, $weight, $field];
	}

	private function detectValueExpression($value_text)
	{
		$arr = explode(self::$Operations[self::DELIMITER], $value_text);
		foreach($arr as &$a)
			$a = trim($a);
		return $arr;
	}

	private function detectQueryModifierList($current_scope, &$cursor)
	{
		$query_modifier_list=[];
		if ($current_scope[$cursor] != self::$Operations[self::OPEN_DIRECTIVE])
		{
            return $query_modifier_list;
		}
		if(substr($current_scope,-1) != self::$Operations[self::CLOSE_DIRECTIVE])
		{
			throw new Exception('You done messed up your curly brackets!');
		}

		$query_modifier_text = substr($current_scope,$cursor+1, -1);

		// LIMIT:1000,SORT:DESC
		$query_modifiers = explode(',',$query_modifier_text);

		foreach ($query_modifiers as $mod) {
			$exploded_mod = explode(':', $mod);
			$query_modifier_list[$exploded_mod[0]]=$exploded_mod[1];
		}
		return $query_modifier_list;
	}
}
