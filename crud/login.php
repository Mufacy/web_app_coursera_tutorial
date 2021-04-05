<?php
    session_start();
    include_once "./PDO.php";
    
    if ( isset($_POST["email"]) && isset($_POST["pass"]) ) 
    {
        if ( strlen($_POST["email"]) < 1 || strlen($_POST["pass"]) < 1)
        {
            $_SESSION["error"] = "Email and password are required";
            error_log ( "Failed login: " . $_SESSION["error"], 0);
            header( 'Location: login.php' ) ;
            return;
        }

        unset($_SESSION["email"]);  // Logout current user
        if ( strpos( $_POST["email"], '@') < 1 ) 
        {
            $_SESSION["error"] = "Email must have an at-sign (@)";
            error_log ( "Failed login: " . $_SESSION["error"], 0);
            header('Location: login.php');
            return;
        }

        $password = 'php123';
        if ( $_POST['pass'] == $password ) 
        {
            $_SESSION["success"] = "Logged in.";    
            $_SESSION["name"] = $_POST["email"];
            error_log ( "Logged in: " . $_POST["email"], 0);
            header('Location: index.php');
            return;
        } 
        else 
        {
            $_SESSION["error"] = "Incorrect password.";
            error_log ( "Failed login: " . $_SESSION["error"], 0);
            header( 'Location: login.php' ) ;
            return;
        }
    }
?>
<html>
<head>
<title>Mohamad Mouaz Al Midani's Automobile Tracker</title>
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
        <p>User Name: <input type="text" name="email" value=""></p>
        <p>Password: <input type="text" name="pass" value=""></p>
        <!-- password is umsi -->
        <p><input type="submit" value="Log In"><a href="index.php">Cancel</a></p>
    </form>
</body>
