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
    // Handle delete event
    if (isset($_POST['delete_event'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
            $stmt->execute([$eventId, $_SESSION['user_id']]);
            
            // Redirect to calendar.php after successful deletion
            header('Location: calendar.php?deleted=1');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
    // Handle update event
    else {
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
                
                // Redirect to calendar.php after successful update
                header('Location: calendar.php?updated=1');
                exit;
                
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
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
                    <img src="logos/logo.png" alt="Left Logo" class="logo-img">
                </div>
                
                <!-- Title Section -->
                <div class="title-section">
                    <h1>Municipality of Calauan</h1>
                    <div class="subtitle">Calendar Management System</div>
                </div>
                
                <!-- Right Logo -->
                <div class="logo">
                    <img src="logos/logo3.png" alt="Right Logo" class="logo-img">
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
                    <button type="button" class="btn btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                       Delete Event
                    </button>
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
        });

        // Delete event function
        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                // Create a form to handle the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Submit to current page
                
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_event';
                deleteInput.value = '1';
                
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>