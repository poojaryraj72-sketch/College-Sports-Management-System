<?php
session_start();
include "db.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$message  = "";
$msg_type = "";
$student  = null;

// ADD
if (isset($_POST['add'])) {
    $eid    = trim($_POST['event_id']);
    $reg    = trim($_POST['reg_no']);
    $name   = trim($_POST['name']);
    $class  = trim($_POST['class']);
    $dob    = $_POST['dob'];
    $parent = trim($_POST['parent']);
    $gender = $_POST['gender'];
    $height = trim($_POST['height']);
    $weight = trim($_POST['weight']);
    $section= trim($_POST['section']);

    if (empty($reg) || empty($name) || empty($eid)) {
        $message  = "Event ID, Register Number and Name are required.";
        $msg_type = "err";
    } else {
        // ✅ Check duplicate based on BOTH RegNo + Event_id
        $chk = mysqli_prepare($conn, "SELECT * FROM student WHERE RegNo = ? AND Event_id = ?");
        mysqli_stmt_bind_param($chk, "ss", $reg, $eid);
        mysqli_stmt_execute($chk);
        $check = mysqli_stmt_get_result($chk);

        if (mysqli_num_rows($check) > 0) {
            $message  = "This student is already registered for this event.";
            $msg_type = "err";
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO student
                                 (Event_id, RegNo, Name, Class, Section, Parent, DOB, Gender, Height, Weight)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssssssssss", $eid, $reg, $name, $class, $section, $parent, $dob, $gender, $height, $weight);
            mysqli_stmt_execute($ins);
            $message  = "Student added successfully!";
            $msg_type = "ok";
        }
    }
}

// SEARCH BEFORE UPDATE
if (isset($_POST['search_update'])) {
    $eid = trim($_POST['event_id']);
    $reg = trim($_POST['reg_no']);
    $stm = mysqli_prepare($conn, "SELECT * FROM student WHERE Event_id = ? AND RegNo = ?");
    mysqli_stmt_bind_param($stm, "ss", $eid, $reg);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);
    if (mysqli_num_rows($res) > 0) {
        $student = mysqli_fetch_assoc($res);
    } else {
        $message  = "Record not found for given Event ID and Register Number.";
        $msg_type = "err";
    }
}

// UPDATE
if (isset($_POST['update'])) {
    $eid    = trim($_POST['event_id']);
    $reg    = trim($_POST['reg_no']);
    $name   = trim($_POST['name']);
    $class  = trim($_POST['class']);
    $dob    = $_POST['dob'];
    $parent = trim($_POST['parent']);
    $gender = $_POST['gender'];
    $height = trim($_POST['height']);
    $weight = trim($_POST['weight']);
    $section= trim($_POST['section']);

    if (empty($reg) || empty($eid)) {
        $message  = "Event ID and Register Number are required.";
        $msg_type = "err";
    } else {
        // ✅ Update specific event registration only
        $upd = mysqli_prepare($conn, "UPDATE student
                             SET Name=?, Class=?, Section=?,
                                 DOB=?, Parent=?, Gender=?, Height=?, Weight=?
                             WHERE Event_id=? AND RegNo=?");
        mysqli_stmt_bind_param($upd, "ssssssssss", $name, $class, $section, $dob, $parent, $gender, $height, $weight, $eid, $reg);
        mysqli_stmt_execute($upd);

        if (mysqli_affected_rows($conn) > 0) {
            $message  = "Student updated successfully!";
            $msg_type = "ok";
        } else {
            $message  = "No changes made or record not found.";
            $msg_type = "err";
        }
    }
}

// DELETE
if (isset($_POST['delete'])) {
    $reg = trim($_POST['reg_no']);
    $eid = trim($_POST['event_id']);

    if (empty($reg) || empty($eid)) {
        $message  = "Both Register Number and Event ID are required to delete.";
        $msg_type = "err";
    } else {
        // ✅ Delete only the specific event registration, not all records of student
        $del = mysqli_prepare($conn, "DELETE FROM student WHERE RegNo = ? AND Event_id = ?");
        mysqli_stmt_bind_param($del, "ss", $reg, $eid);
        mysqli_stmt_execute($del);

        if (mysqli_affected_rows($conn) > 0) {
            $message  = "Student removed from this event successfully!";
            $msg_type = "ok";
        } else {
            $message  = "Record not found for given Register Number and Event ID.";
            $msg_type = "err";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Entry – EventMS</title>
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
            max-width: 360px; width: 90%;
            text-align: center;
            box-shadow: var(--shadow);
            animation: fadeSlide 0.3s ease;
        }
        .confirm-box .icon { font-size: 42px; margin-bottom: 12px; }
        .confirm-box h3 { font-family:'Bebas Neue',sans-serif; font-size:22px; letter-spacing:1px; margin-bottom:8px; }
        .confirm-box p  { font-size:13px; color:var(--muted); margin-bottom:20px; }
        .confirm-btns { display:flex; gap:10px; }
        .field-group-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold);
            margin: 20px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border2);
        }
        .event-badge {
            display: inline-block;
            background: #1e3356;
            color: #f0c040;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 12px;
            margin: 2px 4px 2px 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .events-enrolled {
            margin-top: 10px;
            padding: 10px 14px;
            background: #0f1e33;
            border: 1px solid #1e3356;
            border-radius: 8px;
            font-size: 13px;
            color: #7a91b0;
        }
    </style>
</head>
<body>

<!-- Delete Confirmation Modal -->
<div class="confirm-overlay" id="deleteModal">
    <div class="confirm-box">
        <div class="icon">&#9888;</div>
        <h3>Remove from Event?</h3>
        <p>This will remove the student from <strong>this event only</strong>. Their other event registrations will remain intact.</p>
        <div class="confirm-btns">
            <button class="btn btn-grey btn-full" onclick="closeModal()">Cancel</button>
            <button class="btn btn-red btn-full" onclick="confirmDelete()">&#x2715; Remove</button>
        </div>
    </div>
</div>

<div class="topbar" style="max-width:860px;">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Admin &middot; Student Entry</span>
</div>

<div class="card wide">
    <span class="page-tag">Admin Module</span>
    <h2 class="title">Student <span>Entry</span></h2>
    <p class="subtitle">Register, update or remove student participation records</p>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Show all events the searched student is enrolled in -->
    <?php if ($student): ?>
        <?php
            $ereg = $student['RegNo'];
            $evts = mysqli_prepare($conn, "SELECT e.Event_id, e.Event_name FROM student s
                                           JOIN event e ON s.Event_id = e.Event_id
                                           WHERE s.RegNo = ?");
            mysqli_stmt_bind_param($evts, "s", $ereg);
            mysqli_stmt_execute($evts);
            $evts_res = mysqli_stmt_get_result($evts);
        ?>
        <?php if (mysqli_num_rows($evts_res) > 0): ?>
            <div class="events-enrolled">
                &#127891; <strong style="color:#cdd8ec;"><?php echo htmlspecialchars($student['Name']); ?></strong>
                is enrolled in:
                <?php while ($ev = mysqli_fetch_assoc($evts_res)): ?>
                    <span class="event-badge"><?php echo htmlspecialchars($ev['Event_id']); ?> – <?php echo htmlspecialchars($ev['Event_name']); ?></span>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="op-tabs">
        <button type="button" class="op-tab act-add" id="tab-add"    onclick="switchTab('add')">&#43; Add</button>
        <button type="button" class="op-tab"          id="tab-update" onclick="switchTab('update')">&#9998; Update</button>
        <button type="button" class="op-tab"          id="tab-delete" onclick="switchTab('delete')">&#x2715; Delete</button>
    </div>

    <form method="POST" action="student_entry.php" id="mainForm">

        <div class="two-col">
            <div>
                <label for="reg_no">Register Number *</label>
                <input type="text" id="reg_no" name="reg_no" placeholder="e.g. 22CS001" required
                       value="<?php echo $student['RegNo'] ?? (isset($_POST['reg_no']) ? htmlspecialchars($_POST['reg_no']) : ''); ?>">
            </div>
            <div>
                <label for="event_id">Event ID *</label>
                <input type="text" id="event_id" name="event_id" placeholder="e.g. EVT001" required
                       value="<?php echo $student['Event_id'] ?? (isset($_POST['event_id']) ? htmlspecialchars($_POST['event_id']) : ''); ?>">
            </div>
        </div>

        <div id="extra-fields">
            <div class="field-group-label">&#127891; Personal Info</div>

            <div class="two-col">
                <div>
                    <label for="name">Student Name</label>
                    <input type="text" id="name" name="name" placeholder="Full name"
                           value="<?php echo $student['Name'] ?? ''; ?>">
                </div>
                <div>
                    <label for="parent">Parent / Guardian</label>
                    <input type="text" name="parent" placeholder="Parent's name"
                           value="<?php echo $student['Parent'] ?? ''; ?>">
                </div>
            </div>

            <div class="two-col">
                <div>
                    <label for="class">Class</label>
                    <input type="text" id="class" name="class" placeholder="e.g. 3rd Year"
                           value="<?php echo $student['Class'] ?? ''; ?>">
                </div>
                <div>
                    <label for="section">Section</label>
                    <input type="text" id="section" name="section" placeholder="e.g. A"
                           value="<?php echo $student['Section'] ?? ''; ?>">
                </div>
            </div>

            <div class="field-group-label">&#128101; Physical Details</div>

            <div class="two-col">
                <div>
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob"
                           value="<?php echo $student['DOB'] ?? ''; ?>">
                </div>
                <div>
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male"   <?php if (($student['Gender'] ?? '') == 'Male')   echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if (($student['Gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other"  <?php if (($student['Gender'] ?? '') == 'Other')  echo 'selected'; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="two-col">
                <div>
                    <label for="height">Height (cm)</label>
                    <input type="text" name="height" placeholder="e.g. 172"
                           value="<?php echo $student['Height'] ?? ''; ?>">
                </div>
                <div>
                    <label for="weight">Weight (kg)</label>
                    <input type="text" name="weight" placeholder="e.g. 65"
                           value="<?php echo $student['Weight'] ?? ''; ?>">
                </div>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" name="add" id="btn-add" class="btn btn-gold">&#43; Add Student</button>
            <button type="submit" name="search_update" class="btn btn-outline">&#128269; Search</button>
            <button type="button" id="btn-update" class="btn btn-outline" style="display:none;" onclick="submitUpdate()" disabled>&#9998; Update</button>
            <button type="button" id="btn-delete" class="btn btn-red" onclick="askDelete()">&#x2715; Delete</button>
        </div>

        <input type="submit" id="real-update" name="update" style="display:none;">
        <input type="submit" id="real-delete" name="delete" style="display:none;">

    </form>

    <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>

<script>
function switchTab(tab) {
    ['add','update','delete'].forEach(function(t) {
        document.getElementById('tab-' + t).className = 'op-tab';
    });

    var extra = document.getElementById('extra-fields');
    var btnAdd = document.getElementById('btn-add');
    var btnUpd = document.getElementById('btn-update');
    var btnDel = document.getElementById('btn-delete');

    btnAdd.style.display = 'none';
    btnUpd.style.display = 'none';
    btnDel.style.display = 'none';

    if (tab === 'add') {
        document.getElementById('tab-add').className = 'op-tab act-add';
        btnAdd.style.display = 'inline-flex';
        extra.style.display = 'block';
    } else if (tab === 'update') {
        document.getElementById('tab-update').className = 'op-tab act-upd';
        btnUpd.style.display = 'inline-flex';
        extra.style.display = 'block';
    } else {
        document.getElementById('tab-delete').className = 'op-tab act-del';
        btnDel.style.display = 'inline-flex';
        extra.style.display = 'none';
    }
}

function submitUpdate() { document.getElementById('real-update').click(); }

function askDelete() {
    var reg = document.getElementById('reg_no').value.trim();
    var eid = document.getElementById('event_id').value.trim();
    if (!reg || !eid) {
        alert('Please enter both Register Number and Event ID to delete.');
        return;
    }
    document.getElementById('deleteModal').classList.add('active');
}
function closeModal() { document.getElementById('deleteModal').classList.remove('active'); }
function confirmDelete() { closeModal(); document.getElementById('real-delete').click(); }

(function() {
    var hasRecord = <?php echo isset($student) && $student ? 'true' : 'false'; ?>;
    var btn = document.getElementById('btn-update');
    if (btn && hasRecord) {
        btn.disabled = false;
        switchTab('update');
    }
})();
</script>

</body>
</html>