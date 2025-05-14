<?php
session_start(); 
include_once 'db_include.php';
doDB();

$stmt = $mysqli->prepare("
    SELECT
      topic_id,
      topic_title,
      DATE_FORMAT(topic_create_time, '%b %e %Y at %r') AS fmt_topic_create_time,
      topic_owner
    FROM forum_topics
    WHERE topic_owner = ?
    ORDER BY topic_create_time DESC
");
if (! $stmt) {
    die("Prepare failed: " . $mysqli->error);
}

$stmt->bind_param("s", $_SESSION['curr_username']);
if (! $stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$get_topics_res = $stmt->get_result();

if (mysqli_num_rows($get_topics_res) < 1) {
	$display_block = "<p><em>No topics exist.</em></p>";
} else {
    $display_block = <<<END_OF_TEXT
    <table id="myTable">
    <thead>
    <tr>
    <th><a href="javascript:sortTable(myTable,0,0);">TOPIC TITLE</a></th>
    <th><a href="javascript:sortTable(myTable,1,0);"># of POSTS</a></th>
    </tr>
    </thead>
    <tbody>
END_OF_TEXT;

$stmt = $mysqli->prepare("
      SELECT t.name
        FROM TopicTags tt
        JOIN Tags       t  ON t.id = tt.tag_id
       WHERE tt.topic_id = ?
");

$stmtTags = $mysqli->prepare("
  SELECT t.name
    FROM TopicTags tt
    JOIN Tags       t  ON t.id = tt.tag_id
   WHERE tt.topic_id = ?
");
if (! $stmtTags) {
  die("Prepare failed (tags): " . $mysqli->error);
}

$stmtMedia = $mysqli->prepare("
  SELECT url
    FROM media
   WHERE topic_id = ?
");
if (! $stmtMedia) {
  die("Prepare failed (media): " . $mysqli->error);
}
	while ($topic_info = mysqli_fetch_array($get_topics_res)) {
		$topic_id = $topic_info['topic_id'];
		$topic_title = stripslashes($topic_info['topic_title']);
		$topic_create_time = $topic_info['fmt_topic_create_time'];
		$topic_owner = stripslashes($topic_info['topic_owner']);

		$stmt->bind_param('i', $topic_id);
        $stmt->execute();
        $tagsRes = $stmt->get_result();
        $names   = [];
        while ($r = $tagsRes->fetch_assoc()) {
            $names[] = htmlspecialchars($r['name']);
        }
        $tagList = $names ? implode(', ', $names) : 'None';


    $stmtMedia->bind_param('i', $topic_id);
    $stmtMedia->execute();
    $mediaRes = $stmtMedia->get_result();
    $mediaUrls = [];
    while ($m = $mediaRes->fetch_assoc()) {
      $mediaUrls[] = htmlspecialchars($m['url']);
    }
 
    if ($mediaUrls) {
       $mediaHtml = '';
        foreach ($mediaUrls as $u) {
          $mediaHtml .= "<a href=\"$u\" target=\"_blank\">🎵</a> ";
        }
    } 
    else {
      $mediaHtml = 'None';
    }

		$get_num_posts_sql = "SELECT COUNT(post_id) AS post_count FROM forum_posts WHERE topic_id = '".$topic_id."'";
		$get_num_posts_res = mysqli_query($mysqli, $get_num_posts_sql) or die(mysqli_error($mysqli));

		while ($posts_info = mysqli_fetch_array($get_num_posts_res)) {
			$num_posts = $posts_info['post_count'];
		}

    $display_block .= <<<END_OF_TEXT
    <tr>
      <td onclick="window.location.href = 'showtopic.php?topic_id=$topic_id'">
        <a href="showtopic.php?topic_id=$topic_id">
          <strong>$topic_title</strong>
        </a><br>
        Created on $topic_create_time by $topic_owner<br>
        <em>Tags:</em> $tagList
        <em>Media:</em> {$mediaHtml}
        <button class="deletepost" data-topic-id="$topic_id">Delete</button>
      </td>
      <td class="num_posts_col">$num_posts</td>
    </tr>
    END_OF_TEXT;
	}
	mysqli_free_result($get_topics_res);
	mysqli_free_result($get_num_posts_res);
  $stmtTags->close();
  $stmtMedia->close();

	mysqli_close($mysqli);

	$display_block .= "</tbody>
	</table>";
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="simple.css">
	<link rel="stylesheet" href="NavBar.css">
  <title>Ozzy Topics</title>
  <script type="text/javascript">
  function sortTable(table, col, reverse) {
     var tb = table.tBodies[0];
     var tr = Array.prototype.slice.call(tb.rows, 0);
     var  i;
     reverse = -((+reverse) || -1);
     tr = tr.sort(function (a, b) {
       return reverse 
          * (a.cells[col].textContent.trim()
               .localeCompare(b.cells[col].textContent.trim())
             );
     });
     for(i = 0; i < tr.length; ++i) tb.appendChild(tr[i]);
   }
   document.addEventListener('DOMContentLoaded', ()=>{
    const goHome = document.getElementsByClassName("returntolanding");
    const deletePost = document.getElementsByClassName("deletepost");
    Array.from(goHome).forEach((btn) => {
        btn.addEventListener('click', () => {
          window.location.href = "landing.php";
        });
      });
    Array.from(deletePost).forEach((btn) => {
      btn.addEventListener('click', () => {
        const topicId = btn.dataset.topicId;
        window.location.href = `deletemypost.php?topic_id=${encodeURIComponent(topicId)}`;
      });
    });
   })
  </script>
</head>
<body>
<div class="localnav">
  <img class="logo" src="ozzylogo.png" style="height:50px; width:70px;">
  <button class="returntolanding">Home</button>
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
</div>
  <h1>Topics in My Forum</h1>
  <?php echo $display_block; ?>
  <p>Would you like to <a href="addtopic.html">add a topic</a>?</p>
</body>
</html>
