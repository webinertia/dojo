<?php

declare(strict_types=1);

namespace Dojo\Form\Element;

use Dojo\Form\Element;

final class Editor extends Element
{
    protected $attributes = [
        'type' => 'editor',
    ];
    /**
     * These are here for when no attributes are passed in
     * the calling form or fieldset
     */
    protected $dojoAttributes = [
        'data-dojo-type'  => 'dijit/Editor',
        'data-dojo-props' => 'extraPlugins:
        [
            \'foreColor\',
            \'hiliteColor\',
            {name:\'dijit/_editor/plugins/FontChoice\', command:\'fontName\', generic:true},
            {name: \'dojox/editor/plugins/Save\', iconClassPrefix:\'dijitIcon\', command:\'Save\'}
        ]',
    ];
}
