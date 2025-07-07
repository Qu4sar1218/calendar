<?php
// File: add_event.php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    if (empty($title) || empty($event_date) || empty($start_time) || empty($end_time)) {
        $error = 'Title, date, start time, and end time are required';
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        $error = 'End time must be after start time';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (user_id, title, description, event_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $event_date, $start_time, $end_time]);
            
            // Redirect to calendar.php after successful event addition
            header('Location: calendar.php?added=1');
            exit;
            
        } catch(PDOException $e) {
            $error = 'Failed to add event. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Municipality of Calauan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for logout button */
        .nav-links a[href="logout.php"] {
            background-color: #dc3545;
            color: white !important;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .nav-links a[href="logout.php"]:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <!-- Left Logo -->
                <div class="logo">
                    <img src="logos/logo.png" alt="Municipality Logo">
                </div>
                
                <!-- Title Section -->
                <div class="title-section">
                    <h1>Municipality of Calauan</h1>
                    <div class="subtitle">Calendar Management System</div>
                </div>
                
                <!-- Right Logo -->
                <div class="logo">
                    <img src="logos/logo3.png" alt="Secondary Logo">
                </div>
            </div>
        </div>

        <div class="user-info">
            <div>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
            <div class="nav-links">
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="form-container">
            <h2 class="form-title">Add New Event</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text"  name="title" id="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required placeholder="Enter event title">
                </div>
                
                <div class="form-group">
                    <label for="description">Description </label>
                    <textarea name="description" id="description" placeholder="Enter event description (optional)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date *</label>
                    <input type="date" name="event_date" id="event_date" value="<?php echo htmlspecialchars($_POST['event_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        <input type="time" name="start_time" id="start_time" value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time *</label>
                        <input type="time" name="end_time" id="end_time" value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Add Event</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='calendar.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-focus on title field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('title').focus();
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime && endTime && startTime >= endTime) {
                e.preventDefault();
                alert('End time must be after start time');
                return false;
            }
        });

        // Auto-set end time when start time is selected
        document.getElementById('start_time').addEventListener('change', function() {
            const startTime = this.value;
            const endTimeInput = document.getElementById('end_time');
            
            if (startTime && !endTimeInput.value) {
                const start = new Date('2000-01-01 ' + startTime);
                start.setHours(start.getHours() + 1);
                const endTime = start.toTimeString().slice(0, 5);
                endTimeInput.value = endTime;
            }
        });
    </script>
</body>
</html>