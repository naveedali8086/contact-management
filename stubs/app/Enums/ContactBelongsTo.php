<?php

namespace App\Enums;

use App\Enums\Traits\CommonMethods;

enum ContactBelongsTo: string
{

    use CommonMethods;
    case CUSTOMER = 'App\Models\Customer';
    // case USER = 'App\Models\User';

}
