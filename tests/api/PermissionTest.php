<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require "src/api/base.php";

final class PermissionTest extends TestCase {
    public static function permProvider():array {
        // User permission, Action Permission, is Allowed
        return [
            [Permission::no_access(), Permission::no_access(), true],
            [Permission::no_access(), Permission::read_only(), false],
            [Permission::no_access(), Permission::read_write(), false],
            
            [Permission::read_only(), Permission::no_access(), true],
            [Permission::read_only(), Permission::read_only(), true],
            [Permission::read_only(), Permission::read_write(), false],
            
            [Permission::read_write(), Permission::no_access(), true],
            [Permission::read_write(), Permission::read_only(), true],
            [Permission::read_write(), Permission::read_write(), true],
        ];
    }
    
    public function testDefaultLevels() {
        $this->assertSame((Permission::no_access())->level,0);
        $this->assertSame((Permission::read_only())->level,10);
        $this->assertSame((Permission::read_write())->level,100);
    }

    #[DataProvider('permProvider')]
    public function testAccessChecks(Permission $user_perm, Permission $action_perm, bool $is_allowed) {
        $this->assertSame($user_perm->actionAllowed($action_perm),$is_allowed);
    }
}

?>