<?php
session_start();
include 'db_include.php';
doDB();

//check to see if we're showing the form or adding the post
if (!$_POST) {
   // showing the form; check for required item in query string
   if (!isset($_GET['post_id'])) {
      header("Location: topiclist.php");
      exit;
   }

   //create safe values for use
   $safe_post_id = mysqli_real_escape_string($mysqli, $_GET['post_id']);

   //still have to verify topic and post
   $verify_sql = "SELECT ft.topic_id, ft.topic_title FROM forum_posts
                  AS fp LEFT JOIN forum_topics AS ft ON fp.topic_id =
                  ft.topic_id WHERE fp.post_id = '".$safe_post_id."'";

   $verify_res = mysqli_query($mysqli, $verify_sql)
                 or die(mysqli_error($mysqli));

   if (mysqli_num_rows($verify_res) < 1) {
      //this post or topic does not exist
      header("Location: topiclist.php");
      exit;
   } else {
      //get the topic id and title
      while($topic_info = mysqli_fetch_array($verify_res)) {
         $topic_id = $topic_info['topic_id'];
         $topic_title = stripslashes($topic_info['topic_title']);
      }
?>
<!DOCTYPE html>
<html>
<head>
   <link rel="stylesheet" href="simple.css">
   <link rel="stylesheet" href="NavBar.css">
     <!-- TinyMCE (Community) -->
      <script src="https://cdn.tiny.cloud/1/0aum8n9gxhiod2t3pj2dz5ttqtd1kou2qvtt382fdq3blg15/tinymce/6/tinymce.min.js" 
          referrerpolicy="origin"></script>
  <title>Post Your Reply in <?php echo $topic_title; ?></title>
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
  <h1>Post Your Reply in <?php echo $topic_title; ?></h1>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <p><label for="post_text">Post Text:</label><br>
    <textarea id="post_text" name="post_text" rows="8" cols="40"></textarea>
    <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
    <button type="submit" name="submit" value="submit">Add Post</button>
  </form>
  <script>
    // Initialize TinyMCE after the <textarea> is in the DOM
    tinymce.init({
      selector: '#post_text',
      height: 300,
      menubar: false,
      plugins: 'link image code',
      toolbar: 'undo redo | bold italic | alignleft aligncenter | code'
    });
  </script>
</body>
</html>
<?php
      //free result
      mysqli_free_result($verify_res);

      //close connection to MySQL
      mysqli_close($mysqli);
   }

} else if ($_POST) {
      //check for required items from form
      if ((!$_POST['post_text'])) {
          exit;
      }

      $stmt = $mysqli->prepare(
  "INSERT INTO forum_posts
     (topic_id, post_text, post_create_time, post_owner)
   VALUES
     (?, ?, NOW(), ?)"
);

$stmt->bind_param(
  "iss",
  $_POST['topic_id'],
  $_POST['post_text'],
  $_SESSION['curr_username']
);

$stmt->execute();

      //close connection to MySQL
      mysqli_close($mysqli);

      //redirect user to topic
      header("Location: showtopic.php?topic_id=".$_POST['topic_id']);
      exit;
}
?>

