<?php

namespace Approach\Service;

class Decode
{
    public static array $as = [];

    /**
     * @param format $format
     * @param mixed $data
     * @return array the decoded data in the specified format
     * @throws \InvalidArgumentException if the format is not registered
     */
    public static function as(format $format, mixed $data): mixed
    {
        # handle the case where the format is not registered
        if (!static::has($format)) {
            throw new \InvalidArgumentException(sprintf('The format %s decoder is not registered', $format->name));
        }

        return static::$as[$format->value]($data);
    }

    /**
     * @param format $format 
     * @param callable $decoder the decoder function
     * @param bool $overwrite if true, overwrite the decoder if it is already registered
     * @return void
     * @throws \InvalidArgumentException if the format decoder is already registered and $overwrite is false
     */
    public static function register(format $format, callable $decoder, bool $overwrite = false): void
    {
        if (static::has($format) && !$overwrite) {
            throw new \InvalidArgumentException(sprintf('The format %s decoder is already registered', $format->name));
        }

        static::$as[$format->value] = $decoder;
    }

    /**
     * @param format $format
     * @return bool true if the format decoder is registered, false otherwise
     * 
     */
    public static function has(format $format): bool
    {
        return isset(static::$as[$format->value]);
    }
}

#register raw decoder
Decode::register(format::raw, function ($data) {
    return $data;
});

Decode::register(format::json, function (array $data) {
    $decoded_data = [];
    foreach ($data as $i => $value) {
        $decoded_data[] = json_decode($value, true);
    }

    return $decoded_data;
});
