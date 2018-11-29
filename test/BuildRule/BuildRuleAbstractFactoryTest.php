<?php

namespace NijmegenSync\Test\DataSource\Geoserver\BuildRule;

use NijmegenSync\DataSource\Geoserver\BuildRule\BuildRuleAbstractFactory;
use PHPUnit\Framework\TestCase;

class BuildRuleAbstractFactoryTest extends TestCase
{
    public function testExposesBuildRuleForDescription(): void
    {
        $build_rules = BuildRuleAbstractFactory::getAll();

        $this->assertArrayHasKey('_before', $build_rules);
        $this->assertArrayHasKey('description', $build_rules);
    }
}
