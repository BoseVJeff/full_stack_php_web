<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require "src/api/base.php";

final class TokenTest extends TestCase {
    public function testTokenWithoutLabel() {
        /**
         * @var Token
         */
        $token=Token::generate();
        // Check that the token genearted has the correct prefix i.e. `tfs-`
        $this->assertStringStartsWith('tfs-',$token->token);
        // Check that no label is set by default
        $this->assertNull($token->label);
    }
    
    public function testTokenWithLabel() {
        /**
         * @var Token
         */
        $token=Token::generate("test-label");
        // Check that the token genearted has the correct prefix i.e. `tfs-`
        $this->assertStringStartsWith('tfs-',$token->token);
        // Check that the label is defined correctly
        $this->assertSame($token->label,"test-label");
    }
}

?>