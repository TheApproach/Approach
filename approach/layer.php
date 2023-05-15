<?php

namespace Approach;
use \Approach\Scope;

enum layer: int
{
	case work				= 0;
	case render				= 1;
	case resource			= 2;
	case imprint			= 3;
	case component			= 4;
	case composition		= 5;
	case service			= 6;
	case instrument			= 7;
	case ensemble			= 8;
	case orchestra			= 9;
	case troupe				= 10;
	case tour				= 11;
	case theatre			= 12;


	// e.g. path::statics->path($project_dir);    // '/srv/project/static/'
	public function get($root = null): string
	{
		$root = $root ?? Scope::$Active->project ?? 'Approach';

		// If the current scope is the project scope, check if user customized the path
		if($root == Scope::$Active->project){
			if( isset(Scope::$context[context::layer][$this->value]) ){
				return Scope::$context[context::layer][$this->value];
			}
		}

		return match ($this) {
			self::work       	=> $root . '\\Work',
			self::render     	=> $root . '\\Render',
			self::resource   	=> $root . '\\Resource',
			self::imprint    	=> $root . '\\Imprint',
			self::component  	=> $root . '\\Component',
			self::composition	=> $root . '\\Composition',
			self::service    	=> $root . '\\Service',
			self::instrument 	=> $root . '\\Instrument',
			self::ensemble   	=> $root . '\\Ensemble',
			self::orchestra  	=> $root . '\\Orchestra',
			self::troupe     	=> $root . '\\Troupe',
			self::tour       	=> $root . '\\Tour',
			self::theatre    	=> $root . '\\Theatre',
		};
	}

	public function set(string $value): void
	{
		Scope::$context[context::layer][$this->value] = $value;
	}
};
