<?php

namespace Silicon;

use Silicon\Exception\SiliconException;

class SiliconPreloadCache
{
    /**
     * The directory where the preload cache should be written
     */
    private string $cacheDir;

    /**
     * The cached lua binary dump
     */
    private ?string $cacheBin = null;

    /**
     * Constructor
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;

        // try to load the cache
        if (file_exists($this->getCacheFile())) {
            $this->cacheBin = require $this->getCacheFile();
        }
    }

    private function getCacheFile() : string
    {
        return $this->cacheDir . '/SiliconPreloadCache.php';
    }

    public function hasCache() : bool 
    {
        return !is_null($this->cacheBin);
    }

    public function setCache(string $cache) : void
    {
        $this->cacheBin = $cache;

        // also write the cache
        file_put_contents($this->getCacheFile(), '<?php return ' . var_export($this->cacheBin, true) . ';');
    }

    public function getCache() : string
    {
        if (is_null($this->cacheBin)) {
            throw new SiliconException("Trying to read non build preload cache.");
        }

        return $this->cacheBin;
    }
}
