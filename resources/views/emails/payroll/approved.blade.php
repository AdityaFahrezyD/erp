<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll Disetujui</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); overflow: hidden;">
        <tr>
            <td style="padding: 20px; text-align: center; background-color: #4CAF50; color: white;">
                <h2 style="margin: 0;">Payroll Disetujui</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px;">
                    Halo {{ $payroll->penerima }}, payroll Anda telah <strong>disetujui</strong>. Berikut rincian payroll Anda:
                </p>
                <table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">ID Payroll:</td>
                        <td style="padding: 10px;">{{ $payroll->payroll_id }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">Keterangan:</td>
                        <td style="padding: 10px;">{{ $payroll->keterangan }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">Jumlah:</td>
                        <td style="padding: 10px;">Rp {{ number_format($payroll->harga, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">Tanggal Kirim:</td>
                        <td style="padding: 10px;">{{ \Carbon\Carbon::parse($payroll->tanggal_kirim)->format('d M Y') }}</td>
                    </tr>
                </table>
                <p style="margin-top: 30px; font-size: 16px;">Silakan periksa lampiran atau hubungi HR jika ada pertanyaan.</p>
                <p style="margin-top: 30px; font-size: 16px;">Terima kasih.</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 15px; text-align: center; font-size: 12px; background-color: #eee; color: #777;">
                &copy; {{ date('Y') }} PT. Texio Mitra Digitalisasi. Hak cipta dilindungi undang-undang.
            </td>
        </tr>
    </table>
</body>
</html>
