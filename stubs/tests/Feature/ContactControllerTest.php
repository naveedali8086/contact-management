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
        $contact['belongs_to_id'] = Customer::factory()->create()->value('id');
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
        $contact = $this->createContactWithParent();
        $contact->channel_value = Contact::factory()->getChannelValue($contact->channel);

        $contact = $contact->toArray();
        $response = $this->putJson("/api/contacts/{$contact['id']}", $contact);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->getContactAttributes()]);

        // unset "belongs_to_id" as it does not exist in DB and was only required while sending a request
        unset($contact['belongs_to_id']);
        $this->assertDatabaseHas('contacts', $contact);
    }

    /** @test */
    public function it_cannot_update_a_contact_with_invalid_data()
    {
        $contact = $this->createContactWithParent()->toArray();
        $contact['channel'] = ''; // emptying required field to create validation errors

        $response = $this->putJson("/api/contacts/{$contact['id']}", $contact);

        $response
            ->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['channel']);
    }

    /** @test */
    public function it_can_soft_delete_a_contact()
    {
        $contact = $this->createContactWithParent();

        $response = $this->deleteJson("/api/contacts/$contact->id");

        $response->assertStatus(204);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function it_can_get_a_single_contact()
    {
        $contact = $this->createContactWithParent()->toArray();

        $response = $this->getJson("/api/contacts/{$contact['id']}");

        $response->assertStatus(200);

        $this->assertEquals($contact, $response->json('data'));
    }

    /** @test */
    public function it_can_get_all_contacts()
    {
        $this->createContactWithParent(10);

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
        $attrs = [
            'id',
            'channel',
            'channel_other',
            'channel_value',
            'belongs_to',
            'belongs_to_id',
            'created_at',
            'updated_at'
        ];

        return array_diff($attrs, $except);
    }
    /**
     * It creates contact's parent based on its "belongs_to" attribute's value
     *
     * @param $contactCount
     * @return mixed
     */
    private function createContactWithParent($contactCount = 1)
    {
        $contacts = Contact::factory()->count($contactCount)->make();

        $contacts->each(function (Contact $contact) {

            // Getting contact's parent model fully qualified class name
            $parentModelClass = ContactBelongsTo::getContactParentModelClass($contact->belongs_to);

            // getting contact's parent model
            $parentModel = $parentModelClass::factory()->create();

            $parentModel->contacts()->save($contact);

            $contact->makeHidden(['contactable_id', 'contactable_type']);

            // a contact must send "belongs_to_id" when creating/updating a contact
            $contact->belongs_to_id = $parentModel->id;

        });
        return $contactCount === 1 ? $contacts->first() : $contacts;
    }
}
