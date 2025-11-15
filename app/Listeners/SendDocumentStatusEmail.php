<?php

namespace App\Listeners;

use App\Events\DocumentStatusChanged;
use App\Mail\DocumentStatusMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDocumentStatusEmail
{
    public function handle(DocumentStatusChanged $event)
    {
        $doc = $event->document;

        // Ambil user berdasarkan role
        $kepdes = User::where('role', 'kepdes')->first();
        $sekdes = User::where('role', 'sekdes')->first();

        // Pastikan dua-duanya ada
        if (!$kepdes && !$sekdes) {
            Log::warning('Tidak ada user dengan role kepdes atau sekdes untuk notifikasi dokumen.');
            return;
        }

        switch ($event->newStatus) {
            case 'Draft':
                // Jika sebelumnya ditolak, maka dianggap resubmit
                if ($event->oldStatus === 'Ditolak') {
                    $doc->old_status = 'Ditolak';
                    if ($kepdes) {
                        Mail::to($kepdes->email)->send(new DocumentStatusMail($doc, 'Draft', 'kepdes'));
                    }
                } else {
                    if ($kepdes) {
                        Mail::to($kepdes->email)->send(new DocumentStatusMail($doc, 'Draft', 'kepdes'));
                    }
                }
                break;

            case 'Disetujui':
                // Kirim ke Sekdes (dokumennya disetujui oleh Kepdes)
                if ($sekdes) {
                    Mail::to($sekdes->email)->send(new DocumentStatusMail($doc, 'Disetujui', 'sekdes'));
                }
                break;

            case 'Ditolak':
                // Kirim ke Sekdes (dokumennya ditolak oleh Kepdes)
                if ($sekdes) {
                    Mail::to($sekdes->email)->send(new DocumentStatusMail($doc, 'Ditolak', 'sekdes'));
                }
                break;

            case 'Arsip':
                // Sekdes & Kepdes sama-sama dapat notifikasi Arsip
                if ($kepdes) {
                    Mail::to($kepdes->email)->send(new DocumentStatusMail($doc, 'Arsip', 'kepdes'));
                }
                if ($sekdes) {
                    Mail::to($sekdes->email)->send(new DocumentStatusMail($doc, 'Arsip', 'sekdes'));
                }
                break;
        }
    }
}
