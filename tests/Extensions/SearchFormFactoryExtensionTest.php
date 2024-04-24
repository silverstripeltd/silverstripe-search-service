<?php

namespace SilverStripe\SearchService\Tests\Extensions;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\Form;
use SilverStripe\SearchService\Extensions\SearchFormFactoryExtension;

class SearchFormFactoryExtensionTest extends SapphireTest
{

    protected static $fixture_file = [
        '../fixtures.yml',
        '../pages.yml',
    ];

    public function testDefaultConfigValues(): void
    {
        $expected = ['SilverStripe\Assets\Image'];
        $actual = Config::inst()->get(SearchFormFactoryExtension::class, 'exclude_classes');
        $this->assertEquals($expected, $actual);
    }

    public function testImageSearchIndex(): void
    {
        $image = $this->objFromFixture(Image::class, 'image');
        $this->assertEquals(1, $image->ShowInSearch);

        $searchFormFactoryExtension = new SearchFormFactoryExtension();
        $form = Form::create();
        $searchFormFactoryExtension->updateForm($form, null, 'Form', ['Record' => $image]);
        $this->assertEquals(0, $image->ShowInSearch);

        $file = $this->objFromFixture(File::class, 'pdf-file');
        $searchFormFactoryExtension->updateForm($form, null, 'Form', ['Record' => $file]);
        $this->assertEquals(1, $file->ShowInSearch);
    }

    public function testImageSearchIndexWithExcludedExtension(): void
    {
        // Modify config to exclude pdf files from search
        Config::modify()->set(SearchFormFactoryExtension::class, 'exclude_file_extensions', ['pdf']);

        $file = $this->objFromFixture(File::class, 'pdf-file');
        // Every file has defaults ShowInSearch value of 1 (https://github.com/silverstripe/silverstripe-assets/blob/2/src/File.php#L163)
        $this->assertEquals(1, $file->ShowInSearch);

        $form = Form::create();
        $searchFormFactoryExtension = new SearchFormFactoryExtension();
        $searchFormFactoryExtension->updateForm($form, null, 'Form', ['Record' => $file]);
        $this->assertEquals(0, $file->ShowInSearch);
    }

}
