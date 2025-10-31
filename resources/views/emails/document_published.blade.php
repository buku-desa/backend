@extends('emails.layouts.base')

@section('title', 'Dokumen Dipublish')

@section('content')
    <h2 style="color:#1e3a8a;">Halo,</h2>

    <p>Dokumen <strong>{{ $document->tentang }}</strong> telah resmi <strong style="color:#1d4ed8;">dipublish</strong> dan
        dapat diakses publik.</p>

    <div style="background-color:#f3f4f6; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="margin:5px 0;"><strong>Jenis Dokumen:</strong>
            {{ ucwords(str_replace('_', ' ', $document->jenis_dokumen)) }}</p>
        <p style="margin:5px 0;"><strong>Nomor Diundangkan:</strong> {{ $document->nomor_diundangkan_display }}</p>
        <p style="margin:5px 0;"><strong>Tanggal Diundangkan:</strong>
            {{ $document->tanggal_diundangkan ? \Carbon\Carbon::parse($document->tanggal_diundangkan)->format('d F Y') : '-' }}
        </p>
    </div>

    <p>Dokumen ini sekarang dapat dilihat oleh masyarakat umum di
        {{ $document->jenis_dokumen === 'peraturan_desa' ? 'Buku Lembaran Desa' : 'Berita Desa' }}.</p>

    <a href="{{ $documentUrl }}"
        style="display:inline-block; background-color:#1d4ed8; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px; margin-top:10px;">
        Lihat Dokumen
    </a>
@endsection
