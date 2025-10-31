@extends('emails.layouts.base')

@section('title', 'Dokumen Diajukan Kembali')

@section('content')
    <h2 style="color:#1e3a8a;">Halo, Kepala Desa</h2>

    <p>Dokumen <strong>{{ $document->tentang }}</strong> telah <strong style="color:#1d4ed8;">direvisi dan diajukan
            kembali</strong> oleh Sekretaris Desa.</p>

    <div style="background-color:#fef3c7; border-left:4px solid #f59e0b; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="margin:5px 0; font-weight:600; color:#92400e;">Dokumen Telah Direvisi</p>
        <p style="margin:5px 0; color:#78350f; font-size:14px;">Dokumen ini sebelumnya ditolak dan sekarang telah diperbaiki.
        </p>
    </div>

    <div style="background-color:#f3f4f6; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="margin:5px 0;"><strong>Jenis Dokumen:</strong>
            {{ ucwords(str_replace('_', ' ', $document->jenis_dokumen)) }}</p>
        <p style="margin:5px 0;"><strong>Nomor Ditetapkan:</strong> {{ $document->nomor_ditetapkan }}</p>
        <p style="margin:5px 0;"><strong>Tanggal Ditetapkan:</strong>
            {{ \Carbon\Carbon::parse($document->tanggal_ditetapkan)->format('d F Y') }}</p>
        @if ($document->keterangan)
            <p style="margin:5px 0;"><strong>Keterangan:</strong> {{ $document->keterangan }}</p>
        @endif
    </div>

    <p>Silakan review kembali dokumen yang telah direvisi ini.</p>

    <a href="{{ $documentUrl }}"
        style="display:inline-block; background-color:#1d4ed8; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px; margin-top:10px;">
        Review Dokumen
    </a>
@endsection
