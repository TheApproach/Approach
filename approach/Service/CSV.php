<?php
namespace Approach\Service;

use \Stringable;
use \Approach\nullstate;
use \Approach\path;
use \Approach\Scope;
use \Approach\Render\Node;
use \Approach\Render\Stream;
use \Approach\Resource\Resource;
use \Approach\Resource\aspect_variants;
use \Approach\Composition\Composition;
use \Approach\Component\Component;

enum test:int
{
	case one = 1;
	case two = 2;
	case three = 3;
}


class CSV extends Service
{
	//public static function load(Stringable|string $exchange, ...$opt):?Resource
	public static function load(string|Stringable|Resource $where, $heading_size = 2): ?Resource
	{
		$instream = fopen($where, 'r');
		$keys=$meta=array();

		if($heading_size > 0)
		{
			$keys = fgetcsv($instream);
			--$heading_size;
			while($heading_size > 0)
			{
				$meta[]=fgetcsv($instream);
				--$heading_size;
			}
		}
		while( $scan = fgetcsv($instream) )
		{
			$record=array();
			foreach( $scan as $key => $value )
			$record[$key]=$value;
			$where->payload[]=$record;
		}

		fclose($instream);
		return $where;
	}

	public function save(Resource $what, null|string|Stringable|Stream|Node|Resource $where = null): ?bool
	{
		$emit = null;
		$stream = fopen($where, 'w');
		try{
			$keys = array_keys($what->payload[0]);
			fputcsv($stream, $keys);
			foreach( $what->payload as $record )
			{
				$scan=array();
				foreach( $keys as $key )
					$scan[]=$record[$key];
				fputcsv($stream, $scan);
			}
		}
		catch(\Exception $e)
		{
			$emit = clone Scope::$Active->ErrorRenderable;
			$emit->content = $e->getMessage();
		}
		finally
		{
			fclose($stream);
			if($emit) throw new \Exception($emit->render());
		}
		return true;
	}
}