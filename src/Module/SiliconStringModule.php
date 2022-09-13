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
                if (strlen($delimiter) == 0) {
                    throw new SiliconRuntimeException("Delimiter cannot be empty");
                }
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

            /**
             * string.kfloor(int)
             * 
             * Floors the given int down to the nearest thousand
             */
            'kfloor' => function(int $int) {
                if ($int < 1000) {
                    return [$int];
                }
                elseif ($int < 1000000) {
                    return [floor($int / 1000) . 'K'];
                }
                elseif ($int < 1000000000) {
                    return [floor($int / 1000000) . 'M'];
                }
                else {
                    return [floor($int / 1000000000) . 'B'];
                }
            },

            /**
             * string.humanbytes(int)
             * 
             * Returns a human readable string of the given bytes
             */
            'humanbytes' => function(int $bytes) {
                if ($bytes < 1024) {
                    return [$bytes . 'B'];
                }
                elseif ($bytes < 1048576) {
                    return [round($bytes / 1024, 2) . 'KB'];
                }
                elseif ($bytes < 1073741824) {
                    return [round($bytes / 1048576, 2) . 'MB'];
                }
                else {
                    return [round($bytes / 1073741824, 2) . 'GB'];
                }
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
