<?php
session_start();
$error = "";
if (isset($_POST['btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($username == "Admin" && $password == "@admin") 
	{
        $_SESSION['user']  = "Admin";
        $_SESSION['role']  = "admin";
        header("Location: admin_dashboard.php");
        exit();
    } 
	elseif ($username == "Student" && $password == "@student") 
	{
        $_SESSION['user'] = "Student";
        $_SESSION['role'] = "student";
        header("Location: student_dashboard.php");
        exit();

    } 
	else 
	{
        $error = "Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - EventMS</title>
    <link rel="stylesheet" href="PjCss.css">
</head>
<body>
<div class="topbar" style="max-width:480px;">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Login</span>
</div>
<div class="card">
    <div style="text-align:center; margin-bottom:20px;">
        <div style="width:58px; height:58px; background:#c9a84c; border-radius:14px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:24px; font-weight:bold; color:#0d1b2e; margin:0 auto 10px;">E</div>
        <div style="font-size:18px; font-weight:bold;">EventMS</div>
        <div style="font-size:13px; color:#7a91b0;">Event Management System</div>
    </div>

    <span class="page-tag">Secure Access</span>
    <?php 
	if ($error): 
	?>
        <div class="alert alert-err">
		<?php 
			echo $error; 
		?>
		</div>
    <?php endif; ?>

    <form method="POST" action="index.php">

        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <br>
        <button type="submit" name="btn" class="btn btn-gold btn-full">Login &rarr;</button>

    </form>


</div>

</body>
</html>
