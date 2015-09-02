<?php

namespace Craft;

abstract class SitemapPriority extends BaseEnum
{
    /**
     * {@inheritdoc} BaseEnum::isValidValue
     */
    public static function isValidValue($value, $strict = false)
    {
        $values = static::getConstants();

        return is_numeric($value) && in_array($value, $values, $strict);
    }

    /**
     * {@inheritdoc} BaseEnum::getConstants
     */
    public static function getConstants()
    {
        return range(0, 1, 0.1);
    }
}
