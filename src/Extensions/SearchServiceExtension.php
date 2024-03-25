<?php

namespace SilverStripe\SearchService\Extensions;

use Exception;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\SearchService\DataObject\DataObjectBatchProcessor;
use SilverStripe\SearchService\DataObject\DataObjectDocument;
use SilverStripe\SearchService\Exception\IndexingServiceException;
use SilverStripe\SearchService\Interfaces\IndexingInterface;
use SilverStripe\SearchService\Service\IndexConfiguration;
use SilverStripe\SearchService\Service\Traits\BatchProcessorAware;
use SilverStripe\SearchService\Service\Traits\ConfigurationAware;
use SilverStripe\SearchService\Service\Traits\ServiceAware;
use SilverStripe\Versioned\Versioned;
use Throwable;

/**
 * The extension that provides implicit indexing features to dataobjects
 *
 * @property DataObject|SearchServiceExtension $owner
 * @property string $SearchIndexed
 */
class SearchServiceExtension extends DataExtension
{

    use Configurable;
    use Injectable;
    use ServiceAware;
    use ConfigurationAware;
    use BatchProcessorAware;

    private static array $db = [
        'SearchIndexed' => 'Datetime',
        'ShowInSearch' => 'Boolean(1)',
    ];

    private static array $defaults = [
        'ShowInSearch' => '1',
    ];

    private bool $hasConfigured = false;

    public function __construct(
        IndexingInterface $searchService,
        IndexConfiguration $config,
        DataObjectBatchProcessor $batchProcessor
    ) {
        parent::__construct();

        $this->setIndexService($searchService);
        $this->setConfiguration($config);
        $this->setBatchProcessor($batchProcessor);
    }

    public function updateCMSFields(FieldList $fields): void
    {
        if (!$this->getConfiguration()->isEnabled()) {
            return;
        }

        $indexedField = ReadonlyField::create(
            'SearchIndexed',
            _t(self::class . '.LastIndexed', 'Last indexed in search')
        );

        $showInSearchField = CheckboxField::create(
            'ShowInSearch',
            _t(self::class . '.ShowInSearch', 'Show in search')
        );

        if (!$this->getOwner()->hasExtension(Versioned::class)) {
            $showInSearchField->setDescription(
                _t(
                    self::class . 'ShowInSearch_Description',
                    'This setting will apply to <strong>all</strong> published and unpublished versions of this record.'
                )
            );
        }

        if ($fields->hasTabSet()) {
            $fields->addFieldToTab('Root.Main', $indexedField);

            // In the case of SiteTree objects, we want this checkbox in the settings tab alongside other access
            // restriction settings. If there isn't already a Settings tab, it would be poor UI to add it for one
            // specific field.
            if ($fields->findTab('Root.Settings')) {
                $fields->addFieldToTab('Root.Settings', $showInSearchField);
            } else {
                $fields->addFieldToTab('Root.Main', $showInSearchField);
            }
        } else {
            $fields->push($indexedField);
            $fields->push($showInSearchField);
        }
    }

    /**
     * On dev/build ensure that the indexer settings are up to date
     *
     * @throws IndexingServiceException
     */
    public function requireDefaultRecords(): void
    {
        // Wrap this in a try-catch so that dev/build can continue (with warnings) when no service has been set
        try {
            if (!$this->hasConfigured) {
                $this->getIndexService()->configure();
                $this->hasConfigured = true;
            }
        } catch (Throwable $e) {
            user_error(sprintf('Unable to configure search indexes: %s', $e->getMessage()), E_USER_WARNING);
        }
    }

    /**
     * Index this record into search or queue if configured to do so
     */
    public function addToIndexes(): void
    {
        $document = DataObjectDocument::create($this->owner);
        $this->getBatchProcessor()->addDocuments([$document]);
    }

    /**
     * Remove this item from search
     */
    public function removeFromIndexes(): void
    {
        $document = DataObjectDocument::create($this->owner)->setShouldFallbackToLatestVersion();
        $this->getBatchProcessor()->removeDocuments([$document]);
    }

    /**
     * When publishing the page, push this data to Indexer. The data which is sent to search is the rendered template
     * from the front end
     *
     * @throws Exception
     */
    public function onAfterPublish(): void
    {
        $this->owner->addToIndexes();
    }

    /**
     * When saving an un-versioned DataObject, push this data to Indexer.
     * The data which is sent to search is the rendered template from the front end
     *
     * @throws Exception
     */
    public function onAfterWrite(): void
    {
        if ($this->owner->hasExtension(Versioned::class)) {
            return;
        }

        $this->owner->addToIndexes();
    }

    /**
     * When unpublishing this item, remove from search
     */
    public function onAfterUnpublish(): void
    {
        $this->owner->removeFromIndexes();
    }

    /**
     * Before deleting this record ensure that it is removed from search
     *
     * @throws Exception
     */
    public function onAfterDelete(): void
    {
        if ($this->owner->hasExtension(Versioned::class)) {
            return;
        }

        $this->owner->removeFromIndexes();
    }

}
