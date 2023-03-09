<?php

declare(strict_types=1);

namespace Dojo\Form;

use Laminas\Form\Element as LaminasElement;

use function array_merge;

class Element extends LaminasElement
{
    /** @var array<string, mixed> dojoAttributes*/
    protected $dojoAttributes = [];

        /**
     * @param  null|int|string   $name    Optional name for the element
     * @param  iterable $options Optional options for the element
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = null, iterable $options = [])
    {
        parent::__construct($name, $options);
        // Push the dojoAttributes into the $attributes array
        $this->setAttributes($this->dojoAttributes);
    }
}
