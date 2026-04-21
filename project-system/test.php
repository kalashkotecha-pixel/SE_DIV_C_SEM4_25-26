<?php
$conn = new mysqli('localhost', 'root', '', 'project_management');
if ($conn->connect_error) {
    echo "Connection FAILED: " . $conn->connect_error;
} else {
    echo "Connection SUCCESS!<br>";
    $result = $conn->query("SELECT name, email FROM students");
    echo "Students in database:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['name'] . " (" . $row['email'] . ")<br>";
    }
}
?>