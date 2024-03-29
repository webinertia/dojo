<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

class Button extends Element
{
    protected $dojoAttributes = [
        'data-dojo-type' => 'dijit/form/Button',
        'type'           => 'button',
    ];
}
