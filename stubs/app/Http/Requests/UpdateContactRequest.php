<?php

namespace App\Http\Requests;


use App\Models\Contact;
use Illuminate\Validation\Validator;

class UpdateContactRequest extends ContactRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function after()
    {
        $contactId = $this->route('contact')->id;
        return [
            function (Validator $validator) use ($contactId) {
                Contact::addErrorIfChannelValueExists($validator, $contactId);
            }
        ];
    }
}
