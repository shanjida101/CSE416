<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch birth year & serial from DB
$stmt = $pdo->prepare("SELECT birth, serial FROM user WHERE serial = ?");
$stmt->execute([$_SESSION['user']['serial']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: logout.php");
    exit;
}

$birthYear  = date('Y', strtotime($user['birth']));
$userSerial = $user['serial'];

// Handle "Add to Cart" (auto order placement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'])) {
    $name   = $_POST['product_name'];
    $price  = (float) $_POST['price'];
    $qty    = (int) $_POST['qty'];
    $amount = $price * $qty;

    $stmt = $pdo->prepare("INSERT INTO orders (user_serial, date, item, amount, status) VALUES (?, NOW(), ?, ?, 'On Process')");
    $stmt->execute([$userSerial, $name, $amount]);

    header("Location: dashboard.php");
    exit;
}

// Get cart count from DB (number of orders for this user)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_serial = ?");
$stmt->execute([$userSerial]);
$cart_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<title>Coffee Shop</title>
<style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
    .topbar { display: flex; justify-content: space-between; align-items: center; background: #000; color: white; padding: 12px 20px; }
    .brand { font-size: 18px; font-weight: bold; }
    .navbar { display: flex; align-items: center; gap: 20px; }
    .navbar a { color: white; text-decoration: none; padding: 8px 10px; }
    .navbar a:hover { background: rgba(255, 255, 255, 0.2); border-radius: 5px; }
    .dropdown { position: relative; }
    .dropdown-content { display: none; position: absolute; background: white; min-width: 160px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 999; border-radius: 5px; }
    .dropdown-content a { color: black; padding: 8px 12px; text-decoration: none; display: block; }
    .dropdown-content a:hover { background: #f0f0f0; }
    .dropdown:hover .dropdown-content { display: block; }
    .search-bar input { padding: 6px 10px; border-radius: 5px; border: none; width: 250px; }
    .cart { position: relative; background: white; color: black; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 14px; }
    .cart-badge { position: absolute; top: -5px; right: -5px; background: red; color: white; font-size: 12px; padding: 2px 6px; border-radius: 50%; }
    .banner { position: relative; background: url('banner2.jpg') no-repeat center/cover; height: 250px; display: flex; align-items: center; justify-content: center; text-align: center; color: white; font-size: 32px; font-weight: bold; }
    .banner::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
    .banner-content { position: relative; z-index: 1; }
    .container { max-width: 1000px; margin: 20px auto; padding: 0 20px; }
    .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
    .card { background: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 15px; text-align: center; position: relative; }
    .card img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
    .discount-badge { position: absolute; top: 10px; left: 10px; background: red; color: white; padding: 3px 6px; border-radius: 5px; font-size: 12px; }
    .qty-control { display: flex; justify-content: center; align-items: center; gap: 8px; margin: 10px 0; }
    .qty-control button { width: 28px; height: 28px; border: none; background: #000; color: white; font-size: 18px; border-radius: 5px; cursor: pointer; }
    .qty-control input { width: 40px; text-align: center; border: 1px solid #ccc; border-radius: 5px; padding: 5px; }
    .card button.add-btn { background: #000; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
    .card button.add-btn:hover { background: #333; }
    footer { background: #000; color: white; padding: 15px; text-align: center; margin-top: 30px; }
</style>
<script>
function increaseQty(id) {
    let qtyInput = document.getElementById('qty-' + id);
    qtyInput.value = parseInt(qtyInput.value) + 1;
}
function decreaseQty(id) {
    let qtyInput = document.getElementById('qty-' + id);
    if (parseInt(qtyInput.value) > 1) {
        qtyInput.value = parseInt(qtyInput.value) - 1;
    }
}
</script>
</head>
<body>

<div class="topbar">
    <div class="brand">☕ Coffee Shop</div>

    <nav class="navbar">
        <div class="dropdown">
            <a href="#">Home ▾</a>
            <div class="dropdown-content">
                <a href="dashboard.php">Coffee Menu</a>
                <a href="#">Specials</a>
            </div>
        </div>
        <div class="dropdown">
            <a href="#">Offers ▾</a>
            <div class="dropdown-content">
                <a href="#">Discounts</a>
                <a href="#">Bundle Deals</a>
            </div>
        </div>
        <a href="#">About</a>
    </nav>

    <div>
        Welcome <?= $birthYear ?>-<?= $userSerial ?>  
        <a href="cart.php" class="cart">
            🛒 Cart
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<div class="banner">
    <div class="banner-content">Fresh Coffee, Great Taste!</div>
</div>

<div class="container">
    <h2>Our Coffee Menu</h2>
    <div class="products">
        <?php
        $products = [
            ["Americano", "americano.jpg", 2.00, 2.50, "20% OFF"],
            ["Latte", "latte.jpg", 2.55, 3.00, "15% OFF"],
            ["Cappuccino", "cappuccino.jpg", 2.88, 3.20, "10% OFF"]
        ];
        $id = 1;
        foreach ($products as $p):
        ?>
        <div class="card">
            <div class="discount-badge"><?= $p[4] ?></div>
            <img src="<?= $p[1] ?>" alt="<?= $p[0] ?>">
            <h3><?= $p[0] ?></h3>
            <p><span style="text-decoration: line-through; color: #888;">$<?= number_format($p[3], 2) ?></span> <strong>$<?= number_format($p[2], 2) ?></strong></p>
            <div class="qty-control">
                <button onclick="decreaseQty(<?= $id ?>)">-</button>
                <input type="text" id="qty-<?= $id ?>" value="1" readonly>
                <button onclick="increaseQty(<?= $id ?>)">+</button>
            </div>
            <form method="POST">
                <input type="hidden" name="product_name" value="<?= $p[0] ?>">
                <input type="hidden" name="price" value="<?= $p[2] ?>">
                <input type="hidden" name="qty" id="form-qty-<?= $id ?>" value="1">
                <button type="submit" class="add-btn">Add to Cart</button>
            </form>
        </div>
        <?php $id++; endforeach; ?>
    </div>
</div>

<footer>
    &copy; <?= date("Y") ?> Coffee Shop. All Rights Reserved.
</footer>

<script>
document.querySelectorAll('.qty-control').forEach((control, index) => {
    const productId = index + 1;
    control.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('form-qty-' + productId).value =
                document.getElementById('qty-' + productId).value;
        });
    });
});
</script>

</body>
</html>
