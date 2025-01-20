<?php

declare(strict_types = 1);

namespace App\Utils;

class InputHelper
{

    /**
     * Trims a value if it's a string, returns other types as-is
     *
     * @param mixed $value
     * @return mixed
     */
    public static function trimValue(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Trims all string values in an array
     *
     * @param array $data
     * @return array
     */
    public static function trimArray(array $data): array
    {
        return array_map(fn ($item) => self::trimValue($item), $data);
    }

    public static function isValidKeyInArray(string $key, array $array): bool
    {
        if ( ! array_key_exists($key, $array) ) {
            return false;
        } else {
            return true;
        }
    }

}