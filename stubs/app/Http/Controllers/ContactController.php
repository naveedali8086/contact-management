<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Support\Arr;

class ContactController extends Controller
{

    /**
     * Get a list of Contacts.
     */
    public function index()
    {
        $contact = Contact::paginate(10);

        return new ContactCollection($contact);
    }

    /**
     * Store a Contact in storage.
     */
    public function store(StoreContactRequest $request)
    {
        // i.e. It could be: App\Models\Customer, App\Models\User, ...
        $parentModelClass = $request->input('belongs_to');

        $parentModel = $parentModelClass::findOrFail(
            $request->input('belongs_to_id')
        );

        $contact = $parentModel->contacts()->create(
            Arr::except($request->validated(), ['belongs_to', 'belongs_to_id'])
        );

        if ($contact) {
            return (new ContactResource($contact))
                ->response()
                ->setStatusCode(201);
        } else {
            abort(500, 'Failed to store the contact');
        }
    }

    /**
     * Get the specified Contact.
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    /**
     * Update the specified Contact in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $updated = $contact->update($request->validated());

        if ($updated) {
            return new ContactResource($contact);
        } else {
            abort(500, 'Failed to update the contact');
        }
    }

    /**
     * Remove the specified Contact from storage.
     */
    public function destroy(Contact $contact)
    {
        if ($contact->delete()) {
            return response(null, 204);
        } else {
            abort(500, 'Failed to delete the contact');
        }
    }

}
