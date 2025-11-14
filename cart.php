<?php
include('include/connect.php');

// ตั้ง user_id (ในโปรเจคจริงจะมาจาก session)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// จัดการการเพิ่มสินค้าลงตะกร้า
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    
    // ตรวจสอบว่าสินค้านี้มีในตะกร้าแล้วหรือไม่
    $check_sql = "SELECT * FROM cart_tb WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // ถ้ามีแล้ว ให้เพิ่มจำนวน
        $update_sql = "UPDATE cart_tb SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id";
        mysqli_query($conn, $update_sql);
    } else {
        // ถ้าไม่มี ให้เพิ่มใหม่
        $insert_sql = "INSERT INTO cart_tb (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)";
        mysqli_query($conn, $insert_sql);
    }
    header("Location: cart.php");
    exit();
}

// จัดการการลบสินค้าจากตะกร้า
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $delete_sql = "DELETE FROM cart_tb WHERE cart_id = $cart_id AND user_id = $user_id";
    mysqli_query($conn, $delete_sql);
    header("Location: cart.php");
    exit();
}

// จัดการการแก้ไขจำนวน
if (isset($_POST['update_quantity'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $update_sql = "UPDATE cart_tb SET quantity = $quantity WHERE cart_id = $cart_id AND user_id = $user_id";
        mysqli_query($conn, $update_sql);
    } else {
        $delete_sql = "DELETE FROM cart_tb WHERE cart_id = $cart_id AND user_id = $user_id";
        mysqli_query($conn, $delete_sql);
    }
    header("Location: cart.php");
    exit();
}

// จัดการการลบสินค้าทั้งหมด
if (isset($_GET['clear'])) {
    $clear_sql = "DELETE FROM cart_tb WHERE user_id = $user_id";
    mysqli_query($conn, $clear_sql);
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Shopping Cart</h1>
        <a href="products.php" class="btn btn-secondary mb-3">Back to Products</a>
        
        <?php
        // ดึงข้อมูลตะกร้าของผู้ใช้
        $cart_sql = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.product_price, p.product_image, p.product_stock
                     FROM cart_tb c
                     JOIN product_tb p ON c.product_id = p.product_id
                     WHERE c.user_id = $user_id";
        $cart_result = mysqli_query($conn, $cart_sql);
        $cart_count = mysqli_num_rows($cart_result);
        
        if ($cart_count > 0) {
            $total_price = 0;
        ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ชื่อสินค้า</th>
                            <th>ราคา</th>
                            <th>จำนวน</th>
                            <th>ยอดรวม</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($cart_result)) {
                            $cart_id = $row['cart_id'];
                            $product_name = $row['product_name'];
                            $product_price = $row['product_price'];
                            $quantity = $row['quantity'];
                            $item_total = $product_price * $quantity;
                            $total_price += $item_total;
                            $product_stock = $row['product_stock'];
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($product_name); ?></strong>
                                </td>
                                <td>
                                    ฿<?php echo number_format($product_price, 2); ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="cart_id" value="<?php echo $cart_id; ?>">
                                        <div class="input-group" style="width: 120px;">
                                            <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-secondary" value="<?php echo $quantity - 1; ?>" onclick="this.form.quantity.value = <?php echo $quantity - 1; ?>">-</button>
                                            <input type="number" name="quantity" class="form-control form-control-sm text-center" value="<?php echo $quantity; ?>" min="0" max="<?php echo $product_stock; ?>" onchange="this.form.submit();">
                                            <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-secondary" value="<?php echo $quantity + 1; ?>" onclick="this.form.quantity.value = <?php echo $quantity + 1; ?>">+</button>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <strong>฿<?php echo number_format($item_total, 2); ?></strong>
                                </td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $cart_id; ?>" class="btn btn-sm btn-danger">Remove</a>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <a href="cart.php?clear=1" class="btn btn-warning" onclick="return confirm('ต้องการลบสินค้าทั้งหมดใช่หรือไม่?')">Clear Cart</a>
                    <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
                <div class="col-md-6 text-end">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Order Summary</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>฿<?php echo number_format($total_price, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>฿0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax:</span>
                                <span>฿0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="h5">฿<?php echo number_format($total_price, 2); ?></strong>
                            </div>
                            <button class="btn btn-success w-100 mt-3">Proceed to Checkout</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else {
            echo '<div class="alert alert-info text-center mt-4">';
            echo '<h4>Your cart is empty</h4>';
            echo '<p class="mb-0">Start shopping by visiting our <a href="products.php" class="alert-link">Products page</a></p>';
            echo '</div>';
        }
        
        mysqli_close($conn);
        ?>
    </div>
</body>
</html>
