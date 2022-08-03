<?php

namespace Silicon;

use Silicon\Exception\SiliconCPUTimeoutException;
use Silicon\Exception\SiliconException;
use Silicon\Exception\SiliconLuaSyntaxException;
use Silicon\Exception\SiliconMemoryExhaustionException;
use Silicon\Exception\SiliconRuntimeException;
use ClanCats\Container\Container;

use Hydrogen\HTTP\Payload;

use LuaSandbox;
use LuaSandboxFunction;
use LuaSandboxMemoryError;
use LuaSandboxRuntimeError;
use LuaSandboxSyntaxError;
use LuaSandboxTimeoutError;
use Silicon\Module\SiliconCoreModule;

class LuaContext
{
    /**
     * Container
     */
    private ?Container $container;

    /**
     * Options and settings for the context runtime
     */
    public LuaContextOptions $options;

    /**
     * The actual lua handle
     */
    private LuaSandbox $lua;

    /**
     * Console for logging in the lua runtime
     */
    private SiliconConsole $console;

    /**
     * An array of registered silicon modules
     * 
     * @var array<SiliconModuleInterface>
     */
    private array $registeredModules = [];

    /**
     * Constructor
     * 
     * @param ?SiliconConsole        $console You can pass a custom console to get realtime feedback from the runtime
     * @param ?Container             $container The container is only used to find and automatically register silicon modules
     */
    public function __construct(LuaContextOptions $options, ?SiliconConsole $console = null, ?Container $container = null)
    {
        $this->options = $options;
        $this->container = $container;
        if (!$console) $console = new SiliconConsole;
        $this->console = $console;
        $this->initLuaContext();
    }

    /**
     * Returns an instance of the current console
     */
    public function console() : SiliconConsole
    {
        return $this->console;
    }

    /**
     * Context initializer
     * 
     * @return void
     */
    private function initLuaContext()
    {
        $this->lua = new LuaSandbox();
        $this->lua->disableProfiler();

        // set memory limit
        try {
            $this->lua->setMemoryLimit($this->options->memoryLimit);

             // set time limit
            if ($this->options->enableCPUTimeLimit) {
                $this->lua->setCPULimit($this->options->CPUTimeLimit);
            } else {
                $this->lua->setCPULimit(false);
            }

            // extends core
            $this->extendCoreLibraries();

            // register modules 
            $this->register('silicon', new SiliconCoreModule);
            $this->register('console', $this->console);
            $this->registerContainerModules();

            // after module registration run preload
            $this->preload();
        }
        catch (LuaSandboxMemoryError $e) {
            throw new SiliconMemoryExhaustionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Preload required lua code
     */
    private function preload() : void
    {
        if (is_null($this->options->preloadCache) || $this->options->preloadCache->hasCache() === false) 
        {
            // if no cache build it 
            $combinedCode = "";
            foreach($this->registeredModules as $moduleName => $module) {
                if ($code = $module->preloadLua()) {
                    $combinedCode .= "\n\n-- " . $moduleName . "\n\n";
                    $combinedCode .= $code;
                }
            }

            $preloadBin = $this->lua->loadString($combinedCode)->dump();

            if (!is_null($this->options->preloadCache)) {
                $this->options->preloadCache->setCache($preloadBin);
            }
        }
        elseif (!is_null($this->options->preloadCache)) {
            $preloadBin = $this->options->preloadCache->getCache();
        } else {
            $preloadBin = '';
        }

        // @phpstan-ignore-next-line
        $this->lua->loadBinary($preloadBin, 'preload')->call();
    } 

    /**
     * Adds a few helpful functions to the lua core library
     * 
     * @return void 
     */
    private function extendCoreLibraries()
    {
        /**
         * Extends luas "math" library
         */
        $this->lua->registerLibrary('math', 
        [
            /**
             * math.round(x, precision)
             * 
             * Lua standart library does only support ceil and floor, the php round function 
             * is very helpful allowing to specify the precision
             */
            'round' => function($x, int $precision = 0) {
                return [(float) round($x, $precision)];
            }
        ]);
    }

    /**
     * Registers the given module with the given name in the current context
     */
    public function register(string $moduleName, SiliconModuleInterface $module) : void
    {
        if ($functionArray = $module->getExposedFunctions($this)) {
            $this->lua->registerLibrary($moduleName, $functionArray);
        }

        $this->registeredModules[$moduleName] = $module;
    }

    /**
     * Registers custom Lua Modules from the container
     * 
     * @return void
     */
    private function registerContainerModules() 
    {
        if (!$container = $this->container) return;

        foreach($container->serviceNamesWithMetaData('silicon.module') as $serviceName => $moduleMetaData)
        {
            foreach($moduleMetaData as $moduleMeta)
            {
                if (!is_string($moduleMeta[0])) {
                    throw new SiliconException('Trying to register a lua / silicon module without a module name.');
                }

                $module = $container->get($serviceName);
                if (!$module instanceof SiliconModuleInterface) {
                    throw new SiliconException(sprintf("Trying to register the module %s, which does not implement the SiliconModuleInterface", $serviceName));
                }

                $this->register($moduleMeta[0], $module);
            }
        }
    }

    /**
     * Evaluate inline code 
     * You can pass lua code to this function which is then 
     * evaluated in the current context. 
     * 
     * You can define a return statement, the contents of that will be forwared 
     * and also returned by the PHP function.
     * 
     * @return array<mixed>|null
     */
    public function eval(string $code) : ?array
    {
        try {
            $func = $this->lua->loadString($code, 'SiliconEval');
            $result = $func->call(); // @phpstan-ignore-line

            if ($result === false) return null;
            return $result;
        }
        // wrap the exceptions in our own
        catch (LuaSandboxSyntaxError $e) {
            throw new SiliconLuaSyntaxException($e->getMessage(), $e->getCode(), $e);
        }
        catch (LuaSandboxRuntimeError $e) {
            throw new SiliconRuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        catch (LuaSandboxTimeoutError $e) {
            throw new SiliconCPUTimeoutException($e->getMessage(), $e->getCode(), $e);
        }
        catch (LuaSandboxMemoryError $e) {
            throw new SiliconMemoryExhaustionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Invokes a function in the current context
     * Returns null on failure, this is the main difference to the default LuaSandbox behavior
     * 
     * @param mixed                     ...$args
     * @return null|array<mixed>
     */
    public function invokeFunction(string $functionName, ...$args) : ?array
    {
        try {
            $result = $this->lua->callFunction($functionName, ...$args);
        }
        // wrap the exceptions in our own
        catch (LuaSandboxSyntaxError $e) {
            throw new SiliconLuaSyntaxException($e->getMessage(), $e->getCode(), $e);
        }
        catch (LuaSandboxRuntimeError $e) {
            throw new SiliconRuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        catch (LuaSandboxTimeoutError $e) {
            throw new SiliconCPUTimeoutException($e->getMessage(), $e->getCode(), $e);
        }
        catch (LuaSandboxMemoryError $e) {
            throw new SiliconMemoryExhaustionException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_array($result)) return null;
        return $result;
    }

    /**
     * Creates a from lua callable function closure, @see wrapPhpFunction
     *
     * @param callable|array<mixed>                $function
     */
    public function lambda($function) : LuaSandboxFunction
    {
        // @phpstan-ignore-next-line
        return $this->lua->wrapPhpFunction($function);
    }

    /**
     * @see LuaSandbox::getPeakMemoryUsage
     */
    public function getPeakMemoryUsage() : int 
    {
        return $this->lua->getPeakMemoryUsage();
    }

    /**
     * @see LuaSandbox::getMemoryUsage
     */
    public function getMemoryUsage() : int 
    {
        return $this->lua->getMemoryUsage();
    }

    /**
     * @see LuaSandbox::getCPUUsage
     */
    public function getCPUUsage() : float 
    {
        return $this->lua->getCPUUsage();
    }
}
