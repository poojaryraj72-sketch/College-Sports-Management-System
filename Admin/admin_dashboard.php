<?php
session_start();
include "db.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}
$event_count   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM event"));
$student_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM student"));
$cert_count    = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM certificate"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – EventMS</title>
    <link rel="stylesheet" href="PjCss.css">
    <style>
        .welcome-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, rgba(245,197,24,0.10) 0%, rgba(245,197,24,0.03) 100%);
            border: 1px solid var(--border2);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }
        .welcome-avatar {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dim));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 20px;
            color: #0d1b2e;
            flex-shrink: 0;
        }
        .module-section { margin-top: 4px; }
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .section-header hr {
            flex: 1;
            border: none;
            border-top: 1px solid var(--border);
        }
        .quick-info {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        .qi-item {
            flex: 1;
            min-width: 130px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--muted);
        }
        .qi-item b { color: var(--text); display: block; font-size: 12px; margin-bottom: 3px; font-family:'Barlow Condensed',sans-serif; letter-spacing:1px; text-transform:uppercase; }
    </style>
</head>
<body>

<div class="topbar" style="max-width:860px;">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Admin Dashboard</span>
</div>

<div class="card wide">

    <!-- Welcome Bar -->
    <div class="welcome-bar">
        <div class="welcome-avatar">A</div>
        <div>
            <div style="font-family:'Barlow Condensed',sans-serif; font-size:16px; font-weight:700;">Welcome back, Administrator</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                <?php echo date('l, F j, Y'); ?> &nbsp;&middot;&nbsp; Full access mode
            </div>
        </div>
        <span class="badge badge-admin" style="margin:0 0 0 auto;">&#9679; Online</span>
    </div>

    <!-- Stats -->
    <div style="text-align:center; margin-bottom:20px;">
        <span class="badge badge-admin">Administrator</span>
        <h2 class="title">Admin <span>Dashboard</span></h2>
        <p class="subtitle">Manage events, students, and participation reports</p>
    </div>

    <div class="stat-row">
        <div class="stat-box">
            <div class="num" id="cnt-events">0</div>
            <div class="lbl">&#127919; Events</div>
        </div>
        <div class="stat-box">
            <div class="num" id="cnt-students">0</div>
            <div class="lbl">&#127891; Students</div>
        </div>
        <div class="stat-box">
            <div class="num" id="cnt-certs">0</div>
            <div class="lbl">&#127942; Certificates</div>
        </div>
        <div class="stat-box">
            <div class="num">3</div>
            <div class="lbl">&#128202; Modules</div>
        </div>
    </div>

    <!-- Modules -->
    <div class="module-section">
        <div class="section-header">
            <span class="page-tag">Modules</span>
            <hr>
        </div>
        <div class="nav-grid">
            <a href="event_entry.php" class="nav-card">
                <div class="icon">&#127919;</div>
                <div class="clabel">Event Entry</div>
                <div class="cdesc">Add, edit &amp; remove events</div>
            </a>
            <a href="student_entry.php" class="nav-card">
                <div class="icon">&#127891;</div>
                <div class="clabel">Student Entry</div>
                <div class="cdesc">Manage participants</div>
            </a>
            <a href="reports.php" class="nav-card">
                <div class="icon">&#128202;</div>
                <div class="clabel">Reports</div>
                <div class="cdesc">View participation data</div>
            </a>
        </div>
    </div>

    <!-- Quick Info -->
    <div class="quick-info">
        <div class="qi-item">
            <b>&#128197; System Date</b>
            <?php echo date('d M Y'); ?>
        </div>
        <div class="qi-item">
            <b>&#128336; Session</b>
            Active &amp; Secure
        </div>
        <div class="qi-item">
            <b>&#127760; Role</b>
            Administrator
        </div>
    </div>

    <div class="divider"></div>

    <div style="text-align:center;">
        <a href="logout.php" class="btn btn-grey">&#x238B; &nbsp;Logout</a>
    </div>

</div>

<script>
// Animated counters
function animateCount(elId, target) {
    var el = document.getElementById(elId);
    var start = 0;
    var step = Math.ceil(target / 30);
    var timer = setInterval(function() {
        start += step;
        if (start >= target) { start = target; clearInterval(timer); }
        el.textContent = start;
    }, 30);
}
window.addEventListener('DOMContentLoaded', function() {
    animateCount('cnt-events',   <?php echo (int)$event_count; ?>);
    animateCount('cnt-students', <?php echo (int)$student_count; ?>);
    animateCount('cnt-certs',    <?php echo (int)$cert_count; ?>);
});
</script>

</body>
</html>
