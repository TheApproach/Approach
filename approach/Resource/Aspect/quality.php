<?php
namespace Approach\Resource\Aspect;

/**
 * quality aspect class
 * 
 * @package        Approach
 * @subpackage    Resource
 * @version        2.0.0
 * @category    Aspect
 * @category    Quality
 * @category    Category
 * @category    Tag
 * @category    Keyword
 *     
 */
class quality extends Aspect
{
    public static $label;                                        // label for the quality
    public static $description;                                    // description of the quality
    public static $keywords;                                    // keywords for the quality
    public static $children;                                    // children qualities of the quality
    public static $related;                                        // related qualities of the quality
    public static $type;                                        // the type of the quality
    public static $state;                                        // the present state of the quality
}
