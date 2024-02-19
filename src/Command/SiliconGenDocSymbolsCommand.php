<?php

namespace Silicon\Command;

use Hydrogen\Command\Command;
use Silicon\Docs\SiliconSymbol;
use Silicon\Exception\SiliconDocGenException;

class SiliconGenDocSymbolsCommand extends Command
{
    /**
     * An array of default paths to look for the .md files
     * @var array<string>
     */
    private array $defaultPaths = [];

    /**
     * The default output path for the symbols
     */
    private string $defaultOutputPath = '{resources}/docs/symbols.json';

    /**
     * The amount of characters to display in the detail string
     */
    private int $detailStringChars = 40;

    /**
     * The commands decsription displayed when listening commands
     * if null it will fallback to the description property
     */
    protected ?string $descriptionShort = 'Generates a symbol documentation for a given .md file.';
    
    /**
     * The full command description, displayed on the commands help page
     */
    protected string $description = 'Allows you to pass the path to a .md file to generate a symbol documentation.';

    /**
     * An array of expected arguments 
     *
     * @var array<string, array<string, mixed>>
     */
    protected $expectedArguments = [
        'path' => [
            'description' => "The path to the .md file you want to generate a symbol documentation for.",
            'castTo' => 'string'
        ],
        'output' => [
            'prefix' => 'o',
            'description' => "The path to the output file. If not set it will be written to the default output path.",
            'castTo' => 'string'
        ]
    ];

    /**
     * Adds a default path to look for the .md files
     */
    public function addDefaultPath(string $path) : void
    {
        foreach([
            '{silicon.root}' => __DIR__ . '/../..',
        ] as $key => $value) {
            $path = str_replace($key, $value, $path);
        }

        $this->defaultPaths[] = $path;
    }

    /**
     * Sets the default output path for the symbols
     */
    public function setDefaultOutputPath(string $path) : void
    {
        $resourcePath = defined('PATH_RESOURCES') ? PATH_RESOURCES : __DIR__ . '/../../resources';

        foreach([
            '{resources}' => $resourcePath
        ] as $key => $value) {
            $path = str_replace($key, $value, $path);
        }

        $this->defaultOutputPath = $path;
    }

    /**
     * Scans a directory for .md files
     * 
     * @return array<string>
     */
    private function scanForMdFiles(string $path) : array
    {
        return glob($path . '/*.md') ?: [];
    }
    
    /**
     * Converts the symbols to an array
     * 
     * @param array<SiliconSymbol> $symbols
     * @return array<mixed>
     */
    private function convertSymbolsToArray(array $symbols) : array
    {
        $data = [];
        foreach($symbols as $symbol) {
            $entry = [
                'label' => $symbol->label,
                'type' => $symbol->type,
                'tree' => $symbol->parent . $symbol->label,
                'documentation' => $symbol->docs,
                'detail' => $symbol->detail,
                'insert' => $symbol->insertText,
                'elements' => $this->convertSymbolsToArray($symbol->children)
            ];

            // remove null values
            $entry = array_filter($entry, function($value) {
                return $value !== null;
            });

            $data[] = $entry;
        }

        return $data;
    }

    /**
     * The commands entry point
     * 
     * @return void
     */
    public function execute()
    {
        $paths = $this->defaultPaths;
        if ($this->cli->arguments->get('path')) {
            $paths = array_merge($paths, [(string) $this->cli->arguments->get('path')]);
        }

        $outputPath = $this->defaultOutputPath;
        if ($this->cli->arguments->get('output')) {
            $outputPath = (string) $this->cli->arguments->get('output');
        }

        $mdFiles = [];
        foreach($paths as $path) {
            // check if dir or file
            if (is_dir($path)) {
                $mdFiles = array_merge($mdFiles, $this->scanForMdFiles($path));
            } elseif (is_file($path)) {
                $mdFiles[] = $path;
            } else {
                throw new SiliconDocGenException("The path '{$path}' is not a valid file or directory.");
            }
        }

        $symbols = [];

        // prcess the files
        foreach($mdFiles as $mdFile) {
            $this->info("Processing file: {$mdFile}");
            $symbols = array_merge($symbols, $this->processFile($mdFile));
        }

        // check if output path is writable
        if (!is_writable(dirname($outputPath))) {
            throw new SiliconDocGenException("The output path '{$outputPath}' is not writable.");
        }

        $this->info("Writing symbols to: {$outputPath}");

        // convert to json
        $symbolData = $this->convertSymbolsToArray($symbols);
        $json = json_encode($symbolData, JSON_PRETTY_PRINT);

        $this->cli->json($symbolData);
        
        // wrire to file
        if (!file_put_contents($outputPath, $json)) {
            throw new SiliconDocGenException("Could not write the symbols to: {$outputPath}");
        }

        $this->info("Symbols written to: {$outputPath}");
    }

    /**
     * Splits the given markdown content by the headers
     * 
     * @return array<array{doc: string, children: array<mixed>}>
     */
    private function splitContentByHeaders(string $content, int $headerLevel) : array
    {
        $deli = str_repeat('#', $headerLevel);

        $splits = explode("\n{$deli} ", $content);

        // special handling for the very first line of each
        if (isset($splits[0])) {
            $splits[0] = ltrim($splits[0], "# ");
        }
        
        $splits = array_filter($splits);

        $elements = [];
        foreach($splits as $split) {

            // get position of the next header
            $nextHeaderPos = strpos($split, "\n#");
            if ($nextHeaderPos === false) {
                $nextHeaderPos = strlen($split);
            }

            $elementDocs = substr($split, 0, $nextHeaderPos);
            $childrenDocs = substr($split, $nextHeaderPos);

            $subsplits = [];
            if (strpos($childrenDocs, "# ") !== false) {
                $subsplits = $this->splitContentByHeaders($childrenDocs, $headerLevel + 1);
            }

            $elements[] = [
                'doc' => $elementDocs,
                'children' => $subsplits
            ];
        }

        return $elements;
    }

    /**
     * Returns the pure function declartion for the docs and the insert text for IDEs
     * 
     * @param string $decl 
     * @return array{string, string}
     */
    private function parseFuncDecl(string $decl) : array
    {
        // check if already contains marked params
        $preMarked = (bool) preg_match('/\$\{[0-9]+:/', $decl);

        // when its premarked we have to remove the marked params for the function decl
        if ($preMarked) {
            $fnc = preg_replace('/\$\{[0-9]+:(.*)\}/', '$1', $decl) ?: '';
            return [$fnc, $decl];
        }

        // otherwise we have to build the insert text ourself by looking for the params
        // and marking them with the ${1:} syntax
        $result = preg_replace_callback('/\((.*)\)/', function($matches) use (&$params) {
            $params = explode(',', $matches[1]);
            $params = array_map(function($param, $index) {
                return '${' . ($index + 1) . ':' . trim($param) . '}';
            }, $params, array_keys($params));
            return '(' . implode(', ', $params) . ')';
        }, $decl) ?: '';

        return [$decl, $result];
    }

    /**
     * Parses a symbol from the given markdown slice
     * 
     * @param array{doc: string, children: array<mixed>} $element
     */
    private function parseSymbol(array $element, string $parentSymbolTree = '') : SiliconSymbol
    {
        $lines = explode("\n", $element['doc']);

        // the title is the first line of the doc
        $label = trim(array_shift($lines));

        // trim 
        $lines = explode("\n", trim(implode("\n", $lines)));

        // check for function declartion
        $fncDecl = null;
        $insertText = null;
        if (isset($lines[0]) && isset($lines[1])) {
            $expectedDecl = $label . '(';
            
            // check if there is a lua code block with the expected declartion in the full namespace 
            // this will be used as the function delcaration to set the insert text for IDEs
            if (trim($lines[0]) === '```lua' && substr(trim($lines[1]), 0, strlen($expectedDecl)) === $expectedDecl) {
                // search for the closing code block
                $closing = array_search('```', $lines);
                if ($closing !== false) {
                    $declLines = array_slice($lines, 1, $closing - 1);
                    $fncDecl = implode("\n", $declLines);

                    [$fncDecl, $insertText] = $this->parseFuncDecl($fncDecl);

                    // remove the declartion from the lines
                    $lines = array_slice($lines, $closing + 1);
                }
            }
        }

        $children = [];
        foreach($element['children'] as $child) {
            $children[] = $this->parseSymbol($child, $label . '.');
        }

        // Add a title if there are any docs
        if (count($lines) > 0) {
            array_unshift($lines, '');
            array_unshift($lines, '### ' . $parentSymbolTree . $label);
        }

        $symbol = new SiliconSymbol(
            label: $label,
            parent: $parentSymbolTree,
            docs: implode("\n", $lines),
            fncDecl: $fncDecl,
            insertText: $insertText ?: $label,
            children: $children
        );

        // currently we just assume when there are children it is a namespace aka a module
        if (count($children) > 0) {
            $symbol->type = 'module';
        } 
        // otherwise if we have a function declartion it is a function
        elseif ($fncDecl) {
            $symbol->type = 'function';
        }

        // build the detail for functions
        if ($symbol->type === 'function' && $fncDecl) {
            $detail = str_replace("\n", ' ', $fncDecl);
            // remove double spaces
            $detail = preg_replace('/\s+/', ' ', $detail) ?: '';
            $detail = 'function ' . trim($detail);
            $symbol->detail = $detail;
        }

        // limit the detail to 40 chars
        if ($symbol->detail && strlen($symbol->detail) > $this->detailStringChars) {
            $symbol->detail = substr($symbol->detail, 0, $this->detailStringChars) . '...';
        }

        return $symbol;
    }

    /**
     * Processes a .md file and extracts the symbols
     * 
     * @return array<SiliconSymbol>
     */
    private function processFile(string $path) : array
    {
        if (!$content = file_get_contents($path)) {
            throw new SiliconDocGenException("Could not read the file: {$path}");
        }
        
        // parse by using the headers as a delimiter for the symbols
        // this is hirachical so we can have sub symbols
        $content = $this->splitContentByHeaders($content, 1);

        // now we can process each header recursively to produce symbols.
        $symbols = [];
        foreach($content as $element) {
            $symbols[] = $this->parseSymbol($element);
        }

        return $symbols;
    }
}
