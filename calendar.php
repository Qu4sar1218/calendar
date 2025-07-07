<?php
// File: calendar.php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle delete event
if (isset($_GET['delete_event']) && $_GET['delete_event']) {
    $eventId = (int)$_GET['delete_event'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$eventId, $_SESSION['user_id']]);
        $success = 'Event deleted successfully!';
    } catch (PDOException $e) {
        $errors[] = 'Error deleting event: ' . $e->getMessage();
    }
}

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 1900 || $year > 2100) $year = date('Y');

// Get events for current month
$stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? AND MONTH(event_date) = ? AND YEAR(event_date) = ? ORDER BY event_date, start_time");
$stmt->execute([$_SESSION['user_id'], $month, $year]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize events by date
$eventsByDate = [];
foreach ($events as $event) {
    $date = date('j', strtotime($event['event_date']));
    $eventsByDate[$date][] = $event;
}

// Calendar helper functions
function getFirstDayOfMonth($month, $year) {
    return date('w', mktime(0, 0, 0, $month, 1, $year));
}

function getDaysInMonth($month, $year) {
    return date('t', mktime(0, 0, 0, $month, 1, $year));
}

$firstDay = getFirstDayOfMonth($month, $year);
$daysInMonth = getDaysInMonth($month, $year);
$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

// Previous and next month links
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Municipality of Calauan</title>
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
            max-width: 1000px;
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
        
        .month-nav {
            padding: 20px 30px;
            background-color: #f8f4ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #800080;
        }
        
        .month-nav h2 {
            color: #800080;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .nav-btn {
            background-color: #800080;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .nav-btn:hover {
            background-color: #660066;
            transform: translateY(-2px);
        }
        
        .calendar-container {
            padding: 30px;
        }
        
        .calendar {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .calendar th {
            background-color: #800080;
            color: white;
            padding: 15px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .calendar td {
            height: 120px;
            width: 14.28%;
            border: 1px solid #e0e0e0;
            vertical-align: top;
            position: relative;
            background-color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .calendar td:hover {
            background-color: #f8f4ff;
            box-shadow: 0 2px 8px rgba(128, 0, 128, 0.15);
        }
        
        .day-number {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
            padding: 8px;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .event {
            background-color: #800080;
            color: white;
            padding: 4px 8px;
            margin: 30px 5px 2px 5px;
            border-radius: 4px;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            position: relative;
        }
        
        .event:hover {
            background-color: #660066;
            transform: translateX(3px);
        }
        
        .event:nth-child(even) {
            background-color: #9a0f9a;
        }
        
        .event:nth-child(3n) {
            background-color: #b300b3;
        }
        
        .event-actions {
            position: absolute;
            top: -5px;
            right: -5px;
            display: none;
            gap: 2px;
        }
        
        .event:hover .event-actions {
            display: flex;
        }
        
        .event-btn {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 10px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .edit-btn {
            background-color: #28a745;
        }
        
        .delete-btn {
            background-color: #dc3545;
        }
        
        .empty-day {
            background-color: #f8f8f8 !important;
            cursor: default !important;
        }
        
        .empty-day:hover {
            background-color: #f8f8f8 !important;
            box-shadow: none !important;
        }
        
        .today {
            background-color: #fff0ff !important;
            border: 2px solid #800080 !important;
        }
        
        .today .day-number {
            background-color: #800080;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .add-event-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #800080;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(128, 0, 128, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .add-event-btn:hover {
            background-color: #660066;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(128, 0, 128, 0.4);
        }
        
        .success-message {
            background-color: #e6ffe6;
            border: 1px solid #99ff99;
            color: #006600;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 30px;
            text-align: center;
            font-weight: bold;
        }
        
        .error-messages {
            background-color: #ffe6e6;
            border: 1px solid #ff9999;
            color: #cc0000;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 30px;
        }

        /* Tooltip Styles */
        .event-tooltip {
            position: absolute;
            background-color: #333;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            line-height: 1.4;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            min-width: 200px;
            max-width: 300px;
        }
        
        .event-tooltip.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .event-tooltip::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 20px;
            border: 8px solid transparent;
            border-top-color: #333;
        }
        
        .tooltip-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 6px;
            color: #fff;
        }
        
        .tooltip-time {
            color: #ccc;
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        .tooltip-description {
            color: #e0e0e0;
            font-size: 11px;
            line-height: 1.3;
        }
        
        /* Smooth animations */
        .calendar-container {
            opacity: 0;
            transform: translateY(20px);
            animation: slideUp 0.6s ease forwards;
        }
        
        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
        
        .calendar td {
            animation: fadeInCell 0.4s ease forwards;
        }
        
        @keyframes fadeInCell {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
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
            
            .month-nav {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }
            
            .month-nav h2 {
                font-size: 1.3rem;
            }
            
            .calendar-container {
                padding: 20px;
            }
            
            .calendar td {
                height: 80px;
            }
            
            .calendar th {
                padding: 10px 5px;
                font-size: 0.8rem;
            }
            
            .day-number {
                font-size: 0.9rem;
                padding: 5px;
            }
            
            .event {
                font-size: 9px;
                margin: 25px 3px 1px 3px;
                padding: 3px 5px;
            }
            
            .add-event-btn {
                bottom: 20px;
                right: 20px;
                padding: 12px 20px;
                font-size: 0.9rem;
            }

            .event-tooltip {
                font-size: 11px;
                min-width: 180px;
                max-width: 250px;
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
                <a href="add_event.php">Add Event</a>
                <a href="logout.php">Logout</a>
            </div>
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

        <div class="month-nav">
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="nav-btn">
                ← Previous
            </a>
            <h2><?php echo $monthName . ' ' . $year; ?></h2>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="nav-btn">
                Next →
            </a>
        </div>

        <div class="calendar-container">
            <table class="calendar">
                <tr>
                    <th>Sunday</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                    <th>Saturday</th>
                </tr>
                
                <?php
                $dayCount = 1;
                $today = date('j');
                $currentMonth = date('n');
                $currentYear = date('Y');
                
                for ($week = 0; $week < 6; $week++) {
                    if ($dayCount > $daysInMonth) break;
                    echo "<tr>";
                    
                    for ($day = 0; $day < 7; $day++) {
                        if ($week == 0 && $day < $firstDay) {
                            echo "<td class='empty-day'></td>";
                        } elseif ($dayCount <= $daysInMonth) {
                            $isToday = ($dayCount == $today && $month == $currentMonth && $year == $currentYear);
                            $class = $isToday ? 'today' : '';
                            
                            echo "<td class='$class' onclick='selectDate($dayCount, $month, $year)'>";
                            echo "<div class='day-number'>$dayCount</div>";
                            
                            // Display events for this day
                            if (isset($eventsByDate[$dayCount])) {
                                foreach ($eventsByDate[$dayCount] as $event) {
                                    $startTime = date('h:i A', strtotime($event['start_time']));
                                    $endTime = isset($event['end_time']) && $event['end_time'] ? date('h:i A', strtotime($event['end_time'])) : '';
                                    $timeDisplay = $endTime ? $startTime . ' - ' . $endTime : $startTime;
                                    
                                    $eventTitle = htmlspecialchars($event['title']);
                                    $eventDescription = htmlspecialchars($event['description'] ?? '');
                                    $eventDate = date('F j, Y', strtotime($event['event_date']));
                                    
                                    echo "<div class='event' 
                                            data-title='" . $eventTitle . "' 
                                            data-time='" . $timeDisplay . "' 
                                            data-description='" . $eventDescription . "' 
                                            data-date='" . $eventDate . "'
                                            onclick='viewEvent(" . $event['id'] . ", event)'>";
                                    echo $startTime . ' - ' . $eventTitle;
                                    echo "<div class='event-actions'>";
                                    echo "<button class='event-btn edit-btn' onclick='editEvent(" . $event['id'] . ", event)' title='Edit'>✎</button>";
                                    echo "<button class='event-btn delete-btn' onclick='deleteEvent(" . $event['id'] . ", event)' title='Delete'>✕</button>";
                                    echo "</div>";
                                    echo "</div>";
                                }
                            }
                            
                            echo "</td>";
                            $dayCount++;
                        } else {
                            echo "<td class='empty-day'></td>";
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <a href="add_event.php" class="add-event-btn">+ Add Event</a>

    <!-- Tooltip element -->
    <div class="event-tooltip" id="eventTooltip">
        <div class="tooltip-title"></div>
        <div class="tooltip-time"></div>
        <div class="tooltip-description"></div>
        
    </div>

    <script>
        // Tooltip functionality
        const tooltip = document.getElementById('eventTooltip');
        let tooltipTimeout;

        // Simple animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate calendar cells on load
            const cells = document.querySelectorAll('.calendar td:not(.empty-day)');
            cells.forEach((cell, index) => {
                cell.style.animationDelay = (index * 0.02) + 's';
            });

            // Animate events
            const events = document.querySelectorAll('.event');
            events.forEach((event, index) => {
                event.style.animationDelay = (index * 0.1) + 's';
            });

            // Add event listeners for tooltips
            events.forEach(event => {
                event.addEventListener('mouseenter', showTooltip);
                event.addEventListener('mouseleave', hideTooltip);
                event.addEventListener('mousemove', moveTooltip);
            });

            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    const prevBtn = document.querySelector('.nav-btn');
                    if (prevBtn) prevBtn.click();
                } else if (e.key === 'ArrowRight') {
                    const nextBtn = document.querySelectorAll('.nav-btn')[1];
                    if (nextBtn) nextBtn.click();
                }
            });
        });

        function showTooltip(e) {
            clearTimeout(tooltipTimeout);
            
            const event = e.target;
            const title = event.getAttribute('data-title');
            const time = event.getAttribute('data-time');
            const description = event.getAttribute('data-description');
            const date = event.getAttribute('data-date');

            // Populate tooltip content
            tooltip.querySelector('.tooltip-title').textContent = title;
            tooltip.querySelector('.tooltip-time').textContent = `${date} • ${time}`;
            tooltip.querySelector('.tooltip-description').textContent = description || 'No description available';

            // Position and show tooltip
            moveTooltip(e);
            tooltip.classList.add('show');
        }

        function hideTooltip() {
            tooltipTimeout = setTimeout(() => {
                tooltip.classList.remove('show');
            }, 100);
        }

        function moveTooltip(e) {
            const tooltipRect = tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            let left = e.pageX + 15;
            let top = e.pageY - tooltipRect.height - 15;

            // Adjust position if tooltip goes off-screen
            if (left + tooltipRect.width > viewportWidth) {
                left = e.pageX - tooltipRect.width - 15;
            }
            
            if (top < 0) {
                top = e.pageY + 15;
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }

        function selectDate(day, month, year) {
            // Optional: Add functionality for selecting a date
            console.log('Selected date:', day, month, year);
        }

        function viewEvent(eventId, event) {
            event.stopPropagation();
            // Optional: Add functionality to view event details
            console.log('View event:', eventId);
        }

        // Redirect to edit.php when edit button is clicked
        function editEvent(eventId, event) {
            event.stopPropagation();
            // Redirect to edit.php with the event ID
            window.location.href = 'edit.php?id=' + eventId;
        }

        function deleteEvent(eventId, event) {
            event.stopPropagation();
            if (confirm('Are you sure you want to delete this event?')) {
                window.location.href = '?delete_event=' + eventId;  
            }
        }

        // Hide tooltip when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('event')) {
                tooltip.classList.remove('show');
            }
        });
    </script>
</body>
</html>