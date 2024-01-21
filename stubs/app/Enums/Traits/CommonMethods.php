<?php

namespace App\Enums\Traits;

trait CommonMethods
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function getCommaSeparatedValues(): string
    {
        return implode(',', array_column(self::cases(), 'value'));
    }

}
