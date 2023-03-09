<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

class ResetButton extends Element
{
    protected $attributes = [
        'type'           => 'reset',
    ];
    protected $dojoAttributes = [
        'data-dojo-type' => 'dijit/form/Button',
        'data-dojo-props' => 'label:\'Reset Form\', onClick:function(){return true;}',
    ];
}
