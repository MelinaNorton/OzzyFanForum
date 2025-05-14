<?php
session_start();
if (empty($_SESSION['curr_username'])) {
    header('Location: ozzylogin.php');
    exit;
}
include_once 'db_include.php';
doDB();

$stmt = $mysqli->prepare("SELECT role FROM Users WHERE username = ?");
$stmt->bind_param('s', $_SESSION['curr_username']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

$isAdmin = ($role === 'admin');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Forum Webpage</title>
    <link rel="stylesheet" href="simple.css">
    <link rel="stylesheet" href="NavBar.css">
  </head>
  <body>
    <div class="localnav">
      <img class="logo" src="ozzylogo.png" style="height:50px; width:70px;">
        <button class="mytopics">my topics</button>
        <button class="seetopics">see topics</button>
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
        <button id="logout">logout</button>
        <?php if ($isAdmin): ?>
          <button id="adminPanel" style="background-color:lightblue;">Admin Panel</button>
        <?php endif; ?>
    </div>
  </body>
  <h3><strong>Ozzy's Fan-Forum</strong></h3>
  <div id="description">
    <p><em>New Here?</em></p>
    <p>Ozzy's Fan-Forum is a dedicated place for fans to share thoughts, debate opinions, and rank their favorite songs/albums by</p>
    <p>None other than the Prince of Darkness himself! With hard-working admins working around the clock to remove harmful/offensive posts,</p>
    <p>Ozzy's Fan-Forum also prides itself on being a safe place for discourse free from hate (or egregiously wrong opinions...)</p>
    <img src="coolozzy.jpeg" style="height:250px; width:300px">
  </div>
    <button id="seefeatures">see features</button>
    <button id="seeabout">about</button>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const seetopicsButtons = document.getElementsByClassName("seetopics");
      const seemytopicsButtons = document.getElementsByClassName("mytopics");
      const replypostButtons = document.getElementsByClassName("replypost");
      // Loop over all elements with the class 'seetopics'
      Array.from(seetopicsButtons).forEach((btn) => {
        btn.addEventListener('click', () => {
          window.location.href = "topiclist_withJS.php";
        });
      });
      Array.from(seemytopicsButtons).forEach((btn) => {
        btn.addEventListener('click', () => {
          window.location.href = "topiclistpersonal_JS.php";
        });
      });
      document.getElementById('adminPanel')?.addEventListener('click', () => {
        window.location.href = 'admin_dashboard.php';
      });
      document.getElementById('seefeatures').addEventListener('click', () => {
        window.location.href = "features.html";
      })
      document.getElementById('seeabout').addEventListener('click', () => {
        window.location.href = "about.html";
    })
    document.getElementById('logout').addEventListener('click', () => {
        window.location.href = "ozzylogout.php";
    })
    });
  </script>
  
</html>