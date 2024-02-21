<?php

namespace Silicon;

use Silicon\Exception\SiliconRuntimeDebugBreakpointException;

class SiliconConsole implements SiliconModuleInterface
{
    /**
     * Console log types
     */
    public const LOG_TYPE_INFO = 0;
    public const LOG_TYPE_WARNING = 1;
    public const LOG_TYPE_ERROR = 2;

    /**
     * Maximum number of elements to be dumped in the console
     */
    private int $maxArrayDumpLength = 127;

    /**
     * The contexts console
     * 
     * @var array<array{int, string, int}>>
     */
    private array $consoleData = [];

    /**
     * Return an array of functions that are exposed to the module
     * 
     * @return ?array<string, callable>
     */
    public function getExposedFunctions(LuaContext $ctx) : ?array
    {
        return [
            'print' => [$this, 'printInfo'],
            'log' => [$this, 'logInfo'],
            'warn' => [$this, 'logWarn'],
            'error' => [$this, 'logError'],

            /**
             * console.debug(...value)
             * 
             * Console logs the given value. and stops the lua execution.
             */
            'debug' => function($args, $trace) {


                for ($i = 1; $i <= $args['n']; $i++) {
                    if (!isset($args[$i])) {
                        $args[$i] = null;
                    }
                }

                // sort by array index
                ksort($args);

                // we only really care about the second line in the trace
                $trace = explode("\n", $trace)[2] ?? '';
                // try to parse the line number
                preg_match("/\[string \"SiliconEval\"\]:([0-9]+):/", $trace, $matches);
                $line = $matches[1] ?? 0;

                // always drop the argument count, not needed in PHP
                unset($args['n']);
                $args = array_values($args);
                $this->logInfo(...$args);
                $breakpoint = new SiliconRuntimeDebugBreakpointException("Debug breakpoint [line: $line]");
                $breakpoint->setBreakpointValues($args);
                throw $breakpoint;
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
function log(...)
    console.log(arg)
end
_debug = debug
function debug(...)
    local trace = _debug.traceback()
    console.debug(arg, trace)
end
LUA;
    }

    /**
     * Write a message to the log array
     */
    public function write(int $type, string $message) : void
    {   
        $this->consoleData[] = [$type, $message, time()];
    }

    /**
     * Returns all log messages 
     * 
     * @return array<array{0: int, 1: string, 2: int}>>
     */
    public function all() : array
    {
        return $this->consoleData;
    }

    /**
     * Prints the given string into the console
     * 
     * @param string                $string
     */
    public function printInfo(string $string) : void
    {
        $this->write(self::LOG_TYPE_INFO, $string);
    }

    /**
     * Logs N arguments to the console (info)
     * 
     * @param mixed                $args
     */
    public function logInfo(...$args) : void
    {
        $this->write(self::LOG_TYPE_INFO, $this->convertArgumentsToString(...$args));
    }

    /**
     * Logs N arguments to the console (info)
     * 
     * @param mixed                $args
     */
    public function logWarn(...$args) : void
    {
        $this->write(self::LOG_TYPE_WARNING, $this->convertArgumentsToString(...$args));
    }

    /**
     * Logs N arguments to the console (info)
     * 
     * @param mixed                $args
     */
    public function logError(...$args) : void
    {
        $this->write(self::LOG_TYPE_ERROR, $this->convertArgumentsToString(...$args));
    }

    /**
     * Converts the given array to a human readable form
     * 
     * @param array<mixed>              $data
     */
    private function convertArrayToDebugString(array $data, int $prefixLength = 2) : string
    {
        $pad0 = str_repeat(' ', $prefixLength - 2);
        $pad = str_repeat(' ', $prefixLength);

        $buffer = "[" . count($data) . "]{" . PHP_EOL;

        $data = array_slice($data, 0, $this->maxArrayDumpLength, true);
    
        foreach($data as $k => $v) 
        {
            $buffer .= $pad . $k . ': ';
            
            if (is_array($v)) {
                $buffer .= $this->convertArrayToDebugString($v, $prefixLength + 2);
            } else {
                $buffer .= $this->convertArgumentsToString($v);
            }

            $buffer .= PHP_EOL;
        }

        $buffer .= $pad0 . "}";

        return $buffer;
    }

    /**
     * Converts the given arguments to a human readable string 
     * This function is used to convert varios data type to a string 
     * to be printed in the console.
     * 
     * @param mixed                $args
     */
    public function convertArgumentsToString(...$args) : string
    {
        $buffer = [];
        foreach($args as $argument) {
            if (is_int($argument)) {
                $buffer[] = 'int(' . $argument . ')';
            }
            elseif (is_bool($argument)) {
                $buffer[] = 'bool(' . var_export($argument, true) . ')';
            }
            elseif (is_float($argument)) {
                $buffer[] = 'float(' . $argument . ')';
            }
            elseif (is_string($argument)) {
                $buffer[] = 'string("' . $argument . '")';
            }
            elseif (is_null($argument)) {
                $buffer[] = 'null';
            }
            elseif (is_array($argument)) {
                $buffer[] = $this->convertArrayToDebugString($argument);
            }
            else {
                $buffer[] = 'unknown';
            }
        }

        return implode(', ', $buffer);
    }
}
