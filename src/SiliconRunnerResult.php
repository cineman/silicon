<?php

namespace Silicon;

class SiliconRunnerResult
{
    /**
     * The values returned from the call
     * 
     * @var array<mixed>
     */
    public ?array $return = null;

    /**
     * The lua specific memory peak
     */
    public int $luaMemoryPeak = 0;

    /**
     * The lua specific cpu usage
     */
    public float $luaCpuUsage = 0.0;

    /**
     * Total memory usage
     * Mesured by reading memory use at the beginning and end of the execution
     * Not really precice but can give you a general idea
     */
    public int $totalMemory = 0;

    /**
     * The total CPU time in microseconds 
     * This includes sleeps and PHP execution
     */
    public int $totalTook = 0;

    /**
     * The context which the result was extracted from
     */
    public LuaContext $context;
}
