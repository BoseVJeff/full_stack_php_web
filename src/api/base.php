<?php
$test="Hello World!";

$db_uri=getenv("DB_URI");
$db_user=getenv("DB_USER");
$db_pass=getenv("DB_PASS");

class Database {
    private $con=null;

    function __construct(PDO $conn=null) {
        // This variable is meant to be Null, but is available as an argument purely to facilitate testing.
        $this->con=$conn??new PDO($db_uri,$db_user,$db_pass);
    }

    function __destruct() {
        $this->con=null;
    }
}
?>