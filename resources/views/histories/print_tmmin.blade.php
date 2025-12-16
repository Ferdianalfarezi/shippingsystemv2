<!DOCTYPE html>
<html>
<head>
    <title>DN Details - TMMIN Print</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
            position: relative;
        }
        
        .background-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 29.7cm; /* A4 height */
            margin: 0 auto;
        }
        
        .form-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            padding: 0;
        }
        
        /* Posisi field-field sesuai dengan tabel di gambar */
        .customer-field {
            position: absolute;
            top: 194px;
            right: 633px;
            font-size: 13px ;
            font-weight: bold;
            display: flex;
            justify-content: center;
        }
        
        .delivery-date-field {
            position: absolute;
            top: 210px;
            left: 25px;
            width: 155px;
            height: 69px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        
        .delivery-time-field {
            position: absolute;
            top: 210px;
            left: 120px;
            width: 155px;
            height: 69px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cycle-field {
            position: absolute;
            top: 286px;
            left: 642px;
            width: 150px;
            height: 69px;
            display: flex;
        }
        
        .address-field {
            position: relative;
            margin-left: 565px;
            margin-top: 230px;
        }
        
        .status-field {
            position: absolute;
            top: 210px;
            right: 20px;
            font-size: 14px;
            width: 160px;
            height: 69px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: green;
            font-weight: bold;
        }
        
        .order-no-field {
            position: absolute;
            top: 268px;
            right: -10px;
            width: 592px;
            height: 60px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .page-field {
            position: absolute;
            top: 269px;
            right: 5px;
            width: 167px;
            height: 60px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .shipping-date-field {
            position: absolute;
            top: 338px;
            left: 62px;
            width: 347px;
            height: 64px;
        }
        
        .scan-to-delivery-field {
            position: absolute;
            top: 320px;
            left: 147px;
            width: 337px;
            height: 64px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .receipt-dn-field {
            position: absolute;
            top: 320px;
            right: 145px;
            width: 315px;
            height: 64px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .printed-at-field {
            position: absolute;
            top: 320px;
            right: 49px;
            width: 167px;
            height: 64px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .barcode-container {
            /* Reduce the size by setting a smaller width */
            position: relative;
            width: 200px; /* You can adjust this value to make it even smaller */
            margin: 20px auto;
            margin-top: -50px;
            margin-left: 315px;
            overflow: hidden;
            height: 45px;
        }
        
        .barcode-container img {
            /* Make the image responsive within the container */
            max-width: 130px;
            height: auto;
            margin-top: -10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            @page {
                size: A4;
                margin: 0;
            }
            .background-image {
                width: 100%;
                height: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="background-container">
        <!-- Gambar latar belakang -->
        <img src="{{ asset('images/dntmmin.png') }}" class="background-image" style="width: 100%; position: relative; top: 0; left: 0; z-index: 1;">
        
        <!-- Overlay untuk data -->
        <div class="form-overlay">
            <!-- Customer -->
            <div class="customer-field">{{ $history->customer }}</div>
            
            <!-- Delivery Date -->
            <div class="delivery-date-field">
                {{ \Carbon\Carbon::parse($history->delivery_date)->format('d/m/Y') }}
            </div>

            
            <!-- Delivery Time -->
            <div class="delivery-time-field">{{ $history->delivery_time }}</div>
            
            <!-- Cycle -->
            <div class="cycle-field">{{ $history->cycle }}</div>
            
            <!-- Address -->
            <div class="address-field">{{ $history->address }}</div>

            <div class="barcode-container">
                <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $history->order_no ?? '' }}&code=Code128&multiplebarcodes=false&translate-esc=false&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=%23000000&bgcolor=%23ffffff&codepage=&qunit=Mm&quiet=0" alt="Barcode">
            </div>
            
            <!-- Status -->
            <div class="status-field">Completed</div>
            
            <!-- Order No -->
            <div class="order-no-field">{{ $history->order_no }}</div>
            
            <!-- Page -->
            <div class="page-field">1</div>
            
            <!-- Shipping Date -->
            <div class="shipping-date-field">{{ $history->scan_to_shipping }}</div>
            
            <!-- Scan to Delivery -->
            <div class="scan-to-delivery-field">{{ $history->scan_to_delivery }}</div>
            
            <!-- Receipt DN -->
            <div class="receipt-dn-field">{{ $history->scan_to_history }}</div>
            
            <!-- Printed At -->
            <div class="printed-at-field">{{ now()->format('d M Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>