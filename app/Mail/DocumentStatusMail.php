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
    public $documentUrl;

    public function __construct($document, $status, $role)
    {
        $this->document = $document;
        $this->status = $status;

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        // Tentukan URL berdasarkan role penerima
        $basePath = $role === 'kepdes' ? 'kepdes' : 'sekdes';

        // Tentukan path berdasarkan status
        $this->documentUrl = match ($status) {
            'Arsip' => "{$frontendUrl}/{$basePath}/arsip",
            default => "{$frontendUrl}/{$basePath}/dokumen",
        };
    }

    public function build()
    {
        // Mapping status default → nama view
        $statusViewMap = [
            'Draft'     => 'submitted',
            'Disetujui' => 'approved',
            'Ditolak'   => 'rejected',
            'Publish'   => 'published',
            'Arsip'     => 'archived',
        ];

        // Deteksi kondisi khusus: resubmitted
        // (status baru Draft, tapi sebelumnya Ditolak)
        $viewName = $statusViewMap[$this->status] ?? strtolower($this->status);
        if (isset($this->document->old_status) && $this->status === 'Draft' && $this->document->old_status === 'Ditolak') {
            $viewName = 'resubmitted';
        }

        return $this->subject("Status Dokumen: {$this->status}")
            ->view("emails.document_{$viewName}")
            ->with([
                'document' => $this->document,
            ]);
    }
}
