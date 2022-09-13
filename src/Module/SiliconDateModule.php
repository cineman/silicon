<?php

namespace Silicon\Module;

use Silicon\Exception\SiliconRuntimeException;
use Silicon\LuaContext;
use Silicon\SiliconModuleInterface;

class SiliconDateModule implements SiliconModuleInterface
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
             * date.now()
             * 
             * Returns the current time in seconds since the unix epoch
             */
            'now' => function() {
                return [time()];
            },
            
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
