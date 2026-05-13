<!DOCTYPE html>
<html lang="tr"> <!-- FIXED: Added lang attribute -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YakNet Dynamic Demo (Fixed)</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        header {
            background: #333;
            color: #fff;
            padding: 1rem;
        }

        nav a {
            color: #fff;
            margin-right: 1rem;
            text-decoration: none;
        }

        .container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .btn {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header>
        <div class="container" style="padding: 0;">
            <h1>My Dynamic Site (Accessible)</h1>
            <nav>
                <a href="?page=home">Home</a>
                <a href="?page=contact">Contact</a>
                <a href="?page=about">About</a>
                <!-- FIXED: Added aria-label to empty link, or removed it/added text -->
                <a href="#" class="btn" aria-label="Profile">👤</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php include $viewPath; ?>
    </main>

    <footer style="text-align: center; padding: 2rem; background: #f4f4f4; margin-top: 2rem;">
        &copy;
        <?php echo date('Y'); ?> YakNet Demo
    </footer>
</body>

</html>