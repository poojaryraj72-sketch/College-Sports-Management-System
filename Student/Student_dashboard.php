<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – EventMS</title>
    <link rel="stylesheet" href="PjCss.css">
    <style>
        .welcome-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, rgba(0,230,118,0.08) 0%, rgba(0,230,118,0.02) 100%);
            border: 1px solid rgba(0,230,118,0.25);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }
        .student-avatar {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--green), #00c853);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .tips-card {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
        }
        .tips-card h4 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 10px;
        }
        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: var(--muted);
        }
        .tip-item:last-child { border-bottom: none; }
        .tip-num {
            width: 22px; height: 22px;
            background: var(--gold-glow);
            color: var(--gold);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 13px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

<div class="topbar" style="max-width:560px;">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Student Portal</span>
</div>

<div class="card" style="max-width:520px;">

    <!-- Welcome -->
    <div class="welcome-bar">
        <div class="student-avatar">&#127891;</div>
        <div>
            <div style="font-family:'Barlow Condensed',sans-serif; font-size:16px; font-weight:700;">Welcome, Student!</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                <?php echo date('l, F j, Y'); ?> &nbsp;&middot;&nbsp; Portal active
            </div>
        </div>
        <span class="badge badge-student" style="margin:0 0 0 auto;">&#9679; Live</span>
    </div>

    <div style="text-align:center; margin-bottom:18px;">
        <span class="badge badge-student">Student</span>
        <h2 class="title">Student <span>Dashboard</span></h2>
        <p class="subtitle">Browse events and upload your certificates</p>
    </div>

    <div class="info-bar">
        &#9679; &nbsp;Session active — all event registrations are open
    </div>

    <div style="text-align:center; margin-bottom:4px;">
        <span class="page-tag">Quick Actions</span>
    </div>

    <div class="nav-grid" style="grid-template-columns:1fr 1fr;">
        <a href="view_events.php" class="nav-card">
            <div class="icon">&#128203;</div>
            <div class="clabel">View Events</div>
            <div class="cdesc">Browse available events &amp; rules</div>
        </a>
        <a href="upload_certificate.php" class="nav-card">
            <div class="icon">&#127942;</div>
            <div class="clabel">Upload Certificate</div>
            <div class="cdesc">Submit your achievement</div>
        </a>
    </div>

    <!-- Tips -->
    <div class="tips-card">
        <h4>&#128204; How It Works</h4>
        <div class="tip-item">
            <span class="tip-num">1</span>
            <span>Browse available events and note your Event ID</span>
        </div>
        <div class="tip-item">
            <span class="tip-num">2</span>
            <span>Participate in the event and collect your certificate</span>
        </div>
        <div class="tip-item">
            <span class="tip-num">3</span>
            <span>Upload your certificate (JPG, PNG, or PDF — max 5MB)</span>
        </div>
    </div>

    <div class="divider"></div>

    <div style="text-align:center;">
        <a href="logout.php" class="btn btn-grey">&#x238B; &nbsp;Logout</a>
    </div>

</div>

</body>
</html>
