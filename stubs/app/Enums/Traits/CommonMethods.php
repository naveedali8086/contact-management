<?php

namespace App\Enums\Traits;

trait CommonMethods
{
    public static function getValues($asString = false): array|string
    {
        return $asString ?
            implode(',', array_column(self::cases(), 'value')) :
            array_column(self::cases(), 'value');
    }

    public static function getNames($asString = false): array|string
    {
        return $asString ?
            implode(',', array_column(self::cases(), 'name')) :
            array_column(self::cases(), 'name');
    }

}
