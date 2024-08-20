<?php

namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Render\Container;
use \Approach\Render\Node;
use \Approach\Render\Node\Keyed;
use \Approach\nullstate;

/**
 * field aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Field
 * @category	Property
 * 
 */

class quality extends Container
{
    const _index_map        = 0;
    const _case_map            = 1;
    const label                = 2;
    const description        = 3;
    const keywords            = 4;
    const children            = 5;
    const related                = 6;
    const type                = 7;
    const state                = 8;


    const _approach_quality_profile_ = [
        self::_index_map           => [],
        self::_case_map            => [],
        self::label                => [],
        self::description          => [],
        self::keywords            => [],
        self::children            => [],
        self::related             => [],
        self::type                => [],
        self::state               => [],
    ];

    public static function match(int|string|\Stringable $case)
    {
        if (is_int($case)) return static::_approach_quality_profile_[self::_case_map][$case]                 ?? false;
        else                 return static::_approach_quality_profile_[self::_index_map][strtolower($case)]     ?? false;
    }

    public static function getType($case = null)
    {
        if (!is_int($case)) {
            $case = static::match($case);
        }
        if ($case === null) {
            return nullstate::undeclared;
        }
        return static::_approach_quality_profile_[self::type][$case];
    }

    public static function getProfileProperties($which = null)
    {
        if ($which == null) {
            return [
                'label' => self::label,
                'description' => self::description,
                'keywords' => self::keywords,
                'children' => self::children,
                'related' => self::related,
                'type' => self::type,
                'state' => self::state,
            ];
        } elseif (is_string($which) || $which instanceof \Stringable) {
            return match ($which) {
                'label' => self::label,
                'description' => self::description,
                'keywords' => self::keywords,
                'children' => self::children,
                'related' => self::related,
                'type' => self::type,
                'state' => self::state,
                default                =>    nullstate::undeclared,
            };
        } elseif (is_int($which)) {
            return match ($which) {
                 self::label => 'label',
                 self::description => 'description', 
                 self::keywords => 'keywords',
                 self::children => 'children',
                 self::related => 'related',
                 self::type => 'type',
                 self::state => 'state',
                 default => nullstate::undeclared,
            };
        }
        return nullstate::undeclared;
    }


    public static function getProfile($field, $what = null)
    {
        $constants = null;
        switch ($what) {
            case self::label:
                $constants = static::_approach_quality_profile_[self::label][static::match($field)];
                break;
            case self::type:
                $constants = static::getType($field);
                break;
            default:
                $constants = [];
                foreach (static::getProfileProperties() as $key => $value) {
                    $constants[$key] = static::getProfile($field, $key);
                }
                break;
        }
    }
}
