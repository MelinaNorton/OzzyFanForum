<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once 'db_include.php';
doDB();

$stmt = $mysqli->prepare(
    "SELECT
        post_id,
        topic_id,
        post_text,
        post_create_time,
        post_owner,
        flagged
     FROM forum_posts
     WHERE flagged = ?"
);

if (! $stmt) {
    die("Prepare failed: " . $mysqli->error);
}

$flagValue = 1;
$stmt->bind_param('i', $flagValue);
$stmt->execute();
$result = $stmt->get_result();

$flaggedPosts = [];
while ($row = $result->fetch_assoc()) {
    $flaggedPosts[] = $row;
}

$display_block = <<<END_OF_TEXT
<table id="flaggedPosts">
  <thead>
    <tr>
      <th>Post ID</th>
      <th>Topic ID</th>
      <th>Post Text</th>
      <th>Created</th>
      <th>Author</th>
    </tr>
  </thead>
  <tbody>
END_OF_TEXT;

foreach ($flaggedPosts as $row) {
    $post_id          = htmlspecialchars($row['post_id']);
    $topic_id         = htmlspecialchars($row['topic_id']);
    $post_text        = $row['post_text'];
    $post_create_time = htmlspecialchars($row['post_create_time']);
    $post_owner       = htmlspecialchars($row['post_owner']);

    $display_block .= <<<END_OF_TEXT
    <tr>
      <td>{$post_id}</td>
      <td>
        <a href="showtopic.php?topic_id={$topic_id}">
          {$topic_id}
        </a>
      </td>
      <td>{$post_text}</td>
      <td>{$post_create_time}</td>
      <td>{$post_owner}</td>
      <td><button class="deletepost" data-post-id="$post_id">Remove</button></td>
    </tr>
END_OF_TEXT;
}

$display_block .= <<<END_OF_TEXT
  </tbody>
</table>
END_OF_TEXT;
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="simple.css">
	<link rel="stylesheet" href="NavBar.css">
  <title>Admin Console</title>
  <script type="text/javascript">
  function sortTable(table, col, reverse) {
     var tb = table.tBodies[0];
     var tr = Array.prototype.slice.call(tb.rows, 0);
     var  i;
     reverse = -((+reverse) || -1);
     tr = tr.sort(function (a, b) {
       return reverse // `-1 *` if want opposite order
          * (a.cells[col].textContent.trim()
               .localeCompare(b.cells[col].textContent.trim())
             );
     });
     for(i = 0; i < tr.length; ++i) tb.appendChild(tr[i]);
   }
   // sortTable(tableNode, columId, false);
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
        const postId = btn.dataset.postId;
        window.location.href = `deleteflaggedpost.php?post_id=${encodeURIComponent(postId)}`;
      });
    });
   })
  </script>
</head>
<body>
<div class="localnav">
  <img class="logo" src="ozzylogo.png" style="height:50px; width:70px">
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
  <h1>Admin Console</h1>
  <?php echo $display_block; ?>
</body>
</html>