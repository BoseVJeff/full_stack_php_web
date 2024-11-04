<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require "src/utils/utils.php";

final class UtilsTest extends TestCase
{
    public function testLineBreakAtEndOfLine():void
    {
        $string="Hello World";
        
        Utils::echoln($string);

        $this->expectOutputString("Hello World<br>");
    }

    public function testCheckVar() : void
    {

        // Before variable declarartion, always false
        $this->assertFalse(Utils::checkVar($a, 5));
        
        $a=5;

        // Check if equality is correctly verified after variable declaratio
        $this->assertFalse(Utils::checkVar($a, 4));
        $this->assertTrue(Utils::checkVar($a, 5));
        
        unset($a);
        
        // Should always return false after variable has been unset
        $this->assertFalse(Utils::checkVar($a, 5));
    }

    public function testSimpleRangeParsing():void
    {
        $str1="Range: bytes=0-499";
        $str2="Range: bytes=500-999";

        $this->assertSame(Utils::parseRangeHeader($str1), [[0,499]]);
        $this->assertSame(Utils::parseRangeHeader($str2), [[500,999]]);
    }
    
    public function testPartialRangeParsing():void
    {
        $str1="Range: bytes=900-";
        $str2="Range: bytes=-100";

        $this->assertSame(Utils::parseRangeHeader($str1), [[900,null]]);
        $this->assertSame(Utils::parseRangeHeader($str2), [[null,100]]);
    }
    
    public function testMultipleRangeParsing():void
    {
        $str1="Range: bytes=200-999, 2000-2499, 9500-";
        $str2="Range: bytes=0-499, -499";

        $this->assertSame(Utils::parseRangeHeader($str1), [[200, 999],[2000,2499],[9500,null]]);
        $this->assertSame(Utils::parseRangeHeader($str2), [[0, 499], [null, 499]]);
    }
}
?>
