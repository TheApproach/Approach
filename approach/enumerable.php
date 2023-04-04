<?php

namespace Approach;

abstract class enumerable{
    public static function cases(int $filter = \ReflectionClassConstant::IS_PUBLIC ) {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants(filter: $filter);
    }
}
