<?php

declare(strict_types=1);

namespace Dojo;

class Module
{
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
