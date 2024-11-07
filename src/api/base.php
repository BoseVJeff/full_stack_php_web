<?php declare(strict_types=1);

// The directory to upload the users' files.
// 
// Note that each user will additionally have a folder of their name generated.
$uploads_dir="../uploads/";

/**
 * An enum-ish class with types of permissions and their values.
 * A higher value idicates a greater amount of access.
 * 
 * A permission with a higher value implies all permissions with lower values too.
 * i.e. A user with permission 100 can also perform 50-level actions.
 * 
 * Note that the gaps exist to allow for more permissions in the future.
 */
class Permission
{
    public int $level;

    function __construct(int $permission_level)
    {
        $this->level=$permission_level;
    }

    /**
     * No access allowed.
     */
    static function no_access():Permission
    {
        return new Permission(0);
    }
    
    /**
     * Only read access allowed.
     */
    static function read_only():Permission
    {
        return new Permission(10);
    }
    
    /**
     * Read and write access allowed.
     */
    static function read_write():Permission
    {
        return new Permission(100);
    }

    /**
     * Returns true only if an action requiring `action_permission` can be performed
     * by a user having `$this` permission.
     * 
     * In practice, this is valudated by checking that 
     */
    function actionAllowed(Permission $action_permission):bool
    {
        return ($this->level) >= ($action_permission->level);
    }
}

class Token {
    public string $token;
    public ?string $label;

    function __construct(string $token, ?string $label) {
        $this->token=$token;
        $this->label=$label;
    }

    public static function generate(?string $label=null):Token {
        $tok=uniqid("tfs-",false);
        return new Token($tok,$label);
    }
}
?>
