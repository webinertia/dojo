<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Laminas\Form\Element\Select as LaminasSelect;

class Select extends LaminasSelect
{
    protected $type = 'select';
    protected $attributes = [
        'data-dojo-type' => 'dijit/form/Select'
    ];
}
