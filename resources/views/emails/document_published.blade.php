@extends('emails.layouts.base')

@section('title', 'Dokumen Diterbitkan')

@section('content')
    <h2 style="color:#1e3a8a;">Halo,</h2>
    <p>Dokumen telah resmi <strong style="color:#1d4ed8;">diterbitkan di Buku Lembaran Desa atau Berita Desa</strong>.</p>
    <p>Anda dapat melihat versi final dokumen yang telah disahkan melalui sistem.</p>
    <a href="{{ url('/') }}"
        style="display:inline-block; background-color:#1d4ed8; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px;">Lihat
        Dokumen</a>
@endsection
