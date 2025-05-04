<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll List</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 20%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
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
                <td>{{ $payroll->penerima }}</td>
                <td>{{ $payroll->keterangan }}</td>
                <td>Rp {{ number_format($payroll->harga, 0, ',', '.') }}</td>
                <td>{{ $payroll->email_penerima }}</td>
                <td>{{ $payroll->tanggal_kirim }}</td>
                <td>{{ ucfirst($payroll->approve_status) }}</td>
                <td>{{ $payroll->created_at }}</td>
                <td>{{ $payroll->updated_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
