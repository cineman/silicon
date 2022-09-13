<?php

namespace Silicon\Module;

use Silicon\Exception\SiliconRuntimeException;
use Silicon\LuaContext;
use Silicon\SiliconModuleInterface;

class SiliconArrayModule implements SiliconModuleInterface
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
             * array.merge(array1, array2..)
             * 
             * Merges the given arrays together 
             */
            'merge' => function(array ...$array) {
                return [array_merge(...$array)];
            },

            /**
             * array.keys(array)
             *
             * Returns the keys of the given array
             */
            'keys' => function(array $array) {
                return [array_keys($array)];
            },

            /**
             * array.values(array)
             *
             * Returns the values of the given array (drops the keys)
             */
            'values' => function(array $array) {
                return [array_values($array)];
            },

            /**
             * array.count(array)
             *
             * Returns the number of elements in the given array
             */
            'count' => function(array $array) {
                return [count($array)];
            },

            /**
             * array.column(array, column)
             * 
             * Returns the values of the given column in the given array
             */
            'column' => function(array $array, string $column) {
                return [array_column($array, $column)];
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
