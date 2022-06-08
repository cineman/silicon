<?php

namespace Silicon;

use ClanCats\Container\Container;

class SiliconRunner
{   
    /**
     * An instance of the DI container
     */
    private Container $container;

    /**
     * Script runner constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Creates and returns a LUA context
     */
    public function boot(LuaContextOptions $options, ?SiliconConsole $console = null) : LuaContext
    {
        return new LuaContext($options, $console, $this->container);
    }
}
