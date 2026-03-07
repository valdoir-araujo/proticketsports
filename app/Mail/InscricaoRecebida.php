<?php

namespace App\Mail;

use App\Models\Inscricao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscricaoRecebida extends Mailable
{
    use Queueable, SerializesModels;

    public $inscricao;

    /**
     * Create a new message instance.
     */
    public function __construct(Inscricao $inscricao)
    {
        $this->inscricao = $inscricao;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscrição Recebida - ' . $this->inscricao->evento->nome,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // Certifique-se de que este arquivo blade existe em resources/views/emails/inscricao/recebida.blade.php
            markdown: 'emails.inscricao.recebida',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}