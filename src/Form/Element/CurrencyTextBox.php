<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

final class CurrencyTextBox extends Element
{
    protected $attributes = [
        'type'            => 'text',
    ];
    protected $dojoAttributes = [
        'data-dojo-type'  => 'dijit/form/CurrencyTextBox',
        'data-dojo-props' => 'constraints:{fractional:true}, currency:\'USD\', invalidMessage:\'Invalid Amount. Cents are required.\'',
    ];
}
