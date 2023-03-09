<?php

declare(strict_types=1);

namespace Dojo\Form;

use Laminas\Form\Fieldset as LaminasFieldset;

class FieldSet extends LaminasFieldset
{
    public function __construct($name, $options = [])
    {
        if ($name !== null && isset($options['create_dojo_attach_point'])) {
            $this->setAttribute('data-dojo-attach-point', $this->getName());
        }
    }
}
