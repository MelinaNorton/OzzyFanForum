<?php
session_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//connect to the DB
include_once 'include_ozzy_db.php';
doDB();

//check if the input fields are present
$username = isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : '';
$password = isset($_POST['password']) ? htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') : '';

if (empty($username) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
    exit;
}
//first check if the username returned from the prepared SELECT statement is present in the table
//handle where the get_result() method is unsupported (common with older PHP versions on some CPanel setups).
$stmt = $mysqli->prepare("SELECT id, username, password_hash FROM Users WHERE username = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $mysqli->error]);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();

if (!method_exists($stmt, 'get_result')) {
    $stmt->store_result();
    $stmt->bind_result($id, $db_username, $password_hash);
    if ($stmt->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Incorrect username!"]);
        exit;
    }
    $stmt->fetch();
    $userData = ['id' => $id, 'username' => $db_username, 'password_hash' => $password_hash];
} else {
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Incorrect username!"]);
        exit;
    }
    $userData = $result->fetch_assoc();
}

//compare the hashes
if (!password_verify($password, $userData['password_hash'])) {
    echo json_encode(["success" => false, "message" => "Incorrect password!"]);
    exit;
}

//login & redirect
session_regenerate_id(true);
$_SESSION['user_id'] = $userData['id'];
$_SESSION['curr_username'] = $userData['username'];
header("Location: landing.php");
exit;
?>
