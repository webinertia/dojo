<?php

declare(strict_types=1);

namespace Dojo;

use Dojo\Form\Element\Submit;
use Laminas\Form\Exception;
use Laminas\Form\Form as LaminasForm;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\InputFilter\InputFilterProviderInterface;

use function strtolower;

class Form extends LaminasForm implements InputFilterProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'method'         => 'POST',
        'data-dojo-type' => 'dojox/form/Manager',
    ];

    public function addSubmit(int $priority = 1, string $showText = 'Save'): void
    {
        $this->add(
            [
                'name'       => 'submit',
                'type'       => Submit::class,
                'attributes' => [
                    'value' => $showText,
                    'id'    => strtolower($showText) . $this->getAttribute('name') . 'Button',
                ],
            ],
            ['priority' => $priority],
        );
    }

    public function getMessages(?string $elementName = null): array
    {
        $dojoMessages = [];
        $openBracket    = '[';
        $closingBracket = ']';
        $inputString = 'input' . $openBracket . 'name="';
        if (null === $elementName) {
            $messages = $this->messages;
            foreach ($this->iterator as $name => $element) {
                $messageSet = $element->getMessages();
                if (
                    empty($messageSet)
                    || (! is_array($messageSet) && ! $messageSet instanceof Traversable)
                ) {
                    continue;
                }
                if ($element instanceof FieldsetInterface) {
                     if (is_array($messageSet)) {
                        $i = 0;
                        foreach ($messageSet as $elemName => $elemValue) {
                            $inputString .= $name . $openBracket . $elemName . $closingBracket . '"' . $closingBracket;
                            $dojoMessages[$i]['input'] = $inputString;
                            foreach ($elemValue as $errorType => $errorMessage) {
                                $dojoMessages[$i]['message'] = $errorMessage;
                            }
                            $i++;
                        }
                     }
                }
                $messages[$name] = $messageSet;
            }
            /** returns an array
             * ['input'] = dojo query compt string
             * ['message'] = the actual message that was reported for the input
             */
            return $dojoMessages;
        }

        if (! $this->has($elementName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid element name "%s" provided to %s',
                $elementName,
                __METHOD__
            ));
        }

        $element = $this->get($elementName);
        return $element->getMessages();
    }

    public function getInputFilterSpecification(): array
    {
        return [];
    }
}
