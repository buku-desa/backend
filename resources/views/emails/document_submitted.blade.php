@extends('emails.layouts.base')

@section('title', 'Dokumen Baru Diajukan')

@section('content')
    <h2 style="color:#1e3a8a;">Halo, Kepala Desa</h2>

    <p>Dokumen baru telah <strong style="color:#1d4ed8;">diajukan</strong> oleh Sekretaris Desa dan menunggu persetujuan
        Anda.</p>

    <div style="background-color:#dbeafe; border-left:4px solid #1d4ed8; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="margin:5px 0; font-weight:600; color:#1e40af;">Dokumen Perlu Review</p>
    </div>

    <div style="background-color:#f3f4f6; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="margin:5px 0;"><strong>Tentang:</strong> {{ $document->tentang }}</p>
        <p style="margin:5px 0;"><strong>Jenis Dokumen:</strong>
            {{ ucwords(str_replace('_', ' ', $document->jenis_dokumen)) }}</p>
        <p style="margin:5px 0;"><strong>Nomor Ditetapkan:</strong> {{ $document->nomor_ditetapkan }}</p>
        <p style="margin:5px 0;"><strong>Tanggal Ditetapkan:</strong>
            {{ \Carbon\Carbon::parse($document->tanggal_ditetapkan)->format('d F Y') }}</p>
        @if ($document->keterangan)
            <p style="margin:5px 0;"><strong>Keterangan:</strong> {{ $document->keterangan }}</p>
        @endif
    </div>

    <p>Silakan review dan berikan persetujuan atau penolakan untuk dokumen ini.</p>

    <a href="{{ $documentUrl }}"
        style="display:inline-block; background-color:#1d4ed8; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px; margin-top:10px;">
        Review Dokumen
    </a>
@endsection
