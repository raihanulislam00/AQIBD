// Form validation function
function validateForm() {
    // Get form field values
    var fname = document.getElementById("fname").value.trim();
    var email = document.getElementById("email").value.trim();
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    var location = document.getElementById("location").value.trim();
    var zip = document.getElementById("zip").value.trim();
    var city = document.getElementById("city").value;
    var terms = document.getElementById("terms").checked;

    // Regular expression patterns
    const namePattern = /^[A-Za-z\s\.]+$/;
    const zipPattern = /^\d{4}$/;
    const mailpattern = /^\d{2}-\d{5}-[1-3]@student\.aiub\.edu$/;

    // Validation for each field with improved error messages
    if (!namePattern.test(fname)) {
        showError("Full Name must contain only letters, spaces, and periods.");
        highlightField("fname");
        return false;
    }
    
    if (!mailpattern.test(email)) {
        showError("Email must follow the format: xx-xxxxx-x@student.aiub.edu");
        highlightField("email");
        return false;
    }
    
    // Updated password validation (exactly 8 characters: letters, digits, and special characters allowed)
    if (password.length <= 8) {
        showError("Password must be exactly 8 characters (letters, digits, and special characters allowed).");
        highlightField("password");
        return false;
    }
    
    if (confirmPassword === "" || password !== confirmPassword) {
        showError("Passwords do not match.");
        highlightField("confirm_password");
        return false;
    }
    
    if (location === "") {
        showError("Please enter your location.");
        highlightField("location");
        return false;
    }
    
    if (!zipPattern.test(zip)) {
        showError("Zip Code must be exactly 4 digits.");
        highlightField("zip");
        return false;
    }
    
    if (city === "") {
        showError("Please select a preferred city.");
        highlightField("city");
        return false;
    }
    
    if (!terms) {
        showError("You must agree to the terms and conditions.");
        highlightField("terms");
        return false;
    }

    // If all validations pass
    return true;
}

// Helper function to show error messages in a more user-friendly way
function showError(message) {
    // Remove any existing error message
    const existingError = document.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.backgroundColor = '#ffebee';
    errorDiv.style.color = '#e53935';
    errorDiv.style.padding = '10px 15px';
    errorDiv.style.borderRadius = '5px';
    errorDiv.style.marginBottom = '15px';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.display = 'flex';
    errorDiv.style.alignItems = 'center';
    errorDiv.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
    
    // Add error icon
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle" style="margin-right: 10px; font-size: 1.2rem;"></i>' + message;
    
    // Add to the form
    const form = document.querySelector('form');
    form.insertBefore(errorDiv, form.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        errorDiv.style.opacity = '0';
        errorDiv.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 500);
    }, 5000);
}

// Helper function to highlight invalid fields
function highlightField(fieldId) {
    const field = document.getElementById(fieldId);
    field.style.borderColor = '#e53935';
    field.style.boxShadow = '0 0 0 3px rgba(229, 57, 53, 0.2)';
    
    // Add event listener to remove highlighting when user starts typing
    field.addEventListener('input', function() {
        this.style.borderColor = '';
        this.style.boxShadow = '';
    }, { once: true });
}

// Add AQI color indicators dynamically when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Color code the table rows based on AQI values
    const rows = document.querySelectorAll('table tr:not(:first-child)');
    
    rows.forEach(row => {
        const aqiCell = row.cells[1];
        const aqiValue = parseInt(aqiCell.textContent);
        
        // Apply different classes based on AQI value
        if (aqiValue <= 50) {
            aqiCell.style.backgroundColor = '#a8e05f'; // Good
            aqiCell.style.color = '#33691e';
        } else if (aqiValue <= 100) {
            aqiCell.style.backgroundColor = '#fdd835'; // Moderate
            aqiCell.style.color = '#5d4037';
        } else if (aqiValue <= 150) {
            aqiCell.style.backgroundColor = '#ffb74d'; // Unhealthy for Sensitive Groups
            aqiCell.style.color = '#5d4037';
        } else if (aqiValue <= 200) {
            aqiCell.style.backgroundColor = '#ff8a65'; // Unhealthy
            aqiCell.style.color = '#ffffff';
        } else if (aqiValue <= 300) {
            aqiCell.style.backgroundColor = '#e57373'; // Very Unhealthy
            aqiCell.style.color = '#ffffff';
        } else {
            aqiCell.style.backgroundColor = '#b71c1c'; // Hazardous
            aqiCell.style.color = '#ffffff';
        }
    });
    
    // Add input field animations
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});