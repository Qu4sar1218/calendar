<?php
// File: edit.php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get event ID from URL
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$eventId) {
    redirect('calendar.php');
}

// Get event data
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$eventId, $_SESSION['user_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        redirect('calendar.php');
    }
} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
    redirect('calendar.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    
    $errors = [];
    
    // Validate inputs
    if (empty($title)) {
        $errors[] = 'Event title is required.';
    }
    
    if (empty($event_date)) {
        $errors[] = 'Event date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        $errors[] = 'Invalid date format.';
    }
    
    if (empty($start_time)) {
        $errors[] = 'Start time is required.';
    }
    
    if (!empty($start_time) && !empty($end_time) && $start_time >= $end_time) {
        $errors[] = 'End time must be after start time.';
    }
    
    // Update event if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $event_date, $start_time, $end_time, $eventId, $_SESSION['user_id']]);
            
            $success = 'Event updated successfully!';
            
            // Refresh event data
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
            $stmt->execute([$eventId, $_SESSION['user_id']]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Municipality of Calauan</title>
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
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(128, 0, 128, 0.1);
            overflow: hidden;
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
            font-size: 1.8rem;
            margin: 0;
        }
        
        .subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .title-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
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
            width: 60px;
            height: 60px;
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
            margin-left: 20px;
            padding: 8px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .form-container {
            padding: 40px;
        }
        
        .page-title {
            color: #800080;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #800080;
            padding-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #800080;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #800080;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(128, 0, 128, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .error-messages {
            background-color: #ffe6e6;
            border: 1px solid #ff9999;
            color: #cc0000;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .error-messages ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .error-messages li {
            margin-bottom: 5px;
        }
        
        .success-message {
            background-color: #e6ffe6;
            border: 1px solid #99ff99;
            color: #006600;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
            animation: fadeIn 0.5s ease;
        }
        
        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            min-width: 150px;
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
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .event-info {
            background-color: #f8f4ff;
            border: 1px solid #800080;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .event-info h3 {
            color: #800080;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .event-info .info-item {
            margin-bottom: 8px;
            display: flex;
            gap: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            min-width: 120px;
            white-space: ;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-container {
            animation: fadeIn 0.6s ease;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
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
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .header h1 {
                font-size: 1.4rem;
            }
            
            .subtitle {
                font-size: 0.9rem;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .nav-links a {
                margin: 0 10px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .button-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
            
            .event-info .info-item {
                flex-direction: column;
            }
            
            .info-label {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <!-- Left Logo -->
                <div class="logo">
                    <img src="logo.png" alt="Left Logo" class="logo-img">
                </div>
                
                <!-- Title Section -->
                <div class="title-section">
                    <h1>Municipality of Calauan</h1>
                    <div class="subtitle">Calendar Management System</div>
                </div>
                
                <!-- Right Logo -->
                <div class="logo">
                    <img src="logo1.png" alt="Right Logo" class="logo-img">
                </div>
            </div>
        </div>

        <div class="user-info">
            <div>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
            <div class="nav-links">
                <a href="calendar.php">← Back to Calendar</a>
                <a href="add_event.php">Add Event</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="form-container">
            <h2 class="page-title">Edit Event</h2>
            
            <!-- Current Event Info -->
            <div class="event-info">
                <h3>Current Event Details</h3>
                <div class="info-item">
                    <span class="info-label">Title:</span>
                    <span><?php echo htmlspecialchars($event['title']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date:</span>
                    <span><?php echo date('F j, Y', strtotime($event['event_date'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Time:</span>
                    <span>
                        <?php echo date('g:i A', strtotime($event['start_time'])); ?>
                        <?php if ($event['end_time']): ?>
                            - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($event['description']): ?>
                <div class="info-item">
                    <span class="info-label">Description:</span>
                    <p class="info-label"><?php echo htmlspecialchars($event['description']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <?php if (isset($success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Event Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter event description..."><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="event_date">Event Date <span class="required">*</span></label>
                    <input type="date" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time <span class="required">*</span></label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo $event['start_time']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo $event['end_time'] ?? ''; ?>">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Update Event</button>
                    <a href="calendar.php" class="btn btn-secondary">Cancel</a>
                    <a href="calendar.php?delete_event=<?php echo $event['id']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                       Delete Event
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form validation and enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            
            // Validate end time is after start time
            function validateTimes() {
                if (startTime.value && endTime.value) {
                    if (startTime.value >= endTime.value) {
                        endTime.setCustomValidity('End time must be after start time');
                    } else {
                        endTime.setCustomValidity('');
                    }
                }
            }
            
            startTime.addEventListener('change', validateTimes);
            endTime.addEventListener('change', validateTimes);
            
            // Auto-fill end time (1 hour after start time) if empty
            startTime.addEventListener('change', function() {
                if (this.value && !endTime.value) {
                    const start = new Date('2000-01-01 ' + this.value);
                    start.setHours(start.getHours() + 1);
                    endTime.value = start.toTimeString().substr(0, 5);
                }
            });
            
            // Form submission confirmation
            form.addEventListener('submit', function(e) {
                const confirmed = confirm('Are you sure you want to update this event?');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
            
            // Auto-hide success message after 5 seconds
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>