<?php

namespace Riimu\Kit\ClassLoader;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CacheListClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCachingStorage()
    {
        $result = false;
        $loader = new CacheListClassLoader([]);
        $loader->addBasePath(CLASS_BASE);
        $loader->setCacheHandler(function ($cache) use (& $result) {
            $result = $cache;
        });
        $loader->loadClass('StoreIntoCache');
        $this->assertEquals(['StoreIntoCache' => CLASS_BASE . DIRECTORY_SEPARATOR . 'StoreIntoCache.php'], $result);
    }

    public function testFailLoadingFile()
    {
        $loader = new CacheListClassLoader([]);
        $loader->addBasePath(CLASS_BASE);
        $loader->setVerbose(true);
        $this->assertFalse($loader->loadClass('NonExistantFile'));
    }

    public function testFailLoadingCache()
    {
        $result = false;
        $loader = new CacheListClassLoader(['NonExistantClass' => CLASS_BASE . DIRECTORY_SEPARATOR . 'NonExistantFile.php']);
        $loader->setCacheHandler(function ($cache) use (& $result) {
            $result = $cache;
        });
        $loader->setVerbose(true);
        $this->assertFalse(@$loader->loadClass('NonExistantClass'));
        $this->assertEquals([], $result);
    }

    public function testLoadingBadFile()
    {
        $loader = $this->getMock('Riimu\Kit\ClassLoader\CacheListClassLoader', ['saveCache'], [[]]);
        $loader->expects($this->never())->method('saveCache');
        $loader->setVerbose(false);
        $loader->addBasePath(CLASS_BASE);
        $this->assertNull($loader->loadClass('NoClassHere'));
    }

    public function testCacheLoading()
    {
        $loader = new CacheListClassLoader(['CachedClass' => CLASS_BASE . DIRECTORY_SEPARATOR . 'CachedClass.php']);
        $loader->setVerbose(true);
        $this->assertTrue($loader->loadClass('CachedClass'));
    }
}