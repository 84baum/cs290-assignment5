<?php
session_start();
function deleteAll($mysqliConn) {
    $sql = 'TRUNCATE TABLE vInventory';
    $stmt = $mysqliConn->prepare($sql);
    if($stmt === false) {
      trigger_error("Incorrect SQL");
    }
    $stmt->execute();
    $stmt->close();
}
function deleteVideo($mysqliConn, $removeVideoID) {
    $sql = 'DELETE FROM vInventory WHERE id = ?';    
    $stmt = $mysqliConn->prepare($sql);
    if($stmt === false) {
       trigger_error('Incorrect SQL');
    }
    $stmt->bind_param('i',$removeVideoID);
    $stmt->execute();
    $stmt->close();        
}
function rentVideo($mysqliConn, $rentVideoID) {    
    $rented = 0;
    $sql = 'SELECT rented FROM vInventory WHERE id = ?';   
    $stmt = $mysqliConn->prepare($sql);
    if($stmt === false) {
       trigger_error('Incorrect SQL');
    }
    $stmt->bind_param('i',$rentVideoID);
    $stmt->execute();
    $results = $stmt->get_result();
    $row = $results->fetch_assoc();
    if ($row['rented'] === 0) {
        $rented = 1;
    }
    $stmt->close();
    
    $sql = 'UPDATE vInventory SET rented = ? WHERE id = ?';   
    $stmt = $mysqliConn->prepare($sql);
    if($stmt === false) {
       trigger_error('Incorrect SQL');
    }
    $stmt->bind_param('ii',$rented, $rentVideoID);
    $stmt->execute();
    $stmt->close();
}
function filterVideoByCategory($mysqliConn) {    
    $sql = 'SELECT DISTINCT(category) FROM vInventory';   
    $stmt = $mysqliConn->prepare($sql);
    if($stmt === false) {
       trigger_error('Incorrect SQL');
    }
    $stmt->execute();
    $results = $stmt->get_result();
    echo '<form action="http://web.engr.oregonstate.edu/~picottes/as5/p5.php" method="post">';
    echo '<p>Filter By Category - Currently Viewing: ';
    if (!isset($_SESSION['cateFilter']) ||  $_SESSION['cateFilter'] === "allVideos") {
        echo 'All Videos<p>';
    }
    else {
        echo $_SESSION['cateFilter'] . '<p>';
    }
    echo '<select name="filter">';
    echo '<option value="allVideos">All Videos</option>';
    while ($row = $results->fetch_assoc()) {
          echo '<option value="' . $row['category'] . '">' . $row['category'] . '</option>';
    }
    echo '<input type="submit">';
    echo '</select>';
    echo '</form>';
    $stmt->close();    
}
function rentalStatusTextOuput($numericStatus) {
    $textReplace = 'Rented';
    if ($numericStatus === 1) {
        $textReplace = 'Available';
    }
    return $textReplace;
}
function makeTable($mysqliConn, $filterBy) {
    if (isset($filterBy) && $filterBy != 'allVideos') {
        $sql = 'SELECT id, name, category, length, rented FROM vInventory WHERE category = ?';
        $stmt = $mysqliConn->prepare($sql);
        if($stmt === false) {
           trigger_error('Incorrect SQL');
        }
        $stmt->bind_param('s', $filterBy);
    }
    else {
        $sql = 'SELECT id, name, category, length, rented FROM vInventory';
        $stmt = $mysqliConn->prepare($sql);
        if($stmt === false) {
            trigger_error("Incorrect SQL");
        } 
    }
    $stmt->execute();
    $results = $stmt->get_result();
    echo '<form action="http://web.engr.oregonstate.edu/~picottes/as5/p5.php" method="post">';
    echo '<p><h2>Video Inventory</h2> <p> <table border="1">'; 
    echo '<tr><td> Name <td> Category <td> Length <td> Checked In';
    while ($row = $results->fetch_assoc()) {
        $textRentalStatus = rentalStatusTextOuput($row['rented']);
        echo '<tr><td>' . $row['name'] . '<td>' . $row['category'] . '<td>' . $row['length']
               . '<td>' . $textRentalStatus . 
               '<td><button name="remove" type="submit" value=' . $row['id'] . '>Remove
               </button>
               <td><button name="rent" type="submit" value=' . $row['id'] . '>Rent/Return
               </button>';
    }
    echo '</table>';
    echo '</form>';
    $stmt->close();
}
?>