<?php 

namespace Silicon\Tests\Module;

use Silicon\Exception\SiliconCPUTimeoutException;
use Silicon\Exception\SiliconLuaSyntaxException;
use Silicon\Exception\SiliconMemoryExhaustionException;
use Silicon\Exception\SiliconRuntimeException;
use Silicon\SiliconConsole;
use Silicon\LuaContext;
use Silicon\LuaContextOptions;

class SiliconDateModuleTest extends \PHPUnit\Framework\TestCase
{
    private function createContext(?LuaContextOptions $options = null) : LuaContext
    {
        date_default_timezone_set('Europe/London');

        if (!$options) $options = new LuaContextOptions();
        $context = new LuaContext($options);

        return $context;
    }

    public function testEvalNow()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return date.now()
LUA;    
        $result = $luactx->eval($code);

        $this->assertIsInt($result[0]);
        $this->assertGreaterThan(0, $result[0]);
    }


    public function testEvalFormat()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return date.format(1600000000, 'Y-m-d H:i:s')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals('2020-09-13 13:26:40', $result[0]);
    }

    public function testEvalParse()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return date.parse('2020-09-13 13:26:40', 'Y-m-d H:i:s')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(1600000000, $result[0]);
    }

    public function testEvalParseWithoutFormat()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return date.parse('2020-09-13 13:26:40')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(1600000000, $result[0]);
    }   

    public function testEvalParseWithInvalidFormat()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return date.parse('2020-09-13 02:40:00', 'Y-m-d H:s')
LUA;    
        $result = $luactx->eval($code);

        $this->assertFalse($result[0]);
    }

}
