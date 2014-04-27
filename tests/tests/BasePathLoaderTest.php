<?php

use Riimu\Kit\ClassLoader\BasePathLoader;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/MIT MIT License
 */
class BasePathLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testRegistrationHandling()
    {
        $loader = new BasePathLoader();
        $this->assertFalse($loader->isRegistered());
        $this->assertTrue($loader->register());
        $this->assertTrue($loader->isRegistered());
        $this->assertTrue($loader->unregister());
        $this->assertFalse($loader->isRegistered());
    }

    public function testRegisteringMultipleTimes ()
	{
		$loader = new BasePathLoader();
		$loader->register();
		$this->assertTrue($loader->register());
		$this->assertTrue($loader->unregister());
		$this->assertFalse($loader->isRegistered());
		$this->assertFalse($loader->unregister());
	}

    public function testRegisteringMultipleLoaders ()
	{
		$loader = new BasePathLoader();
		$loader2 = new BasePathLoader();

		$loader->register();
		$this->assertFalse($loader2->isRegistered());
		$this->assertTrue($loader2->register());
		$this->assertTrue($loader2->isRegistered());
		$this->assertTrue($loader->isRegistered());
		$this->assertTrue($loader->unregister());
		$this->assertTrue($loader2->isRegistered());
		$this->assertTrue($loader2->unregister());
	}

    public function testCallingAutoloadCall()
	{
		$loader = $this->getMock('Riimu\Kit\ClassLoader\BasePathLoader', ['load']);
		$loader->expects($this->once())->method('load')->will($this->returnValue(false));

		$this->assertTrue($loader->register());
		$this->assertFalse(class_exists('ThisClassDoesNotExist'));
		$this->assertTrue($loader->unregister());
	}

    public function testMissingClass ()
	{
		$loader = new BasePathLoader();
		$this->loadTest($loader, 'ThisClassDoesNotExist', false);
	}

    public function testBasePath()
    {
        $loader = new BasePathLoader();
        $this->loadTest($loader, 'BaseDirClass', false);
        $loader->addBasePath(CLASS_BASE);
        $this->loadTest($loader, 'BaseDirClass', true);
    }

    public function testClassNameInNamespace()
    {
        $loader = new BasePathLoader();
        $loader->addNamespacePath('BaseNSClass', CLASS_BASE);
        $this->loadTest($loader, 'BaseNSClass', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadingExistingClass()
    {
        $loader = new BasePathLoader();
        $loader->setSilent(true);
        $this->assertSame(null, $loader->load('Riimu\Kit\ClassLoader\BasePathLoader'));
        $loader->setSilent(false);
        $loader->load('Riimu\Kit\ClassLoader\BasePathLoader');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidClassName()
    {
        $loader = new BasePathLoader();
        $loader->setSilent(true);
        $this->assertSame(null, $loader->load('0'));
        $loader->setSilent(false);
        $loader->load('0');
    }

    public function testLoadingViaIncludePath()
    {
        $loader = new BasePathLoader();
        $includePath = get_include_path();

        $loader->setLoadFromIncludePath(false);
        $this->loadTest($loader, 'pathSuccess', false);
        $loader->setLoadFromIncludePath(true);
        $this->loadTest($loader, 'pathSuccess', false);

        set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_BASE .
            DIRECTORY_SEPARATOR . 'include_path');
        $this->loadTest($loader, 'pathSuccess', true);

        set_include_path($includePath);
    }

    public function testMissingClassWithoutException()
    {
        $loader = new BasePathLoader();
        $loader->addBasePath(CLASS_BASE);
        $loader->setSilent(true);
        $this->assertSame(null, $loader->load('NoClassHere'));
        return $loader;
    }

    /**
     * @depends testMissingClassWithoutException
     * @expectedException \RuntimeException
     */
    public function testMissingClassWithException(BasePathLoader $loader)
    {
        $loader->setSilent(false);
        $loader->load('NoClassHere');
    }

    public function testNamespaceLoadOrder()
    {
        $loader = new BasePathLoader();
        $loader->addNamespacePath('testns', [CLASS_BASE . DIRECTORY_SEPARATOR . 'pathA']);
        $loader->addNamespacePath(['testns\\' => CLASS_BASE . DIRECTORY_SEPARATOR . 'pathB' . DIRECTORY_SEPARATOR]);
        $this->loadTest($loader, 'testns\nsClass', true);
        $this->assertEquals('B', testns\nsClass::$source);
    }

    public function testAutoChainingDifferentTypes()
    {
        $loader = new BasePathLoader();
        $loader->addBasePath(CLASS_BASE);
        $loader->register();
        $this->assertTrue(class_exists('SomeClass', true));
        $this->assertTrue(interface_exists('SomeInterface', false));
        $this->assertTrue(trait_exists('SomeTrait', false));
        $loader->unregister();
    }

    public function testDifferentExtensions()
    {
        $loader = new BasePathLoader();
        $loader->addBasePath(CLASS_BASE);
        $loader->setFileExtensions(['.inc']);
        $this->loadTest($loader, 'DifferentExt', true);
    }

    public function testMissingFromNamespace()
    {
        $loader = new BasePathLoader();
        $loader->addNamespacePath('testns', CLASS_BASE . DIRECTORY_SEPARATOR . 'pathA');
        $this->loadTest($loader, 'testns\ThisDoesNotExist', false);
    }

    public function testLoadingClassWithUnderscores()
    {
        $loader = new BasePathLoader();
        $loader->addNamespacePath('pathB_testns_', CLASS_BASE);
        $this->loadTest($loader, 'pathB_testns_UnderScored', true);
    }

    public function testPrefixClassLoading()
    {
        $loader = new BasePathLoader();
        $loader->addPrefixPath('FooBar', CLASS_BASE . DIRECTORY_SEPARATOR . 'pathA');
        $loader->addPrefixPath('FooBar', [CLASS_BASE . DIRECTORY_SEPARATOR . 'pathB']);
        $this->loadTest($loader, 'FooBar\IsNotClass', false);
        $this->loadTest($loader, '\FooBar\PrefClassA', true);
        $this->loadTest($loader, 'FooBar\testns\PrefClassB', true);
    }

	private function loadTest (BasePathLoader $loader, $class, $exists)
	{
        $loader->setSilent(false);
		$this->assertSame($exists, $loader->load($class));
		$this->assertSame($exists, class_exists($class, false));
	}
}