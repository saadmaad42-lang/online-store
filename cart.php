```php
<?php

session_start();

require_once "config/database.php";


// إنشاء السلة إذا لم تكن موجودة
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


// إضافة منتج إلى السلة
if (isset($_GET["add"]) && is_numeric($_GET["add"])) {

    $product_id = intval($_GET["add"]);

    $sql = "SELECT id, name, price, stock, image
            FROM products
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $product_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $product = $result->fetch_assoc();

        if ($product["stock"] > 0) {

            if (isset($_SESSION["cart"][$product_id])) {

                if (
                    $_SESSION["cart"][$product_id]["quantity"]
                    < $product["stock"]
                ) {

                    $_SESSION["cart"][$product_id]["quantity"]++;

                }

            } else {

                $_SESSION["cart"][$product_id] = [

                    "id" => $product["id"],

                    "name" => $product["name"],

                    "price" => $product["price"],

                    "image" => $product["image"],

                    "quantity" => 1

                ];

            }

        }

    }

    header("Location: cart.php");

    exit;
}


// زيادة الكمية
if (isset($_GET["increase"]) && is_numeric($_GET["increase"])) {

    $product_id = intval($_GET["increase"]);

    if (isset($_SESSION["cart"][$product_id])) {

        $sql = "SELECT stock FROM products WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $product_id);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $product = $result->fetch_assoc();

            if (
                $_SESSION["cart"][$product_id]["quantity"]
                < $product["stock"]
            ) {

                $_SESSION["cart"][$product_id]["quantity"]++;

            }

        }

    }

    header("Location: cart.php");

    exit;
}


// تقليل الكمية
if (isset($_GET["decrease"]) && is_numeric($_GET["decrease"])) {

    $product_id = intval($_GET["decrease"]);

    if (isset($_SESSION["cart"][$product_id])) {

        $_SESSION["cart"][$product_id]["quantity"]--;

        if ($_SESSION["cart"][$product_id]["quantity"] <= 0) {

            unset($_SESSION["cart"][$product_id]);

        }

    }

    header("Location: cart.php");

    exit;
}


// حذف منتج
if (isset($_GET["remove"]) && is_numeric($_GET["remove"])) {

    $product_id = intval($_GET["remove"]);

    unset($_SESSION["cart"][$product_id]);

    header("Location: cart.php");

    exit;
}


// حساب الإجمالي
$total = 0;

foreach ($_SESSION["cart"] as $item) {

    $total += $item["price"] * $item["quantity"];

}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>سلة المشتريات</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>


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

            <?php if (isset($_SESSION["user_id"])): ?>

                <li>
                    <a href="logout.php">
                        تسجيل الخروج
                    </a>
                </li>

            <?php else: ?>

                <li>
                    <a href="login.php">
                        تسجيل الدخول
                    </a>
                </li>

            <?php endif; ?>

        </ul>

    </div>

</nav>


<section class="products-section">

    <div class="container">

        <h2 class="section-title">
            🛒 سلة المشتريات
        </h2>


        <?php if (empty($_SESSION["cart"])): ?>

            <div class="empty-cart">

                <h3>
                    السلة فارغة
                </h3>

                <p>
                    لم تقم بإضافة أي منتجات حتى الآن.
                </p>

                <a href="products.php" class="btn">
                    تصفح المنتجات
                </a>

            </div>

        <?php else: ?>


            <div class="cart-container">


                <?php foreach ($_SESSION["cart"] as $item): ?>

                    <div class="cart-item">


                        <div class="cart-item-image">

                            <?php if (!empty($item["image"])): ?>

                                <img
                                    src="assets/images/<?php
                                    echo htmlspecialchars($item["image"]);
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars($item["name"]);
                                    ?>"
                                >

                            <?php else: ?>

                                <span>🛍️</span>

                            <?php endif; ?>

                        </div>


                        <div class="cart-item-info">

                            <h3>

                                <?php
                                echo htmlspecialchars($item["name"]);
                                ?>

                            </h3>


                            <p>

                                السعر:

                                <strong>
                                    $
                                    <?php
                                    echo number_format(
                                        $item["price"],
                                        2
                                    );
                                    ?>
                                </strong>

                            </p>


                            <div class="quantity">

                                <a
                                    href="cart.php?decrease=<?php
                                    echo $item["id"];
                                    ?>"
                                >
                                    −
                                </a>

                                <span>
                                    <?php echo $item["quantity"]; ?>
                                </span>

                                <a
                                    href="cart.php?increase=<?php
                                    echo $item["id"];
                                    ?>"
                                >
                                    +
                                </a>

                            </div>


                            <p>

                                الإجمالي:

                                <strong>

                                    $
                                    <?php

                                    echo number_format(
                                        $item["price"]
                                        * $item["quantity"],
                                        2
                                    );

                                    ?>

                                </strong>

                            </p>


                            <a
                                href="cart.php?remove=<?php
                                echo $item["id"];
                                ?>"
                                class="remove-btn"
                            >
                                حذف
                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>


                <div class="cart-summary">

                    <h3>
                        إجمالي السلة:
                    </h3>

                    <strong>

                        $
                        <?php
                        echo number_format($total, 2);
                        ?>

                    </strong>


                    <div>

                        <a
                            href="products.php"
                            class="btn"
                        >
                            متابعة التسوق
                        </a>


                        <a
                            href="checkout.php"
                            class="btn"
                        >
                            إتمام الطلب
                        </a>

                    </div>

                </div>

            </div>


        <?php endif; ?>

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
