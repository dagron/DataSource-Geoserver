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

        $build_rule = new PreparingBuildRule('_before');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertEquals(
            ['title' => 'test'],
            $data
        );

        $this->assertEquals(
            [
                'Dataset: _before: No description harvested, skipping metadata extraction',
            ],
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

        $build_rule = new PreparingBuildRule('_before');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertEquals(
            ['title' => 'test', 'description' => null],
            $data
        );

        $this->assertEquals(
            [
                'Dataset: _before: No description harvested, skipping metadata extraction',
            ],
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

        $build_rule = new PreparingBuildRule('_before');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertSame(
            ['title' => 'test', 'description' => ''],
            $data
        );

        $this->assertEquals(
            [
                'Dataset: _before: No description harvested, skipping metadata extraction',
            ],
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

        $build_rule = new PreparingBuildRule('_before');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertSame(
            [
                'title'       => 'test',
                'description' => 'test',
                'theme'       => [],
            ],
            $data
        );

        $this->assertEquals(
            [
                'Dataset: _before: Attempting title metadata extraction',
                'Dataset: _before: Title starting pattern not present, skipping',
                'Dataset: _before: Attempting theme metadata extraction',
                'Dataset: _before: Theme starting pattern not present, skipping',
            ],
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

        $build_rule = new PreparingBuildRule('_before');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertSame(
            [
                'title'       => 'test',
                'description' => '[Thema: asdasdasd',
                'theme'       => [],
            ],
            $data
        );

        $this->assertEquals(
            [
                'Dataset: _before: Attempting title metadata extraction',
                'Dataset: _before: Title starting pattern not present, skipping',
                'Dataset: _before: Attempting theme metadata extraction',
                'Dataset: _before: Theme closing pattern not present, skipping',
            ],
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

        $build_rule = new PreparingBuildRule('_before');
        $build_rule->applyRule($dataset, $data, [], [], [], [], $notices, 'Dataset:');

        $this->assertSame(
            ['title' => 'test', 'description' => 'asd', 'theme' => ['test']],
            $data
        );

        $this->assertSame(
            [
                'Dataset: _before: Attempting title metadata extraction',
                'Dataset: _before: Title starting pattern not present, skipping',
                'Dataset: _before: Attempting theme metadata extraction',
                'Dataset: _before: Extracted 1 theme(s) from harvested description',
            ],
            $notices
        );
    }
}
