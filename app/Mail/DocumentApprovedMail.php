<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $documentUrl;

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

    public function build()
    {
        return $this->subject('Dokumen Anda Disetujui oleh Kepala Desa')
            ->view('emails.document_approved');
    }
}
