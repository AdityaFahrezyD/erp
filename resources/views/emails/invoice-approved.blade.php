<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); overflow: hidden;">
        <tr>
            <td style="padding: 20px; text-align: center; background-color: #4CAF50; color: white;">
                <h2 style="margin: 0;">Invoice #{{ $invoice->invoice_id }} Approved</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px;">
                    Hello {{ $invoice->recipient }}{{ $invoice->company ? ' from ' . $invoice->company : '' }}, Your invoice has been <strong>approved</strong>. Please find the details below:
                </p>
                <table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">Invoice ID:</td>
                        <td style="padding: 10px;">{{ $invoice->invoice_id }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">Amount:</td>
                        <td style="padding: 10px;">
                            Rp {{ number_format($invoice->invoice_amount, 0, ',', '.') }}
                        </td>

                    </tr>
                    @if (!empty($invoice->information))
                        <tr>
                            <td style="padding: 10px; background-color: #f2f2f2; font-weight: bold;">Description:</td>
                            <td style="padding: 10px;">{{ $invoice->information }}</td>
                        </tr>
                    @endif
                </table>
                <p style="margin-top: 30px; font-size: 16px;">Download invoice for details.</p>
                <p style="margin-top: 30px; font-size: 16px;">Thank you for your business!</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 15px; text-align: center; font-size: 12px; background-color: #eee; color: #777;">
                &copy; {{ date('Y') }} PT. Texio Mitra Digitalisasi. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
