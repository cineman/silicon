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

            /**
             * array.get(array, key, default)
             * 
             * Returns the value of the given key in the given array, if the key is not found in the array, it returns the default value
             */
            'getPath' => function(array $array, string $path, $default = null) {
                return [$this->getPathArray($array, $path, $default)];
            },

            /**
             * array.groupBy(array, key)
             * 
             * Groups the given array of arrays by the given key, if the key is not found in the array, it is ignored
             */
            'groupBy' => function(array $array, string $key) {
                $result = [];
                foreach ($array as $item) {
                    if (!array_key_exists($key, $item)) {
                        continue;
                    }
                    $result[$item[$key]][] = $item;
                }
                return [$result];
            },

            /**
             * array.flatten(array)
             * 
             * Flattens the a deep array structure into a flat assoc one
             */
            'flatten' => function(array $array, string $delimiter = '.') {
                return [$this->flattenArray($array, $delimiter)];
            },
        ];
    }

    /**
     * Returns a value from a nested array using a dot separated path
     * 
     * Returns null if at any point the path is not found
     * 
     * @param array<mixed> $array
     * @return mixed
     */
    public function getPathArray(array $array, string $path, mixed $default = null) : mixed
    {
        $keys = explode('.', $path);
        $current = $array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $current)) {
                return $default;
            }
            $current = $current[$key];
        }
        return $current;
    }

    /**
     * Flatten a deep array structure into a flat assoc one
     * 
     * example
     * from:
     * {
     *   "foo": {
     *     "bar": "baz",
     *     "bar2": {
     *      "baz": "foo"
     *     }
     *   }
     * }
     * 
     * to:
     * {
     *   "foo.bar": "baz",
     *   "foo.bar2.baz": "foo"
     * }
     * 
     * @param array<mixed> $array
     * @param string $delimiter The delimiter to use for the keys
     * @param string $prefix The prefix to use for the keys
     * @return array<mixed>
     */
    private function flattenArray(array $array, string $delimiter = '.', string $prefix = '') : array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $delimiter, $prefix . $key . $delimiter));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
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
