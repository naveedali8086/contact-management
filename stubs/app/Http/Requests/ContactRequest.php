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
                    ['required', 'max:255'])
            ],

            'belongs_to' => ['required', Rule::enum(ContactBelongsTo::class)],

            'belongs_to_id' => ['required']
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                Contact::addErrorIfChannelValueExists($validator);
            }
        ];
    }
}
