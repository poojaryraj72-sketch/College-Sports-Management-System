<?php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "event";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #080c12; color: #e8edf5;
               display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .err-box { background: #0e1520; border: 1px solid rgba(255,61,87,0.4);
                   border-radius: 14px; padding: 30px; max-width: 500px; text-align:center; }
        .err-icon { font-size: 48px; margin-bottom: 14px; }
        h2 { color: #ff3d57; font-size: 20px; margin-bottom: 10px; }
        p  { color: #6a7f9e; font-size: 14px; line-height: 1.6; }
        code { background: #14203a; padding: 2px 8px; border-radius: 4px; color: #f5c518; font-size: 13px; }
    </style>
    <div class='err-box'>
        <div class='err-icon'>&#9888;</div>
        <h2>Database Connection Failed</h2>
        <p>Could not connect to MySQL database <code>event</code>.</p>
        <p style='margin-top:12px;'>Error: <strong style='color:#ff3d57;'>" . mysqli_connect_error() . "</strong></p>
        <p style='margin-top:12px;'>
            Make sure <code>XAMPP</code> is running and the database <code>event</code> exists.
        </p>
    </div>");
}
?>

