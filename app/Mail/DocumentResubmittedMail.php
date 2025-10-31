<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Mail\Mailable;

class DocumentResubmittedMail extends Mailable
{
    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('[LDBD] Dokumen Revisi Diajukan Kembali')
            ->view('emails.document_resubmitted');
    }
}
