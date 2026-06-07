<?php

namespace SilverStripe\SearchService\Tests\Tasks;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\SearchService\Interfaces\IndexingInterface;
use SilverStripe\SearchService\Tasks\SearchConfigure;
use SilverStripe\SearchService\Tests\Fake\ServiceFake;
use SilverStripe\SearchService\Tests\SearchServiceTest;

class SearchConfigureTest extends SearchServiceTest
{

    public function testTask(): void
    {
        $mock = $this->getMockBuilder(ServiceFake::class)
            ->onlyMethods(['configure'])
            ->getMock();
        $mock->expects($this->once())
            ->method('configure');
        Injector::inst()->registerService($mock, IndexingInterface::class);

        $task = SearchConfigure::create();
        $this->runTask($task);
    }

}
