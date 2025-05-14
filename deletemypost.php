<?php
session_start();
include_once 'db_include.php';
doDB();

$topic_id = $_GET['topic_id'] ?? null;
if (!($delStmt = $mysqli->prepare(
    "DELETE FROM forum_topics WHERE topic_id = ?"
))) {
    die("Prepare failed: " . $mysqli->error);
}

$delStmt->bind_param("i", $topic_id);

if (! $delStmt->execute()) {
    die("Execute failed: " . $delStmt->error);
}
$delStmt->close();
$back = $_SERVER['HTTP_REFERER'] ?? 'topiclistpersonal_JS.php';
header("Location: {$back}");
?>