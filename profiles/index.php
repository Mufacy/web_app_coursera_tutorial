<?php
    include_once "./PDO.php";

    $databaseService = new databaseService();
    $conn = $databaseService->getConnection();

    $query = "select Name, Headline from users join profile ON users.user_id = profile.user_id";

    $stmt = $conn->prepare($query);

    $stmt->execute();
?>
<!DOCHTML>
<html>
<head> </head>

<body>
    <h1>Mohamad Mouaz Al Midani's Resume Registry</h1>
    <?php
        if (!isset($_SESSION['userId']))
        {
            echo('<a href="login.php">Please log in</a>\n');
        }
    ?>
    <table border="1">
    <?php
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo ("<tr>");
            echo ("<td>".$row['Name']."</td>");
            echo ("<td>".$row['Headline']."</td>");
            if (isset($_SESSION['userId']))
            {
                echo ('<td> <a href="Edit.php?profile_id="   </a>
                            <a href="Delete.php?profile_id=" </a>' . "</td>");   
            }
            echo ("</tr>");
        }

        if (isset($_SESSION['userId']))
        {
            echo ('<a href="add.php" </a>');
        }
    ?>
    </table>
</body>
</html>