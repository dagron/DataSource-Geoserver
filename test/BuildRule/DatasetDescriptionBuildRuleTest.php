<?php

namespace NijmegenSync\Test\DataSource\Geoserver\BuildRule;

use DCAT_AP_DONL\DCATDataset;
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
}
