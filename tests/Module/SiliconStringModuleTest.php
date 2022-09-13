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

    public function testEvalKFloor()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return 
    string.kfloor(123),
    string.kfloor(1234),
    string.kfloor(12345),
    string.kfloor(123456),
    string.kfloor(1234567),
    string.kfloor(12345678),
    string.kfloor(123456789),
    string.kfloor(1234567890)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals('123', $result[0]);
        $this->assertEquals('1K', $result[1]);
        $this->assertEquals('12K', $result[2]);
        $this->assertEquals('123K', $result[3]);
        $this->assertEquals('1M', $result[4]);
        $this->assertEquals('12M', $result[5]);
        $this->assertEquals('123M', $result[6]);
        $this->assertEquals('1B', $result[7]);
    }

    public function testEvalHumanBytes()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
return 
    string.humanbytes(123),
    string.humanbytes(1234),
    string.humanbytes(12345),
    string.humanbytes(123456),
    string.humanbytes(1234567),
    string.humanbytes(12345678),
    string.humanbytes(123456789),
    string.humanbytes(1234567890)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals('123B', $result[0]);
        $this->assertEquals('1.21KB', $result[1]);
        $this->assertEquals('12.06KB', $result[2]);
        $this->assertEquals('120.56KB', $result[3]);
        $this->assertEquals('1.18MB', $result[4]);
        $this->assertEquals('11.77MB', $result[5]);
        $this->assertEquals('117.74MB', $result[6]);
        $this->assertEquals('1.15GB', $result[7]);
    }

}
