<?php

declare(strict_types=1);

namespace Dojo\Form\View\Helper;

use Dojo\Form\Element\Button;
use Laminas\Form\Element\Captcha;
use Laminas\Form\Element\MonthSelect;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\LabelAwareInterface;
use Laminas\Form\View\Helper\FormElementErrors;
use Laminas\Form\View\Helper\FormLabel;
use Laminas\Form\View\Helper\FormRow;

use function in_array;
use function method_exists;
use function sprintf;
use function strtolower;

class DijitFormRow extends FormRow
{
    public const LABEL_APPEND  = 'append';
    public const LABEL_PREPEND = 'prepend';

    /**
     * The class that is added to element that have errors
     *
     * @var string
     */
    protected $inputErrorClass = 'input-error';

    /**
     * The attributes for the row label
     *
     * @var array
     */
    protected $labelAttributes = [];

    /**
     * Where will be label rendered?
     *
     * @var string
     */
    protected $labelPosition = self::LABEL_PREPEND;

    /**
     * Are the errors are rendered by this helper?
     *
     * @var bool
     */
    protected $renderErrors = true;

    /**
     * Form label helper instance
     *
     * @var null|FormLabel
     */
    protected $labelHelper;

    /**
     * Form element helper instance
     *
     * @var null|FormElement
     */
    protected $elementHelper;

    /**
     * Form element errors helper instance
     *
     * @var null|FormElementErrors
     */
    protected $elementErrorsHelper;

    /** @var null|string */
    protected $partial;

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @template T as null|ElementInterface
     * @psalm-param T $element
     * @psalm-return (T is null ? self : string)
     * @return string|FormRow
     */
    public function __invoke(
        ?ElementInterface $element = null,
        ?string $labelPosition = null,
        ?bool $renderErrors = null,
        ?string $partial = null
    ) {
        if (! $element) {
            return $this;
        }

        if ($labelPosition === null) {
            $labelPosition = $this->getLabelPosition();
        }

        if ($renderErrors !== null) {
            $this->setRenderErrors($renderErrors);
        }

        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this->render($element, $labelPosition);
    }

    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @throws Exception\DomainException
     */
    public function render(ElementInterface $element, ?string $labelPosition = null): string
    {
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();

        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();

        if ($labelPosition === null) {
            $labelPosition = $this->labelPosition;
        }

        if (isset($label) && '' !== $label) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }
        }

        // Does this element have errors ?
        if ($element->getMessages() && $inputErrorClass) {
            $classAttributes  = $element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '';
            $classAttributes .= $inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        if ($this->partial) {
            $vars = [
                'element'         => $element,
                'label'           => $label,
                'labelAttributes' => $this->labelAttributes,
                'labelPosition'   => $labelPosition,
                'renderErrors'    => $this->renderErrors,
            ];

            return $this->view->render($this->partial, $vars);
        }

        $elementErrors = '';
        if ($this->renderErrors) {
            $elementErrors = $elementErrorsHelper->render($element);
        }

        $elementString = $elementHelper->render($element);

        // hidden elements do not need a <label> -https://github.com/zendframework/zf2/issues/5607
        $type = $element->getAttribute('type');
        if (isset($label) && '' !== $label && $type !== 'hidden') {
            $labelAttributes = [];

            if ($element instanceof LabelAwareInterface) {
                $labelAttributes = $element->getLabelAttributes();
            }

            if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                $label = $escapeHtmlHelper($label);
            }

            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }

            // Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            if (
                $type === 'multi_checkbox'
                || $type === 'radio'
                || $element instanceof MonthSelect
                || $element instanceof Captcha
            ) {
                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s</fieldset>',
                    $label,
                    $elementString
                );
            } else {
                // Ensure element and label will be separated if element has an `id`-attribute.
                // If element has label option `always_wrap` it will be nested in any case.
                if (
                    $element->hasAttribute('id')
                    && ($element instanceof LabelAwareInterface && ! $element->getLabelOption('always_wrap'))
                ) {
                    $labelOpen  = '';
                    $labelClose = '';
                    $label      = $labelHelper->openTag($element) . $label . $labelHelper->closeTag();
                } else {
                    $labelOpen  = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }

                if (
                    $label !== '' && (! $element->hasAttribute('id'))
                    || ($element instanceof LabelAwareInterface && $element->getLabelOption('always_wrap'))
                ) {
                    $label = '<span>' . $label . '</span>';
                }

                // Button element is a special case, because label is always rendered inside it
                if ($element instanceof Button) {
                    $labelOpen = $labelClose = $label = '';
                }

                if ($element instanceof LabelAwareInterface && $element->getLabelOption('label_position')) {
                    $labelPosition = $element->getLabelOption('label_position');
                }

                switch ($labelPosition) {
                    case self::LABEL_PREPEND:
                        $markup = $labelOpen . $label . $elementString . $labelClose;
                        break;
                    case self::LABEL_APPEND:
                    default:
                        $markup = $labelOpen . $elementString . $label . $labelClose;
                        break;
                }
            }

            if ($this->renderErrors) {
                $markup .= $elementErrors;
            }
        } else {
            if ($this->renderErrors) {
                $markup = $elementString . $elementErrors;
            } else {
                $markup = $elementString;
            }
        }

        return $markup;
    }

    /**
     * Set the class that is added to element that have errors
     *
     * @return $this
     */
    public function setInputErrorClass(string $inputErrorClass)
    {
        $this->inputErrorClass = $inputErrorClass;
        return $this;
    }

    /**
     * Get the class that is added to element that have errors
     */
    public function getInputErrorClass(): string
    {
        return $this->inputErrorClass;
    }
}
