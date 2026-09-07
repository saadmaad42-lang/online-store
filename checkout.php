<?php

session_start();

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| التحقق من تسجيل الدخول
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| التحقق من وجود السلة
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$cart = $_SESSION["cart"];

$products = [];
$total = 0;
$error = "";
$success = false;
$order_id = null;


/*
|--------------------------------------------------------------------------
| جلب المنتجات الموجودة في السلة
|--------------------------------------------------------------------------
*/

foreach ($cart as $product_id => $quantity) {

    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    $stmt = $conn->prepare(
        "SELECT id, name, price, stock, image
         FROM products
         WHERE id = ?"
    );

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $result = $stmt->get_result();

    /*
    | التحقق من وجود المنتج
    */

    if ($result->num_rows > 0) {

        $product = $result->fetch_assoc();

        /*
        | إضافة الكمية المطلوبة
        */

        $product["quantity"] = $quantity;

        /*
        | حساب السعر الإجمالي للمنتج
        */

        $product["subtotal"] =
            $product["price"] * $quantity;

        /*
        | إضافة المنتج إلى قائمة المنتجات
        */

        $products[] = $product;

        /*
        | إضافة سعر المنتج إلى الإجمالي
        */

        $total += $product["subtotal"];


        /*
        |--------------------------------------------------------------------------
        | التحقق من المخزون
        |--------------------------------------------------------------------------
        */

        if ($quantity > $product["stock"]) {

            $error .=
                "الكمية المطلوبة من المنتج \"" .
                htmlspecialchars($product["name"]) .
                "\" غير متوفرة في المخزون. " .
                "المتوفر حاليًا: " .
                $product["stock"] .
                "، المطلوب: " .
                $quantity .
                ".<br><br>";
        }

    } else {

        /*
        | إذا كان المنتج غير موجود في قاعدة البيانات
        */

        $error .=
            "المنتج رقم " .
            $product_id .
            " غير موجود في قاعدة البيانات.<br><br>";
    }

}


/*
|--------------------------------------------------------------------------
| تنفيذ الطلب عند الضغط على تأكيد الطلب
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {

    /*
    | بدء Transaction
    */

    $conn->begin_transaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | إعادة التحقق من المخزون
        |--------------------------------------------------------------------------
        */

        foreach ($cart as $product_id => $quantity) {

            $product_id = (int)$product_id;
            $quantity = (int)$quantity;

            $stmt = $conn->prepare(
                "SELECT id, name, price, stock
                 FROM products
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {

                throw new Exception(
                    "أحد المنتجات الموجودة في السلة غير موجود في قاعدة البيانات."
                );
            }

            $product = $result->fetch_assoc();

            /*
            | التحقق من المخزون
            */

            if ($quantity > $product["stock"]) {

                throw new Exception(
                    "الكمية المطلوبة من المنتج \"" .
                    $product["name"] .
                    "\" غير متوفرة. " .
                    "المتوفر: " .
                    $product["stock"] .
                    "، المطلوب: " .
                    $quantity
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | إنشاء الطلب
        |--------------------------------------------------------------------------
        */

        $status = "pending";

        $stmt = $conn->prepare(
            "INSERT INTO orders
            (user_id, total, status)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "ids",
            $user_id,
            $total,
            $status
        );

        if (!$stmt->execute()) {

            throw new Exception(
                "حدث خطأ أثناء إنشاء الطلب."
            );
        }


        /*
        | الحصول على رقم الطلب
        */

        $order_id = $conn->insert_id;


        /*
        |--------------------------------------------------------------------------
        | إضافة تفاصيل الطلب وتحديث المخزون
        |--------------------------------------------------------------------------
        */

        foreach ($cart as $product_id => $quantity) {

            $product_id = (int)$product_id;
            $quantity = (int)$quantity;


            /*
            | الحصول على السعر الحالي
            */

            $stmt = $conn->prepare(
                "SELECT price, stock
                 FROM products
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            $result = $stmt->get_result();

            $product = $result->fetch_assoc();

            $price = $product["price"];


            /*
            |--------------------------------------------------------------------------
            | إضافة المنتج إلى order_items
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "INSERT INTO order_items
                (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiid",
                $order_id,
                $product_id,
                $quantity,
                $price
            );

            if (!$stmt->execute()) {

                throw new Exception(
                    "حدث خطأ أثناء إضافة تفاصيل الطلب."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | تحديث المخزون
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "UPDATE products
                 SET stock = stock - ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "ii",
                $quantity,
                $product_id
            );

            if (!$stmt->execute()) {

                throw new Exception(
                    "حدث خطأ أثناء تحديث مخزون المنتج."
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | تأكيد Transaction
        |--------------------------------------------------------------------------
        */

        $conn->commit();


        /*
        | تفريغ السلة بعد نجاح الطلب
        */

        $_SESSION["cart"] = [];

        $success = true;

    } catch (Exception $e) {

        /*
        |--------------------------------------------------------------------------
        | التراجع عن جميع العمليات
        |--------------------------------------------------------------------------
        */

        $conn->rollback();

        $error = $e->getMessage();
    }
}

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        إتمام الطلب - المتجر الإلكتروني
    </title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>


<body>


<!-- ==============================
     Navbar
============================== -->

<header>

    <nav class="navbar">

        <div class="container">

            <h1 class="logo">
                المتجر الإلكتروني
            </h1>

            <ul class="nav-links">

                <li>
                    <a href="index.php">
                        الرئيسية
                    </a>
                </li>

                <li>
                    <a href="products.php">
                        المنتجات
                    </a>
                </li>

                <li>
                    <a href="cart.php">
                        السلة
                    </a>
                </li>

                <li>
                    <a href="logout.php">
                        تسجيل الخروج
                    </a>
                </li>

            </ul>

        </div>

    </nav>

</header>


<!-- ==============================
     Main
============================== -->

<main>


<?php if ($success): ?>

    <!-- ==============================
         Success Message
    ============================== -->

    <section class="checkout-section">

        <div class="success-box">

            <h2>
                تم تأكيد الطلب بنجاح ✓
            </h2>


            <p>

                شكرًا لك،

                <?php
                echo htmlspecialchars(
                    $_SESSION["user_name"]
                );
                ?>

            </p>


            <p>

                رقم الطلب:

                <strong>

                    #

                    <?php
                    echo $order_id;
                    ?>

                </strong>

            </p>


            <p>

                إجمالي الطلب:

                <strong>

                    $

                    <?php
                    echo number_format(
                        $total,
                        2
                    );
                    ?>

                </strong>

            </p>


            <div class="checkout-actions">

                <a
                    href="products.php"
                    class="btn"
                >
                    متابعة التسوق
                </a>


                <a
                    href="index.php"
                    class="btn"
                >
                    الصفحة الرئيسية
                </a>

            </div>

        </div>

    </section>


<?php else: ?>


    <!-- ==============================
         Checkout
    ============================== -->

    <section class="checkout-section">

        <div class="checkout-container">


            <h2 class="section-title">
                إتمام الطلب
            </h2>


            <!-- ==============================
                 Error Message
            ============================== -->

            <?php if (!empty($error)): ?>

                <div class="message">

                    <?php
                    echo $error;
                    ?>

                </div>

            <?php endif; ?>


            <!-- ==============================
                 Checkout Grid
            ============================== -->

            <div class="checkout-grid">


                <!-- ==============================
                     Customer Information
                ============================== -->

                <div class="checkout-box">

                    <h3>
                        معلومات العميل
                    </h3>


                    <p>

                        <strong>
                            الاسم:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION["user_name"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            البريد الإلكتروني:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION["user_email"]
                        );
                        ?>

                    </p>


                    <p>
                        سيتم استخدام الحساب الحالي
                        لتنفيذ الطلب.
                    </p>

                </div>


                <!-- ==============================
                     Order Summary
                ============================== -->

                <div class="checkout-box">

                    <h3>
                        ملخص الطلب
                    </h3>


                    <?php foreach ($products as $product): ?>

                        <div class="checkout-item">


                            <div>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["name"]
                                    );
                                    ?>

                                </strong>


                                <p>

                                    الكمية:

                                    <?php
                                    echo $product["quantity"];
                                    ?>

                                </p>


                                <p>

                                    المتوفر:

                                    <?php
                                    echo $product["stock"];
                                    ?>

                                </p>

                            </div>


                            <strong>

                                $

                                <?php
                                echo number_format(
                                    $product["subtotal"],
                                    2
                                );
                                ?>

                            </strong>


                        </div>

                    <?php endforeach; ?>


                    <!-- ==============================
                         Total
                    ============================== -->

                    <div class="checkout-total">

                        <span>
                            الإجمالي:
                        </span>


                        <strong>

                            $

                            <?php
                            echo number_format(
                                $total,
                                2
                            );
                            ?>

                        </strong>

                    </div>


                    <!-- ==============================
                         Confirm Order
                    ============================== -->

                    <form method="POST">

                        <button
                            type="submit"
                            class="btn checkout-btn"
                            <?php
                            if (!empty($error)) {
                                echo "disabled";
                            }
                            ?>
                        >

                            تأكيد الطلب

                        </button>

                    </form>


                    <!-- ==============================
                         Back To Cart
                    ============================== -->

                    <a
                        href="cart.php"
                        class="back-link"
                    >
                        العودة إلى السلة
                    </a>

                </div>

            </div>

        </div>

    </section>


<?php endif; ?>


</main>


<!-- ==============================
     Footer
============================== -->

<footer>

    <p>

        جميع الحقوق محفوظة © 2026
        المتجر الإلكتروني

    </p>

</footer>


</body>

</html>
