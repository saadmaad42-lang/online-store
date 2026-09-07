```php
<?php

require_once "config/database.php";

$sql = "SELECT products.*, categories.name AS category_name
        FROM products
        INNER JOIN categories
        ON products.category_id = categories.id
        ORDER BY products.id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>المتجر الإلكتروني</title>

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


    <!-- القسم الرئيسي -->

    <section class="hero">

        <div class="container">

            <h2>مرحبًا بك في متجرنا الإلكتروني</h2>

            <p>
                اكتشف مجموعة متنوعة من المنتجات بأسعار مناسبة
            </p>

            <a href="products.php" class="btn">
                تصفح المنتجات
            </a>

        </div>

    </section>


    <!-- المنتجات -->

    <section class="products-section">

        <div class="container">

            <h2 class="section-title">
                أحدث المنتجات
            </h2>


            <div class="products-grid">

                <?php

                if ($result && $result->num_rows > 0):

                    while ($product = $result->fetch_assoc()):

                ?>

                    <div class="product-card">

                        <div class="product-image">

                            <?php if (!empty($product['image'])): ?>

                                <img
                                    src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                >

                            <?php else: ?>

                                <span>🛍️</span>

                            <?php endif; ?>

                        </div>


                        <div class="product-info">

                            <span class="category">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>

                            <h3>
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>

                            <p>
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>

                            <div class="product-bottom">

                                <strong>
                                    $<?php echo number_format($product['price'], 2); ?>
                                </strong>

                                <span>
                                    المخزون: <?php echo $product['stock']; ?>
                                </span>

                            </div>

                            <a
                                href="product.php?id=<?php echo $product['id']; ?>"
                                class="btn"
                            >
                                عرض التفاصيل
                            </a>

                        </div>

                    </div>

                <?php

                    endwhile;

                else:

                ?>

                    <p class="no-products">
                        لا توجد منتجات حاليًا.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </section>


    <!-- التذييل -->

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
