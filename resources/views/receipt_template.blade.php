<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
<h1>Receipt</h1>
<p>User: {{ $user->name }}</p>
<p>Event ID: {{ $event->id }}</p>
<p>Total Price: ${{ $supplement->total_price }}</p>
<p>Food Details: {{ json_encode($supplement->food_details) }}</p>
<p>Drinks Details: {{ json_encode($supplement->drinks_details) }}</p>
<p>Accessories Details: {{ json_encode($supplement->accessories_details) }}</p>
</body>
</html>
