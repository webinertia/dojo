<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

class MultiCheckbox extends Element
{
    /** @var array<string, mixed> $dojoAttributes*/
    protected $dojoAttributes = ['data-dojo-type' => 'dijit/form/CheckBox'];
}
