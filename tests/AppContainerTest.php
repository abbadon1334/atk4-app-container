<?php

declare(strict_types=1);

namespace Atk4\Container\Tests;

use Atk4\Container\AppContainer;
use Atk4\Core\Phpunit\TestCase;

class AppContainerTest extends TestCase
{
    public function testAddFactorySeed(): void
    {
        TestFactory::$counter = 0;

        $container = new AppContainer();

        $this->assertSame(0, TestFactory::$counter);

        $container->addFactorySeed(TestFactory::class, [TestFactory::class, 'di_var']);
        $this->assertSame(0, TestFactory::$counter);

        $objFromFactory = $container->get(TestFactory::class);
        $this->assertSame(1, TestFactory::$counter);

        $this->assertInstanceOf(TestFactory::class, $objFromFactory);
        $this->assertSame('di_var', $objFromFactory->var);

        $container->get(TestFactory::class);
        $this->assertSame(2, TestFactory::$counter);
    }

    public function testAddFactory(): void
    {
        $container = new AppContainer();
        $container->addFactory('every_call_different_result', function (AppContainer $container) {
            return uniqid();
        });

        $this->assertNotSame(
            $container->get('every_call_different_result'),
            $container->get('every_call_different_result')
        );
    }

    public function testHas(): void
    {
        $container = new AppContainer();
        $container->set('test', 'test1');

        $this->assertTrue($container->has('test'));
    }

    public function testGetSet(): void
    {
        $container = new AppContainer();
        $container->set('test', 'test1');

        $this->assertSame('test1', $container->get('test'));
    }

    public function testAddFactoryUsingContainer(): void
    {
        $container = new AppContainer();
        $container->set('a', fn (AppContainer $container) => $container->get('b'));
        $container->set('b', 'val_b');

        $this->assertSame('val_b', $container->get('a'));
    }

    public function testAddSingletonSeed(): void
    {
        TestFactory::$counter = 0;

        $container = new AppContainer();
        $this->assertSame(0, TestFactory::$counter);

        $container->addSingletonSeed(TestFactory::class, [TestFactory::class, 'di_var']);
        $this->assertSame(0, TestFactory::$counter);

        $container->get(TestFactory::class);
        $this->assertSame(1, TestFactory::$counter);

        $container->get(TestFactory::class);
        $this->assertSame(1, TestFactory::$counter);

        /** @var TestFactory $singleton */
        $singleton = $container->get(TestFactory::class);
        $this->assertSame(4, $singleton->add(2, 2));
    }

    public function testLoadConfig(): void
    {
        $container = new AppContainer();
        $container->readConfig(__DIR__ . '/data/config.php');

        $this->assertSame('loaded with config', $container->get('test'));
    }
}

class TestFactory
{
    public static int $counter = 0;

    public string $var;

    public function __construct(string $var)
    {
        ++self::$counter;

        $this->var = $var;
    }

    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
