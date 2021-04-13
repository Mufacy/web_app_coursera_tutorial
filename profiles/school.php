<?php
    include_once "./PDO.php";
    $term = $_GET['term'];
    error_log("Looking up typeahead term=".$term);

    $databaseService = new databaseService();
    $conn = $databaseService->getConnection();

    $stmt = $conn->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
    $stmt->execute(array( ':prefix' => $_REQUEST['term']."%"));
    $retval = array();
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) 
    {
        $retval[] = $row['name'];
    }

    echo(json_encode($retval, JSON_PRETTY_PRINT));

?>