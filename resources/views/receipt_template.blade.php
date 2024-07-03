<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        h1, h2, h3 {
            text-align: center;
        }
        .info, .totals {
            margin-bottom: 20px;
        }
        .info div, .totals div {
            margin-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .centered-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            height: 50%; /* Ensure it takes full height of parent container */
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Receipt</h1>

    <div class="info">
        <h2>User Information</h2>
        <div>Name: {{ $user->name }}</div>
        <div>Email: {{ $user->email }}</div>
    </div>

    <div class="info">
        <h2>Event Information</h2>
        <div>Location: {{ $event->location->name }}</div>
        <div>Date: {{ $event->date }}</div>
        <div>Time: {{ $event->start_time }} - {{ $event->end_time }}</div>
        <div>Description: {{ $event->description }}</div>
        <div>Number of People Invited: {{ $event->num_people_invited }}</div>
    </div>

    <h2>Food</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Item Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($foodItems as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['parsed_price'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ $item['parsed_price'] * $item['quantity'] }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="total">Total Food</td>
            <td class="total">{{ $totalFood }}</td>
        </tr>
        </tfoot>
    </table>

    <h2>Drinks</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Item Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($drinkItems as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['parsed_price'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ $item['parsed_price'] * $item['quantity'] }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="total">Total Drinks</td>
            <td class="total">{{ $totalDrinks }}</td>
        </tr>
        </tfoot>
    </table>

    <h2>Accessories</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Item Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($accessoryItems as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['parsed_price'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ $item['parsed_price'] * $item['quantity'] }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="total">Total Accessories</td>
            <td class="total">{{ $totalAccessories }}</td>
        </tr>
        </tfoot>
    </table>

    <div class="totals">
        <h2>Grand Total</h2>
        <div class="total">Grand Total: {{ $grandTotal }}</div>
    </div>
    <!-- QR Code section -->
    <div class="centered-content">
        <h2>QR Code for Download</h2>
        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
        <p>Scan the QR code to download your receipt.</p>
    </div>
</div>
</body>
</html>
