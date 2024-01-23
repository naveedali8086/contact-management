<?php

namespace Database\Factories;

use App\Enums\ContactBelongsTo;
use App\Enums\ContactChannels;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Channel may be Email, Mobile, Other
            'channel' => fake()->randomElement(ContactChannels::cases()),

            // following two attributes' value will be overridden in afterMaking
            // callback as per the "channel" attribute's value i.e.
            // if channel=Email, then channel_value should be a valid email and if
            // channel=Mobile, then channel_value should be a valid mobile number.
            'channel_other' => null,
            'channel_value' => null,
            'belongs_to' => fake()->randomElement(ContactBelongsTo::cases())
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Contact $contact) {

            $contact->channel_value = $this->getChannelValue($contact->channel);

            if ($contact->channel === ContactChannels::OTHER) {
                $contact->channel_other = fake()->unique()->word();
            }

        });
    }

    public function getChannelValue(ContactChannels $channel): string
    {
        return match ($channel) {
            ContactChannels::EMAIL => fake()->unique()->safeEmail(),
            ContactChannels::MOBILE => fake()->unique()->e164PhoneNumber(),
            ContactChannels::OTHER => fake()->unique()->word(),
        };
    }
}
