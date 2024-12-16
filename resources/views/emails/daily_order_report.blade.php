<!DOCTYPE html>
<html>
<head>
    <title>Daily Orders Report</title>
</head>
<body>
<h1>Daily Orders Report - {{ $data['date'] }}</h1>
<p><strong>Total Orders:</strong> {{ $data['totalOrders'] }}</p>
<p><strong>Total Price:</strong> {{ $data['totalPrice'] }}</p>
<p><strong>Delivered Orders:</strong> {{ $data['deliveredOrders'] }}</p>
<p><strong>Paid Orders:</strong> {{ $data['paidOrders'] }}</p>
</body>
</html>
