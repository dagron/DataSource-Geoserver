<?php

namespace NijmegenSync\Test\DataSource\Geoserver\BuildRule;

use DCAT_AP_DONL\DCATDataset;
use DCAT_AP_DONL\DCATLiteral;
use NijmegenSync\DataSource\Geoserver\BuildRule\DatasetDescriptionBuildRule;
use PHPUnit\Framework\TestCase;

class DatasetDescriptionBuildRuleTest extends TestCase
{
    public function testAddsContentToTheDescriptionProperty(): void
    {
        $dataset = new DCATDataset();
        $builder = new DatasetDescriptionBuildRule('description');
        $data    = ['title' => 'test', 'geoserver_layers' => ['a']];
        $notices = [];

        $this->assertNull($dataset->getDescription());

        $builder->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertNotNull($dataset->getDescription());
    }

    public function testWhenDescriptionIsPresentAndNonEmptyThatDescriptionIsUsed(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => 'test'];
        $notices = [];

        $build_rule = new DatasetDescriptionBuildRule('Description');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertSame(
            ['title' => 'test', 'description' => 'test'],
            $data
        );

        $this->assertEquals(
            ['Dataset: Description: Using description found in geoserver'],
            $notices
        );

        $expected_dataset = new DCATDataset();
        $expected_dataset->setDescription(new DCATLiteral('test'));

        $this->assertEquals($expected_dataset, $dataset);
    }
}
