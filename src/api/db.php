<?php
declare (strict_types = 1);
require_once "base.php";
require_once "user.php";
// require_once "../utils/CustomException.php";
// require_once "CustomException.php";

class Database
{
    /**
     * @var mixed
     */
    private $con = null;

    /**
     * @var mixed
     */
    private $db_uri = null;

    /**
     * @var mixed
     */
    private $db_user = null;

    /**
     * @var mixed
     */
    private $db_pass = null;

    /**
     * @var PDOStatement|null
     */
    private $get_perm_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $token_perm_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $token_user_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $get_tokens_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $create_user_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $latest_created_user_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $get_user_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $delete_user_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $set_email_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $set_pass_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $set_token_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $add_file_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $file_own_stmt = null;

    /**
     * @var PDOStatement|null
     */
    private $file_meta_stmt = null;

    /**
     * The last exception that was seen by this class.
     */
    public ?PDOException $last_exception = null;

    /**
     * @param PDO|null $conn
     */
    public function __construct(PDO | null $conn = null)
    {
        $db_uri  = getenv("DB_URI");
        $db_user = getenv("DB_USER");
        $db_pass = getenv("DB_PASS");
        // This variable is meant to be Null, but is available as an argument purely to facilitate testing.
        $this->con = $conn ?? new PDO($db_uri, $db_user, $db_pass);

        // Preparing statements
        $this->get_perm_stmt            = $this->con->prepare("SELECT access FROM file_access WHERE file_id=:file AND user_id=:user");
        $this->token_perm_stmt          = $this->con->prepare("SELECT perm_level FROM token WHERE token=:token AND user_id=:id");
        $this->token_user_stmt          = $this->con->prepare("SELECT token.user_id, user.name, user.email FROM token JOIN user ON token.user_id = user.id WHERE token.token=:token");
        $this->get_tokens_stmt          = $this->con->prepare("SELECT token,label FROM token WHERE user_id=:user");
        $this->set_token_stmt           = $this->con->prepare("INSERT INTO token (token, label, user_id) VALUES (:token,:label,:id)");
        $this->create_user_stmt         = $this->con->prepare("INSERT INTO user (name, password, email) VALUES (:name, :password, :email)");
        $this->latest_created_user_stmt = $this->con->prepare("SELECT id, name, password, email, created_at FROM user ORDER BY created_at DESC LIMIT 1");
        // Matching by name only as that is the only public identifier
        $this->get_user_stmt    = $this->con->prepare("SELECT id, name, password, email, created_at FROM user WHERE name=:name LIMIT 1");
        $this->delete_user_stmt = $this->con->prepare("DELETE FROM user WHERE id=:id");
        $this->set_email_stmt   = $this->con->prepare("UPDATE user SET email=:email WHERE id=:id");
        $this->set_pass_stmt    = $this->con->prepare("UPDATE user SET password=:password WHERE id=:id");
        $this->add_file_stmt    = $this->con->prepare("INSERT INTO file (name, user_id, original_name, size, mimetype) VALUES (:name, :id, :orig, :size, :mime)");
        $this->file_own_stmt    = $this->con->prepare("SELECT user_id FROM file WHERE name=:file");
        $this->file_meta_stmt   = $this->con->prepare("SELECT id, name, user_id, upload, original_name, size, mimetype FROM file WHERE name=:file AND user_id=:user");
    }

    public function __destruct()
    {
        $this->con = null;
    }

    public function errorInfo()
    {
        return $this->con->errorInfo();
    }

    public function getLastError()
    {
        return $this->last_exception;
    }

    public static function hashPassword(string $password): string
    {
        // TODO: Bcrypt is susceptible to brute-force attacks
        // To prevent this, consider peppering using `hash_hmac` or similar
        // Ref: https://www.php.net/manual/en/function.password-hash.php#124138
        $hash_pass = password_hash($password, PASSWORD_BCRYPT);

        return $hash_pass;
    }

    /**
     * Creates a user entry in the database.
     *
     * Returns the created user object if it succeeds.
     *
     * Returns null if the user already exists.
     *
     * @param string $user_name The public username for this account
     * @param string $password The password for this account
     * @param ?string $email The email associated with this account
     */
    public function createUser(string $user_name, string $password, ?string $email): User | null
    {
        $this->create_user_stmt->bindParam(':name', $user_name, PDO::PARAM_STR);
        // TODO: Bcrypt is susceptible to brute-force attacks
        // To prevent this, consider peppering using `hash_hmac` or similar
        // Ref: https://www.php.net/manual/en/function.password-hash.php#124138
        $hash_pass = Database::hashPassword($password);
        $this->create_user_stmt->bindParam(':password', $hash_pass, PDO::PARAM_STR);
        $this->create_user_stmt->bindParam(':email', $email, PDO::PARAM_STR);

        try {
            $this->create_user_stmt->execute();

            // echo $this->errorInfo();

            $this->latest_created_user_stmt->execute();

            $user = null;
            foreach ($this->latest_created_user_stmt as $u) {
                $user = new User($u['id'], $u['name'], $u['email'], $this);
            }

            return $user;
        } catch (PDOException $e) {
            $this->last_exception = $e;
            if ($e->errorInfo[1] == 1062) {
                // https://dev.mysql.com/doc/mysql-errors/5.7/en/server-error-reference.html#error_er_dup_entry
                return null;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get the user with the credentails mentioned.
     *
     * Note that a successful result from this function should be treated as a valid login or equivalent.
     */
    public function getUser(string $name, string $password): User | null
    {
        $this->get_user_stmt->bindParam(":name", $name, PDO::PARAM_STR);

        try {
            $this->get_user_stmt->execute();

            $user = null;
            foreach ($this->get_user_stmt as $u) {
                if (password_verify($password, $u['password'])) {
                    $user = new User($u['id'], $u['name'], $u['email'], $this);
                } else {
                    return null;
                }
            }

            return $user;
        } catch (PDOException $e) {
            $this->last_exception = $e;
            return null;
        }
    }

    // Change password, email
    public function setEmail(?string $email, User $user): void
    {
        $this->set_email_stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $this->set_email_stmt->bindParam(":id", $user->id, PDO::PARAM_INT);

        try {
            $this->set_email_stmt->execute();
        } catch (PDOException $e) {
            $this->last_exception = $e;
            throw $e;
        }
    }

    public function setPassword(string $newPassword, User $user): void
    {
        $pass = Database::hashPassword($newPassword);
        $this->set_pass_stmt->bindParam(":password", $pass, PDO::PARAM_STR);
        $this->set_pass_stmt->bindParam(":id", $user->id, PDO::PARAM_INT);

        try {
            $this->set_pass_stmt->execute();
        } catch (PDOException $e) {
            $this->last_exception = $e;
            throw $e;
        }
    }

    public function deleteUser(User $user): void
    {
        $this->delete_user_stmt->bindParam(":id", $user->id, PDO::PARAM_INT);

        try {
            $this->delete_user_stmt->execute();
        } catch (PDOException $e) {
            $this->last_exception = $e;
            throw $e;
        }
    }

    public function createToken(User $user, ?string $label = null): Token | null
    {
        $tok = uniqid("tfs-", false);

        $this->set_token_stmt->bindParam(":token", $tok, PDO::PARAM_STR);
        $this->set_token_stmt->bindParam(":label", $label, PDO::PARAM_STR);
        $this->set_token_stmt->bindParam(":id", $user->id, PDO::PARAM_INT);

        try {
            $this->set_token_stmt->execute();
            return new Token($tok, $label);
        } catch (PDOException $e) {
            $this->last_exception = $e;
            if ($e->errorInfo[1] == 1452) {
                // https://dev.mysql.com/doc/mysql-errors/5.7/en/server-error-reference.html#error_er_no_referenced_row_2
                return null;
            } else {
                throw $e;
            }
        }

    }

    /**
     * Get all access tokens generated for the user
     */
    public function getTokens(User $user): array
    {
        $this->get_tokens_stmt->bindParam(":user", $user->id, PDO::PARAM_INT);
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

    public function addFileMetadata(User $user, string $name, string $original_name, int $size, string $mimetype): bool
    {
        $this->add_file_stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $this->add_file_stmt->bindParam(":id", $user->id, PDO::PARAM_INT);
        $this->add_file_stmt->bindParam(":orig", $original_name, PDO::PARAM_STR);
        $this->add_file_stmt->bindParam(":size", $size, PDO::PARAM_INT);
        $this->add_file_stmt->bindParam(":mime", $mimetype, PDO::PARAM_STR);

        try {
            $this->add_file_stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->last_exception = $e;
            if ($e->errorInfo[1] == 1452) {
                // https://dev.mysql.com/doc/mysql-errors/5.7/en/server-error-reference.html#error_er_no_referenced_row_2
                return false;
            } else {
                throw $e;
            }
        }
    }

    public function getFileOwnerId(string $file_id): int
    {
        $this->file_own_stmt->bindParam(":file", $file_id, PDO::PARAM_STR);

        try {
            $this->file_own_stmt->execute();

            $id = -1;
            foreach ($this->file_own_stmt as $row) {
                $id = $row['user_id'];
            }
            return $id;
        } catch (PDOException $e) {
            $this->last_exception = $e;
            return -1;
        }
    }

    public function getFileInfo(string $file_name, int $user_id): UserFile | null
    {
        $this->file_meta_stmt->bindParam(":file", $file_name, PDO::PARAM_STR);
        $this->file_meta_stmt->bindParam(":user", $user_id, PDO::PARAM_INT);

        try {
            $this->file_meta_stmt->execute();

            $file = null;
            foreach ($this->file_meta_stmt as $row) {
                $file = new UserFile($row);
            }
            return $file;
        } catch (PDOException $e) {
            $this->last_exception = $e;
            return null;
        }
    }

    /**
     * Returns the level of access the user has for a given file.
     *
     * Returns an int representing the level of access.
     *
     * @phpstan-impure
     */
    public function getPermission(string $file_id, int $user_id): int
    {
        $this->get_perm_stmt->bindParam(':file', $file_id, PDO::PARAM_STR);
        $this->get_perm_stmt->bindParam(':user', $user_id, PDO::PARAM_INT);
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
     * Returns the level of access (int) that a token has.
     */
    public function getTokenPermission(string $token, User $user): int
    {
        $this->token_perm_stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $this->token_perm_stmt->bindParam(':id', $user->id, PDO::PARAM_INT);

        try {
            $this->token_perm_stmt->execute();

            $level = 0;
            foreach ($this->token_perm_stmt as $row) {
                // Taking the highest level if the same token is defined multiple times for the same user
                $lvl = $row['perm_level'];
                if ($lvl > $level) {
                    $level = $lvl;
                }
            }
            return $level;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getTokenUser(string $token): User | null
    {
        $this->token_user_stmt->bindParam(":token", $token, PDO::PARAM_STR);

        try {
            $this->token_user_stmt->execute();
            $user = null;
            foreach ($this->token_user_stmt as $row) {
                $user = new User($row['user_id'], $row['name'], $row['email']);
            }
            return $user;
        } catch (PDOException $e) {
            return null;
        }
    }
}
