<?php
include 'db_include.php';
doDB();

if (!isset($_GET['topic_id'])) {
	header("Location: topiclist.php");
	exit;
}

$safe_topic_id = mysqli_real_escape_string($mysqli, $_GET['topic_id']);

$verify_topic_sql = "SELECT topic_title FROM forum_topics WHERE topic_id = '".$safe_topic_id."'";
$verify_topic_res =  mysqli_query($mysqli, $verify_topic_sql) or die(mysqli_error($mysqli));

if (mysqli_num_rows($verify_topic_res) < 1) {
	$display_block = "<p><em>You have selected an invalid topic.<br>
	Please <a href=\"topiclist.php\">try again</a>.</em></p>";
} else {
	while ($topic_info = mysqli_fetch_array($verify_topic_res)) {
		$topic_title = stripslashes($topic_info['topic_title']);
	}
	$get_posts_sql = "SELECT post_id, post_text, DATE_FORMAT(post_create_time, '%b %e %Y<br>%r') AS fmt_post_create_time, post_owner FROM forum_posts WHERE topic_id = '".$safe_topic_id."' ORDER BY post_create_time ASC";
	$get_posts_res = mysqli_query($mysqli, $get_posts_sql) or die(mysqli_error($mysqli));

	$display_block = <<<END_OF_TEXT
	<p>Showing posts for the <strong>$topic_title</strong> topic:</p>
	<table>
	<tr>
	<th>AUTHOR</th>
	<th>POST</th>
	</tr>
END_OF_TEXT;

	while ($posts_info = mysqli_fetch_array($get_posts_res)) {
		$post_id = $posts_info['post_id'];
		$post_text = nl2br(stripslashes($posts_info['post_text']));
		$post_create_time = $posts_info['fmt_post_create_time'];
		$post_owner = stripslashes($posts_info['post_owner']);

		$delete_button = '';
    	if (isset($_SESSION['curr_username']) && $_SESSION['username'] === $topic_owner) {
        $delete_button = '<button class="deletepost" data-topic-id="'
                       . $topic_id
                       . '">Delete</button>';
    	}

		$display_block .= <<<END_OF_TEXT
		<tr>
		  <td>
			<p>{$post_owner}</p>
			<p>
			  Created on:<br>
			  {$post_create_time}
			</p>
			{$delete_button}
		  </td>
		  <td>
			<div class="post-text-scroll">
			  <?php echo $post_text
			</div>
		  </td>
		  <td onclick="window.location.href = 'replytopost.php?post_id={$post_id}'">
			<form action="report.php" method="post" style="display:inline">
			  <input type="hidden" name="post_id"  value="{$post_id}">
			  <input type="hidden" name="topic_id" value="{$topic_id}">
			  <button type="submit">Report</button>
			</form>
			<p>
			  <a href="replytopost.php?post_id={$post_id}">
				<strong>REPLY TO POST</strong>
			  </a>
			</p>
		  </td>
		</tr>
	END_OF_TEXT;
	}

	mysqli_free_result($get_posts_res);
	mysqli_free_result($verify_topic_res);

	mysqli_close($mysqli);

	$display_block .= "</table>";
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="simple.css">
	<link rel="stylesheet" href="NavBar.css">
<title>Posts in Topic</title>
<button class="gohome">Home</button>
  <style type="text/css">
	table {
		border: 1px solid black;
		border-collapse: collapse;
	}
	th {
		border: 1px solid black;
		padding: 6px;
		font-weight: bold;
		background: #ccc;
	}
	td {
		border: 1px solid black;
		padding: 6px;
		vertical-align: top;
	}
	.num_posts_col { text-align: center; }
  </style>
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
  <h1>Posts in Topic</h1>
  <?php echo $display_block; ?>
  <script>
	const goHome = document.getElementsByClassName("gohome");
    	Array.from(goHome).forEach((btn) => {
        	btn.addEventListener('click', () => {
          		window.location.href = "landing.php";
        	});
      	});
  </script>
</body>
</html>
