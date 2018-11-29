<?php

namespace NijmegenSync\Test\DataSource\Geoserver\BuildRule;

use DCAT_AP_DONL\DCATDataset;
use NijmegenSync\DataSource\Geoserver\BuildRule\PreparingBuildRule;
use PHPUnit\Framework\TestCase;

class PreparingBuildRuleTest extends TestCase
{
    public function testDataDoesNotChangeWhenDescriptionIsNotPresent(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test'];
        $notices = [];

        $build_rule = new PreparingBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertEquals(
            ['title' => 'test'],
            $data
        );

        $this->assertEquals(
            [],
            $notices
        );

        $this->assertEquals(
            new DCATDataset(),
            $dataset
        );
    }

    public function testDataDoesNotChangeWhenDescriptionIsNull(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => null];
        $notices = [];

        $build_rule = new PreparingBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertEquals(
            ['title' => 'test', 'description' => null],
            $data
        );

        $this->assertEquals(
            [],
            $notices
        );

        $this->assertEquals(
            new DCATDataset(),
            $dataset
        );
    }

    public function testDataDoesNotChangeWhenDescriptionIsEmpty(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => ''];
        $notices = [];

        $build_rule = new PreparingBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertSame(
            ['title' => 'test', 'description' => ''],
            $data
        );

        $this->assertEquals(
            [],
            $notices
        );

        $this->assertEquals(
            new DCATDataset(),
            $dataset
        );
    }

    public function testWhenDescriptionDoesNotMatchThePatternNoActionIsTaken(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => 'test'];
        $notices = [];

        $build_rule = new PreparingBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertSame(
            ['title' => 'test', 'description' => 'test'],
            $data
        );

        $this->assertEquals(
            [],
            $notices
        );

        $this->assertEquals(
            new DCATDataset(),
            $dataset
        );
    }

    public function testNoticesAreGeneratedWhenNoClosingPatternIsFound(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => '[Thema: asdasdasd'];
        $notices = [];

        $build_rule = new PreparingBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertSame(
            ['title' => 'test', 'description' => '[Thema: asdasdasd'],
            $data
        );

        $this->assertEquals(
            ['could not extract themes from harvested description, no ] character found'],
            $notices
        );

        $this->assertEquals(
            new DCATDataset(),
            $dataset
        );
    }

    public function testThemesAreRemovedFromTheDescription(): void
    {
        $dataset = new DCATDataset();
        $data    = ['title' => 'test', 'description' => '[Thema: test ]asd'];
        $notices = [];

        $build_rule = new PreparingBuildRule();
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices);

        $this->assertSame(
            ['title' => 'test', 'description' => 'asd', 'theme' => ['test']],
            $data
        );

        $this->assertSame(
            ['extracted 1 themes from harvested description'],
            $notices
        );
    }
}
