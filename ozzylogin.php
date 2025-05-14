<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="NavBar.css">
    <link rel="stylesheet" href="simple.css">
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
    <br/>
    <h3><strong>Home to 30+ Posts, All About Ozzy!</strong></h3>
    <h3>(Use user:ozzyadmin & pass:CrazyTrain!)</h3>
    <form id="Login" action="ozzy_submit_pass.php" method="post">
        <img src="ozzylogo.png" class="logo" style="height:50px; width:60px">
        Username: <input type="text" name="username" id="username" value=""><br>
        Password: <input type="password" name="password" id="password" value=""><br>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="submit" value="Login">
    </form>
    <form id="Signup" action="signup.php" method="post">
        <p>If you don't have an account yet, sign up!</p>
        Email: <input type="text" name="new_email" id="new_email" value=""><br>
        Username: <input type="text" name="new_username" id="new_username" value=""><br>
        Password: <input type="password" name="new_password" id="new_password" value=""><br>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="submit" value="submit">
    </form>
</body>