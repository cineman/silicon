<?php

namespace Silicon\Module;

use Silicon\Exception\SiliconRuntimeException;
use Silicon\LuaContext;
use Silicon\SiliconModuleInterface;

class SiliconCoreModule implements SiliconModuleInterface
{
    /**
     * Return an array of functions that are exposed to the module
     * 
     * @return ?array<string, callable>
     */
    public function getExposedFunctions(LuaContext $ctx) : ?array
    {
        return [
            /**
             * silicon.sleep(microseconds)
             * 
             * Waits in the execution for the given amount of microseconds.
             */
            'sleep' => function(int $microseconds) use(&$ctx) {

                if ($microseconds > ($ctx->options->CPUTimeLimit * 1000 * 1000)) {
                    throw new SiliconRuntimeException("You cannot sleep longer than the allowed CPU time limit.");
                }

                usleep($microseconds);
            }
        ];
    }

    /**
     * Returns a string of lua code to be preloaded before any other interaction 
     * with the sandbox. Keep in mind an error here and everything will fail!!
     * 
     * The preloaded lua code gets compiled and cached in binary form, you probably 
     * need to clear the cache when doing changes to preload code.
     */
    public function preloadLua() : ?string
    {
        return <<<'LUA'

LUA; 
    }
}
