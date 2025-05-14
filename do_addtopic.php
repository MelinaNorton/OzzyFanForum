<?php
session_start();

include 'db_include.php';
doDB();

if ((!$_POST['topic_title']) || (!$_POST['post_text'])) {
	header("Location: addtopic.html");
	exit;
}

$clean_topic_title = mysqli_real_escape_string($mysqli, $_POST['topic_title']);
$clean_post_text = mysqli_real_escape_string($mysqli, $_POST['post_text']);
$clean_post_link;

$add_topic_sql = 
    "INSERT INTO forum_topics (topic_title, topic_create_time, topic_owner) VALUES ('{$clean_topic_title}', NOW(), '{$_SESSION['curr_username']}')";


$add_topic_res = mysqli_query($mysqli, $add_topic_sql) or die(mysqli_error($mysqli));

$topic_id = mysqli_insert_id($mysqli);

$add_post_sql = "INSERT INTO forum_posts (topic_id, post_text, post_create_time, post_owner) VALUES ('".$topic_id."', '".$clean_post_text."',  now(), '".$clean_topic_owner."')";

$add_post_res = mysqli_query($mysqli, $add_post_sql) or die(mysqli_error($mysqli));

$tagsmap = [
  'opinion' => 1,
  'media' => 2,
  'ranking' => 3,
  'discussion' => 4,
];
$rawTags = $_POST['tags'] ?? [];
$tagIDs = [];
foreach ($rawTags as $tagName) {
    if (isset($tagsmap[$tagName])) {
        $tagIDs[] = $tagsmap[$tagName];
    }
}
$stmt = $mysqli->prepare(
  "INSERT INTO TopicTags (topic_id, tag_id) VALUES (?, ?)"
);

foreach ($tagIDs as $tag_id) {
  $stmt->bind_param('ii', $topic_id, $tag_id);
  $stmt->execute();
}

if (!empty($_POST['media_link'])) {
  $media_link = $_POST['media_link'];
  $mediastmt = $mysqli->prepare(
      "INSERT INTO media (topic_id, url) VALUES (?, ?)"
  );
  if (! $mediastmt) {
      die("Prepare failed: " . $mysqli->error);
  }

  $mediastmt->bind_param("is", $topic_id, $media_link);

  if (! $mediastmt->execute()) {
      die("Execute failed: " . $mediastmt->error);
  }
  $mediastmt->close();
}

mysqli_close($mysqli);

$display_block = "<p>The <strong>".$_POST["topic_title"]."</strong> topic has been created.</p>";
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="simple.css">
  <link rel="stylesheet" href="NavBar.css">
  <title>New Topic Added</title>
</head>
<body>
<div id="navbar">
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          fetch('../navbar.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('navbar').innerHTML = data;
                });
        });
    </script>
    </div>
  <h1>New Topic Added</h1>
  <?php echo $display_block; ?>
  <script>
  setTimeout(() => {
    window.location.href = 'topiclistpersonal_JS.php';
  }, 2000);  // wait 2 seconds
</script>
</body>
</html>
