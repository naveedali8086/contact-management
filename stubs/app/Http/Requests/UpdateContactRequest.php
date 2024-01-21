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

    public function rules(): array
    {
        $rules = parent::rules();
        // deleting following two attributes' rules as they are not needed while updating a contact
        unset($rules['belongs_to']);
        unset($rules['belongs_to_id']);
        info(json_encode($rules));
        return $rules;
    }

    public function after()
    {
        $contactId = $this->route('contact')->id;
        return [
            function (Validator $validator) use ($contactId) {
                Contact::addErrorIfContactExists($validator, 'channel_value', $contactId);
            }
        ];
    }
}
