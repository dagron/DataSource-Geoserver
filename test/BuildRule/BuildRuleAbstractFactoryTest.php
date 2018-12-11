<?php

namespace NijmegenSync\Test\DataSource\Geoserver\BuildRule;

use NijmegenSync\DataSource\Geoserver\BuildRule\BuildRuleAbstractFactory;
use PHPUnit\Framework\TestCase;

class BuildRuleAbstractFactoryTest extends TestCase
{
    public function testExposesDatasetBuildRules(): void
    {
        $build_rules = BuildRuleAbstractFactory::getAllDatasetBuildRules();

        $this->assertArrayHasKey('_before', $build_rules);
    }

    public function testExposesNoDistributionBuildRules(): void
    {
        $build_rules = BuildRuleAbstractFactory::getAllDistributionBuildRules();

        $this->assertTrue(0 == \count($build_rules));
    }
}
