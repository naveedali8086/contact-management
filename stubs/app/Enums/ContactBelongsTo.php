<?php

namespace App\Enums;

use App\Enums\Traits\CommonMethods;

enum ContactBelongsTo: string
{

    use CommonMethods;

    case CUSTOMER = 'Customer';

    // case USER = 'User';

    public static function getContactParentModelClass(ContactBelongsTo $belongsTo): string
    {
        return match ($belongsTo) {
            self::CUSTOMER => 'App\Models\Customer',
            // self::USER => 'App\Models\User',
        };
    }

}
