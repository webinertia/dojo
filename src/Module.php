<?php

declare(strict_types=1);

namespace Dojo;

use DateTimeInterface;

class Module
{
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'app_settings' => [
                'server' => [
                    'dojo_time_format' => DateTimeInterface::RFC3339, // matches app_settings['server']['db_time_format']
                ],
            ],
            'service_manager' => $provider->getDependencyConfig(),
            'view_helpers'    => $provider->getViewHelperConfig(),
            'form_element'    => $provider->getFormElementConfig(),
            'filters'         => $provider->getFilterConfig(),
        ];
    }
}
