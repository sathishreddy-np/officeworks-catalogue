<?php

// PHP start tag.

// Mail classes describe one email: who it is about, the subject, and the template.

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
// Lets the enquiry be saved with the email if we later queue it.
use Illuminate\Queue\SerializesModels;

// This email goes to the sales inbox when a customer submits the enquire form.
class EnquiryReceived extends Mailable
{
    use SerializesModels;

    // public makes $enquiry available inside the Blade email template as $enquiry.
    public function __construct(public Enquiry $enquiry) {}

    // The envelope is the outside of the email: the subject line.
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product enquiry: '.$this->enquiry->product_title,
        );
    }

    // The content points at the Blade file that draws the email body.
    public function content(): Content
    {
        return new Content(
            // resources/views/mail/enquiry-received.blade.php
            markdown: 'mail.enquiry-received',
        );
    }
}
