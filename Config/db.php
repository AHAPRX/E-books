<?php
$password="";
$database="finalbook1";
$server="localhost";
$username="root";

$conn = new mysqli($server,$username,$password,$database);

if($conn -> connect_error){
    die("Connection Error" . $conn -> connect_error);
}
?>