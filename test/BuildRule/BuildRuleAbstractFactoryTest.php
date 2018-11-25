<?php

namespace NijmegenSync\Test\DataSource\Geoserver\BuildRule;

use NijmegenSync\DataSource\Geoserver\BuildRule\BuildRuleAbstractFactory;
use PHPUnit\Framework\TestCase;

class BuildRuleAbstractFactoryTest extends TestCase
{
    public function testExposesBuildRuleForDescription(): void
    {
        $build_rules = BuildRuleAbstractFactory::getAll();

        $this->assertTrue(\array_key_exists('description', $build_rules));
    }
}
