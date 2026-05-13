<h2>Latest Products</h2>
<p>Welcome to our dynamic product catalog. (Intentionally filled with errors)</p>

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
            <!-- ERROR: Dynamic image without alt text -->
            <img src="images/<?php echo $product['image']; ?>" width="100">
            <h3>
                <?php echo $product['name']; ?>
            </h3>
            <p>
                <?php echo $product['price']; ?>
            </p>
            <button class="btn">Buy Now</button>
            <!-- Error: Button might need specific accessible name if icon-only, but text is okay here. -->
        </div>
    <?php endforeach; ?>
</div>

<hr>

<h3>Sponsors</h3>
<div>
    <!-- ERROR: Decorative image causing noise, missing alt="" -->
    <img src="https://via.placeholder.com/150">
    <img src="https://via.placeholder.com/150">
</div>