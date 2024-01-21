<?php

namespace App\Http\Requests;

use App\Enums\ContactBelongsTo;
use App\Enums\ContactChannels;
use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'channel' => ['required', 'max:255', Rule::enum(ContactChannels::class)],

            'channel_other' => ['required_if:channel,' . ContactChannels::OTHER->value, 'max:255'],

            'channel_value' => [
                // Adding dynamic rules on "channel_value" attribute based on "channel's" attribute value
                Rule::when(function ($input) {
                    return in_array(
                        $input['channel'],
                        [ContactChannels::EMAIL->value, ContactChannels::MOBILE->value]
                    );
                },
                    Contact::channelValueAdditionalRules($this->input('channel')),
                    // default rules, that will always apply on "channel_value" attribute
                    // TODO: check if default rules would always be appended or needs to set explicitly
                    ['required', 'max:255'])
            ],

            'belongs_to' => ['required', Rule::enum(ContactBelongsTo::class)],

            'belongs_to_id' => ['required']

            // TODO: also make sure that channel is email, value is a proper email and if phone number/whatsApp
            // it is a valid phone no.
            // TODO: need to think about whatsapp_enabled?
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                // TODO: check if after hook only executes after all validations are passed
                // if no, then add one check below that will check if "channel_value" is empty or not
                Contact::addErrorIfContactExists($validator, 'channel_value');
            }
        ];
    }
}
