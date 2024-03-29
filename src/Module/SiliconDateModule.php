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

            /**
             * date.format(timestamp, format)
             * 
             * Formats the given timestamp using the given format
             */
            'format' => function(int $timestamp, string $format) {
                return [date($format, $timestamp)];
            },

            /**
             * date.parse(string, format)
             * 
             * Parses the given string using the given format, will return false if the string cannot be parsed
             */
            'parse' => function(string $string, ?string $format = null) {
                if ($format === null) {
                    return [strtotime($string)];
                } else {
                    // use DateTimeImmutable to parse the date
                    $dt = \DateTimeImmutable::createFromFormat($format, $string);
                    if ($dt === false) {
                        return [false];
                    }
                    return [$dt->getTimestamp()];
                }
            },

            /**
             * date.diff(timestamp1, timestamp2)
             *
             * Returns the difference between the two timestamps in seconds
             */
            'diff' => function(int $timestamp1, int $timestamp2) {
                return [abs($timestamp1 - $timestamp2)];
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
