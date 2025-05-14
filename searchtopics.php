<?php
session_start();
include_once 'db_include.php';
doDB();

if (empty($_POST['searchtopics'])) {
    exit;
}
$clean_search = mysqli_real_escape_string($mysqli, $_POST['searchtopics']);

$search_sql = "
  SELECT
    ft.topic_id,
    ft.topic_title,
    DATE_FORMAT(ft.topic_create_time, '%b %e %Y at %r') AS fmt_time,
    ft.topic_owner
  FROM forum_topics AS ft
  WHERE ft.topic_owner LIKE '%{$clean_search}%'
     OR ft.topic_title  LIKE '%{$clean_search}%'
     OR ft.topic_id IN (
         SELECT tt.topic_id
           FROM TopicTags tt
           JOIN Tags       t  ON t.id = tt.tag_id
          WHERE t.name LIKE '%{$clean_search}%'
     )
  ORDER BY ft.topic_create_time DESC
";

$search_res = mysqli_query($mysqli, $search_sql) or die(mysqli_error($mysqli));

$stmt = $mysqli->prepare("
  SELECT t.name
    FROM TopicTags tt
    JOIN Tags       t ON t.id = tt.tag_id
   WHERE tt.topic_id = ?
");

if (mysqli_num_rows($search_res) < 1) {
    echo '<p><em>No search results for "' 
         . htmlspecialchars($clean_search, ENT_QUOTES) 
         . '".</em></p>';
    echo <<<HTML
    <script>
        setTimeout(function() {
        window.location.href = 'topiclist_withJS.php';
        }, 3000);
    </script>
    HTML;
    exit;
}

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

while ($row = mysqli_fetch_assoc($search_res)) {
    $topic_id           = $row['topic_id'];
    $topic_title        = htmlspecialchars(stripslashes($row['topic_title']));
    $topic_create_time  = $row['fmt_time'];          // <-- fixed alias
    $topic_owner        = htmlspecialchars(stripslashes($row['topic_owner']));

    $stmt->bind_param('i', $topic_id);
    $stmt->execute();
    $tagsRes = $stmt->get_result();
    $names = [];
    while ($r = $tagsRes->fetch_assoc()) {
        $names[] = htmlspecialchars($r['name']);
    }
    $tagList = $names ? implode(', ', $names) : 'None';

    $cntRes = mysqli_query(
        $mysqli,
        "SELECT COUNT(*) AS post_count 
           FROM forum_posts 
          WHERE topic_id = $topic_id"
    ) or die(mysqli_error($mysqli));
    $num_posts = mysqli_fetch_assoc($cntRes)['post_count'];

    $display_block .= <<<END_OF_TEXT
    <tr>
      <td>
        <a href="showtopic.php?topic_id={$topic_id}">
          <strong>{$topic_title}</strong>
        </a><br>
        Created on {$topic_create_time} by {$topic_owner}<br>
        <em>Tags:</em> {$tagList}
      </td>
      <td class="num_posts_col">{$num_posts}</td>
    </tr>
END_OF_TEXT;
}

$display_block .= <<<END_OF_TEXT
  </tbody>
</table>
END_OF_TEXT;

$stmt->close();
mysqli_close($mysqli);
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="simple.css">
	<link rel="stylesheet" href="NavBar.css">
  <title>Search Results</title>
  <button class="seetopics">Topics</button>
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
    const goHome = document.getElementsByClassName("seetopics");
    const deletePost = document.getElementsByClassName("deletepost");
    Array.from(goHome).forEach((btn) => {
        btn.addEventListener('click', () => {
          window.location.href = "topiclist_withJS.php";
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
  <h1>Search Results</h1>
  <?php echo $display_block; ?>
</body>
</html>
