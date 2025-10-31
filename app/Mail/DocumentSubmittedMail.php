<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Mail\Mailable;

class DocumentSubmittedMail extends Mailable
{
    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('[LDBD] Dokumen Baru Menunggu Persetujuan')
            ->view('emails.document_submitted');
    }
}
