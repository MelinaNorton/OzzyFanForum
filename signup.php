<?php
session_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//connect to the DB
include_once 'include_ozzy_db.php';
doDB();

//check if the input fields are present
$nusername = isset($_POST['new_username']) ? htmlspecialchars($_POST['new_username'], ENT_QUOTES, 'UTF-8') : '';
$npassword = isset($_POST['new_password']) ? htmlspecialchars($_POST['new_password'], ENT_QUOTES, 'UTF-8') : '';
$nemail = isset($_POST['new_email']) ? htmlspecialchars($_POST['new_email'], ENT_QUOTES, 'UTF-8') : '';
if (empty($nusername) || empty($npassword)) {
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
    exit;
}

$stmt = $mysqli->prepare("SELECT id, username, password_hash FROM Users WHERE username = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $mysqli->error]);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();

if (!method_exists($stmt, 'get_result')) {
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Username already exists."
        ]);
        exit;
    }
} else {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Username already exists."
        ]);
        exit;
    }
}

$hashedPassword = password_hash($npassword, PASSWORD_DEFAULT);
$insertStmt = $mysqli->prepare(
    "INSERT INTO Users (username, password_hash, email) VALUES (?, ?, ?)"
);
if (!$insertStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Insert prepare failed: " . $mysqli->error
    ]);
    exit;
}
$insertStmt->bind_param("sss", $nusername, $hashedPassword, $nemail);

if (!$insertStmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Could not create user: " . $insertStmt->error
    ]);
    exit;
}

echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Successful</title>
  <link rel="stylesheet" href="simple.css">
  <script>
    // Redirect in 10 seconds
    setTimeout(function() {
      window.location.href = "ozzylogin.php";
    }, 10000);
  </script>
  <!-- Fallback for non-JS browsers -->
  <meta http-equiv="refresh" content="10;url=ozzylogin.php">
</head>
<body>
  <div style="max-width:400px;margin:5rem auto;padding:1rem;
              font-family:system-ui,sans-serif;
              text-align:center;
              border:1px solid #ddd;
              border-radius:8px;
              box-shadow:0 2px 4px rgba(0,0,0,0.1);">
    <h2>User successfully created!</h2>
    <p>Redirecting you to the login page in 10 seconds…</p>
    <p><a href="ozzylogin.php">Click here</a> if you don’t want to wait.</p>
  </div>
</body>
</html>';
exit;
?>