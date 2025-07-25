<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $_data;

    /**
     * Create a new message instance.
     */
    public function __construct($details)
    {
        $this->_data = $details;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Confirmation Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.payment-success',
            with: $this->_data,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $invoiceId = $this->_data['tranId'];
        $invoiceUrl = url("invoice/{$invoiceId}");

        // Fetch the PDF content
        $response = Http::get($invoiceUrl);

        // Check if the response is successful and is a PDF
        if ($response->successful() && $response->header('content-type') === 'application/pdf') {
            return [
                Attachment::fromPath(public_path('assets/Gravity_Logo.png')),
                Attachment::fromData(
                    fn() => $response->body(),
                    "invoice_{$invoiceId}.pdf"
                )->withMime('application/pdf'),
            ];
        } else {
            // Fallback: only attach the logo, or handle error as needed
            return [
                Attachment::fromPath(public_path('assets/Gravity_Logo.png')),
            ];
        }
    }
}
