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
	case region				= 10;
	case world				= 11;
	case cosmos				= 12;


	// e.g. path::statics->path($project_dir);    // '/srv/project/static/'
	public function get(): string
	{
		return match ($this) {
			self::work         => (Scope::$Active->project ?? 'Approach') . '\\Work',
			self::render       => (Scope::$Active->project ?? 'Approach') . '\\Render',
			self::resource     => (Scope::$Active->project ?? 'Approach') . '\\Resource',
			self::imprint      => (Scope::$Active->project ?? 'Approach') . '\\Imprint',
			self::component    => (Scope::$Active->project ?? 'Approach') . '\\Component',
			self::composition  => (Scope::$Active->project ?? 'Approach') . '\\Composition',
			self::service      => (Scope::$Active->project ?? 'Approach') . '\\Service',
			self::instrument   => (Scope::$Active->project ?? 'Approach') . '\\Instrument',
			self::ensemble     => (Scope::$Active->project ?? 'Approach') . '\\Ensemble',
			self::orchestra    => (Scope::$Active->project ?? 'Approach') . '\\Orchestra',
			self::region       => (Scope::$Active->project ?? 'Approach') . '\\Region',
			self::world        => (Scope::$Active->project ?? 'Approach') . '\\World',
			self::cosmos       => (Scope::$Active->project ?? 'Approach') . '\\Cosmos',
		};
	}

	public function set(string $value): void
	{
		Scope::$context[context::layer][$this->value] = $value;
	}
};
