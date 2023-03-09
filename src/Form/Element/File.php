<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Laminas\Form\Element\File as LaminasFile;

class File extends LaminasFile
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type'           => 'file',
        'multiple'       => true,
        'data-dojo-type' => 'dojox/form/FileInput',
    ];
}
