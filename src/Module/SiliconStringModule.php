<?php

namespace Silicon\Module;

use Silicon\Exception\SiliconRuntimeException;
use Silicon\LuaContext;
use Silicon\SiliconModuleInterface;

class SiliconStringModule implements SiliconModuleInterface
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
             * string.explode(string, delimiter)
             * 
             * Explodes the given string into an array using the given delimiter
             */
            'explode' => function(string $string, string $delimiter) {
                return [explode($delimiter, $string)];
            },

            /**
             * string.implode(array, delimiter)
             * 
             * Implodes the given array into a string using the given delimiter
             */
            'implode' => function(array $array, string $delimiter) {
                return [implode($delimiter, $array)];
            },

            /**
             * string.trim(string)
             * 
             * Trims the given string, removing whitespace and breaks from the beginning and end
             */
            'trim' => function(string $string) {
                return [trim($string)];
            },

            /**
             * string.replace(string, search, replace)
             * 
             * Replaces the given search string with the given replace string in the given string
             */
            'replace' => function(string $string, string $search, string $replace) {
                return [str_replace($search, $replace, $string)];
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
