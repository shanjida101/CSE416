<?php
require 'config.php';

// Make sure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userSerial = $_SESSION['user']['serial'];

// Fetch all orders for this user
$stmt = $pdo->prepare("SELECT user_serial, date, item, amount, status FROM orders WHERE user_serial = ? ORDER BY date DESC");
$stmt->execute([$userSerial]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead {
            background: #000;
            color: white;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        table tr:nth-child(even) {
            background: #f2f2f2;
        }
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .on-process {
            background: orange;
            color: white;
        }
        .completed {
            background: green;
            color: white;
        }
        .cancelled {
            background: red;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Order History </h2>
    <table>
        <thead>
            <tr>
                <th>Serial</th>
                <th>Date</th>
                <th>Item</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['user_serial']) ?></td>
                        <td><?= htmlspecialchars($order['date']) ?></td>
                        <td><?= htmlspecialchars($order['item']) ?></td>
                        <td>$<?= number_format($order['amount'], 2) ?></td>
                        <td>
                            <span class="status 
                                <?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
