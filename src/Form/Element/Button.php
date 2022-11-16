<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Laminas\Form\Element\Button as LaminasButton;

class Button extends LaminasButton
{
    protected $attributes = [
        'data-dojo-type' => 'dijit/form/Button',
        'type'           => 'button',
    ];
}
