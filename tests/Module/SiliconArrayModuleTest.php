<?php 

namespace Silicon\Tests\Module;

use Silicon\Exception\SiliconCPUTimeoutException;
use Silicon\Exception\SiliconLuaSyntaxException;
use Silicon\Exception\SiliconMemoryExhaustionException;
use Silicon\Exception\SiliconRuntimeException;
use Silicon\SiliconConsole;
use Silicon\LuaContext;
use Silicon\LuaContextOptions;

class SiliconArrayModuleTest extends \PHPUnit\Framework\TestCase
{
    private function createContext(?LuaContextOptions $options = null) : LuaContext
    {
        if (!$options) $options = new LuaContextOptions();
        $context = new LuaContext($options);

        return $context;
    }

    public function testEvalMerge()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
local b = {4, 5, 6}
local c = {7, 8, 9}
return array.merge(a, b, c)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $result[0]);

        // now test string keys
        $code = <<<'LUA'
local a = {a = 'A', b = 'B'}
local b = {c = 'C', d = 'D'}
local c = {e = 'E', f = 'F'}
return array.merge(a, b, c)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'], $result[0]);

        // now test key override 
        $code = <<<'LUA'
local a = {a = 'A', b = 'B'}
local b = {c = 'C', d = 'D'}
local c = {e = 'E', f = 'F', b = 'BB'}
return array.merge(a, b, c)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['a' => 'A', 'b' => 'BB', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F'], $result[0]);
    }


    public function testEvalKeys()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.keys(a)
LUA;    
        $result = $luactx->eval($code);

        // lua array index starts at 1
        $this->assertEquals([1, 2, 3], $result[0]);

        // now test string keys
        $code = <<<'LUA'
local a = {a = 'A', b = 'B'}
return array.keys(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['a', 'b'], $result[0]);
    }

    public function testEvalValues()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.values(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals([1, 2, 3], $result[0]);

        // now test string keys
        $code = <<<'LUA'
local a = {a = 'A', b = 'B'}
return array.values(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['A', 'B'], $result[0]);
    }

    public function testEvalCount()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.count(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(3, $result[0]);

        // now test string keys
        $code = <<<'LUA'
local a = {a = 'A', b = 'B'}
return array.count(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(2, $result[0]);
    }

    public function testEvalColumn()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {{a = 'A', b = 'B'}, {a = 'AA', b = 'BB'}}
return array.column(a, 'a'), array.column(a, 'b')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['A', 'AA'], $result[0]);
        $this->assertEquals(['B', 'BB'], $result[1]);
    }
    

}
