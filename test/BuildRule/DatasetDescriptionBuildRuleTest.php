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
        $builder = new DatasetDescriptionBuildRule();
        $data    = ['title' => 'test'];
        $notices = [];

        $this->assertNull($dataset->getDescription());

        $builder->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertNotNull($dataset->getDescription());
    }

    public function testWhenDescriptionIsPresentAndNonEmptyThatDescriptionIsUsed(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => 'test'];
        $notices = [];

        $build_rule = new DatasetDescriptionBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertSame(
            ['title' => 'test', 'description' => 'test'],
            $data
        );

        $this->assertEquals(
            ['Description: using description found in geoserver'],
            $notices
        );

        $expected_dataset = new DCATDataset();
        $expected_dataset->setDescription(new DCATLiteral('test'));

        $this->assertEquals($expected_dataset, $dataset);
    }
}
