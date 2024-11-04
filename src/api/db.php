<?php
declare(strict_types=1);
require "base.php";

class Database {
    private $con=null;

    private $db_uri=null;
    private $db_user=null;
    private $db_pass=null;

    private $conn=null;

    function __construct(PDO $conn=null) {
        $db_uri=getenv("DB_URI");
        $db_user=getenv("DB_USER");
        $db_pass=getenv("DB_PASS");
        // This variable is meant to be Null, but is available as an argument purely to facilitate testing.
        $this->con=$this->conn??new PDO($db_uri,$db_user,$db_pass);
    }

    function __destruct() {
        $this->con=null;
    }
}
?>