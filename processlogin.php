<?php
session_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'include_db.php';
doDB();

$username = isset($_GET['username']) ? htmlspecialchars($_GET['username'], ENT_QUOTES, 'UTF-8') : '';
$password = isset($_GET['password']) ? htmlspecialchars($_GET['password'], ENT_QUOTES, 'UTF-8') : '';

if (empty($username) || empty($password)) {
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

if (!password_verify($password, $userData['password_hash'])) {
    echo json_encode(["success" => false, "message" => "Incorrect password!"]);
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $userData['id'];

header("Location: PokeApp.html");
exit;
?>
