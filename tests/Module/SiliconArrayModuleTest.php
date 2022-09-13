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
    
    public function testEvalSum()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.sum(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(6, $result[0]);
    }

    public function testEvalAverage()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.average(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(2, $result[0]);
    }

    public function testEvalMin()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.min(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(1, $result[0]);
    }

    public function testEvalMax()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.max(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(3, $result[0]);
    }

    public function testEvalMedian()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3, 4, 5, 6, 7, 8, 9}
return array.median(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(5, $result[0]);


        // now test even number of elements
        $code = <<<'LUA'
local a = {1, 2, 3, 4, 5, 6, 7, 8}
return array.median(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(4.5, $result[0]);
    }

    public function testEvalContains()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {1, 2, 3}
return array.contains(a, 1), array.contains(a, 4)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(true, $result[0]);
        $this->assertEquals(false, $result[1]);
    }


    public function testEvalHas()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {a = 'A', b = 'B'}
return array.has(a, 'a'), array.has(a, 'c')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(true, $result[0]);
        $this->assertEquals(false, $result[1]);
    }

    public function testEvalGroupBy()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local cars = {
    {name = 'S60' , brand = 'Volvo'},
    {name = 'S80' , brand = 'Volvo'},
    {name = 'XC60', brand = 'Volvo'},
    {name = 'RS6' , brand = 'Audi'},
    {name = 'RS7' , brand = 'Audi'},
    {name = 'F150', brand = 'Ford'},
    {name = 'F250', brand = 'Ford'},
}
return array.groupBy(cars, 'brand')
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals([
            'Volvo' => [
                ['name' => 'S60' , 'brand' => 'Volvo'],
                ['name' => 'S80' , 'brand' => 'Volvo'],
                ['name' => 'XC60', 'brand' => 'Volvo'],
            ],
            'Audi' => [
                ['name' => 'RS6' , 'brand' => 'Audi'],
                ['name' => 'RS7' , 'brand' => 'Audi'],
            ],
            'Ford' => [
                ['name' => 'F150', 'brand' => 'Ford'],
                ['name' => 'F250', 'brand' => 'Ford'],
            ],
        ], $result[0]);
    }

    public function testEvalFlatten()
    {   
        $luactx = $this->createContext();
        $code = <<<'LUA'
local a = {a = 'A', b = 'B', c = {d = 'D', e = 'E'}}
return array.flatten(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['a' => 'A', 'b' => 'B', 'c.d' => 'D', 'c.e' => 'E'], $result[0]);

        // test with numeric keys
        $code = <<<'LUA'
local a = {a = 'A', b = 'B', c = {'D', 'E', 'F'}}
return array.flatten(a)
LUA;    
        $result = $luactx->eval($code);

        $this->assertEquals(['a' => 'A', 'b' => 'B', 'c.1' => 'D', 'c.2' => 'E', 'c.3' => 'F'], $result[0]);
    }
}
