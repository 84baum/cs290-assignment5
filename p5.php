<?php
    session_start();
    if (!isset($_SESSION['initialVisit'])) {
        $_SESSION['initialVisit'] = 1;
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset = "UTF-8">
    <title>Picotte Assignment 5</title>
  </head>
  <body>
	<form action="http://web.engr.oregonstate.edu/~picottes/as5/p5.php" method="post">
	  <fieldset>
	  <legend>Add Movie</legend>
	  Name:
	  <input type="text" name="videoName">
	  Category:
	  <input type="text" name="videoCategory">
	  Length:
	  <input type="number" name="videoLength">
	  <input type="submit">
	  </fieldset>
	</form>
    <?php
    //ini_set('display_errors', 'On');
    include 'storedInfo.php';
    include 'tableOperations.php';
    
    $mysqliConn = new mysqli("oniddb.cws.oregonstate.edu", "picottes-db", $password, "picottes-db");
    if ($mysqliConn->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqliConn->connect_errno . ")" . $mysqliConn->connect_error;
    }
    
    if (isset($_POST['delete'])) {
        deleteAll($mysqliConn);
    }
    else if(isset($_POST['remove'])) {
        $removeVideoID = $_POST['remove'];
        deleteVideo($mysqliConn, $removeVideoID);
    }
    else if(isset($_POST['rent'])) {
        $rentVideoID = $_POST['rent'];
        rentVideo($mysqliConn, $rentVideoID);
    } 
    else if (isset($_POST['filter'])) {
        $_SESSION['cateFilter'] = $_POST['filter'];
    }   
    else {
        if ($_SESSION['initialVisit'] === 0 && !isset($_POST['videoName']) || $_POST['videoName'] == ""
            || $_POST['videoName'] === NULL) {
            echo 'Please enter a video name.';
        }
        else {
        $_SESSION['initialVisit'] = 0;
        $sql = 'INSERT INTO vInventory (name, category, length) VALUES (?,?,?)';
        $name = $_POST['videoName'];
        $category = $_POST['videoCategory'];
        $length = $_POST['videoLength'];
        $stmt = $mysqliConn->prepare($sql);
        if ($stmt === false) {
            trigger_error("Incorrect SQL");
        }
        $stmt->bind_param('ssi', $name, $category, $length);
        $stmt->execute();
        $stmt->close();
        }
    }
    filterVideoByCategory($mysqliConn);
    makeTable($mysqliConn, $_SESSION['cateFilter']);
    $mysqliConn->close();
    unset($mysqliConn);
  ?>
  <form action="http://web.engr.oregonstate.edu/~picottes/as5/p5.php" method="post">
    <button name="delete" type="submit">Delete All Videos</button>
  </form>
  </body>
</html>