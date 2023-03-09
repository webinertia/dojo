<?php

declare(strict_types=1);

namespace Dojo;

use Laminas\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                View\Container::class => InvokableFactory::class,
            ]
        ];
    }

    public function getViewHelperConfig(): array
    {
        return [
            'aliases'   => [
                'dijitFormCollection' => Form\View\Helper\DijitFormCollection::class,
                'dijitFormEditor'     => Form\View\Helper\DijitFormEditor::class,
                'formEditor'          => Form\View\Helper\DijitFormEditor::class,
                'formeditor'          => Form\View\Helper\DijitFormEditor::class,
                'dijitFormRow'        => Form\View\Helper\DijitFormRow::class,
                'dijitForm'           => Form\View\Helper\DijitForm::class,
                'dijitFormFileList'   => Form\View\Helper\DijitFormUploaderFileList::class,
                'dojo'                => View\Helper\Dojo::class,
             ],
            'factories' => [
                Form\View\Helper\DijitFormCollection::class       => InvokableFactory::class,
                Form\View\Helper\DijitFormEditor::class           => InvokableFactory::class,
                Form\View\Helper\DijitFormRow::class              => InvokableFactory::class,
                Form\View\Helper\DijitForm::class                 => InvokableFactory::class,
                Form\View\Helper\DijitFormUploaderFileList::class => InvokableFactory::class,
                View\Helper\Dojo::class                           => View\Helper\Factory\DojoFactory::class,
            ],
        ];
    }

    public function getFormElementConfig(): array
    {
        return [
            'factories' => [
                Form\Element\Button::class            => InvokableFactory::class,
                Form\Element\CurrencyTextBox::class   => InvokableFactory::class,
                Form\Element\DateTextBox::class       => InvokableFactory::class,
                Form\Element\ComboBox::class          => InvokableFactory::class,
                Form\Element\Editor::class            => InvokableFactory::class,
                Form\Element\Select::class            => InvokableFactory::class,
                Form\Element\Submit::class            => InvokableFactory::class,
                Form\Element\TextBox::class           => InvokableFactory::class,
                Form\Element\Editor::class            => InvokableFactory::class,
                Form\Element\File::class              => InvokableFactory::class,
                Form\Element\MultiCheckbox::class     => InvokableFactory::class,
                Form\Element\ValidationTextBox::class => InvokableFactory::class,
            ],
        ];
    }

    public function getFilterConfig(): array
    {
        return [
            'factories' => [
                Filter\UploaderFilter::class => InvokableFactory::class,
            ],
        ];
    }
}
