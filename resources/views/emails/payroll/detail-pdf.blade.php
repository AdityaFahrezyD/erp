<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detail Payroll</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; }
        .header { background-color: #4CAF50; color: white; text-align: center; padding: 10px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 8px; border: 1px solid #ddd; }
        .label { font-weight: bold; background-color: #f2f2f2; }
        .footer { text-align: center; font-size: 10px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- <img src="{{ asset('storage/logo/texio_indonesia_cover.jpeg') }}" alt="Texio Indonesia Logo"> -->
            <h2>Detail Payroll</h2>
        </div>

        <div class="section">
            <div class="section-title">Informasi Payroll</div>
            <table>
                <tr>
                    <td class="label">Nama Pegawai</td>
                    <td>{{ $payroll->pegawai->nama ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Penggajian</td>
                    <td>{{ \Carbon\Carbon::parse($payroll->tanggal_kirim)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Gaji</td>
                    <td>
                        @switch($payroll->jenis_gaji)
                            @case('gaji_pokok') Gaji Pokok @break
                            @case('thr') THR @break
                            @case('tunjangan') Tunjangan @break
                            @default {{ ucfirst($payroll->jenis_gaji) }}
                        @endswitch
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Detail Keuangan</div>
            <table>
                @if($payroll->jenis_gaji === 'gaji_pokok')
                    <tr>
                        <td class="label">Gaji Kotor</td>
                        <td>Rp {{ number_format($gross_salary, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jumlah Bonus</td>
                        <td>Rp {{ number_format($total_bonuses, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tunjangan Makan</td>
                        <td>Rp {{ number_format($tunjangan_mkn, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tunjangan Transportasi</td>
                        <td>Rp {{ number_format($tunjangan_trns, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Potongan Denda</td>
                        <td>Rp {{ number_format($total_deductions, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Potongan Asuransi</td>
                        <td>Rp {{ number_format($asuransi, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Potongan Pajak</td>
                        <td>Rp {{ number_format($pajak, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Gaji Bersih</td>
                        <td>Rp {{ number_format($gross_salary + $total_bonuses + $tunjangan_mkn + $tunjangan_trns - $total_deductions - $pajak - $asuransi, 0, ',', '.') }}</td>
                    </tr>
                @elseif($payroll->jenis_gaji === 'thr')
                    <tr>
                        <td class="label">Masa Kerja (Bulan)</td>
                        <td>{{ $masa_kerja_bulan }}</td>
                    </tr>
                    <tr>
                        <td class="label">Gaji Kotor</td>
                        <td>Rp {{ number_format($gross_salary, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tunjangan Posisi</td>
                        <td>Rp {{ number_format($tunjangan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Gaji Bersih</td>
                        <td>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                    </tr>
                @elseif($payroll->jenis_gaji === 'tunjangan')
                    <tr>
                        <td class="label">Gaji Tunjangan</td>
                        <td>Rp {{ number_format($tunjangan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Gaji Bersih</td>
                        <td>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            © {{ date('Y') }} PT. Texio Mitra Digitalisasi. Hak cipta dilindungi undang-undang.
        </div>
    </div>
</body>
</html>