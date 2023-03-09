<?php

declare(strict_types=1);

namespace Dojo\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\AbstractHelper;

use function sprintf;

class DijitFormUploaderFileList extends AbstractHelper
{
    /**
     * Attributes valid for the input tag type="file"
     *
     * @var array
     */
    protected $validTagAttributes = [
        'name'      => true,
        'accept'    => true,
        'autofocus' => true,
        'disabled'  => true,
        'form'      => true,
        'multiple'  => true,
        'required'  => true,
        'type'      => true,
    ];

    /**
     * Render a form <input> element from the provided $element
     *
     * @throws Exception\DomainException
     */
    public function render(ElementInterface $element): string
    {
        $name = $element->getName();

        $attributes         = $element->getAttributes();
        $attributes['name'] = $name;

        return sprintf(
            '<div %s></div>',
            $this->createAttributesString($attributes)
        );
    }

    /**
     * Determine input type to use
     */
    protected function getType(ElementInterface $element): string
    {
        return 'uploaderFileList';
    }
}
