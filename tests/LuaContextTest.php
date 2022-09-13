<?php 

namespace Silicon\Tests;

use Silicon\Exception\SiliconCPUTimeoutException;
use Silicon\Exception\SiliconLuaSyntaxException;
use Silicon\Exception\SiliconMemoryExhaustionException;
use Silicon\Exception\SiliconRuntimeDebugBreakpointException;
use Silicon\Exception\SiliconRuntimeException;
use Silicon\SiliconConsole;
use Silicon\LuaContext;
use Silicon\LuaContextOptions;

class LuaContextTest extends \PHPUnit\Framework\TestCase
{
    private function createContext(?LuaContextOptions $options = null) : LuaContext
    {
        if (!$options) $options = new LuaContextOptions();
        $context = new LuaContext($options);

        return $context;
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(LuaContext::class, $this->createContext());
    }

    public function testEvalConsle()
    {   
        $luactx = $this->createContext();
        
        // scalarish
        $luactx->eval('console.log("Foo")');
        $luactx->eval('console.log("Bar", 1, 2)');
        $luactx->eval('console.log(true, false)');

        // arrays
        $luactx->eval('console.log({"A", "B"})');
        $luactx->eval('console.log({"A", {1, 2, 3}, "C"})');

        // log levels
        $luactx->eval('console.warn("W")');
        $luactx->eval('console.error("E")');

        $logs = $luactx->console()->all();

        $this->assertEquals('string("Foo")', $logs[0][1]);
        $this->assertEquals('string("Bar"), int(1), int(2)', $logs[1][1]);
        $this->assertEquals('bool(true), bool(false)', $logs[2][1]);
        $this->assertEquals(
'[2]{
  1: string("A")
  2: string("B")
}', $logs[3][1]);

$this->assertEquals(
'[3]{
  1: string("A")
  2: [3]{
    1: int(1)
    2: int(2)
    3: int(3)
  }
  3: string("C")
}', $logs[4][1]);


        $this->assertEquals(SiliconConsole::LOG_TYPE_INFO, $logs[0][0]);
        $this->assertEquals(SiliconConsole::LOG_TYPE_WARNING, $logs[5][0]);
        $this->assertEquals(SiliconConsole::LOG_TYPE_ERROR, $logs[6][0]);
    }

    public function testEvalSyntaxError()
    {
        $this->expectException(SiliconLuaSyntaxException::class);
        $luactx = $this->createContext();
        $luactx->eval('wsadasdo asd")');
    }

    public function testEvalUnavailableCall()
    {
        $this->expectException(SiliconRuntimeException::class);
        $luactx = $this->createContext();
        $luactx->eval('os.exit()');
    }

    public function testEvalCPULimit()
    {
        $this->expectException(SiliconCPUTimeoutException::class);
        $opt = new LuaContextOptions();
        $opt->CPUTimeLimit = 0.5;
        $luactx = $this->createContext($opt);
        $luactx->eval('while true do end');
    }

    public function testEvalMemoryLimit()
    {
        $this->expectException(SiliconMemoryExhaustionException::class);
        $opt = new LuaContextOptions();
        $opt->memoryLimit = 1024 * 1024;
        $luactx = $this->createContext($opt);
        $luactx->eval('local x = "x"; while true do x = x .. x; end');
    }

    public function testNotEnoughMemory()
    {
        $this->expectException(SiliconMemoryExhaustionException::class);
        $opt = new LuaContextOptions();
        $opt->memoryLimit = 42;
        $luactx = $this->createContext($opt);
    }

    public function testCallFunction()
    {
        $luactx = $this->createContext();
        $code = <<<'LUA'
function twice(val)
    return val * 2
end
function add(a, b)
    return a + b
end
LUA;    
        // load a few function 
        $luactx->eval($code);

        $this->assertEquals([10], $luactx->invokeFunction('twice', 5));
        $this->assertEquals([5], $luactx->invokeFunction('add', 2, 3));
    }

    public function testBuildPattern()
    {
        $luactx = $this->createContext();
        $code = <<<'LUA'
function build(holder)
    holder.push(1)
    holder.push(2)
    holder.push(3)
end
LUA;    
        $internalArr = [];

        // load a few function 
        $luactx->eval($code);
        $luactx->invokeFunction('build', [
            'push' => $luactx->lambda(function($v) use(&$internalArr) {
                $internalArr[] = $v;
            })
        ]);

        $this->assertEquals([1, 2, 3], $internalArr);
    }

    public function testDebug()
    {
        $luactx = $this->createContext();
        $code = <<<'LUA'
debug('foo')
LUA;    
        try {
            $luactx->eval($code);
        } catch (SiliconRuntimeDebugBreakpointException $e) {
            $this->assertEquals(['foo'], $e->getBreakpointValues());
        }

        $this->assertEquals('string("foo")', $luactx->console()->all()[0][1]);

        // test with multiple arguments
        $code = <<<'LUA'
debug('foo', 'bar', 1, 2, 3)
LUA;    
        try {
            $luactx->eval($code);
        } catch (SiliconRuntimeDebugBreakpointException $e) {
            $this->assertEquals(['foo', 'bar', 1, 2, 3], $e->getBreakpointValues());
        }

        $this->assertEquals('string("foo"), string("bar"), int(1), int(2), int(3)', $luactx->console()->all()[1][1]);
    }
}
