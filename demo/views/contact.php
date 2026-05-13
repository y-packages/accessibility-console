<h2>Contact Us</h2>
<p>Get in touch with us.</p>

<form action="" method="post">
    <div style="margin-bottom: 1rem;">
        <!-- ERROR: No label for input -->
        <div>Name:</div>
        <input type="text" name="fullname" placeholder="John Doe" style="padding: 8px; width: 100%;">
    </div>

    <div style="margin-bottom: 1rem;">
        <!-- ERROR: Label exists but not associated with For/ID -->
        <label>Email Address</label>
        <br>
        <input type="email" name="email" style="padding: 8px; width: 100%;">
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="message">Message</label>
        <br>
        <textarea id="message" name="message" rows="5" style="width: 100%;"></textarea>
    </div>

    <!-- ERROR: Submit button without clear text if it was an input type=image, but button is usually fine. 
         Let's add a problematic icon link -->
    <a href="#" class="btn">
        <i class="fa fa-send"></i> <!-- Empty link text error (if we had that rule) -->
    </a>

    <button type="submit" class="btn">Send Message</button>
</form>