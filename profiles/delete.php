<?php
require_once "pdo.php";
session_start();

$databaseService = new databaseService();
$conn = $databaseService->getconnection();

if (!isset($_SESSION['name']))
{
    die("ACCESS DENIED");
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) 
{
    $sql = "DELETE FROM profile WHERE profile_id = :pid";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pid', $_POST['profile_id']);
    $stmt->execute();
    $_SESSION['success'] = 'Profile deleted ';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) )
 {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $conn->prepare("SELECT first_name, last_name, profile_id FROM profile where profile_id = :pid");
$stmt->execute(array(":pid" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) 
{
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<head> <title> Mohamad Mouaz Al Midani's Profile Delete</title> </head>
<p>Confirm: Deleting <?= htmlentities($row['first_name']) . " " . htmlentities($row['last_name'])?></p>

<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" name="delete">
<a href="index.php">Cancel</a>
</form>
