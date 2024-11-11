<?php declare (strict_types = 1);

require_once "base.php";
require_once "db.php";

// The directory to upload the users' files.
//
// Note that each user will additionally have a folder of their name generated.
$uploads_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";

class User
{
    /**
     * @var Database|null The database that will be used to store file metadata
     */
    private Database|null $db;

    /**
     * @var int The user that is accessing the file
     */
    public int $id;

    /**
     * @var string The user's public name
     */
    public string $name;

    /**
     * @var string|null The user's email if available
     */
    public string|null $email;

    /**
     * @var Token[]
     */
    public array $tokens = [];

    /**
     * @param int $user_id
     * @param string $name
     * @param string|null $email
     * @param Database|null $database
     */
    public function __construct(int $user_id, string $name, ?string $email, Database | null $database = null)
    {
        $this->id    = $user_id;
        $this->name  = $name;
        $this->email = $email;
        $this->db    = $database ?? new Database();
    }

    public function __destruct()
    {
        $this->db = null;
    }

    /**
     * @param string $user_name
     * @param string $password
     * @param string|null $email
     * @param Database|null $database
     * @return User|null
     */
    public static function createUser(string $user_name, string $password, ?string $email = null, Database | null $database = null): User | null
    {
        $db = $database ?? new Database();
        return $db->createUser($user_name, $password, $email);
    }

    /**
     * @param string $username
     * @param string $password
     * @param Database|null $database
     * @return User|null
     */
    public static function getUser(string $username, string $password, Database | null $database = null): User | null
    {
        $db = $database ?? new Database();
        return $db->getUser($username, $password);
    }

    public static function getTokenUser(string $token, Database | null $database = null): User | null
    {
        $db = $database ?? new Database();
        return $db->getTokenUser($token);
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): bool
    {
        try {
            $this->db->setEmail($email, $this);
            $this->email = $email;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): bool
    {
        try {
            $this->db->setPassword($password, $this);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Prefer setting the variable to null to call the destructor and close the database connection opened by this class.
     */
    public function deleteUser(): bool
    {
        try {
            $this->db->deleteUser($this);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function createToken(?string $label = null): Token | null
    {
        try {
            $tok = $this->db->createToken($this, $label);
            if ($tok) {
                $this->tokens[] = $tok;
                return $tok;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTokens(): array
    {
        try {
            $this->tokens = $this->db->getTokens($this);
            return $this->tokens;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Returns the uploaded file hash/name if successful, `null` if not.
     */
    public function addFile(string $file, ?string $original_name = null, ?int $size = null, ?string $mime_type = null): string | null
    {
        if (! is_file($file)) {
            return null;
        }
        // TODO: Make this configurable
        $file_hash = hash_file('sha256', $file);
        // Using the timestamp as the OG name if its not available.
        // This may happen when uploading from a buffer, etc.
        // See https://stackoverflow.com/a/5322309 for ISO timestamp
        $file_og_name = $original_name ?? time();
        // In bytes
        $file_size = $size ?? filesize($file);
        // Requires `fileinfo` extension to be enabled
        $file_mimetype = $mime_type ?? mime_content_type($file);

        if (! is_dir($this->name)) {
            mkdir($this->name, 0777, true);
        }

        $file_path = $this->name . "/" . $file_hash;

        try {
            move_uploaded_file($file, $file_path);

            $this->db->addFileMetadata($this, $file_hash, $file_og_name, $file_size, $file_mimetype);

            return $file_hash;
        } catch (Exception $e) {
            echo $e;
            return null;
        }
    }

    public function getAccessLevel(string $filename, ?Token $token = null): int
    {
        try {
            $ownerId = $this->db->getFileOwnerId($filename);
            if ($ownerId == $this->id) {
                return Permission::own()->level;
            }

            $file_access_level = $this->db->getPermission($filename, $this->id);
            $access_level      = $file_access_level;
            if ($token != null) {
                $token_access_level = $this->db->getTokenPermission($token->token, $this);
                // If both are defined, overall access level is the intersection (lower) of the two
                $access_level = min($file_access_level, $token_access_level);
            }
            return $access_level;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function toJson(): array
    {
        return [
            "id"    => $this->id,
            "name"  => $this->name,
            "email" => $this->email,
        ];
    }
}
