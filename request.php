<?php
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
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

// Process city selection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_cities'])) {
    $selectedCities = $_POST['selected_cities'];
    
    // Escape each city
    $escapedCities = array_map(function($city) use ($conn) {
        return $conn->real_escape_string($city);
    }, $selectedCities);
    
    if (count($escapedCities) <= 10) {
        $_SESSION['selected_cities'] = $escapedCities;
        header("Location: show.php");
        exit();
    } else {
        $error = "Please select exactly 10 cities.";
    }
}

// Get all cities
$sql = "SELECT City FROM cities"; // Removed AQI from the query
$result = $conn->query($sql);
$allCities = [];
while ($row = $result->fetch_assoc()) {
    $allCities[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Quality Request</title>
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
        }

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
            padding-top: 0;
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

        /* Rest of your existing styles for the main content */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
        }

        .city-selection-box {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 600px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .city-selection-box h2 {
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .highlight {
            color: var(--primary-color);
            font-weight: 600;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message {
            background-color: #fee;
            color: var(--danger-color);
            border: 1px solid #fcc;
        }

        .city-search {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .city-search i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .city-search input {
            width: 100%;
            padding: 12px 45px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .city-search input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .selection-counter {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .city-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            max-height: 400px;
            overflow-y: auto;
            padding: 1rem;
            border: 2px solid #f8f9fa;
            border-radius: 8px;
        }

        .city-item {
            display: flex;
            align-items: center;
        }

        .city-item input[type="checkbox"] {
            display: none;
        }

        .city-item label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            background: white;
        }

        .city-item input[type="checkbox"]:checked + label {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .btn-save {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-save:hover {
            background: var(--primary-dark);
        }

    </style>
</head>
<body>
    <div class="main-container">
        <header>
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
                            <a href="logout.php" class="action-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <img src="images.png" alt="Air Quality Banner" class="top-banner">
            <h1><i class="fas fa-wind"></i> Air Quality Monitoring Dashboard</h1>
        </header>

        <div class="container">
            <div class="city-selection-box">
                <h2><i class="fas fa-map-marked-alt"></i> City Selection</h2>
                <p class="subtitle">Select exactly <span class="highlight">10 cities</span> to monitor their air quality</p>
                
                <?php if (isset($error)): ?>
                    <div class="message error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form action="request.php" method="POST">
                    <div class="city-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="city-search" placeholder="Search cities...">
                    </div>
                    
                    <div class="selection-counter">
                        <span id="selected-count">0</span>/10 cities selected
                    </div>
                    
                    <div class="city-grid">
                        <?php foreach ($allCities as $city): ?>
                            <div class="city-item">
                                <input type="checkbox" 
                                       id="city_<?php echo htmlspecialchars(strtolower(str_replace(' ', '_', $city['City']))); ?>" 
                                       name="selected_cities[]" 
                                       value="<?php echo htmlspecialchars($city['City']); ?>">
                                <label for="city_<?php echo htmlspecialchars(strtolower(str_replace(' ', '_', $city['City']))); ?>">
                                    <i class="fas fa-city"></i>
                                    <span><?php echo htmlspecialchars($city['City']); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Selection
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Profile dropdown toggle
        function toggleProfile() {
            console.log('Profile button clicked'); 
            var dropdown = document.getElementById('profileDropdown');
            
            if (dropdown) {
                dropdown.classList.toggle('show');
                console.log('Dropdown classes:', dropdown.className); 
                
                // Close other open dropdowns
                var allDropdowns = document.querySelectorAll('.profile-dropdown-content');
                allDropdowns.forEach(function(item) {
                    if (item !== dropdown && item.classList.contains('show')) {
                        item.classList.remove('show');
                    }
                });
            } else {
                console.error('Profile dropdown not found');
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

        // City selection counter
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected_cities[]"]');
            const counter = document.getElementById('selected-count');
            const maxSelections = 10;
            
            function updateCounter() {
                const checkedCount = document.querySelectorAll('input[type="checkbox"][name="selected_cities[]"]:checked').length;
                counter.textContent = checkedCount;
                
                if (checkedCount > maxSelections) {
                    counter.classList.add('error');
                } else {
                    counter.classList.remove('error');
                }
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedCount = document.querySelectorAll('input[type="checkbox"][name="selected_cities[]"]:checked').length;
                    
                    if (checkedCount > maxSelections) {
                        this.checked = false;
                    }
                    updateCounter();
                });
            });
            
            // Initialize counter
            updateCounter();
            
            // City search functionality
            const searchInput = document.getElementById('city-search');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cityItems = document.querySelectorAll('.city-item');
                
                cityItems.forEach(item => {
                    const cityName = item.querySelector('span').textContent.toLowerCase();
                    if (cityName.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>