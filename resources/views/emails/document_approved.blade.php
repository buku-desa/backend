@extends('emails.layouts.base')

@section('title', 'Dokumen Disetujui')

@section('content')
    <h2 style="color:#1e3a8a;">Halo Sekretaris Desa,</h2>
    <p>Dokumen yang Anda ajukan telah <strong style="color:#16a34a;">disetujui oleh Kepala Desa</strong>.</p>
    <div style="background-color:#f3f4f6; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="margin:5px 0;"><strong>Jenis Dokumen:</strong>
            {{ ucwords(str_replace('_', ' ', $document->jenis_dokumen)) }}</p>
        <p style="margin:5px 0;"><strong>Nomor Ditetapkan:</strong> {{ $document->nomor_ditetapkan }}</p>
        <p style="margin:5px 0;"><strong>Tanggal Ditetapkan:</strong>
            {{ \Carbon\Carbon::parse($document->tanggal_ditetapkan)->format('d F Y') }}</p>
    </div>
    <p>Selanjutnya, dokumen tersebut dapat diproses untuk tahap penerbitan.</p>
    <a href="{{ $documentUrl }}"
        style="display:inline-block; background-color:#16a34a; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px;">Lihat
        Dokumen</a>
@endsection
