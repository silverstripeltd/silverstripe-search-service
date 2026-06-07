<?php

namespace SilverStripe\SearchService\Tests\Tasks;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\SearchService\Jobs\ClearIndexJob;
use SilverStripe\SearchService\Service\SyncJobRunner;
use SilverStripe\SearchService\Tasks\SearchClearIndex;
use SilverStripe\SearchService\Tests\SearchServiceTest;

class SearchClearIndexTest extends SearchServiceTest
{

    public function testTask(): void
    {
        $config = $this->mockConfig();
        $config->set('use_sync_jobs', true);
        $mock = $this->getMockBuilder(SyncJobRunner::class)
            ->onlyMethods(['runJob'])
            ->getMock();
        $mock->expects($this->once())
            ->method('runJob')
            ->with($this->callback(function (ClearIndexJob $job) {
                return $job->getIndexName() === 'foo';
            }));

        Injector::inst()->registerService($mock, SyncJobRunner::class);

        $task = SearchClearIndex::create();
        $this->runTask($task, ['index' => 'foo']);

        $this->expectException('InvalidArgumentException');
        $this->runTask($task);
    }

}
