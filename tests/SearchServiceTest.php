<?php

namespace SilverStripe\SearchService\Tests;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SearchService\DataObject\DataObjectDocument;
use SilverStripe\SearchService\Extensions\SearchServiceExtension;
use SilverStripe\SearchService\Interfaces\IndexingInterface;
use SilverStripe\SearchService\Service\IndexConfiguration;
use SilverStripe\SearchService\Tests\Fake\DataObjectFakeAlternate;
use SilverStripe\SearchService\Tests\Fake\DataObjectFake;
use SilverStripe\SearchService\Tests\Fake\DataObjectFakePrivate;
use SilverStripe\SearchService\Tests\Fake\DataObjectFakeVersioned;
use SilverStripe\SearchService\Tests\Fake\DataObjectSubclassFake;
use SilverStripe\SearchService\Tests\Fake\ImageFake;
use SilverStripe\SearchService\Tests\Fake\IndexConfigurationFake;
use SilverStripe\SearchService\Tests\Fake\ServiceFake;
use SilverStripe\SearchService\Tests\Fake\TagFake;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class SearchServiceTest extends SapphireTest
{

    protected static $required_extensions = [
        DataObjectFake::class => [
            SearchServiceExtension::class,
        ],
        DataObjectFakeAlternate::class => [
            SearchServiceExtension::class,
        ],
        DataObjectFakePrivate::class => [
            SearchServiceExtension::class,
        ],
        DataObjectFakeVersioned::class => [
            SearchServiceExtension::class,
        ],
        DataObjectSubclassFake::class => [
            SearchServiceExtension::class,
        ],
        ImageFake::class => [
            SearchServiceExtension::class,
        ],
        TagFake::class => [
            SearchServiceExtension::class,
        ],
    ];

    protected function mockConfig(): IndexConfigurationFake
    {
        Injector::inst()->registerService($config = new IndexConfigurationFake(), IndexConfiguration::class);
        SearchServiceExtension::singleton()->setConfiguration($config);

        return $config;
    }

    protected function mockService(): ServiceFake
    {
        Injector::inst()->registerService($service = new ServiceFake(), IndexingInterface::class);
        SearchServiceExtension::singleton()->setIndexService($service);

        return $service;
    }

    protected function loadIndex(int $count = 10): ServiceFake
    {
        $service = $this->mockService();

        for ($i = 0; $i < $count; $i++) {
            $dataobject = DataObjectFake::create([
                'Title' => 'Dataobject ' . $i,
            ]);

            $dataobject->write();
            $doc = DataObjectDocument::create($dataobject);
            $service->addDocument($doc);
        }

        return $service;
    }

    protected function runTask(BuildTask $task, array $options = []): string
    {
        $arguments = [];
        foreach ($options as $name => $value) {
            $arguments["--{$name}"] = $value;
        }
        $input = new ArrayInput(
            $arguments,
            new InputDefinition($task->getOptions())
        );
        $buffer = new BufferedOutput();
        $task->run($input, new PolyOutput(PolyOutput::FORMAT_ANSI, wrappedOutput: $buffer));

        return $buffer->fetch();
    }

}
