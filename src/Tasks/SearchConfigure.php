<?php

namespace SilverStripe\SearchService\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SearchService\Exception\IndexingServiceException;
use SilverStripe\SearchService\Interfaces\IndexingInterface;
use SilverStripe\SearchService\Service\Traits\ServiceAware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Syncs index settings to a search service.
 *
 * Note this runs on dev/build automatically but is provided separately for uses where dev/build is slow (e.g 100,000+
 * record tables)
 */
class SearchConfigure extends BuildTask
{

    use ServiceAware;

    protected static string $commandName = 'SearchConfigure';

    protected string $title = 'Search Service Configure';

    protected static string $description = 'Sync search index configuration';

    public function __construct(IndexingInterface $searchService)
    {
        parent::__construct();

        $this->setIndexService($searchService);
    }

    /**
     * @throws IndexingServiceException
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $this->getIndexService()->configure();

        $output->writeln('Done.');

        return Command::SUCCESS;
    }

}
