<h2>Contact Us</h2>
<p>Get in touch with us.</p>

<form action="" method="post">
    <div style="margin-bottom: 1rem;">
        <!-- FIXED: Wrapped in label -->
        <label>
            Name:
            <input type="text" name="fullname" placeholder="John Doe" style="padding: 8px; width: 100%;">
        </label>
    </div>

    <div style="margin-bottom: 1rem;">
        <!-- FIXED: Added for/id association -->
        <label for="email">Email Address</label>
        <br>
        <input type="email" id="email" name="email" style="padding: 8px; width: 100%;">
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="message">Message</label>
        <br>
        <textarea id="message" name="message" rows="5" style="width: 100%;"></textarea>
    </div>

    <!-- FIXED: Added inner text or aria-label -->
    <a href="#" class="btn" aria-label="Send via WhatsApp">
        <i class="fa fa-send"></i> Send
    </a>

    <button type="submit" class="btn">Send Message</button>
</form>