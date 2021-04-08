<?php
    include_once "./PDO.php";
    session_start();


    $databaseService = new databaseService();
    $conn = $databaseService->getConnection();

    $query = "select Name, Headline from users join profile ON users.user_id = profile.user_id";

    $stmt = $conn->prepare($query);

    $stmt->execute();
?>
<!DOCHTML>
<html>
<head> <title>Mohamad Mouaz Al Midani's Resume Registry</title> </head>

<body>
    <h1>Mohamad Mouaz Al Midani's Resume Registry</h1>
    <?php
        if (isset($_SESSION['success']))
        {
            echo ('<p style="color:green;">'.$_SESSION['success']."</p>\n");
            unset($_SESSION['success']);
        }
        if (!isset($_SESSION['user_id']))
        {
            echo('<a href="login.php">Please log in</a>'. "\n");
        }
        else
        {
            echo('<a href="logout.php">Logout</a>'. "\n");
        }


        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo ("<tr>");
            echo ("<td>".$row['Name']."</td>");
            echo ("<td>".$row['Headline']."</td>");
            if (isset($_SESSION['user_id']))
            {
                echo ('<td> <a href="Edit.php?profile_id=">Edit</a> | 
                            <a href="Delete.php?profile_id=">Delete</a>' . "</td>");   
            }
            echo ("</tr>");
        }

        if (isset($_SESSION['userId']))
        {
            echo ('<a href="add.php" </a>');
        }
    ?>
    </table>

    <?php
        echo('<a href="add.php">Add New Entry </a>');
    ?>
</body>
</html>