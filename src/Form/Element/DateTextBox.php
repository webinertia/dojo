<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

final class DateTextBox extends Element
{
    protected $attributes = [
        'type'           => 'text',
    ];
    protected $dojoAttributes = [
        'data-dojo-type' => 'dijit/form/DateTextBox',
    ];
}
