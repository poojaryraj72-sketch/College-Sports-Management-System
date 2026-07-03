<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: index.php");
    exit();
}

$rows = [];
$event_info = null;
$no_result = false;
$searched = false;

if (isset($_POST['search'])) {
    $searched = true;
    $eid = trim($_POST['eid']);

    $estmt = mysqli_prepare($conn, "SELECT Event_id, Event_name, Rules FROM event WHERE Event_id = ?");
    mysqli_stmt_bind_param($estmt, "s", $eid);
    mysqli_stmt_execute($estmt);
    $eresult = mysqli_stmt_get_result($estmt);
    if (mysqli_num_rows($eresult) > 0) {
        $event_info = mysqli_fetch_assoc($eresult);
    }

    $query = "SELECT s.RegNo, s.Name, s.Class, s.Section,
                     s.DOB, s.Gender, s.Height, s.Weight, c.file AS certificate_file
              FROM student s
              LEFT JOIN certificate c ON s.RegNo = c.RegNo AND s.Event_id = c.Event_id
              WHERE s.Event_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $eid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    } else {
        $no_result = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - EventMS</title>
<link rel="stylesheet" href="PjCss.css">
<style>
.student-table { width:100%; border-collapse:collapse; margin-top:16px; font-size:14px;}
.student-table th { background:#1e3356; color:#f0c040; padding:10px 14px; text-align:left; cursor:pointer;}
.student-table td { padding:9px 14px; border-bottom:1px solid #1e3356; color:#cdd8ec;}
.student-table tr:hover td { background:#162844; }
a.cert-link { color:#4fc3f7; text-decoration:none;}
a.cert-link:hover { text-decoration:underline;}
.fade-in {animation:fade .4s ease-in;}
@keyframes fade { from{opacity:0; transform:translateY(10px);} to{opacity:1; transform:none;} }
</style>
</head>
<body>
<div class="topbar">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Admin · Reports</span>
</div>
<div class="card wide fade-in">
    <h2 class="title">Participant <span>Report</span></h2>

    <form method="POST" id="searchForm">
        <label for="eid">Event ID</label>
        <input type="text" id="eid" name="eid" placeholder="e.g. EVT001"
               value="<?php echo isset($_POST['eid']) ? htmlspecialchars($_POST['eid']) : ''; ?>" required>
        <button type="submit" name="search" class="btn btn-gold">🔍 Search</button>
    </form>

    <?php if ($searched): ?>
        <?php if ($event_info): ?>
            <div style="margin-top:20px;" class="fade-in">
                <table class="rep-table">
                    <tr><th>Event ID</th><td><?php echo htmlspecialchars($event_info['Event_id']); ?></td></tr>
                    <tr><th>Event Name</th><td><?php echo htmlspecialchars($event_info['Event_name']); ?></td></tr>
                    <tr><th>Rules</th><td><?php echo nl2br(htmlspecialchars($event_info['Rules'])); ?></td></tr>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!$no_result): ?>
            <h3 style="margin-top:24px;">Registered Students</h3>
            <input type="text" id="searchBox" placeholder="Filter by name or reg no..." style="padding:6px 10px;width:40%;margin-bottom:8px;">
            <table class="student-table fade-in" id="studentTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reg No</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>DOB</th>
                        <th>Gender</th>
                        <th>Height (cm)</th>
                        <th>Weight (kg)</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo htmlspecialchars($row['RegNo']); ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Class']); ?></td>
                        <td><?php echo htmlspecialchars($row['Section']); ?></td>
                        <td><?php echo htmlspecialchars($row['DOB']); ?></td>
                        <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['Height']); ?></td>
                        <td><?php echo htmlspecialchars($row['Weight']); ?></td>
                        <td>
                            <?php if ($row['certificate_file']): ?>
                                <a href="uploads/<?php echo htmlspecialchars($row['certificate_file']); ?>" 
                                   target="_blank" class="cert-link">View</a>
                            <?php else: ?>
                                <span style="color:#999;">Not uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-err fade-in">No students registered for this Event ID.</div>
        <?php endif; ?>
    <?php endif; ?>
	 <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>

<script>
// live filtering
document.getElementById('searchBox')?.addEventListener('keyup', function(){
    const val = this.value.toLowerCase();
    document.querySelectorAll('#studentTable tbody tr').forEach(row=>{
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
    });
});

// clickable sort headers
document.querySelectorAll('.student-table th').forEach((th, idx)=>{
    th.addEventListener('click', ()=>{
        const tbody = document.querySelector('#studentTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const asc = th.classList.toggle('asc');
        rows.sort((a,b)=>{
            const A = a.children[idx].innerText.toLowerCase();
            const B = b.children[idx].innerText.toLowerCase();
            return asc ? A.localeCompare(B) : B.localeCompare(A);
        });
        rows.forEach(r=>tbody.appendChild(r));
    });
});
</script>
</body>
</html>
