<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judul ?? 'Buku Lembaran dan Berita Desa' }}</title>
    <style>
        @page {
            margin: 28mm 18mm 28mm 18mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }

        h2,
        h3 {
            margin: 0 0 10px 0;
            text-align: center;
        }

        .meta {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 6px;
            vertical-align: top;
        }

        th {
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .sign {
            width: 100%;
            margin-top: 28px;
        }

        .sign td {
            border: 0;
            text-align: center;
            padding-top: 40px;
        }

        .small {
            font-size: 11px;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <h2>{{ $judul ?? 'Buku Lembaran dan Berita Desa' }}</h2>
    <div class="meta small">
        Periode: {{ \Carbon\Carbon::parse($start)->format('d-m-Y') }} s.d.
        {{ \Carbon\Carbon::parse($end)->format('d-m-Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:6%;">NOMOR<br>URUT</th>
                <th style="width:16%;">JENIS PERATURAN<br>DI DESA</th>
                <th style="width:18%;">NOMOR DAN<br>TANGGAL DITETAPKAN</th>
                <th style="width:34%;">TENTANG</th>
                <th style="width:11%;">DIUNDANGKAN<br><span class="small">TANGGAL</span></th>
                <th style="width:9%;">DIUNDANGKAN<br><span class="small">NOMOR</span></th>
                <th style="width:6%;">KET</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $r)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $r['jenis_label'] }}</td>
                    <td class="small">
                        <div>{{ $r['nomor_ditetapkan'] ?: '-' }}</div>
                        <div>{{ $r['tanggal_ditetapkan'] ?: '-' }}</div>
                    </td>
                    <td>{{ $r['tentang'] }}</td>
                    <td class="center">{{ $r['tanggal_diundangkan'] ?: '-' }}</td>
                    <td class="center">
                        @if (!empty($r['nomor_diundangkan_disp']))
                            {{ $r['nomor_diundangkan_disp'] }}
                        @elseif(!empty($r['nomor_diundangkan']))
                            {{ $r['nomor_diundangkan'] }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="center">{{ $r['keterangan'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td>
                MENGETAHUI<br>
                <strong>KEPALA DESA</strong>
                <div style="height:60px;"></div>
                <span>................................</span>
            </td>
            <td>
                <strong>SEKRETARIS DESA</strong>
                <div style="height:60px;"></div>
                <span>................................</span>
            </td>
        </tr>
    </table>
</body>

</html>
