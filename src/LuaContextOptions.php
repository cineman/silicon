<?php

namespace Silicon;

class LuaContextOptions
{
    /**
     * Some memory limit helpers for convenience
     */
    public const MEML_128KB = 131072;
    public const MEML_512KB = 524288;
    public const MEML_1MB = 1048576;
    public const MEML_2MB = 2097152;
    public const MEML_5MB = 5242880;
    public const MEML_8MB = 8388608;
    public const MEML_16MB = 16777216;
    public const MEML_32MB = 33554432;
    public const MEML_64MB = 67108864;
    public const MEML_128MB = 134217728;
    public const MEML_256MB = 268435456;
    public const MEML_512MB = 536870912;
    public const MEML_1GB = 1073741824;
    public const MEML_2GB = 2147483648;
    public const MEML_4GB = 4294967296;

    /**
     * Configures if the CPU Time should be limited, if false a script 
     * can pretty much run forever...
     */
    public bool $enableCPUTimeLimit = true;

    /**
     * Configures the actual CPU Time limit in seconds 
     * Not supported on windows..
     */
    public float $CPUTimeLimit = 10.0;

    /**
     * The contexts memory limit in bytes 
     */
    public int $memoryLimit = self::MEML_16MB;

    /**
     * Custom preload cache instance to optimize preload loading
     */
    public ?SiliconPreloadCache $preloadCache = null;

    /**
     * Boolean if the "array" silicon library should be loaded
     */
    public bool $libArrayEnabled = true;

    /**
     * Boolean if the "string" silicon library should be loaded
     */
    public bool $libStringEnabled = true;

    /**
     * Boolean if the "date" silicon library should be loaded
     */
    public bool $libDateEnabled = true;
}
