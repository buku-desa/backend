<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $status;

    public function __construct($document, $status)
    {
        $this->document = $document;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject("Status Dokumen: {$this->status}")
            ->view("emails.document_{$this->status}")
            ->with([
                'document' => $this->document,
            ]);
    }
}
