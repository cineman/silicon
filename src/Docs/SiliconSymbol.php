<?php

namespace Silicon\Docs;

class SiliconSymbol
{   
    /**
     * @param array<SiliconSymbol> $children
     */
    public function __construct(
        public string $label,
        public string $parent,
        public string $docs,
        public string $type = 'unknown',
        public ?string $detail = null,
        public ?string $fncDecl = null,
        public ?string $insertText = null,
        public array $children = [],
    )
    {
        
    }
}
