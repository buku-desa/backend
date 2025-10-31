@extends('emails.layouts.email')

@section('content')
    <h2>📁 Dokumen Telah Diarsipkan</h2>

    <p>Halo,</p>

    <p>Dokumen berikut telah resmi <strong>diarsipkan</strong> dalam sistem Lembaran Desa dan Berita Desa (LDBD):</p>

    <ul>
        <li><strong>Jenis Dokumen:</strong> {{ ucfirst(str_replace('_', ' ', $document->tipe)) }}</li>
        <li><strong>Tentang:</strong> {{ $document->tentang }}</li>
        <li><strong>Status:</strong> {{ $document->status }}</li>
        @if ($document->nomor_dokumen)
            <li><strong>Nomor Dokumen:</strong> {{ $document->nomor_dokumen }}</li>
        @endif
        @if ($document->tanggal_diundangkan)
            <li><strong>Tanggal Diundangkan:</strong>
                {{ \Carbon\Carbon::parse($document->tanggal_diundangkan)->format('d M Y') }}</li>
        @endif
    </ul>

    <p>Dokumen ini kini tersimpan dalam arsip dan dapat diakses kembali melalui sistem sesuai kewenangan Anda.</p>

    <p>Terima kasih atas kerja samanya dalam menjaga tata kelola administrasi desa.</p>
@endsection
