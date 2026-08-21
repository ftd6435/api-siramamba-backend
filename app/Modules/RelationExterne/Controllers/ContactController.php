<?php

namespace App\Modules\RelationExterne\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RelationExterne\Mail\ContactAutoReplyMail;
use App\Modules\RelationExterne\Mail\ContactNotificationAdminMail;
use App\Modules\RelationExterne\Models\Contact;
use App\Modules\RelationExterne\Requests\ContactRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->get();

        return $this->successResponse($contacts, 'Liste des contacts');
    }

    public function store(ContactRequest $request)
    {
        $data = $request->validated();
        $contact = Contact::create($data);

        $adminAddress = config('mail.from.address');
        $adminName = config('mail.from.name');

        Mail::to($adminAddress, $adminName)->queue(
            new ContactNotificationAdminMail($contact)
        );

        Mail::to($contact->email, $contact->name)->queue(
            new ContactAutoReplyMail($contact)
        );

        return $this->successResponse($contact, 'Message envoyer avec succès');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return $this->noContentSuccessResponse('Contact supprimé avec succès');
    }
}
