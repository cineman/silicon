<?php

namespace Silicon\Exception;

class SiliconRuntimeDebugBreakpointException extends SiliconRuntimeException
{
    /**
     * The values that are beeing debugged in the breakpoint
     * 
     * @var array<mixed>
     */
    private array $breakpointValues = [];

    /**
     * Returns the values that are beeing debugged in the breakpoint
     * 
     * @return array<mixed>
     */
    public function getBreakpointValues() : array
    {
        return $this->breakpointValues;
    }

    /**
     * Sets the values that are beeing debugged in the breakpoint
     * 
     * @param array<mixed>          $breakpointValues 
     */
    public function setBreakpointValues(array $breakpointValues) : void
    {
        $this->breakpointValues = $breakpointValues;
    }
}
