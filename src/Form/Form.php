<?php

declare(strict_types=1);

namespace Dojo\Form;

use Laminas\Form\Form as LaminasForm;

class Form extends LaminasForm
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'method'         => 'POST',
        'data-dojo-type' => 'dijit/form/Form',
    ];
}
