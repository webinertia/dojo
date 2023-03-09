<?php

declare(strict_types=1);

namespace Dojo\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\AbstractHelper;

class DijitFormEditor extends AbstractHelper
{
    /**
     * Attributes valid for the input tag
     *
     * @var array
     */
    protected $validTagAttributes = [
        'autocomplete' => true,
        'autofocus'    => true,
        'cols'         => true,
        'dirname'      => true,
        'disabled'     => true,
        'form'         => true,
        'inputmode'    => true,
        'maxlength'    => true,
        'minlength'    => true,
        'name'         => true,
        'placeholder'  => true,
        'readonly'     => true,
        'required'     => true,
        'rows'         => true,
        'wrap'         => true,
    ];

    public function __invoke(?ElementInterface $element = null)
    {
        if (! $element) {
            return $this;
        }

        return $this->render($element);
    }

    public function render(ElementInterface $element): string
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes         = $element->getAttributes();
        $attributes['name'] = $name;
        $content            = (string) $element->getValue();
        $escapeHtml         = $this->getEscapeHtmlHelper();

        return sprintf(
            '<div %s>%s</div>',
            $this->createAttributesString($attributes),
            $escapeHtml($content)
        );
    }
}
