<?php 

namespace App\Tests\PJASScript;

use Silicon\SiliconConsole;

class SiliconConsoleTest extends \PHPUnit\Framework\TestCase
{
    private function createConsole() : SiliconConsole
    {
        return new SiliconConsole;
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(SiliconConsole::class, $this->createConsole());
    }

    public function testConvertToString()
    {   
        $console = $this->createConsole();
        
        // test base types
        $this->assertEquals('int(42)', $console->convertArgumentsToString(42));
        $this->assertEquals('string("foo")', $console->convertArgumentsToString('foo'));

        // test multiple
        $this->assertEquals('int(42), int(69)', $console->convertArgumentsToString(42, 69));
        $this->assertEquals('string("420"), int(69)', $console->convertArgumentsToString('420', 69));
    }

    public function testWrite()
    {   
        $console = $this->createConsole();
        $console->write(SiliconConsole::LOG_TYPE_INFO, 'Test1');
        $console->write(SiliconConsole::LOG_TYPE_WARNING, 'Test2');
        $console->write(SiliconConsole::LOG_TYPE_ERROR, 'Test3');

        $messages = $console->all();

        $this->assertEquals(SiliconConsole::LOG_TYPE_INFO, $messages[0][0]);
        $this->assertEquals(SiliconConsole::LOG_TYPE_WARNING, $messages[1][0]);
        $this->assertEquals(SiliconConsole::LOG_TYPE_ERROR, $messages[2][0]);

        $this->assertEquals('Test1', $messages[0][1]);
        $this->assertEquals('Test2', $messages[1][1]);
        $this->assertEquals('Test3', $messages[2][1]);
    }

    public function testLog()
    {   
        $console = $this->createConsole();
        $console->logInfo(69, 420);

        $messages = $console->all();
        
        $this->assertEquals(SiliconConsole::LOG_TYPE_INFO, $messages[0][0]);
        $this->assertEquals('int(69), int(420)', $messages[0][1]);
    }
}
