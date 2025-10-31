<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class DocumentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $sekdes; // Tambahkan ini biar bisa dipakai di blade

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->sekdes = User::where('role', 'sekdes')->first();
    }

    public function build()
    {
        return $this->subject('Dokumen Anda Ditolak oleh Kepala Desa')
            ->view('emails.document_rejected')
            ->with([
                'document' => $this->document,
                'sekdes' => $this->sekdes,
            ]);
    }
}
