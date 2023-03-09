<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

class ValidationTextBox extends Element
{
    protected $attributes = [
        'type' => 'text',
    ];
    /**
     * the concrete implementation should setup the validation as follows
     * 'data-dojo-props' => 'validator:dojox.validate.isText, constraints:{minLength:1, maxLength:255}, invalidMessage:\'Your validation message\'',
     */
    protected $dojoAttributes = [
        'data-dojo-type' => 'dijit/form/ValidationTextBox',
    ];
}
