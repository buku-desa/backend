@extends('emails.layouts.base')

@section('title', 'Dokumen Baru Diajukan')

@section('content')
    <h2 style="color:#1e3a8a;">Halo Kepala Desa,</h2>
    <p>Telah ada dokumen baru yang <strong>diajukan oleh Sekretaris Desa</strong> untuk ditinjau dan disetujui.</p>
    <p>Silakan melakukan pemeriksaan terhadap isi dokumen tersebut sebelum memberi keputusan.</p>
    <a href="{{ url('/') }}"
        style="display:inline-block; background-color:#2563eb; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px;">Buka
        Sistem LDBD</a>
@endsection
