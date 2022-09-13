<?php 

namespace Silicon\Tests\Module;

use Silicon\Exception\SiliconCPUTimeoutException;
use Silicon\Exception\SiliconLuaSyntaxException;
use Silicon\Exception\SiliconMemoryExhaustionException;
use Silicon\Exception\SiliconRuntimeException;
use Silicon\SiliconConsole;
use Silicon\LuaContext;
use Silicon\LuaContextOptions;

class SiliconStringModuleTest extends \PHPUnit\Framework\TestCase
{
    private function createContext(?LuaContextOptions $options = null) : LuaContext
    {
        if (!$options) $options = new LuaContextOptions();
        $context = new LuaContext($options);

        return $context;
    }

    public function testStandardFunctionsAvailable()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return string.len('hello world')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(11, $result[0]);
    }

    public function testEvalExplode()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return string.explode('hello world', ' ')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['hello', 'world'], $result[0]);
    }

    public function testEvalImplode()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return string.implode({'hello', 'world'}, ' ')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals('hello world', $result[0]);
    }

    public function testEvalTrim()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return string.trim(' hello world ')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals('hello world', $result[0]);
    }

    public function testEvalReplace()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return string.replace('hello world', 'world', 'universe')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals('hello universe', $result[0]);
    }

}
