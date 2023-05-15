<?php

namespace Approach\Service;

class Encode
{
    static $as = [];

    /**
     * @param format $format
     * @param array $data
     * @return mixed the encoded data in the specified format
     * @throws \InvalidArgumentException if the format is not registered
     */
    public static function as(format $format, array $data): mixed
    {
        # handle the case where the format is not registered
        if (!static::has($format)) {
            throw new \InvalidArgumentException(sprintf('The %s format encoder is not registered', $format->name));
        }

        return static::$as[$format->value]($data);
    }


    /**
     * @param format $format 
     * @param callable $encoder the encoder function
     * @param bool $overwrite if true, overwrite the encoder if it is already registered
     * @return void
     * @throws \InvalidArgumentException if the format encoder is already registered and $overwrite is false
     */
    public static function register(format $format, callable $encoder, bool $overwrite = false): void
    {

        if (static::has($format) && !$overwrite) {
            throw new \InvalidArgumentException(sprintf('The %s format encoder is already registered', $format->name));
        }

        static::$as[$format->value] = $encoder;
    }

    /**
     * @param format $format
     * @return bool true if the format encoder is registered, false otherwise
     * 
     */
    public static function has(format $format): bool
    {
        return isset(static::$as[$format->value]);
    }
}

#register raw encoder
Encode::register(format::raw, function ($data) {
    return $data;
});

Encode::register(format::json, function (array $data) {
    $encoded_data = [];
    foreach ($data as $i => $value) {
        $encoded_data[] = json_encode($value, true);
    }
    return $encoded_data;
    // return json_encode($data);
});
