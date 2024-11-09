<?php
declare (strict_types = 1);
require "base.php";

class Database
{
    private $con = null;

    private $db_uri  = null;
    private $db_user = null;
    private $db_pass = null;

    /**
     * @var PDOStatement|null
     */
    private $get_perm_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $get_tokens_stmt = null;

    public function __construct(PDO | null $conn = null)
    {
        $db_uri  = getenv("DB_URI");
        $db_user = getenv("DB_USER");
        $db_pass = getenv("DB_PASS");
        // This variable is meant to be Null, but is available as an argument purely to facilitate testing.
        $this->con = $conn ?? new PDO($db_uri, $db_user, $db_pass);

        // Preparing statements
        $get_perm_stmt         = $this->con->prepare("SELECT access FROM file_access WHERE file_id=:file AND user_id=:user");
        $this->get_tokens_stmt = $this->con->prepare("SELECT token,label FROM token WHERE user_id=:user");
    }

    public function __destruct()
    {
        $this->con = null;
    }

    /**
     * Returns the level of access the user has for a given file.
     *
     * Returns an int representing the level of access.
     *
     * @phpstan-impure
     */
    public function getPermission(string $file_id, string $user_id): int
    {
        $this->get_perm_stmt->bindParam(':file', $file_id);
        $this->get_perm_stmt->bindParam(':user', $user_id);
        $this->get_perm_stmt->execute();

        // Default value
        // TODO: Make this configurable by the admin
        $level = 0;
        foreach ($this->get_perm_stmt as $row) {
            $lvl = $row['access'];
            // If multiple access levels defined, take the highest
            if ($lvl > $level) {
                $level = $lvl;
            }
        }
        return $level;
    }

    /**
     * Get all access tokens generated for the user
     */
    public function getTokens(string $user_id): array
    {
        $this->get_tokens_stmt->bindParam(":user", $user_id);
        $this->get_tokens_stmt->execute();

        /**
         * @var Token[]
         */
        $tokens = [];
        foreach ($this->get_tokens_stmt as $row) {
            $tokens[] = new Token($row['token'], $row['label']);
        }

        return $tokens;
    }
}
