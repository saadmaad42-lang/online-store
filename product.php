```php
<?php

require_once "config/database.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("المنتج غير موجود.");
}

$product_id = intval($_GET['id']);

$sql = "SELECT products.*, categories.name AS category_name
        FROM products
        INNER JOIN categories
        ON products.category_id = categories.id
        WHERE products.id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $product_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("المنتج غير موجود.");
}

$product = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($product['name']); ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <!-- شريط التنقل -->

    <nav class="navbar">

        <div class="container">

            <h1 class="logo">🛒 متجري</h1>

            <ul class="nav-links">

                <li>
                    <a href="index.php">الرئيسية</a>
                </li>

                <li>
                    <a href="products.php">المنتجات</a>
                </li>

                <li>
                    <a href="cart.php">🛒 السلة</a>
                </li>

                <li>
                    <a href="login.php">تسجيل الدخول</a>
                </li>

            </ul>

        </div>

    </nav>


    <!-- تفاصيل المنتج -->

    <section class="products-section">

        <div class="container">

            <div class="product-details">

                <!-- صورة المنتج -->

                <div class="product-details-image">

                    <?php if (!empty($product['image'])): ?>

                        <img
                            src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        >

                    <?php else: ?>

                        <span>🛍️</span>

                    <?php endif; ?>

                </div>


                <!-- بيانات المنتج -->

                <div class="product-details-info">

                    <span class="category">

                        <?php
                        echo htmlspecialchars(
                            $product['category_name']
                        );
                        ?>

                    </span>


                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $product['name']
                        );
                        ?>

                    </h2>


                    <p>

                        <?php
                        echo htmlspecialchars(
                            $product['description']
                        );
                        ?>

                    </p>


                    <h3>

                        $
                        <?php
                        echo number_format(
                            $product['price'],
                            2
                        );
                        ?>

                    </h3>


                    <p>

                        الكمية المتوفرة:
                        <strong>
                            <?php echo $product['stock']; ?>
                        </strong>

                    </p>


                    <?php if ($product['stock'] > 0): ?>

                        <a
                            href="cart.php?add=<?php echo $product['id']; ?>"
                            class="btn"
                        >
                            🛒 إضافة إلى السلة
                        </a>

                    <?php else: ?>

                        <p>
                            ❌ المنتج غير متوفر حاليًا.
                        </p>

                    <?php endif; ?>


                    <br>

                    <a href="products.php" class="btn">
                        العودة إلى المنتجات
                    </a>

                </div>

            </div>

        </div>

    </section>


    <footer>

        <div class="container">

            <p>
                © 2026 المتجر الإلكتروني - جميع الحقوق محفوظة
            </p>

        </div>

    </footer>

</body>

</html>
```
