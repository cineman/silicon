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
     * A runner can have a default preload cache injected into each context if the 
     * options have no cache assigned to it.
     */
    private ?SiliconPreloadCache $defaultPreloadCache = null;

    /**
     * Script runner constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Allows setting a default preloading cache for the contexts
     */
    public function setDefaultPreloadCache(SiliconPreloadCache $cache) : void
    {
        $this->defaultPreloadCache = $cache;
    }
    
    /**
     * Creates and returns a LUA context
     */
    public function boot(?LuaContextOptions $options = null, ?SiliconConsole $console = null) : LuaContext
    {
        if (is_null($options)) {
            $options = new LuaContextOptions();
        }

        if (is_null($options->preloadCache)) {
            $options->preloadCache = $this->defaultPreloadCache;
        }

        return new LuaContext($options, $console, $this->container);
    }

    /**
     * Runs the given lua code and returns a silicon runner result object
     */
    public function run(string $code, ?LuaContextOptions $options = null, ?SiliconConsole $console = null) : SiliconRunnerResult
    {
        $result = new SiliconRunnerResult;

        $starttime = microtime(true);
        $startmem = memory_get_usage();

        // create context and evaluate
        $ctx = $this->boot($options, $console);
        $result->context = $ctx;
        $result->return = $ctx->eval($code);

        // write stats
        $result->luaCpuUsage = $ctx->getCPUUsage();
        $result->luaMemoryPeak = $ctx->getPeakMemoryUsage();
        $result->totalTook = (int) ((microtime(true) - $starttime) * 1000 * 1000); // u
        $result->totalMemory = memory_get_usage() - $startmem;

        return $result;
    }
}
