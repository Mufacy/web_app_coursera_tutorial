<?php
require_once "pdo.php";
session_start();
?>
<html>
<head>
    <title> Mohamad Mouaz Al Midani's CRUD</title>
</head>
<body>
<div class="container">
<h2>Welcome to the Automobiles Database</h2>
<?php
if ( isset($_SESSION['error']) ) 
{
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) 
{
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}
if (!isset($_SESSION['name']))
{ ?>
<p><a href="login.php">Please log in</a></p>
<p>Attempt to <a href="add.php">add data</a> without logging in</p>
<?php 
} 
else
{
echo('<table border="1">'."\n");
?>

<tr>
    <td style="font-weight:bold">Make</td>
    <td style="font-weight:bold">Model</td>
    <td style="font-weight:bold">Year</td>
    <td style="font-weight:bold">Mileage</td>
    <td style="font-weight:bold">Action</td>
</tr>

<?php
$stmt = $pdo->query("SELECT auto_id, make, year, mileage, model FROM autos");
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo "<tr><td>";
    echo(htmlentities($row['make']));
    echo("</td><td>");
    echo(htmlentities($row['model']));
    echo("</td><td>");
    echo(htmlentities($row['year']));
    echo("</td><td>");
    echo(htmlentities($row['mileage']));
    echo("</td><td>");
    echo('<a href="edit.php?autos_id='.$row['auto_id'].'">Edit</a> / ');
    echo('<a href="delete.php?autos_id='.$row['auto_id'].'">Delete</a>');
    echo("</td></tr>\n");
}
?>
</table>
<a href="add.php">Add New Entry</a>
<p>
<a href="logout.php">Logout</a>
</p>
<?php } ?>