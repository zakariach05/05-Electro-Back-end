<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de votre commande #' . $this->order->id . ' - Electro-05',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
        );
    }

    public function attachments(): array
    {
        // Only attach PDF if GD extension is available
        if (!extension_loaded('gd')) {
            return [];
        }
        
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.invoice', ['order' => $this->order]);
            
            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), 'Facture_' . $this->order->id . '.pdf')
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PDF attachment error: " . $e->getMessage());
            return [];
        }
    }
}
