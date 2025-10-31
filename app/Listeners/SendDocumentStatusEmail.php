<?php

namespace App\Listeners;

use App\Events\DocumentStatusChanged;
use App\Mail\{
    DocumentSubmittedMail,
    DocumentResubmittedMail,
    DocumentApprovedMail,
    DocumentRejectedMail,
    DocumentPublishedMail,
    DocumentArchivedMail
};
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendDocumentStatusEmail
{
    public function handle(DocumentStatusChanged $event)
    {
        $doc = $event->document;

        // Ambil user berdasarkan role (pastikan nama role sesuai di tabel)
        $kepdes = User::whereHas('roles', fn($q) => $q->where('name', 'Kepala Desa'))->first();
        $sekdes = User::whereHas('roles', fn($q) => $q->where('name', 'Sekretaris Desa'))->first();

        switch ($event->newStatus) {
            case 'Draft':
                // Jika sebelumnya ditolak, maka dianggap resubmit
                if ($event->oldStatus === 'Ditolak') {
                    if ($kepdes) {
                        Mail::to($kepdes->email)->send(new DocumentResubmittedMail($doc));
                    }
                } else {
                    if ($kepdes) {
                        Mail::to($kepdes->email)->send(new DocumentSubmittedMail($doc));
                    }
                }
                break;

            case 'Disetujui':
                if ($sekdes) {
                    Mail::to($sekdes->email)->send(new DocumentApprovedMail($doc));
                }
                break;

            case 'Ditolak':
                if ($sekdes) {
                    Mail::to($sekdes->email)->send(new DocumentRejectedMail($doc));
                }
                break;

            case 'Publish':
                $recipients = [];
                if ($sekdes) $recipients[] = $sekdes->email;
                if ($kepdes) $recipients[] = $kepdes->email;
                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new DocumentPublishedMail($doc));
                }
                break;

            case 'Arsip':
                $kepdes = User::where('role', 'kepdes')->first();
                $sekdes = User::where('role', 'sekdes')->first();

                if ($kepdes) {
                    Mail::to($kepdes->email)->send(new DocumentArchivedMail($doc));
                }
                if ($sekdes) {
                    Mail::to($sekdes->email)->send(new DocumentArchivedMail($doc));
                }
                break;
        }
    }
}
