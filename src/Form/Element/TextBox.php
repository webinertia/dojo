<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

final class TextBox extends Element
{
    protected $dojoAttributes = [
        'type' => 'text',
        'data-dojo-type' => 'dijit/form/TextBox',
    ];
}
