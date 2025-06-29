<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'users';
$username = 'root';
$password = '';

$rememberedEmail = '';
$loginError = '';

// Check remembered email
if (isset($_COOKIE['remembered_email'])) {
    $rememberedEmail = htmlspecialchars($_COOKIE['remembered_email']);
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        $loginError = "Connection failed: " . $conn->connect_error;
    } else {
        $email = $conn->real_escape_string($_POST['login_email']);
        $password = $_POST['login_password'];
        $remember = isset($_POST['remember']) ? true : false;

        // Find user in database
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                
                // Remember email if requested
                if ($remember) {
                    $expire = time() + 30 * 24 * 60 * 60;
                    setcookie('remembered_email', $email, $expire, '/');
                } else {
                    setcookie('remembered_email', '', time() - 3600, '/');
                }
                
                header("Location: request.php");
                exit();
            } else {
                $loginError = "Invalid email or password.";
            }
        } else {
            $loginError = "Invalid email or password.";
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Quality Monitoring Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .aqi-info-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .aqi-scale-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 20px;
            border: 2px solid #e0e8f0;
        }
        
        .aqi-scale-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .aqi-scale-header i {
            color: #3498db;
        }
        
        .aqi-levels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .aqi-level {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .aqi-good { background: #27ae60; color: white; }
        .aqi-moderate { background: #f39c12; color: white; }
        .aqi-sensitive { background: #e67e22; color: white; }
        .aqi-unhealthy { background: #e74c3c; color: white; }
        .aqi-very-unhealthy { background: #8e44ad; color: white; }
        .aqi-hazardous { background: #2c3e50; color: white; }
        
        .group-members-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
        }
        
        .group-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .group-header h3 {
            margin: 0;
            color: white;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .member-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .member-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .member-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .member-id {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .health-tips {
            margin-top: 15px;
        }
        
        .tip-section {
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .tip-section h4 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        
        .tip-section h4 i {
            color: #3498db;
        }
        
        .tip-section ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .tip-section li {
            margin-bottom: 5px;
            color: #34495e;
            font-size: 14px;
        }
        
        /* Enhanced Color Picker Styling */
        .color-picker-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .color-picker-container:hover {
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
        }
        
        .color-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .color-preview:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        
        .color-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.3) 50%, transparent 60%);
            pointer-events: none;
        }
        
        #fav_color {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .color-info {
            flex: 1;
        }
        
        .color-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 2px;
        }
        
        .color-hex {
            font-size: 12px;
            color: #7f8c8d;
            font-family: 'Courier New', monospace;
            background: rgba(0, 0, 0, 0.05);
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .color-suggestions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        
        .color-suggestion {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .color-suggestion:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <header>
            <img src="images.png" alt="Air Quality Banner" class="top-banner">
            <h1><i class="fas fa-wind"></i> Air Quality Monitoring Dashboard</h1>
            <p>Real-time air quality data for major cities in Bangladesh</p>
        </header>

        <div class="container">
            <div class="left-panel">
                <div class="box box-1">
                    <h3><i class="fas fa-chart-bar"></i> Air Quality Index Data</h3>
                    <p>Current AQI measurements for major cities:</p>
                    <table>
                        <tr>
                            <th>City</th>
                            <th>Air Quality Index</th>
                        </tr>
                        <tr>
                            <td>Dhaka</td>
                            <td>150</td>
                        </tr>
                        <tr>
                            <td>Tangail</td>
                            <td>120</td>
                        </tr>
                        <tr>
                            <td>Rajshahi</td>
                            <td>130</td>
                        </tr>
                        <tr>
                            <td>Khulna</td>
                            <td>140</td>
                        </tr>
                        <tr>
                            <td>Sylhet</td>
                            <td>110</td>
                        </tr>
                        <tr>
                            <td>Barishal</td>
                            <td>160</td>
                        </tr>
                        <tr>
                            <td>Rangpur</td>
                            <td>170</td>
                        </tr>
                        <tr>
                            <td>Mymenshing</td>
                            <td>180</td>        
                        </tr>
                        <tr>
                            <td>Gazipur</td>
                            <td>190</td>
                        </tr>
                        <tr>
                            <td>Cox's Bazar</td>
                            <td>200</td>    
                        </tr>
                    </table>
                </div>
                
                <div class="box box-2">
                    <div class="aqi-info-section">

                        
                        <!-- Group Members Section -->
                        <div class="group-members-section">
                            <div class="group-header">
                                <i class="fas fa-users fa-2x"></i>
                                <h3>Project Team Members</h3>
                            </div>
                            <div class="members-grid">
                                <div class="member-card">
                                    <div class="member-photo">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="member-name">RAIHANUL ISLAM</div>
                                    <div class="member-id">ID: 22-46680-1</div>
                                </div>
                                
                                <div class="member-card">
                                    <div class="member-photo">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="member-name">SHAHRIAR REZA</div>
                                    <div class="member-id">ID: 22-46673-1</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="right-section">
                <div class="form-container">
                    <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                    <form action="process.php" method="POST" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="fname"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="fname" name="fname" placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" placeholder="xx-xxxxx-x@student.aiub.edu">
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="password" name="password" placeholder="8 characters (letters, digits, special chars)">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password">
                        </div>
                        <div class="form-group">
                            <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                            <input type="text" id="location" name="location" placeholder="Your current location">
                        </div>
                        <div class="form-group">
                            <label for="zip"><i class="fas fa-map-pin"></i> Zip Code</label>
                            <input type="text" id="zip" name="zip" placeholder="4-digit zip code">
                        </div>
                        <div class="form-group">
                            <label for="city"><i class="fas fa-city"></i> Preferred City</label>
                            <select id="city" name="city">
                                <option value="">Select City</option>
                                <option value="Dhaka">Dhaka</option>
                                <option value="Chittagong">Chittagong</option>
                                <option value="Khulna">Khulna</option>
                                <option value="Rangpur">Rangpur</option>
                                <option value="Rajshahi">Rajshahi</option>
                                <option value="Barishal">Barishal</option>
                                <option value="Comilla">Comilla</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fav_color"><i class="fas fa-palette"></i> Background Color</label>
                            <div class="color-picker-container">
                                <div class="color-input-wrapper">
                                    <div class="color-preview" id="colorPreview" style="background-color: #3498db;">
                                        <input type="color" id="fav_color" name="fav_color" value="#3498db">
                                    </div>
                                    <div class="color-info">
                                        <div class="color-name" id="colorName">Custom Color</div>
                                        <div class="color-hex" id="colorHex">#3498db</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="checkbox-container">
                            <input type="checkbox" id="terms" name="terms">
                            <label for="terms">I agree to the <a href="#">terms and conditions</a></label>
                        </div>
                        <button type="submit"><i class="fas fa-paper-plane"></i> Submit</button>
                    </form>
                </div>
                <!-- Login Box -->
                <div class="form-container login-container">
                    <h2><i class="fas fa-sign-in-alt"></i> Log In</h2>
                    <?php if (!empty($loginError)): ?>
                        <div class="error-message"><?php echo $loginError; ?></div>
                    <?php endif; ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="form-group">
                            <label for="login_email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="login_email" name="login_email" placeholder="Enter your email" value="<?php echo $rememberedEmail; ?>">
                        </div>
                        <div class="form-group">
                            <label for="login_password"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="login_password" name="login_password" placeholder="Enter your password">
                        </div>
                        <div class="checkbox-container">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <button type="submit" name="login"><i class="fas fa-sign-in-alt"></i> Log In</button>
                    </form>
                    <div class="form-footer">
                        <p>Don't have an account? <a href="#" id="show-register">Register Now</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Enhanced color picker functionality
        const colorInput = document.getElementById('fav_color');
        const colorPreview = document.getElementById('colorPreview');
        const colorName = document.getElementById('colorName');
        const colorHex = document.getElementById('colorHex');
        

        
        function updateColorDisplay(color) {
            colorPreview.style.backgroundColor = color;
            colorHex.textContent = color.toUpperCase();
            colorName.textContent = colorNames[color.toLowerCase()] || 'Custom Color';
        }
        
        function setColor(color) {
            colorInput.value = color;
            updateColorDisplay(color);
        }
        
        colorInput.addEventListener('change', function() {
            updateColorDisplay(this.value);
        });
        
        // Initialize with default color
        updateColorDisplay('#3498db');
    </script>
</body>
</html>