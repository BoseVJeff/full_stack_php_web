<?php declare (strict_types = 1);

require_once "base.php";
require_once "db.php";
require_once "user.php";

/**
 * Class to interact with files owned by a user.
 */
class UserFile
{
    // Also user directory name
    /**
     * @var User|null The user that is accessing the file
     */
    private $user = null;

    // Also file name
    /**
     * @var string|null The ID for the file being accessed
     */
    private $fileId = null;

    /**
     * @var Database|null The database that will be used to store file metadata
     */
    private $db = null;

    /**
     * @var string The directory that files wil be uploaded to
     */
    private $uploadDir = $uploads_dir;

    public function __construct(string $file_id, User $user, Database | null $database = null)
    {
        $this->fileId = $file_id;
        $this->user   = $user;
        $this->db     = $database ?? new Database();
    }

    public function exists(): bool
    {
        // Path to be tested is upload_dir/userId/fileId
        return is_file($this->uploadDir . "/" . $this->user->name . "/" . $this->fileId);
    }

    public function __destruct()
    {
        $this->db = null;
    }
}
