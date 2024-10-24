<?php
declare(strict_types=1);
require "base.php";

$conn=null;

try {
    $conn=new PDO($db_uri,$db_user,$db_pass);
} catch (PDOException $e) {
    echo "Error:<br>".$e;
}
?>