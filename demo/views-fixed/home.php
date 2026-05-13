<h2>Latest Products</h2>
<p>Welcome to our accessible product catalog.</p>

<?php
$products = [
    ['id' => 1, 'name' => 'Super Gadget', 'image' => 'gadget.jpg', 'price' => '$99'],
    ['id' => 2, 'name' => 'Mega Widget', 'image' => 'widget.png', 'price' => '$49'],
    ['id' => 3, 'name' => 'Ultra Thing', 'image' => 'thing.jpg', 'price' => '$199'],
];
?>

<div class="products">
    <?php foreach ($products as $product): ?>
        <div class="card">
            <!-- FIXED: Added alt attribute describing the product -->
            <img src="images/<?php echo $product['image']; ?>" width="100"
                alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h3>
                <?php echo $product['name']; ?>
            </h3>
            <p>
                <?php echo $product['price']; ?>
            </p>
            <button class="btn">Buy Now</button>
        </div>
    <?php endforeach; ?>
</div>

<hr>

<h3>Sponsors</h3>
<div>
    <!-- FIXED: Added empty alt="" for decorative images -->
    <img src="https://via.placeholder.com/150" alt="">
    <img src="https://via.placeholder.com/150" alt="">
</div>