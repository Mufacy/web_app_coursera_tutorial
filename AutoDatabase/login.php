<?php
    include_once "./PDO.php";
    session_start();

    if ( isset($_POST["account"]) && isset($_POST["pw"]) ) 
    {
        if ( strlen($_POST['account']) < 1 || strlen($_POST["pw"]) < 1)
        {
            $_SESSION['error'] = "Email and password are required";
            header( 'Location: login.php' ) ;
            return;
        }

        unset($_SESSION["account"]);  // Logout current user
        if ( strpos( $_POST['account'], '@') < 1 ) 
        {
            $_SESSION['error'] = "Email must have an at-sign (@)";
            header('Location: login.php');
            return;
        }

        $password = hash('md5', 'salt'+$_POST['pw']);
        if ( $_POST['pw'] == $password ) 
        {
            $_SESSION['success'] = "Logged in.";    
            $_SESSION['name'] = $_POST['email'];
            header('Location: view.php');
            return;
        } 
        else 
        {
            $_SESSION['error'] = "Incorrect password.";
            header( 'Location: login.php' ) ;
            return;
        }
    }
?>
<html>
<head>
</head>
<body style="font-family: sans-serif;">
<h1>Please Log In</h1>
<?php
    if ( isset($_SESSION["error"]) ) 
    {
        echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
        unset($_SESSION["error"]);
    }
?>
    <form method="post">
        <p>Account: <input type="text" name="account" value=""></p>
        <p>Password: <input type="text" name="pw" value=""></p>
        <!-- password is umsi -->
        <p><input type="submit" value="Log In">
        <a href="app.php">Cancel</a></p>
    </form>
</body>
