<?php

declare(strict_types=1);

namespace Atk4\Container;

use Atk4\Core\Factory;
use Psr\Container\ContainerInterface;

class AppContainer implements ContainerInterface
{
    private static AppContainer $instance;

    protected AppContainerConfig $config;

    public function __construct()
    {
        $this->config = new AppContainerConfig();
        self::$instance = $this;
    }

    public static function instance() : self {
        return self::$instance;
    }

    public function readConfig(string ...$file): void
    {
        $this->config->readConfig($file, 'php');
    }

    public function get(string $id)
    {
        $value = $this->config->getConfig($id);

        if (is_callable($value)) {
            return $value($this);
        }

        return $value;
    }

    public function has(string $id): bool
    {
        return $this->get($id) !== null;
    }

    /**
     * @param mixed $object
     */
    public function set(string $id, $object): void
    {
        $this->config->setConfig($id, $object);
    }

    public function addFactory(string $key, callable $callable): void
    {
        $this->set($key, $callable);
    }

    public function addFactorySeed(string $key, array $seed, array $defaults = []): void
    {
        $this->set($key, fn (AppContainer $container = null) => Factory::factory($seed, $defaults));
    }

    public function addSingletonSeed(string $key, array $seed, array $defaults = []): void
    {
        $this->set($key, function (self $container = null) use ($key, $seed, $defaults) {
            $this->set($key, Factory::factory($seed, $defaults));

            return $this->get($key);
        });
    }
}
