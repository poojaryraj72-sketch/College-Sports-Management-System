<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$message  = "";
$msg_type = "";
$event    = null;

// ADD
if (isset($_POST['add'])) {
    $eid   = trim($_POST['eid']);
    $ename = trim($_POST['ename']);
    $rules = trim($_POST['rules']);

    if (empty($eid) || empty($ename) || empty($rules)) {
        $message  = "All fields are required.";
        $msg_type = "err";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM event WHERE Event_id = '$eid'");
        if (mysqli_num_rows($check) > 0) {
            $message  = "Event ID already exists.";
            $msg_type = "err";
        } else {
            mysqli_query($conn, "INSERT INTO event (Event_id, Event_name, Rules)
                                 VALUES ('$eid', '$ename', '$rules')");
            $message  = "Event added successfully!";
            $msg_type = "ok";
        }
    }
}

// SEARCH BEFORE UPDATE
if (isset($_POST['search_event'])) {
    $eid = trim($_POST['eid']);
    $res = mysqli_query($conn, "SELECT * FROM event WHERE Event_id = '$eid'");
    if (mysqli_num_rows($res) > 0) {
        $event = mysqli_fetch_assoc($res);
    } else {
        $message  = "Event not found.";
        $msg_type = "err";
    }
}

// UPDATE
if (isset($_POST['update'])) {
    $eid   = trim($_POST['eid']);
    $ename = trim($_POST['ename']);
    $rules = trim($_POST['rules']);

    if (empty($eid) || empty($ename) || empty($rules)) {
        $message  = "All fields are required.";
        $msg_type = "err";
    } else {
        mysqli_query($conn, "UPDATE event SET Event_name = '$ename', Rules = '$rules' WHERE Event_id = '$eid'");
        if (mysqli_affected_rows($conn) > 0) {
            $message  = "Event updated successfully!";
            $msg_type = "ok";
        } else {
            $message  = "No changes made.";
            $msg_type = "err";
        }
    }
}

// DELETE
if (isset($_POST['delete'])) {
    $eid = trim($_POST['eid']);
    if (empty($eid)) {
        $message  = "Event ID is required.";
        $msg_type = "err";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM event WHERE Event_id = '$eid'");
        if (mysqli_num_rows($check) == 0) {
            $message  = "Event not found.";
            $msg_type = "err";
        } else {
            mysqli_query($conn, "DELETE FROM event WHERE Event_id = '$eid'");
            $message  = "Event deleted successfully!";
            $msg_type = "ok";
        }
    }
}

// Load all events for preview table
$all_events = mysqli_query($conn, "SELECT * FROM event ORDER BY Event_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Entry – EventMS</title>
    <link rel="stylesheet" href="PjCss.css">
    <style>
        .confirm-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .confirm-overlay.active { display: flex; }
        .confirm-box {
            background: var(--card-bg);
            border: 1px solid var(--border2);
            border-radius: var(--radius);
            padding: 28px 24px;
            max-width: 360px;
            width: 90%;
            text-align: center;
            box-shadow: var(--shadow);
            animation: fadeSlide 0.3s ease;
        }
        .confirm-box .icon { font-size: 42px; margin-bottom: 12px; }
        .confirm-box h3 { font-family:'Bebas Neue',sans-serif; font-size:22px; letter-spacing:1px; margin-bottom: 8px; }
        .confirm-box p  { font-size: 13px; color: var(--muted); margin-bottom: 20px; }
        .confirm-btns { display: flex; gap: 10px; }
        .preview-section { margin-top: 28px; }
        .preview-section h4 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px; letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 10px;
        }
        .event-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 6px;
            transition: border-color 0.2s;
        }
        .event-row:hover { border-color: var(--border2); }
        .event-id-badge {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px; letter-spacing: 1px;
            background: var(--gold-glow);
            color: var(--gold);
            border: 1px solid var(--border2);
            padding: 2px 8px;
            border-radius: 5px;
            flex-shrink: 0;
        }
        .event-name { font-weight: 600; font-size: 14px; flex: 1; }
        .use-btn {
            background: none; border: 1px solid var(--border);
            color: var(--muted); font-size: 11px;
            padding: 3px 8px; border-radius: 5px;
            cursor: pointer; font-family: 'Barlow Condensed', sans-serif;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .use-btn:hover { border-color: var(--gold-dim); color: var(--gold); }
    </style>
</head>
<body>

<div class="topbar" style="max-width:560px;">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Admin &middot; Event Entry</span>
</div>

<!-- Delete Confirmation Modal -->
<div class="confirm-overlay" id="deleteModal">
    <div class="confirm-box">
        <div class="icon">&#9888;</div>
        <h3>Delete Event?</h3>
        <p>This action cannot be undone. All related student participation records may be affected.</p>
        <div class="confirm-btns">
            <button class="btn btn-grey btn-full" onclick="closeModal()">Cancel</button>
            <button class="btn btn-red btn-full" onclick="confirmDelete()">&#x2715; Delete</button>
        </div>
    </div>
</div>

<div class="card" style="max-width:520px;">
    <span class="page-tag">Admin Module</span>
    <h2 class="title">Event <span>Entry</span></h2>
    <p class="subtitle">Add, update or delete event records</p>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="op-tabs">
        <button type="button" class="op-tab act-add" id="tab-add"    onclick="switchTab('add')">&#43; Add</button>
        <button type="button" class="op-tab"          id="tab-update" onclick="switchTab('update')">&#9998; Update</button>
        <button type="button" class="op-tab"          id="tab-delete" onclick="switchTab('delete')">&#x2715; Delete</button>
    </div>

    <form method="POST" action="event_entry.php" id="mainForm">

        <label for="eid">Event ID *</label>
        <input type="text" id="eid" name="eid" placeholder="e.g. EVT001" required
               value="<?php echo $event['Event_id'] ?? (isset($_POST['eid']) ? htmlspecialchars($_POST['eid']) : ''); ?>">

        <div id="extra-fields">
            <label for="ename">Event Name *</label>
            <input type="text" id="ename" name="ename" placeholder="e.g. 100m Sprint"
                   value="<?php echo $event['Event_name'] ?? ''; ?>">

            <label for="rules">Rules &amp; Description *</label>
            <textarea id="rules" name="rules" placeholder="Enter event rules and description..."><?php echo $event['Rules'] ?? ''; ?></textarea>
        </div>

        <div class="btn-row">
            <button type="submit" name="add" id="btn-add" class="btn btn-gold">&#43; Add Event</button>
            <button type="submit" name="search_event" class="btn btn-outline">&#128269; Search</button>
            <button type="button" name="update" id="btn-update" class="btn btn-outline" style="display:none;" onclick="submitUpdate()" disabled>&#9998; Update</button>
            <button type="button" name="delete" id="btn-delete" class="btn btn-red"    style="display:none;" onclick="askDelete()">&#x2715; Delete</button>
        </div>

        <!-- Hidden submit buttons for update & delete -->
        <input type="submit" id="real-update" name="update" style="display:none;">
        <input type="submit" id="real-delete" name="delete" style="display:none;">

    </form>

    <!-- Live Event List -->
    <?php if (mysqli_num_rows($all_events) > 0): ?>
    <div class="preview-section">
        <h4>&#128203; Existing Events</h4>
        <?php mysqli_data_seek($all_events, 0); while ($ev = mysqli_fetch_assoc($all_events)): ?>
        <div class="event-row">
            <span class="event-id-badge"><?php echo htmlspecialchars($ev['Event_id']); ?></span>
            <span class="event-name"><?php echo htmlspecialchars($ev['Event_name']); ?></span>
            <button class="use-btn" onclick="fillId('<?php echo addslashes($ev['Event_id']); ?>')">Use ID</button>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>

<script>
var currentTab = 'add';

function switchTab(tab) {
    currentTab = tab;
    ['add','update','delete'].forEach(function(t) {
        document.getElementById('tab-' + t).className = 'op-tab';
    });
    ['btn-add','btn-update','btn-delete'].forEach(function(b) {
        var el = document.getElementById(b);
        if (el) el.style.display = 'none';
    });

    var extra = document.getElementById('extra-fields');

    if (tab === 'add') {
        document.getElementById('tab-add').className = 'op-tab act-add';
        document.getElementById('btn-add').style.display = 'inline-flex';
        extra.style.display = 'block';
    } else if (tab === 'update') {
        document.getElementById('tab-update').className = 'op-tab act-upd';
        document.getElementById('btn-update').style.display = 'inline-flex';
        extra.style.display = 'block';
    } else {
        document.getElementById('tab-delete').className = 'op-tab act-del';
        document.getElementById('btn-delete').style.display = 'inline-flex';
        extra.style.display = 'none';
    }
}

function fillId(id) {
    document.getElementById('eid').value = id;
    document.getElementById('eid').focus();
}

function submitUpdate() {
    document.getElementById('real-update').click();
}

function askDelete() {
    document.getElementById('deleteModal').classList.add('active');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
function confirmDelete() {
    closeModal();
    document.getElementById('real-delete').click();
}

// Enable update button if PHP found a record
(function() {
    var hasRecord = <?php echo isset($event) && $event ? 'true' : 'false'; ?>;
    var btn = document.getElementById('btn-update');
    if (btn && hasRecord) {
        btn.disabled = false;
        switchTab('update');
    }
})();
</script>

</body>
</html>
