<?php

namespace App\Modules\RelationExterne\Mail;

use App\Modules\RelationExterne\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactNotificationAdminMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Contact $contact
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Site Web] Nouveau message : ' . $this->contact->subject,
            replyTo: [
                new Address($this->contact->email, $this->contact->name),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.contact-notification',
            with: [
                'contact' => $this->contact,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
