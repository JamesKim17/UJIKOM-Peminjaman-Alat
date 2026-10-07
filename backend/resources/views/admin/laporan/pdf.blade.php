<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman Alat</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2 class="text-center">Laporan Transaksi Peminjaman Alat</h2>
    <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Tgl Pinjam</th>
                <th>Peminjam</th>
                <th>Alat (Jumlah)</th>
                <th>Status</th>
                <th>Tgl Kembali (Aktual)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peminjamans as $pinjam)
            <tr>
                <td>{{ \Carbon\Carbon::parse($pinjam->tgl_pinjam)->format('d/m/Y') }}</td>
                <td>{{ $pinjam->user->name ?? '-' }}</td>
                <td>
                    @foreach($pinjam->detailPinjam as $detail)
                        {{ $detail->alat->nama_alat ?? '-' }} ({{ $detail->jumlah }})<br>
                    @endforeach
                </td>
                <td>{{ ucfirst($pinjam->status) }}</td>
                <td>
                    {{ $pinjam->pengembalian ? \Carbon\Carbon::parse($pinjam->pengembalian->tgl_kembali)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>