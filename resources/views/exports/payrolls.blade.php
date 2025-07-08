<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll List</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; } /* Ubah ke 100% agar lebih rapi */
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Daftar Payroll</h1>
    <table>
        <thead>
            <tr>
                <th>Penerima</th>
                <th>Keterangan</th>
                <th>Harga</th>
                <th>Email Penerima</th>
                <th>Tanggal Kirim</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $payroll)
            <tr>
                <td>{{ $payroll->pegawai->nama ?? 'N/A' }}</td>
                <td>{{ $payroll->jenis_gaji }}</td>
                <td>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                <td>{{ $payroll->email_penerima ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($payroll->tanggal_kirim)->format('d M Y') }}</td>
                <td>{{ ucfirst($payroll->approve_status) }}</td>
                <td>{{ $payroll->created_at }}</td>
                <td>{{ $payroll->updated_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>