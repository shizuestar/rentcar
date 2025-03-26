<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Transaction Report</h2>
    <p>Period: {{ request('start') }} - {{ request('end') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Customer</th>
                <th>Car</th>
                <th>Days Rent</th>
                <th>Return Date</th>
                <th>Total Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->user->name }}</td>
                <td>{{ $transaction->car->name }}</td>
                <td>{{ (new DateTime($transaction->rent_date))->diff(new DateTime($transaction->return_date))->days }} days</td>
                <td>{{ $transaction->return_date }}</td>
                <td>Rp {{ number_format($transaction->total, 0) }}</td>
                <td>{{ $transaction->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>