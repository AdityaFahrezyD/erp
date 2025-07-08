<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    <style>
        h4 {
            margin: 0;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }
        .margin-top {
            margin-top: 1.25rem;
        }
        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }
        table {
            width: 100%;
            border-spacing: 0;
        }
        table.products {
            font-size: 0.875rem;
        }
        table.products tr {
            background-color: rgb(96 165 250);
        }
        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }
        table tr.items {
            background-color: rgb(241 245 249);
        }
        table tr.items td {
            padding: 0.5rem;
        }
        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .header img {
            width: 100px; /* Ukuran gambar dapat disesuaikan */
            height: auto;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- <img src="{{ asset('storage/logo/texio_indonesia_cover.jpeg') }}" alt="Texio Indonesia Logo"> -->
        <h2>Invoice</h2>
    </div>
    
    <table class="w-full">
        <tr>
            <td class="w-half">
                <h2>Invoice ID: {{ $invoice->invoice_id }}</h2>
            </td>
        </tr>
    </table>
 
    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>To:</h4></div>
                    <div>{{ $invoice->recipient }}{{ $invoice->company_name ? ' from ' . $invoice->company_name : '' }},</div>
                </td>
                <td class="w-half">
                    <div><h4>From:</h4></div>
                    <div>PT. Texio Mitra Digitalisasi</div>
                </td>
            </tr>
        </table>
    </div>
 
    <div class="margin-top">
        <table class="products">
            <tr>
                <th>Qty</th>
                <th>Description</th>
                <th>Price</th>
            </tr>
            <tr class="items">
                <td>
                    1
                </td>
                <td>
                    {{ $invoice->information }}
                </td>
                <td>
                    Rp {{ number_format($invoice->invoice_amount, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>
 
    <div class="total">
        Total: Rp {{ number_format($invoice->invoice_amount, 0, ',', '.') }}
    </div>
 
    <div class="footer margin-top">
        <div>Thank you</div>
        <div>© PT. Texio Mitra Digitalisasi</div>
    </div>
</body>
</html>