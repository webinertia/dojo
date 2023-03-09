<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

final class ComboBox extends Element
{
    protected $attributes = [
        'type'           => 'select',
    ];
    protected $dojoAttributes = [
        'data-dojo-type' => 'dijit/form/ComboBox'
    ];
}
