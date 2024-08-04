<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        h2, h3 {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 40px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Weekly Report</h2>

    <!-- Top Profitable Hosts -->
    <div class="section">
        <h3>Top Profitable Hosts</h3>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Profit</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($top_profitable_hosts as $host)
                <tr>
                    <td>{{ $host['id'] }}</td>
                    <td>{{ $host['name'] }}</td>
                    <td>{{ number_format($host['profit'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Profitable Locations -->
    <div class="section">
        <h3>Top Profitable Locations</h3>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Profit</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($top_profitable_locations as $location)
                <tr>
                    <td>{{ $location['id'] }}</td>
                    <td>{{ $location['name'] }}</td>
                    <td>{{ number_format($location['profit'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Ordered Food -->
    <div class="section">
        <h3>Top Ordered Food</h3>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($top_ordered_food as $item)
                <tr>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Ordered Drinks -->
    <div class="section">
        <h3>Top Ordered Drinks</h3>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($top_ordered_drinks as $item)
                <tr>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Ordered Accessories -->
    <div class="section">
        <h3>Top Ordered Accessories</h3>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($top_ordered_accessories as $item)
                <tr>
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Monthly User Growth -->
    <div class="section">
        <h3>Monthly User Growth</h3>
        <table class="table">
            <thead>
            <tr>
                <th>Year</th>
                <th>Month</th>
                <th>Registrations</th>
                <th>Growth Rate (%)</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($monthly_user_growth as $growth)
                <tr>
                    <td>{{ $growth['year'] }}</td>
                    <td>{{ $growth['month'] }}</td>
                    <td>{{ $growth['count'] }}</td>
                    <td>{{ $growth['growth_rate'] !== null ? number_format($growth['growth_rate'], 2) : 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
