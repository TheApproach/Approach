<?php


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

class FilterParser
{
    public mixed $url;
    public array $properties = [];
    public array $comparisons = [];
    public int $p_count = 0;
    public int $c_count = 0;

    public function __construct(mixed $url)
    {
        $this->url = $url;
    }

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

    public static array $Operations = [
        self::ASSIGN => ':',
        self::EQUAL_TO => ' eq ',
        self::NOT_EQUAL_TO => ' ne ',
        self::LESS_THAN => ' lt ',
        self::GREATER_THAN => ' gt ',
        self::_AND_ => ' AND ',
        self::_OR_ => ' OR ',
        self::_HAS_ => ' HAS ',
        self::LESS_THAN_EQUAL_TO => ' le ',
        self::GREATER_THAN_EQUAL_TO => ' ge ',
        self::RANGE => '..',
        self::OPEN_DIRECTIVE => '{',
        self::CLOSE_DIRECTIVE => '}',
        self::OPEN_GROUP => '(',
        self::CLOSE_GROUP => ')',
        self::OPEN_INDEX => '[',
        self::CLOSE_INDEX => ']',
        self::OPEN_WEIGHT => '{',
        self::CLOSE_WEIGHT => '}',
        self::NEED_PREFIX => '$',
        self::REJECT_PREFIX => '!',
        self::WANT_PREFIX => '~',
        self::DELIMITER => ',',
    ];

    function getDelimiterPositionEfficient($haystack)
    {
        $length = strlen($haystack);
        $delimiters = [
            ':',
            '/',
            '?',
            '#',
            '[',
            ']',
            '@',
            '!',
            '$',
            '&',
            '\'',
            '(',
            ')',
            '*',
            '+',
            ',',
            ';',
            '=',
        ];
        $lowestIndex = INF;

        foreach ($delimiters as $delimiter) {
            $currentCharPositions = [];
            $charLength = strlen($delimiter);

            for ($i = 0; $i <= $length - $charLength; $i++) {
                if (substr($haystack, $i, $charLength) === $delimiter) {
                    $currentCharPositions[] = $i;
                }
            }

            if (!empty($currentCharPositions) && min($currentCharPositions) < $lowestIndex) {
                $lowestIndex = min($currentCharPositions);
            }
        }

        return $lowestIndex === INF ? -1 : $lowestIndex;
    }

    function extractRanges($string): array|bool
    {
        $result = array();
        $start = 0;

        while (($openPos = strpos($string, self::$Operations[self::OPEN_INDEX], $start)) !== false) {
            $closePos = strpos($string, self::$Operations[self::CLOSE_INDEX], $openPos);

            if ($closePos === false) {
                return false;
            }

            $content = substr($string, $openPos + 1, $closePos - $openPos - 1);
            $result[] = $content;

            $start = $closePos + 1;
        }

        return $result;
    }

    function parseRange($range): array|bool
    {
        $range = trim($range);

        if (empty($range)) {
            return false;
        }

        $parts = self::splitString($range);
        $result = [];

        foreach ($parts as $part) {
            $part = trim($part);
            $parsedPart = $this->parsePart($part);
            $result[] = $parsedPart;
        }

        return $result;
    }

    function parsePart($part): array
    {
        // Check for AND, OR, HAS
        $logicalOps = [self::$Operations[self::_AND_], self::$Operations[self::_OR_], self::$Operations[self::_HAS_]];
        foreach ($logicalOps as $op) {
            if (($pos = strpos($part, $op)) !== false) {
                $left = trim(substr($part, 0, $pos));
                $right = trim(substr($part, $pos + strlen($op)));
                if (self::isRange($left)) {
                    $left = self::parseRange($left);
                }
                if (self::isRange($right)) {
                    $right = self::parseRange($right);
                }

                $this->comparisons[] = $this->c_count;
                $this->c_count++;
                return [$left, $op, $right];
            }
        }

        foreach (self::$Operations as $opValue) {
            if (($pos = strpos($part, $opValue)) !== false) {
                $field = trim(substr($part, 0, $pos));
                $value = trim(substr($part, $pos + strlen($opValue)));
                if (!empty($field) && $value !== '') {
                    if (self::isRange($value)) {
                        $value = substr($value, 1, -1);
                        $value = self::parseRange($value);
                    }

                    $this->comparisons[] = $this->c_count;
                    $this->c_count++;
                    return [$field, $opValue, $value];
                }
            }
        }

        $this->properties[] = $this->p_count;
        $this->p_count++;
        return [$part];
    }

    private function splitString($string): array
    {
        $result = [];
        $start = 0;
        $length = strlen($string);

        while (($pos = strpos($string, ',', $start)) !== false) {
            $result[] = substr($string, $start, $pos - $start);
            $start = $pos + strlen(',');
        }

        if ($start < $length) {
            $result[] = substr($string, $start);
        }

        return $result;
    }

    public function parsePath(string $path): array|string
    {
        $first_bracket = strpos($path, self::$Operations[self::OPEN_INDEX]);

        $name = substr($path, 0, $first_bracket);
        $ranges = self::extractRanges($path);
        if ($first_bracket === false || $ranges === false) {
            return $path;
        }

        $res = [];
        $res['name'] = $name;
        $res['ranges'] = [];

        foreach ($ranges as $range) {
            $res['ranges'][] = self::parseRange($range);
        }

        return $res;
    }

    function isRange($path): bool
    {
        // check if any one operator is present
        $operators = [
            self::$Operations[self::EQUAL_TO],
            self::$Operations[self::NOT_EQUAL_TO],
            self::$Operations[self::LESS_THAN],
            self::$Operations[self::GREATER_THAN],
            self::$Operations[self::LESS_THAN_EQUAL_TO],
            self::$Operations[self::GREATER_THAN_EQUAL_TO],
            self::$Operations[self::_AND_],
            self::$Operations[self::_OR_],
            self::$Operations[self::_HAS_],
        ];

        foreach ($operators as $operator) {
            if (str_contains($path, $operator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse a URI into its components
     *
     * @return array An array out of the components of the URL
     * @access public
     * @static
     */
    public function parseUri(): array
    {
        $primary = parse_url($this->url);
        $res = [];

        $pathCombined = $primary['path'];
        // only till a delimiter
        $first_delim = self::getDelimiterPositionEfficient($pathCombined);
        $first_delim = false;
        $pathCombined = $first_delim === false ? $pathCombined : substr($pathCombined, 0, $first_delim);

        // check if there is a function call in the end
        // like [].hello()
        // so, detect the first .
        //after the last ]
        $last_bracket = strrpos($pathCombined, self::$Operations[self::CLOSE_INDEX]);
        $first_dot = strpos($pathCombined, '.', $last_bracket);
        if ($first_dot !== false) {
            $res['function'] = substr($pathCombined, $first_dot + 1);
        }

        $paths = explode('/', $pathCombined);
        $paths = array_filter($paths, function ($path) {
            return $path !== '';
        });
        $res['scheme'] = $primary['scheme'] ?? '';
        $res['host'] = $primary['host'] ?? '';
        $res['port'] = $primary['port'] ?? '';
        $res['queries'] = [];

        if (isset($primary['query'])) {
            $queries = explode('&', $primary['query']);
            foreach ($queries as $query) {
                $parts = explode('=', $query);
                $res['queries'][$parts[0]] = $parts[1];
            }
        }

        $res['paths'] = [];

        foreach ($paths as $path) {
            $res['paths'][] = self::parsePath($path);
        }

        return $res;
    }
}