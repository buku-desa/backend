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
    public $documentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;

        // Generate URL ke frontend
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        // URL menuju halaman detail dokumen di frontend
        $this->documentUrl = $frontendUrl . '/documents/' . $document->id;

        // Atau kalau beda route berdasarkan jenis dokumen:
        // if ($document->jenis_dokumen === 'peraturan_desa') {
        //     $this->documentUrl = $frontendUrl . '/buku-lembaran/' . $document->id;
        // } else {
        //     $this->documentUrl = $frontendUrl . '/berita-desa/' . $document->id;
        // }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Dokumen Telah Diarsipkan')
                    ->view('emails.document_achived')
                    ->with([
                        'document' => $this->document,
                    ]);
    }
}
