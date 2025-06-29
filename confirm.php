<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Confirmed</title>
   
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .message-box {
            background-color: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }

        .message-box .icon {
            font-size: 3rem;
            color: #2ecc71;
            margin-bottom: 1rem;
        }

        .message-box h1 {
            color: #3498db;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .message-box p {
            font-size: 1.1rem;
            color: #555;
        }

        .home-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 12px 24px;
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .home-link:hover {
            background: linear-gradient(to right, #2980b9, #2471a3);
        }
    </style>
</head>
<body>

    <div class="message-box">
        <div class="icon"><i class="fas fa-thumbs-up"></i></div>
        <h1>Your registration has been confirmed!</h1>
        <p>Thank you for joining our service. You will now receive updates and alerts based on your preferences.</p>
        <a href="index.php" class="home-link"><i class="fas fa-home"></i> Go to Home</a>
    </div>

</body>
</html>
