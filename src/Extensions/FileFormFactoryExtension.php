<?php

namespace SilverStripe\SearchService\Extensions;

use SilverStripe\Assets\File;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;

/**
 * Extension that updates the CMS fields of `FileFormFactory`.
 */
class FileFormFactoryExtension extends Extension
{
    public function updateFormFields(FieldList $fields, $controller, $formName, $context): void
    {

        $indexedField = DatetimeField::create(
            'SearchIndexed',
            _t(File::class . '.LastIndexed', 'Last indexed in search')
        )->setReadonly(true);

        $showInSearchField = CheckboxField::create(
            'ShowInSearch',
            _t(File::class . '.ShowInSearch', 'Show in search')
        );

        $fields->addFieldToTab('Editor.Details', $indexedField);
        $fields->addFieldToTab('Editor.Details', $showInSearchField);

    }
}

