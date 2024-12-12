<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Certificate {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .certificate-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Fracashare Logo" class="logo">
        <div class="certificate-title">Tax Certificate {{ $year }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <strong>Name:</strong> {{ $user->full_name }}
        </div>
        <div class="info-row">
            <strong>Email:</strong> {{ $user->email }}
        </div>
        <div class="info-row">
            <strong>Tax Year:</strong> {{ $year }}
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount (NGN)</th>
                <th>Tax Rate</th>
                <th>Tax Amount (NGN)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Capital Gains</td>
                <td>@money($summary['capital_gains']['total_gains'])</td>
                <td>10%</td>
                <td>@money($summary['capital_gains']['tax_amount'])</td>
            </tr>
            <tr>
                <td>Dividend Income</td>
                <td>@money($summary['dividends']['total_dividends'])</td>
                <td>10%</td>
                <td>@money($summary['dividends']['tax_amount'])</td>
            </tr>
            <tr>
                <td>VAT Paid</td>
                <td colspan="2">7.5% on transaction fees</td>
                <td>@money($summary['vat_paid'])</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Total Tax Liability</strong></td>
                <td><strong>@money($summary['total_tax_liability'])</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ $generated_at }}</p>
        <p>This is an automatically generated certificate. For any queries, please contact our support team.</p>
        <p>Fracashare - Enabling fractional ownership of high-value assets</p>
    </div>
</body>
</html>