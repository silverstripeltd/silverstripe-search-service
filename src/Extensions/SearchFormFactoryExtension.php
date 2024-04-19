<?php

namespace SilverStripe\SearchService\Extensions;

use SilverStripe\Assets\Image;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormFactory;

class SearchFormFactoryExtension extends Extension
{

    public function updateForm(
        Form $form,
        ?RequestHandler $controller = null,
        string $name = FormFactory::DEFAULT_NAME,
        array $context = []
    ): void {
        $fields = $form->Fields()->findOrMakeTab('Editor.Details');
        $file = $context['Record'] ?? null;

        if (!$fields || !$file || $file instanceof Image) {
            return;
        }

        $fields->push(
            CheckboxField::create(
                'ShowInSearch',
                _t(
                    'SilverStripe\\AssetAdmin\\Controller\\AssetAdmin.SHOWINSEARRCH',
                    'Show in search?'
                )
            )
        );

        $fields->push(
            DatetimeField::create(
                'SearchIndexed',
                _t(
                    'SilverStripe\\SearchService\\Extensions\\SearchServiceExtension.LastIndexed',
                    'Last indexed in search'
                )
            )
                ->setReadonly(true)
        );
    }

}
