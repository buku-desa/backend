@extends('emails.layouts.base')

@section('title', 'Dokumen Diajukan Ulang')

@section('content')
    <h2 style="color:#1e3a8a;">Halo Kepala Desa,</h2>
    <p>Dokumen yang sebelumnya ditolak telah <strong>diajukan ulang oleh Sekretaris Desa</strong> setelah dilakukan
        perbaikan.</p>
    <p>Silakan meninjau ulang dokumen tersebut untuk memberikan keputusan selanjutnya.</p>
    <a href="{{ url('/') }}"
        style="display:inline-block; background-color:#2563eb; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px;">Buka
        Sistem LDBD</a>
@endsection
