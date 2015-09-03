<?php

namespace Craft;

abstract class Sitemap_Priority extends BaseEnum
{
    /**
     * Formats a number into a valid priority value.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function formatValue($value)
    {
        return number_format($value, 1);
    }

    /**
     * {@inheritdoc} BaseEnum::isValidValue()
     */
    public static function isValidValue($value, $strict = false)
    {
        $values = static::getConstants();

        return is_numeric($value) && in_array(self::formatValue($value), $values, $strict);
    }

    /**
     * {@inheritdoc} BaseEnum::getConstants()
     */
    public static function getConstants()
    {
        return array_map(array(__CLASS__, 'formatValue'), range(0, 1, 0.1));
    }
}
