<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/header.php";
require __DIR__."/../handlers/cartDetails.php";
?>

<link rel="stylesheet" href="/assests/css/filter.css">

<section class="cart-item page">
    <h2>Book Cart</h2>
    <div class="product-table">
        <table cellspacing="0">
            <tr class="heading">
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>

            <?php foreach ($cartItems as $item): ?>
                <?php
                    $price = ($item->Discount_Percentage === NULL) ? $item->Original_Price : $item->Discount_Price;
                    $lineTotal = $price * $item->quantity;
                ?>
                <tr class="data">
                    <td>
                        <img src="/images/<?= htmlspecialchars($item->coverImage) ?>" alt="">
                    </td>
                    <td><?= htmlspecialchars($item->title) ?></td>
                    <td><?= htmlspecialchars($price) ?> $</td>
                    <td>
                        <div class="input-group">
                            <div class="quantity">
                                <input
                                    type="text"
                                    value="<?= (int)$item->quantity ?>"
                                    class="quantity-field"
                                    style="width: 4.5rem"
                                    readonly
                                />
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($lineTotal) ?> $</td>
                    <td><i class="fa-solid fa-pencil"></i></td>
                    <td><button class="btn">Update</button></td>
                </tr>
            <?php endforeach; ?>

        </table>
    </div>
</section>

<section class="discount-summary">

    <div class="summary-section">
        <h4>Cart Subtotal</h4>
        <div class="order-detail-table">
            <table>
                <tr>
                    <td>Order Subtotal</td>
                    <td><?= htmlspecialchars($allTotal) ?>$</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td><?= $shipping ?>$</td>
                </tr>

                <tr>
                    <td>Total</td>
                    <td><?= htmlspecialchars($total) ?>$</td>
                </tr>
            </table>
            <button><a href="/pages/checkout.php">Proceed To Checkout</a></button>
        </div>
    </div>
</section>

<?php 
require __DIR__."/../includes/footer.php";
?>