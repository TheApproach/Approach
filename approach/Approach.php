<?php

/*
	Title: Renderale Utility Functions for Approach

	Copyright 2002-2022 Garet Claborn
*/

namespace Approach;

/*

$catch_deprecated = function(
    int $error_code,
    string $message,
    string $offending_file,
    int $offending_line,
    array $active_variables
){   
    global $DebugMode;
    static $caught = [ ];
    $alert_dir = '/srv/myproject/alerts/'.$error_code .'/';
    $log_dir = '/srv/myproject/log/'.$error_code .'/';

    if( $DebugMode ){
         file_put_contents( $log_dir . 'deprecated.log', PHP_EOL.$message.PHP_EOL, , FILE_APPEND | LOCK_EX);
    }
    if( empty( $caught[ $message ] ) ){
       $caught[ $message ] = true;
       touch(  $alert_dir . 'DEPRECATED' . $message );
    }
};

set_error_handler(  callback: $catch_deprecated,  error_levels: = E_DEPRECATED );

*/


class Approach
{
	public static function isArrayAssociative($arr1){
		if (array_keys($arr1) !== range(0, count($arr1) - 1))
			return true;
		else
			return false;
	}

	function CheckSession()
	{
		if (session_status() === PHP_SESSION_NONE) {
			@ ini_set('session.gc_maxlifetime', 604800);
			@ session_name(Scope::$Active->project);
			@ session_start();
		}
	}

	function approach_dump($refer)
	{
		ob_start();
		var_dump($refer);
		$r=ob_get_contents();
		ob_end_clean();
		return $r;
	}

	/*

	These functions let you primarily search through types of class renderable by
	common CSS selectors such as ID, Class, Attribute and Tag.

	Also the JavaScript Events have a require listed at the bottom of this source
	JavaScript events need to look for your </head> element *or* the  </body> elemenet
	and dynamically place event bindings, script linking or direct code at these
	locations.


	Use

	$Collection = RenderSearch($anyRenderable,'.Buttons');

	Or Directly


	$SingleTag=function GetRenderable($SearchRoot, 1908);                       //System side render ID $renderable->id;
	$SingleTag=function GetRenderableByPageID($root,'MainContent');             //Client side page ID

	$MultiElements=function GetRenderablesByClass($root, 'Buttons');
	$MultiElements=function GetRenderablesByTag($root, 'div');


	*/

	function filterToXML( $node, $tag, $content, $styles, $properties)
	{
		$output='<' . $tag;
		foreach($properties as $property => $value)
		{
			$output .= ' '.$property.'="'.$value.'"';
		}
		$output .= ' class="';
		foreach($styles as $class)
		{
			$output .= $class.' ';
		}
		$output .= '"'. 'id="'.$tag . $node->id . '">';
		$output .=$content . '</'.$tag.'>';
	}

	function toFile($filename, $data)
	{
		$fh = fopen($filename, 'w');
		if($fh)
			fwrite($fh, $data);
		else $data = '<span class="ioERROR error"> Can\'t open '.$filename.' file. </span>';
		fclose($fh);
	}


	function GetFile($path, $override=false)
	{
		//return file_get_contents($path);
		global $APPROACH_REGISTERED_FILES;
		if(!isset($APPROACH_REGISTERED_FILES[$path]) || $override)
			$APPROACH_REGISTERED_FILES[$path] = file_get_contents($path);
		return $APPROACH_REGISTERED_FILES[$path];
	}    //Local Scope File Caching

	function curl($url)
	{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
	}

	function Blame($Container)
	{
		$Reason='';
		foreach($Container as $key => $value)
		{
			$Reason.=('Key: '. $key .' Value: '. $value ."\r\n");
		}
		exit($Reason);
	}
	function Complain($Container)
	{
		$Reason='';
		foreach($Container as $key => $value)
		{
			$Reason.=('Key: '. $key .' Value: '. $value ."\r\n");
		}
		print_r($Reason);
		return false;
	}




	//function _($root, $search){    return RenderSearch($root, $search); }
	function RenderSearch(&$root, $search)
	{
		$scope = $search[0];
		$search = substr($search, 1);
		$renderObject=null;
		switch($scope)
		{
			case '@': $renderObject=self::GetRenderable($root, $search); break;
			case '#': $renderObject=self::GetRenderableByPageID($root, $search); break;
			case '.': $renderObject=self::GetRenderablesByClass($root, $search); break;
			default:  $renderObject=\Approach\Render\XML::GetByTag($root, $search); break;
		}

		return $renderObject;
	}

	function GetRenderable(\Approach\Render\Node &$SearchRoot, $SearchID)
	{
		if($SearchRoot->_render_id == $SearchID) return $SearchRoot;

		foreach($SearchRoot->nodes as $renderObject)
		{
				$result = self::GetRenderable($renderObject,$SearchID);
				if($result instanceof \Approach\Render\HTML)
				{
					if($result->id == $SearchID) return $result;
				}
		}
	}



	function GetRenderablesByTag(&$root, $tag)
	{
		$Store=Array();

		foreach($root->children as $child)   //Get Head
		{
			if($child->tag == $tag)
			{
				$Store[]=$child;
			}
			foreach($child->children as $children)
			{
				$Store = array_merge($Store, self::GetRenderablesByTag($children, $tag));
			}
		}
		return $Store;
	}

	function GetRenderablesByClass(&$root, $class)
	{
		$Store = array();

		foreach($root->children as $child)   //Get Head
		{
			$t=$child->classes;
			$child->buildClasses();

			if(strpos($child->classes,$class))
			{
				$Store[]=$child;
			}
			foreach($child->children as $children)
			{
				$Store = array_merge($Store, self::GetRenderablesByClass($children, $class));
			}
			$child->classes=$t;
		}
		return $Store;
	}

	function GetRenderableByPageID(&$root,$PageID)
	{
		$Store = new \Approach\Render\HTML('div');
		$Store->id = 'DEFAULT_ID___ELEMENT_NOT_FOUND';
		foreach($root->children as $child)   //Get Head
		{
			if($child->pageID == $PageID)
			{
				$Store = $child;
				return $child;
			}
			foreach($child->children as $children)
			{
				$Store = self::GetRenderableByPageID($children, $PageID);
				if($Store->pageID == $PageID) return $Store;
			}
		}
		return $Store;
	}


	// mysite.com/page[ComponentType][0..1000].token(*Component);
	// mysite.com/page [mycomponent][0..111].token(*OtherComponent) ; filter
	// [mycomponent][0..111].token(*OtherComponent)',
	function resolveUriSelector($selector_string )
	{
		// Parse selector portion of URL sub-component
		$cursor = 0;

		$index	= [
			'open_type'		=>	false,			// Component Name
			'close_type'	=>	false,
			// 'open_group'	=>	false,			// Component Group
			// 'close_group'	=>	false,
			'open_range'	=>	false,			// Component Group Index
			'close_range'	=>	false
		];
		$selector_type = NULL;	// If no range at all
		$selector_range = '*';	// If you ignored the 2nd part of range, select all. Same as [Type][*]
		$remainder = NULL;

		// Scan URL sub-component for square brackets in mysite.com/page[Type][0..99]
		$L=strlen($selector_string);
		for (; $cursor<$L; $cursor++)
		{
			$character = $selector_string[$cursor];

			if (false === $index['open_type'] && $character === '[') {
				$index['open_type'] = $cursor;
			} else if (false === $index['close_type'] && $character === ']'){
				$index['close_type'] = $cursor;
			}
			// else if (false === $index['open_group'] && $character === '['){
			// 	$index['open_group'] = $cursor;
			// } else if (false === $index['close_group'] && $character === ']'){
			// 	$index['close_group'] = $cursor;
			// }
			else if (false === $index['open_range'] && $character === '['){
				$index['open_range'] = $cursor;
			} else if (false === $index['close_range'] && $character === ']'){
				$index['close_range'] = $cursor;
			}
		}

		// Extract component type name From URL mysite.com/page[Type][0..99]
		if(false !== $index['open_type'] && false !== $index['close_type']){
			$selector_type = substr($selector_string, $index['open_type']+1, $index['close_type'] - $index['open_type']-1);
			if( $L > $index['close_type']){
				$remainder = substr($selector_string, $index['close_type']+1);
			} else{
				$remainder = NULL;
			}
		}

		// if(false !== $index['open_group'] && false !== $index['close_group']){
		// 	$selector_type = substr($selector_string, $index['open_group']+1, $index['close_group'] - $index['open_group']-1);
		// 	if( $L > $index['close_group']){
		// 		$remainder = substr($selector_string, $index['close_group']+1);
		// 	} else{
		// 		$remainder = NULL;
		// 	}
		// }

		// Extract indexed range From URL mysite.com/page[Type][0..99]
		if(false !== $index['open_range'] && false !== $index['close_range']){
			$selector_range = substr($selector_string, $index['open_range']+1, $index['close_range'] - $index['open_range'] - 1);
			if( $L > $index['close_range']){
				$remainder = substr($selector_string, $index['close_range']+1);
			}	else{
				$remainder = NULL;
			}

			$range_text = trim($selector_range);
			if(strpos($range_text,'..') !== false){							// Selecting a range of components
				$selector_range = explode('..', $range_text);
				$selector_range[0] = trim($selector_range[0]);
				$selector_range[1] = trim($selector_range[1]);
			}
			elseif($range_text == '*'){										// Selecting all components
				$selector_range = [NULL,NULL];
			}
			elseif(is_numeric($range_text)){								// Selecting single components
				$selector_range = [(int)$range_text];
			}
			else{															// Maybe someone customized ResolveComponents and ComponentList uses keys
				$selector = $range_text;
			}
		}

		return [
			'selector_type'		=> $selector_type,
			// 'selector_group'	=> $selector_type,
			'selector_range' 	=> $selector_range,
			'remainder'			=> $remainder
		];
	}

	/*
	*  AMAZON APIS UTILITY FUNCTIONS
	*/

	function parseAmazonFlat($input)
	{
		$data = explode("\n",$input);

		$column_keys = explode("\t", array_shift($data) );
		$product_list = [];

		foreach($data as $line)
		{
			$product = [];
			if(empty($line)) continue;
			$values = explode("\t", $line);
			try{ for($i=0, $L=count($column_keys); $i < $L; ++$i)
			{
				$key = $column_keys[$i];
				$values[$i] = str_replace(['&', '\'', '"'],
										['&amp;','&apos;','&quot;'],
										$values[$i]
								);
				$product[ $key ] = utf8_encode($values[ $i ]);
			} }
			catch(\Exception $e) { var_dump($line); }

			$product_list[] = $product;
		}

		return $product_list;
	}

	function parseAmazonFinance($input)
	{
		//$data = explode("\n",$input);
		$data = str_getcsv($input, "\n");
		$column_keys = str_getcsv( array_shift($data), ',' , '"', '\\');

		while(count($column_keys) < 4)
			$column_keys = str_getcsv( array_shift($data), ',' , '"', '\\');
		foreach($column_keys as &$k){
			var_dump($k);
			$k = str_replace(   ['"','order ', ' order','product '],
								['','','',''],
								$k
							);
	//      var_dump($k);
		//    echo '---';
			}
		$item_list = [];
		foreach($data as $line)
		{
			if(empty($line)) continue;  $item = [];
			$values = str_getcsv($line, ',' , '"', '\\');
			try{ for($i=0, $L=count($column_keys); $i < $L; ++$i)
			{
				$key = $column_keys[$i];
			// if($key=='description' || $key=='id' || $key=='type') continue;

				$values[$i] = str_replace(['&', '\'', '"'],
										['&amp;','&apos;','&quot;'],
										$values[$i]
								);
				$item[ $key ] = utf8_encode($values[ $i ]);
			} }
			catch(\Exception $e) { var_dump($values); }

			$item_list[] = $item;
		}

		return $item_list;
	}

	function isDecimal($v){
		if(!is_float($v)) return false;
		return ($v - floor($v)) * 100 == floor(($v - floor($v)) * 100);
	}

	function isDateTime($v){
		if(!is_string($v)) return false;
		return strtotime($v) !== false;
	}


	const Block = 0;
	const BlockIndex = 1;

	function translateRankToBlock( int $cursor_rank, int $block_size){
		$a = false;
		$b = false;

		if($block_size < $cursor_rank){			// $a is the offset, $b is the block number
			$a = $cursor_rank % $block_size;
			$b = ($cursor_rank - $a) / $block_size;
		}
		elseif($block_size >= $cursor_rank){	// correction until we're passed the first block
			$a = $cursor_rank;
			$b = 0;
		}
		return [
			self::Block		=> $b,
			self::BlockIndex	=> $a
		];
	}

	function translateBlockToRank( array $cursor, int $block_size){
		$b = $cursor[ self::Block ];
		$a = $cursor[ self::BlockIndex ];

		return $b * $block_size + $a;
	}

	function translateBlockCoordinate($cursor_block, $cursor_index, $block_a, $block_b){
		$rank = $this->translateBlockToRank(
			[
				$cursor_block,
				$cursor_index
			],
			$block_a
		);
		return $this->translateRankToBlock($rank, $block_b);
	}


	function apply(array $range, $fn, $i = 0, $L = NULL)
	{
		for ($L = $L ?? count($range); $i < $L; $i++) {
			$fn($i, $range[$i]);
		}
	}
}