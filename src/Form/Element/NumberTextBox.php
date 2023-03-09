<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Laminas\Form\Element\Text;

class NumberTextBox extends Text
{
    protected $attributes = [
        'data-dojo-type' => 'dijit/form/NumberTextBox',
    ];
}
