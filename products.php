<?php
include('include/connect.php');
session_start();
$_SESSION['user_id'] = 1;

// รับค่า category
$cat = $_GET['cat'] ?? null;

// ดึง category ทั้งหมดจาก product_tb
$cat_sql = "SELECT DISTINCT category_name FROM product_tb";
$cat_result = mysqli_query($conn, $cat_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    
<div class="container mt-4">

    <!-- Category Menu -->
    <div class="d-flex justify-content-between mb-3">
        <h1>Products Page</h1>

        <div>
            <label class="fw-bold me-2">เลือกหมวดหมู่:</label>
            <select onchange="location.href='products.php?cat=' + this.value" class="form-select d-inline-block w-auto">
                <option value="">ทั้งหมด</option>
                <?php while($c = mysqli_fetch_assoc($cat_result)): ?>
                    <option value="<?= $c['category_name'] ?>" <?= ($cat == $c['category_name']) ? 'selected' : '' ?>>
                        <?= $c['category_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <a href="index.php" class="btn btn-secondary mb-3">Back to Home</a>

    <div class="row">
        <?php
        // ถ้ามีการเลือก cat
        if ($cat) {
            $sql = "SELECT * FROM product_tb WHERE category_name = '$cat' ORDER BY created_at DESC";
        } else {
            $sql = "SELECT * FROM product_tb ORDER BY created_at DESC";
        }

        $result = mysqli_query($conn, $sql);

        // ตรวจข้อมูล
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $product_id = $row['product_id'];
                $product_name = $row['product_name'];
                $product_detail = $row['product_detail'];
                $product_price = $row['product_price'];
                $product_stock = $row['product_stock'];
                $product_image = $row['product_image'];
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <!-- image -->
                        <?php if($product_image): ?>
                            <img src="<?= htmlspecialchars($product_image); ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                <span class="text-muted">No Image</span>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product_name); ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars(substr($product_detail, 0, 100)); ?>...</p>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="h5">฿<?= number_format($product_price, 2); ?></span>
                                <span class="badge bg-<?= ($product_stock > 0) ? 'success' : 'danger'; ?>">
                                    <?= ($product_stock > 0) ? 'Stock: '.$product_stock : 'Out of Stock'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-footer bg-white">
                            <?php if($product_stock > 0): ?>
                                <a href="cart.php?add=<?= $product_id; ?>" class="btn btn-primary w-100">Add to Cart</a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<div class="col-12"><p class="alert alert-warning text-center">ไม่พบสินค้า</p></div>';
        }
        ?>
    </div>
</div>
</body>
</html>
