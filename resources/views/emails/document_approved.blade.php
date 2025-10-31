@extends('emails.layouts.base')

@section('title', 'Dokumen Disetujui')

@section('content')
    <h2 style="color:#1e3a8a;">Halo Sekretaris Desa,</h2>
    <p>Dokumen yang Anda ajukan telah <strong style="color:#16a34a;">disetujui oleh Kepala Desa</strong>.</p>
    <p>Selanjutnya, dokumen tersebut dapat diproses untuk tahap penerbitan.</p>
    <a href="{{ url('/') }}"
        style="display:inline-block; background-color:#16a34a; color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px;">Lihat
        Dokumen</a>
@endsection
