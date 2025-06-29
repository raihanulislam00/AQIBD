<?php
session_start();
$fav_color = $_COOKIE['fav_color'] ?? '#3498db';
// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Redirect if no cities selected
if (!isset($_SESSION['selected_cities'])) {
    header("Location: request.php");
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'info';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Escape city names
$escapedCities = array_map(function($city) use ($conn) {
    return $conn->real_escape_string($city);
}, $_SESSION['selected_cities']);

// Create IN clause
$cityList = "'" . implode("','", $escapedCities) . "'";

// Get selected cities data
$sql = "SELECT City, Country, AQI FROM cities WHERE City IN ($cityList)";
$result = $conn->query($sql);
$selectedCitiesData = [];
while ($row = $result->fetch_assoc()) {
    $selectedCitiesData[] = $row;
}

// Prepare AQI data
$aqiValues = [];
foreach ($selectedCitiesData as $city) {
    $aqi = $city['AQI'];
    $color = '#4CAF50'; // Good
    $status = 'Good';
    
    if ($aqi > 150) {
        $color = '#e57373';
        $status = 'Unhealthy';
    } elseif ($aqi > 100) {
        $color = '#ffb74d';
        $status = 'Unhealthy for Sensitive Groups';
    } elseif ($aqi > 50) {
        $color = '#FFEB3B';
        $status = 'Moderate';
    }
    
    $aqiValues[$city['City']] = [
        'value' => $aqi,
        'color' => $color,
        'status' => $status
    ];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Cities Air Quality</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --accent-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --text-dark: #2c3e50;
            --text-light: #ecf0f1;
            --background-light: #f8f9fa;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            --primary-color: <?php echo $fav_color; ?>;
            --primary-dark: <?php echo adjustBrightness($fav_color, -20); ?>;

        }
         <?php
        function adjustBrightness($hex, $steps) {
            $steps = max(-255, min(255, $steps));
            $hex = str_replace('#', '', $hex);
            
            if (strlen($hex) == 3) {
                $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
            }
            
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
            
            $r = max(0, min(255, $r + $steps));
            $g = max(0, min(255, $g + $steps));
            $b = max(0, min(255, $b + $steps));
            
            $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
            $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
            $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
            
            return '#'.$r_hex.$g_hex.$b_hex;
        }
        ?>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--text-dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            text-align: center;
            position: relative;
            overflow: visible;
            min-height: 160px;
        }

        header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .top-banner {
            max-width: 100%;
            height: auto;
            max-height: 80px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }

        .back-btn {
            position: absolute;
            left: 30px;
            top: 30px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 8px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            z-index: 10;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Profile Dropdown Styles */
        .profile-corner {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 9998;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5rem;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            outline: none;
        }

        .profile-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 5px);
            background-color: white;
            min-width: 320px;
            max-width: 400px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 0;
            z-index: 9999;
            animation: fadeIn 0.2s ease-out;
            border: 1px solid #eee;
            overflow: hidden;
            max-height: 500px;
            overflow-y: auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-dropdown-content.show {
            display: block;
        }

        .profile-header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .profile-header i {
            margin-bottom: 10px;
            font-size: 3rem;
        }

        .profile-header h3 {
            margin: 10px 0 5px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .profile-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .profile-details {
            padding: 20px;
            background: #f8f9fb;
        }

        .profile-details h4 {
            color: var(--text-dark);
            font-size: 1rem;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-details p {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            color: #555;
            font-size: 0.95rem;
            padding: 8px 0;
        }

        .profile-details i {
            width: 20px;
            text-align: center;
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .profile-actions {
            padding: 15px 20px 20px;
            background: white;
            border-top: 1px solid #f0f0f0;
        }

        .profile-actions h4 {
            color: var(--text-dark);
            font-size: 1rem;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: #555;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            margin-bottom: 8px;
            border: 1px solid transparent;
        }

        .action-item:hover {
            background-color: #f5f7fa;
            border-color: #e9ecef;
            transform: translateX(2px);
        }

        .action-item i {
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .action-item.edit-profile {
            color: var(--primary-color);
        }

        .action-item.edit-profile:hover {
            background-color: rgba(52, 152, 219, 0.1);
            border-color: rgba(52, 152, 219, 0.2);
        }

        .action-item.settings {
            color: #6c757d;
        }

        .action-item.settings:hover {
            background-color: rgba(108, 117, 125, 0.1);
            border-color: rgba(108, 117, 125, 0.2);
        }

        .action-item.logout {
            color: var(--danger-color);
            margin-top: 8px;
            border-top: 1px solid #f0f0f0;
            padding-top: 15px;
        }

        .action-item.logout:hover {
            background-color: rgba(231, 76, 60, 0.1);
            border-color: rgba(231, 76, 60, 0.2);
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
        }

        .city-results-box {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 800px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .city-results-box:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }

        .city-results-box h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .city-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .city-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s;
            border-left: 4px solid var(--primary-color);
        }

        .city-card:hover {
            background: #f0f0f0;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .city-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .city-country {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 10px;
        }

        .city-aqi {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .aqi-value {
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
            color: white;
            font-size: 0.9rem;
        }

        .aqi-status {
            font-size: 0.85rem;
            color: #555;
        }

        @media (max-width: 768px) {
            .city-grid {
                grid-template-columns: 1fr;
            }

            .city-results-box {
                padding: 1.5rem;
            }

            header h1 {
                font-size: 1.5rem;
                padding-top: 10px;
            }

            .back-btn {
                position: relative;
                left: 0;
                top: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <header>
            <a href="request.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            
            <div class="profile-corner">
                <div class="profile-dropdown">
                    <button class="profile-btn" onclick="toggleProfile()">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <div class="profile-dropdown-content" id="profileDropdown">
                        <!-- Profile Header -->
                        <div class="profile-header">
                            <i class="fas fa-user-circle"></i>
                            <h3><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?></h3>
                        </div>
                        
                        <!-- Profile Details -->
                        <div class="profile-details">
                            <h4><i class="fas fa-info-circle"></i> Account Details</h4>
                            <p><i class="fas fa-envelope"></i> <?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'No email provided'; ?></p>
                        </div>
                        
                        <!-- Profile Actions -->
                        <div class="profile-actions">
                            <h4><i class="fas fa-cog"></i> Actions</h4>                          
                            <a href="logout.php" class="action-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <img src="images.png" alt="Air Quality Banner" class="top-banner">
            <h1><i class="fas fa-wind"></i> Selected Cities Air Quality</h1>
        </header>

        <div class="container">
            <div class="city-results-box">
                <h2><i class="fas fa-map-marked-alt"></i> Your Selected Cities</h2>
                
                <div class="city-grid">
                    <?php foreach ($selectedCitiesData as $city): ?>
                        <div class="city-card">
                            <div class="city-name">
                                <i class="fas fa-city"></i> <?php echo htmlspecialchars($city['City']); ?>
                            </div>
                            <div class="city-country">
                                <i class="fas fa-globe-asia"></i> <?php echo htmlspecialchars($city['Country']); ?>
                            </div>
                            <div class="city-aqi">
                                <span class="aqi-status">AQI: <?php echo $aqiValues[$city['City']]['status']; ?></span>
                                <span class="aqi-value" style="background-color: <?php echo $aqiValues[$city['City']]['color']; ?>">
                                    <?php echo $city['AQI']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile dropdown toggle
        function toggleProfile() {
            var dropdown = document.getElementById('profileDropdown');
            
            if (dropdown) {
                dropdown.classList.toggle('show');
                
                // Close other open dropdowns
                var allDropdowns = document.querySelectorAll('.profile-dropdown-content');
                allDropdowns.forEach(function(item) {
                    if (item !== dropdown && item.classList.contains('show')) {
                        item.classList.remove('show');
                    }
                });
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.profile-corner')) {
                var dropdowns = document.querySelectorAll('.profile-dropdown-content');
                dropdowns.forEach(function(dropdown) {
                    dropdown.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>