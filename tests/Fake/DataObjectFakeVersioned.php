<?php

namespace SilverStripe\SearchService\Tests\Fake;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\SearchService\Extensions\SearchServiceExtension;
use SilverStripe\SearchService\Interfaces\DocumentInterface;
use SilverStripe\Versioned\Versioned;

/**
 * @property string $Title
 * @property int $ShowInSearch
 * @mixin SearchServiceExtension
 * @mixin Versioned
 */
class DataObjectFakeVersioned extends DataObject implements TestOnly, DocumentInterface
{

    private static string $table_name = 'DataObjectFakeVersioned';

    private static array $extensions = [
        SearchServiceExtension::class,
        Versioned::class,
    ];

    private static array $db = [
        'Title' => 'Varchar',
    ];

    public function canView(mixed $member = null): bool
    {
        return true;
    }

    public function getIdentifier(): string
    {
        return (string) $this->ID;
    }

    public function shouldIndex(): bool
    {
        return (bool) $this->ShowInSearch;
    }

    public function markIndexed(): void
    {
        $this->ShowInSearch = 1;
    }

    public function toArray(): array
    {
        return [
            'Title' => $this->Title,
        ];
    }

    public function getSourceClass(): string
    {
        return static::class;
    }

}
