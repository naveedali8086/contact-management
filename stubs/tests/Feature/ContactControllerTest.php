<?php

namespace Tests\Feature;

use App\Enums\ContactBelongsTo;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    // This trait resets the database after each test.
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create()
        );
    }

    /** @test */
    public function it_can_create_a_contact()
    {
        $contact = Contact::factory()->make()->toArray();
        // following two attributes tell to whom this contact would belong to
        $contact['belongs_to'] = fake()->randomElement(ContactBelongsTo::cases());
        $contact['belongs_to_id'] = Customer::factory()->create()->value('id');

        $response = $this->postJson('/api/contacts', $contact);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['data' => $this->getContactAttributes()]);

        $this->assertEquals($contact['channel'], $response->json('data.channel'));
        $this->assertEquals($contact['channel_value'], $response->json('data.channel_value'));
    }

    /** @test */
    public function it_cannot_create_a_contact_with_invalid_data()
    {
        $contact = Contact::factory()->make()->toArray();
        $contact['channel'] = ''; // emptying required field to create validation errors

        $response = $this->postJson('/api/contacts', $contact);

        $response
            ->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['channel']);

        // Because Contact's channel_value is unique for each contactable_type, so making sure that the
        // Contact with the specified channel_value's ($contact['channel_value']) does not exist in DB
        $this->assertDatabaseMissing('contacts', [
            'channel' => $contact['channel'],
            'channel_value' => $contact['channel_value']
        ]);
    }

    /** @test */
    public function it_can_update_a_contact()
    {
        $contactToBeUpdated = Contact::factory()
            // As per Laravel Docs:
            // Magic methods may not be used to create morphTo relationships.
            // Instead, the for method must be used directly and the name of
            // the relationship must be explicitly provided
            ->for(Customer::factory(), 'contactable')
            ->create()
            ->toArray();

        $newContact = Contact::factory()->make()->toArray();

        $response = $this->putJson("/api/contacts/{$contactToBeUpdated['id']}", $newContact);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getContactAttributes()]);

        $this->assertDatabaseHas('contacts', array_merge(['id' => $contactToBeUpdated['id']], $newContact));
    }

    /** @test */
    public function it_cannot_update_a_contact_with_invalid_data()
    {
        $contact = Contact::factory()
            ->for(Customer::factory(), 'contactable')
            ->create()
            ->toArray();

        $invalidData = [...$contact];
        $invalidData['channel'] = ''; // emptying required field to create validation errors

        $response = $this->putJson("/api/contacts/{$contact['id']}", $invalidData);

        $response
            ->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['channel']);

        $this->assertDatabaseMissing('contacts', $invalidData);
    }

    /** @test */
    public function it_can_soft_delete_a_contact()
    {
        $contact = Contact::factory()
            ->for(Customer::factory(), 'contactable')
            ->create();

        $response = $this->deleteJson("/api/contacts/$contact->id");

        $response->assertStatus(204);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function it_can_get_a_single_contact()
    {
        $contact = Contact::factory()
            ->for(Customer::factory(), 'contactable')
            ->create()
            ->toArray();

        $response = $this->getJson("/api/contacts/{$contact['id']}");

        $response->assertStatus(200);

        $this->assertEquals(
            Arr::except($contact, ['contactable_id', 'contactable_type']),
            $response->json('data')
        );
    }

    /** @test */
    public function it_can_get_all_contacts()
    {
        Contact::factory()
            ->count(10)
            ->for(Customer::factory(), 'contactable')
            ->create();

        $response = $this->getJson('/api/contacts');

        $response
            ->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    // '*' means there can be multiple items in the 'data' array.
                    '*' => $this->getContactAttributes()
                ]
            ]);
    }

    private function getContactAttributes(array $except = []): array
    {
        $addCustomerAttrs = [
            'id',
            'channel',
            'channel_other',
            'channel_value',
            'created_at',
            'updated_at'
        ];

        return array_diff($addCustomerAttrs, $except);
    }
}
