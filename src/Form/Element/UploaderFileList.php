<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Laminas\Form\Element;

class UploaderFileList extends Element
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type'           => 'uploaderFileList',
        'data-dojo-type' => 'dojox/form/uploader/FileList',
    ];

}
