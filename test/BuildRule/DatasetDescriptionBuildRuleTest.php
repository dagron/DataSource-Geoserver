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

        $this->assertNull($dataset->getDescription());

        $builder->applyRule($dataset, ['title' => 'test'], [], [], [], []);

        $this->assertNotNull($dataset->getDescription());
    }
}
