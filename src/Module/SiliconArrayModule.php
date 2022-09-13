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

            /**
             * array.sum(array)
             *
             * Returns the sum of the given array values
             */
            'sum' => function(array $array) {
                return [array_sum($array)];
            },

            /**
             * array.average(array)
             *
             * Returns the average of the given array values
             */
            'average' => function(array $array) {
                return [array_sum($array) / count($array)];
            },

            /**
             * array.min(array)
             *
             * Returns the minimum value of the given array
             */
            'min' => function(array $array) {
                return [min($array)];
            },

            /**
             * array.max(array)
             *
             * Returns the maximum value of the given array
             */
            'max' => function(array $array) {
                return [max($array)];
            },

            /**
             * array.median(array)
             * 
             * Returns the median value of the given array
             */
            'median' => function(array $array) {
                $count = count($array);
                $middle = floor($count / 2);
                sort($array, SORT_NUMERIC);
                $median = $array[$middle];
                if ($count % 2 == 0) {
                    $median = ($median + $array[$middle - 1]) / 2;
                }
                return [$median];
            },


            /**
             * array.contains(array, value)
             * 
             * Returns true if the given array contains the given value
             */
            'contains' => function(array $array, $value) {
                return [in_array($value, $array)];
            },

            /**
             * array.has(array, key)
             *
             * Returns true if the given array has the given key
             */
            'has' => function(array $array, $key) {
                return [array_key_exists($key, $array)];
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
