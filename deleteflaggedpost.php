<?php
session_start();
include_once 'db_include.php';
doDB();

$post_id = $_GET['post_id'] ?? null;

if (!($delStmt = $mysqli->prepare(
    "DELETE FROM forum_posts WHERE post_id = ?"
))) {
    die("Prepare failed: " . $mysqli->error);
}

$delStmt->bind_param("i", $post_id);

if (! $delStmt->execute()) {
    die("Execute failed: " . $delStmt->error);
}
$delStmt->close();
header("Location: admin_dashboard.php");
exit;
?>