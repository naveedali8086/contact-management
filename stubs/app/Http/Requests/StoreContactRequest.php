<?php

namespace App\Http\Requests;


class StoreContactRequest extends ContactRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
