<?php

namespace SilverStripe\SearchService\Extensions\Subsites;

use SilverStripe\Core\Extension;
use SilverStripe\SearchService\DataObject\DataObjectDocument;
use SilverStripe\SearchService\Interfaces\DocumentInterface;

class IndexConfigurationExtension extends Extension
{
    /**
     * @param DocumentInterface $doc
     * @param array $indexes
     */
    public function updateIndexesForDocument(DocumentInterface $doc, array &$indexes): void
    {
        $docSubsiteId = 0;

        if ($doc instanceof DataObjectDocument) {
            $docSubsiteId = $doc->getDataObject()->SubsiteID ?? 0;
        }

        $this->updateDocumentWithoutSubsite($doc, $indexes, (int)$docSubsiteId);
        $this->updateDocumentWithSubsite($doc, $indexes, (int)$docSubsiteId);
    }

    /**
     * DataObject does not have a defined SubsiteID. So if the developer explicitly defined the dataObject to be
     * included in the Subsite Index configuration then allow the dataObject to be added in.
     */
    protected function updateDocumentWithoutSubsite(DocumentInterface $doc, array &$indexes, int $docSubsiteId): void
    {
        // Document that implement subsite not processed here
        if ($docSubsiteId) {
            return;
        }

        foreach ($indexes as $indexName => $data) {
            // DataObject explicitly defined on Subsite index definition
            $explicitClasses = $data['includeClasses'] ?? [];
            if (!isset($explicitClasses[$doc->getDataObject()->ClassName])) {
                unset($indexes[$indexName]);
                break;
            }
        }
    }
    protected function updateDocumentWithSubsite(DocumentInterface $doc, array &$indexes, int $docSubsiteId): void
    {
        // Document that does not implement subsite are not processed here
        if (!$docSubsiteId) {
            return;
        }

        foreach ($indexes as $indexName => $data) {
            $subsiteId = $data['subsite_id'] ?? 'all';

            if ($subsiteId !== 'all' && $docSubsiteId !== (int)$subsiteId) {
                unset($indexes[$indexName]);
            }
        }
    }
}
