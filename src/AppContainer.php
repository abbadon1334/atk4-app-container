<?php

declare(strict_types=1);

namespace Atk4\Container;

use Atk4\Core\Factory;
use Psr\Container\ContainerInterface;

class AppContainer implements ContainerInterface
{
    protected AppContainerConfig $config;

    public function __construct()
    {
        $this->config = new AppContainerConfig();
    }

    public function readConfig(...$file)
    {
        $this->config->readConfig($file);
    }

    public function get(string $id)
    {
        $value = $this->config->getConfig($id);
        if (is_callable($value)) {
            return $value();
        }

        return $value;
    }

    public function has(string $id): bool
    {
        return $this->get($id) !== null;
    }

    public function set(string $id, $object)
    {
        $this->config->setConfig($id, $object);
    }

    public function addFactorySeed(string $key, array $seed, array $defaults = [])
    {
        $this->set($key, Factory::factory($seed, $defaults));
    }

    public function addFactoryCallable(string $key, callable $callable)
    {
        $this->set($key, $callable);
    }

    public function addLazyFactorySeed(string $key, array $seed, array $defaults = [])
    {
        $this->set($key, function () use ($key, $seed, $defaults) {
            $this->addFactorySeed($key, $seed, $defaults);

            return $this->get($key);
        });
    }
}
