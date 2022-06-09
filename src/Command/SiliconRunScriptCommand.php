<?php

namespace Silicon\Command;

use ClanCats\Container\Container;
use Hydrogen\Command\Command;
use League\CLImate\CLImate;
use Silicon\LuaContextOptions;
use Silicon\SiliconConsole;
use Silicon\SiliconRunner;

class SiliconRunScriptCommand extends Command
{
    /**
     * Container instance required by the runner
     */
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * The commands decsription displayed when listening commands
     * if null it will fallback to the description property
     */
    protected ?string $descriptionShort = 'Runs a lua script with the silicon sandbox.';
    
    /**
     * The full command description, displayed on the commands help page
     */
    protected string $description = 'Allows you to pass the path to a lua script to execute directly from the commamnd line. ' . 
    'Output is directly redirected to the CLI.';
    
    /**
     * An array of expected arguments 
     *
     * @var array<string, array<string, mixed>>
     */
    protected $expectedArguments = [
        'path' => [
            'description' => "The path to the lua file you want to run.",
            'castTo' => 'string'
        ],
    ];

    /**
     * @author: Kevin Friend-> https://gist.github.com/liunian/9338301?permalink_comment_id=1804497#gistcomment-1804497
     */
    public static function humanFilesize(int $size, int $precision = 2) : string {
        static $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).$units[$i];
    }
    

    /**
     * The commands entry point
     * 
     * @return void
     */
    public function execute()
    {
        if ($this->container->has('silicon.runner')) {
            /** @var SiliconRunner */
            $runner = $this->container->get('silicon.runner');
        } else {
            $this->cli->out('<yellow>No `@silicon.runner` alias or service defined, creating new runner...</yellow>');
            $runner = new SiliconRunner($this->container);
        }

        // get the current terminal width
        $twidth = (int) exec('tput cols');

        $console = new class extends SiliconConsole {
            public CLImate $cli;
            public int $twidth;

            public function write(int $type, string $message) : void
            {   
                $metadataline = sprintf(' <cyan>(mem: <blue>%s</blue>)</cyan>', SiliconRunScriptCommand::humanFilesize(memory_get_usage()));
                $metaDataLen = strlen(strip_tags($metadataline));

                $lines = explode(PHP_EOL, $message);
                if (strlen($lines[0]) > $this->twidth) {
                    $firstLineA = substr($lines[0], 0, $this->twidth);
                    $firstLineB = substr($lines[0], $this->twidth);
                    $lines[0] = $firstLineB;
                    array_unshift($lines, $firstLineA);
                }

                $firstLineLen = strlen($lines[0]);

                if ($firstLineLen + $metaDataLen > $this->twidth) {
                    $cutFirstLine = substr($lines[0], 0, $firstLineLen - $metaDataLen);
                    $cutOff = substr($lines[0], $firstLineLen - $metaDataLen);

                    $lines[0] = $cutFirstLine;
                    $lines[1] = $cutOff . ($lines[1] ?? '');
                }

                $pad = $this->twidth - (strlen($lines[0]) + $metaDataLen);
                if ($pad > 0) {
                    $lines[0] .= str_repeat(' ', $pad);
                }

                $lines[0] .= $metadataline;

                if ($type === SiliconConsole::LOG_TYPE_WARNING) {
                    $this->cli->out('<yellow>' . implode(PHP_EOL, $lines) . '</yellow>');
                } elseif ($type === SiliconConsole::LOG_TYPE_ERROR) {
                    $this->cli->out('<red>' . implode(PHP_EOL, $lines) . '</red>');
                } else {
                    $this->cli->out(implode(PHP_EOL, $lines));
                }

            }
        };

        if (!$luaPath = (string) $this->cli->arguments->get('path')) {
            $this->cli->error('Please specify the path to the lua file to be executed.');
        }


        $this->cli->out(sprintf('executing: <blue>%s</blue>', $luaPath));
        $this->cli->out(str_repeat('_', $twidth));

        
        $options = new LuaContextOptions;

        $console->cli = $this->cli;
        $console->twidth = $twidth;

        $ctx = $runner->boot($options, $console);

        $luaCode = file_get_contents($luaPath) ?: '';
        $ctx->eval($luaCode);

        $this->cli->out(str_repeat('_', $twidth));

        $padding = $this->cli->padding(20, ' ');
        $padding->label('lua peak mem')->result(sprintf('<blue>%s</blue>', SiliconRunScriptCommand::humanFilesize($ctx->getPeakMemoryUsage())));
        $padding->label('lua cpu')->result(sprintf('<blue>%d</blue>', $ctx->getCPUUsage()));
    }
}
