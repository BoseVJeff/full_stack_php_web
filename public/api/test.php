<?php
require "../../src/api/base.php";

header("Content-Type: application/json");

// echo "<br>Testing Database connection...";

// if($conn==null) {
//     echo "<br>Error connecting to database!";
// } else {
//     echo "<br>Connection established! Closing connection...";
//     $conn=null;
//     echo "<br>Connection closed!";
// }

$db=new Database();
$db=null;

$data=["db_connection"=>"success", "db_dispose"=> "success"];

echo json_encode($data);
?>