<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Mail\Mailable;

class DocumentPublishedMail extends Mailable
{
    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('[LDBD] Dokumen Telah Diterbitkan')
            ->view('emails.document_published');
    }
}
