<?php
session_start();
include_once 'db_include.php';
doDB();

if (empty($_POST['post_id']) || !ctype_digit($_POST['post_id'])) {
    header('Location: showtopic.php');
    exit;
}
$post_id  = (int)$_POST['post_id'];
$topic_id = isset($_POST['topic_id']) && ctype_digit($_POST['topic_id'])
             ? (int)$_POST['topic_id']
             : null;

$stmt = $mysqli->prepare("
  UPDATE forum_posts
     SET flagged = NOT flagged
   WHERE post_id = ?
");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$stmt->close();

if ($topic_id) {
    header("Location: topiclist_withJS.php");
} else {
    header('Location: topiclist_withJS.php');
}
exit;
