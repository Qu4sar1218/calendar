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

        // Redirect to remove the GET parameter and refresh data
        header("Location: calendar.php?deleted=1");
        exit;
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
    <link rel="stylesheet" href="style.css">
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
            <div class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
            <div class="nav-links">
                <a href="add_event.php" class="nav-link">Add Event</a>
                <a href="logout.php" class="nav-link">Logout</a>
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

    <!-- Fixed Add Event Button -->
    <div class="add-event-btn-container">
        <a href="add_event.php" class="add-event-btn">+ Add Event</a> 
    </div>

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