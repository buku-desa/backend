<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentArchivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Dokumen Telah Diarsipkan')
                    ->view('emails.documents.archived')
                    ->with([
                        'document' => $this->document,
                    ]);
    }
}
