<?php
    include_once "./PDO.php";
    session_start();
    $databaseService = new databaseService();
    $conn = $databaseService->getConnection();

    if(isset($_POST['email']) && isset($_POST['pass']))
    {
        if(!isset($_POST['email']) || !isset($_POST['pass']))
        {
            $_SESSION['error'] = "Invalid email address";
            header("Location: login.php");
            return;
        }

        if (strpos($_POST['email'], '@') < 1)
        {
            $_SESSION['error'] = "Invalid email address";
            header("Location: login.php");
            return;
        }

        $email = $_POST['email'];

        $pass = $_POST['pass'];
        $salt = 'XyZzy12*_';
        $check = hash('md5', $salt.$pass);

        $query = "select user_id, name from users where email = :em and password = :pw";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':em', $email);
        $stmt->bindParam(':pw', $check);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (row !== false)
        {
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
   
            // Redirect the browser to index.php
            header("Location: index.php");
            return;
        }
        else
        {
            $_SESSION['error'] = "Invalid password";
            header("Location: login.php");
            return;
        }
    }
?>
<!DOCTYPE html>
<html>
<head> <title>Chuck Severance's Login Page</title> 
</head>
<body>
<h1> Please Log In </h1>
    <?php
        if (isset($_SESSION['error']))
        {
            echo('<p style="color:red;"> '.$_SESSION['error'] . "</p>\n");
            unset($_SESSION['error']);
        }
    ?>
<form method="post" action="login.php">
    Email:<input name="email" id="email" type="text"/>
    <p>
    Password:<input name="pass" id="pass" type="password"/>
    </p>
    <input type="submit" onclick="return doValidate();"  value="Log in" />
    <input type="submit" name="cancel" value="Cancel">
</form>

<script>
function doValidate() 
{
    console.log('Validating...');
    try 
    {
        addr = document.getElementById('email').value;
        pw = document.getElementById('pass').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") 
        {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) 
        {
            alert("Invalid email address");
            return false;
        }
        return true;
    } 
    catch(e) 
    {
        return false;
    }
    return false;
}
</script>
</body>
</html>