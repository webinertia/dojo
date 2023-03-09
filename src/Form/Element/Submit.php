<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

final class Submit extends Element
{
    protected $attributes = [
        'type'            => 'submit',
    ];
    protected $dojoAttributes = [
        'data-dojo-type'  => 'dijit/form/Button',
        'data-dojo-props' => 'iconClass: \'dijitIcon dijitIconSave\', showLabel: false',
    ];
}
