<?php
session_start();
include "db.php";

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$message = "";
$msg_type = "";

if (isset($_POST['upload'])) {
    $regno = trim($_POST['student_id']);
    $event_id = trim($_POST['event_id']);

    if (!isset($_FILES['certificate']) || $_FILES['certificate']['error'] != 0) {
        $message = "Please select a valid file.";
        $msg_type = "err";
    } elseif (empty($regno) || empty($event_id)) {
        $message = "Student ID and Event ID are required.";
        $msg_type = "err";
    } else {
        $file = $_FILES['certificate'];
        $stmt = $conn->prepare("SELECT RegNo FROM student WHERE RegNo = ? AND Event_id = ?");
        $stmt->bind_param("ss", $regno, $event_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $message = "Invalid Student ID or Event ID. Please verify your details.";
            $msg_type = "err";
        } else {
            $stmt = $conn->prepare("SELECT id FROM certificate WHERE RegNo = ? AND Event_id = ?");
            $stmt->bind_param("ss", $regno, $event_id);
            $stmt->execute();
            $dup = $stmt->get_result();

            if ($dup->num_rows > 0) {
                $message = "You have already uploaded a certificate for this event.";
                $msg_type = "err";
            } else {
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    $message = "Only JPG, PNG, PDF files are allowed.";
                    $msg_type = "err";
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $message = "Maximum file size is 5MB.";
                    $msg_type = "err";
                } else {
                    $upload_dir = "uploads/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                    $filename = uniqid("cert_", true) . "." . $ext;
                    $filepath = $upload_dir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $stmt = $conn->prepare("INSERT INTO certificate (RegNo, Event_id, file) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $regno, $event_id, $filename);
                        if ($stmt->execute()) {
                            $message = "Certificate uploaded successfully! Your submission is under review.";
                            $msg_type = "ok";
                        } else {
                            $message = "Database error. Please try again.";
                            $msg_type = "err";
                        }
                    } else {
                        $message = "File upload failed. Please try again.";
                        $msg_type = "err";
                    }
                }
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
    <title>Upload Certificate – EventMS</title>
    <link rel="stylesheet" href="PjCss.css">
    <style>
        .preview-container { margin-top:10px; display:none; }
        .preview-container img, .preview-container iframe { 
            max-width: 100%; border:1px solid #ccc; border-radius:6px; 
            padding:4px; background:#fff; 
        }
        .alert { transition: all 0.4s ease; opacity:0; transform:translateY(-10px);}
        .alert.show { opacity:1; transform:translateY(0);}
        .btn-full:hover { transform:scale(1.03); transition:0.3s; }
        .progress-bar { margin-top:10px; height:6px; background:#eee; border-radius:3px; overflow:hidden; display:none;}
        .progress-fill { width:0%; height:100%; background:#f0c040; transition:width .3s ease;}
    </style>
</head>
<body>
<div class="topbar">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Student · Certificate Upload</span>
</div>

<div class="card" style="max-width:500px;">
    <h2 class="title">Upload <span>Certificate</span></h2>
    <p class="subtitle">Submit your event certificate for verification</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo htmlspecialchars($msg_type); ?> show">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="upload_certificate.php" enctype="multipart/form-data" id="uploadForm">
        <label>Student Register Number *</label>
        <input type="text" name="student_id" required>

        <label>Event ID *</label>
        <input type="text" name="event_id" required>

        <label>Certificate File *</label>
        <input type="file" name="certificate" id="certificate" accept=".jpg,.jpeg,.png,.pdf" required>

        <div class="preview-container" id="previewContainer"></div>

        <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>

        <br><br>
        <button type="submit" name="upload" class="btn btn-gold btn-full">Upload Certificate</button>
    </form>

    <hr>
    <p style="font-size:13px; color:#666;">
        Allowed: JPG, PNG, PDF · Max size 5MB · One upload per event.
    </p>
	<a href="Student_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>

<script>
const fileInput = document.getElementById('certificate');
const preview = document.getElementById('previewContainer');
const fill = document.getElementById('progressFill');
const bar = document.querySelector('.progress-bar');

fileInput.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    preview.innerHTML = '';
    preview.style.display = 'block';
    if (['jpg','jpeg','png'].includes(ext)) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
    } else if (ext === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = URL.createObjectURL(file);
        iframe.height = '300px';
        preview.appendChild(iframe);
    }
});

// Simulated progress animation on submit
document.getElementById('uploadForm').addEventListener('submit', function() {
    bar.style.display = 'block';
    fill.style.width = '0%';
    let width = 0;
    const interval = setInterval(() => {
        if (width >= 100) clearInterval(interval);
        else { width += 10; fill.style.width = width + '%'; }
    }, 100);
});
</script>
</body>
</html>
