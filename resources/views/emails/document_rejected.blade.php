@extends('emails.layouts.base')

@section('title', 'Dokumen Ditolak')

@section('content')
    <h2 style="color:#1e3a8a;">Halo Sekretaris Desa,</h2>
    <p>Dokumen yang Anda ajukan telah <strong style="color:#dc2626;">ditolak oleh Kepala Desa</strong> setelah proses
        peninjauan.</p>
    <p>Silakan lakukan perbaikan sesuai catatan yang diberikan, lalu ajukan kembali.</p>
    <a href="{{ url('/') }}"
        style="display:inline-block; background-color:#dc2626; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px;">Perbaiki
        Dokumen</a>
@endsection
