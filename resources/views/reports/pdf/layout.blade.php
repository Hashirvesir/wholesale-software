<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title')</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .company-info {
            float: left;
            width: 60%;
        }

        .report-info {
            float: right;
            width: 40%;
            text-align: right;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .details-box {
            width: 100%;
            clear: both;
            margin-bottom: 20px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th {
            background-color: #f3f4f6;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ddd;
            padding: 10px 0;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .summary-box {
            margin-top: 20px;
            float: right;
            width: 40%;
        }

        .summary-row {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-label {
            font-weight: bold;
        }

        .summary-value {
            text-align: right;
        }

        @page {
            margin: 0.7in 0.4in;
        }

        header {
            position: fixed;
            top: -0.5in;
            left: 0;
            right: 0;
            height: 0.5in;
        }

        footer {
            position: fixed;
            bottom: -0.4in;
            left: 0;
            right: 0;
            height: 0.4in;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <header>
        <div class="clearfix">
            <div class="company-info">
                <div class="company-name">{{ $settings->company_name }}</div>
                <div style="font-size: 10px;">{{ $settings->company_address }}</div>
                <div style="font-size: 10px;">{{ $settings->company_phone }} | {{ $settings->company_email }}</div>
            </div>
            <div class="report-info">
                <div class="report-title">@yield('report_name')</div>
                <div style="font-size: 10px;">Printed on: {{ now()->format('d M, Y H:i') }}</div>
            </div>
        </div>
    </header>

    <footer>
        Page <span class="pagenum"></span> - {{ $settings->company_name }}
        <div style="margin-top: 5px; font-weight: bold;">Developed by Invenco Tel: +92 324 3980274</div>
    </footer>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("Verdana");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>
