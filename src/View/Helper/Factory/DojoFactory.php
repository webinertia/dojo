<?php

declare(strict_types=1);

namespace Dojo\View\Helper\Factory;

use Dojo\View\Container;
use Dojo\View\Helper\Dojo;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DojoFactory implements FactoryInterface
{
    /** @inheritDoc */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Dojo
    {
        return new $requestedName($container->get(Container::class));
    }
}
