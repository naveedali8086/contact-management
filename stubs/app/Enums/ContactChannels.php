<?php

namespace App\Enums;

use App\Enums\Traits\CommonMethods;

enum ContactChannels: string
{
    use CommonMethods;

    case EMAIL = 'Email';
    case MOBILE = 'Mobile';
    case OTHER = 'Other';
}
