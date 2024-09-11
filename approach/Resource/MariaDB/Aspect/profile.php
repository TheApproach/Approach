<?php
namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Resource\Aspect\Aspect;

class profile
{
    static array $profile = [
        Aspect::operation => [],
        Aspect::field => [],
        Aspect::quality => [],
        Aspect::quantity => [],
        Aspect::map => [],
        Aspect::state => [],
        Aspect::access => [],
    ];
    public static function getProfile()
    {
        return [];
    }
    public static function getCoolerProfile()
    {
        return [];
    }
    public static function getCases()
    {
        return [];
    }
    public static function getIndices()
    {
        return [];
    }
}
