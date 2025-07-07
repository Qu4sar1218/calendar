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
            
            $success = 'Event added successfully!';
            // Clear form
            $_POST = [];
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(128, 0, 128, 0.1);
            overflow: hidden;
            animation: slideUp 0.6s ease forwards;
        }
        
        .header {
            background-color: #800080;
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header-content {
            text-align: center;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }
        
        .subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .title-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        .logo:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        
        .logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        
        .user-info {
            background-color: #9a0f9a;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 8px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-title {
            color: #800080;
            font-size: 1.3rem;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            font-size: 0.95rem;
        }
        
        input[type="text"], 
        input[type="date"], 
        input[type="time"], 
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }
        
        input[type="text"]:focus, 
        input[type="date"]:focus, 
        input[type="time"]:focus, 
        textarea:focus {
            outline: none;
            border-color: #800080;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(128, 0, 128, 0.1);
        }
        
        textarea {
            height: 100px;
            resize: vertical;
            font-family: inherit;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #800080;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #660066;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(128, 0, 128, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d1edff;
            border: 1px solid #b8daff;
            color: #0c5460;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .container {
                margin: 10px;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .title-section {
                order: 1;
            }
            
            .logo {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .header h1 {
                font-size: 1.3rem;
            }
            
            .subtitle {
                font-size: 0.8rem;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .nav-links a {
                margin: 0 5px;
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .form-title {
                font-size: 1.1rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                padding: 12px;
                font-size: 0.95rem;
            }
        }
        
        /* Custom date and time input styling */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(0.6);
            cursor: pointer;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover {
            filter: invert(0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <!-- Left Logo -->
                <div class="logo">
                    <img src="logo.png" alt="Municipality Logo">
                </div>
                
                <!-- Title Section -->
                <div class="title-section">
                    <h1>Municipality of Calauan</h1>
                    <div class="subtitle">Calendar Management System</div>
                </div>
                
                <!-- Right Logo -->
                <div class="logo">
                    <img src="logo1.png" alt="Secondary Logo">
                </div>
            </div>
        </div>

        <div class="user-info">
            <div>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
            <div class="nav-links">
                <a href="calendar.php">← Back to Calendar</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="form-container">
            <h2 class="form-title">Add New Event</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required placeholder="Enter event title">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" placeholder="Enter event description (optional)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" name="event_date" id="event_date" value="<?php echo htmlspecialchars($_POST['event_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
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