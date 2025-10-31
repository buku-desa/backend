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
    public $documentUrl;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->sekdes = User::where('role', 'sekdes')->first();

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
        return $this->subject('Dokumen Anda Ditolak oleh Kepala Desa')
            ->view('emails.document_rejected')
            ->with([
                'document' => $this->document,
                'sekdes' => $this->sekdes,
            ]);
    }
}
