<?php

namespace Silicon;

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
            'log' => [$this, 'logInfo'],
            'warn' => [$this, 'logWarn'],
            'error' => [$this, 'logError'],
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
     * @return array<array{int, string, int}>>
     */
    public function all() : array
    {
        return $this->consoleData;
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
            elseif (is_string($argument)) {
                $buffer[] = 'string("' . $argument . '")';
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
