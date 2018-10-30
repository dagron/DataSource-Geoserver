<?php

use GeoserverDataSource\GeoserverDataSourceManager;
use NijmegenSync\Contracts\Exceptions\InitializationException;
use NijmegenSync\Contracts\IFileSystemHelper;
use PHPUnit\Framework\TestCase;


class GeoserverDataSourceManagerTest extends TestCase {

    public function testAcceptsImplementationsOfInterfaceRatherThanSpecificImplementation(): void
    {
        $manager = new GeoserverDataSourceManager();
        $implementation = new class() implements IFileSystemHelper {
            public function readFile(string $file): string { return ''; }
            public function fileExists(string $file): bool { return true; }
        };
        $manager->setFileSystemHelper($implementation);
        $this->assertTrue(true);
    }
    public function testThrowsExceptionWhenFileSystemHelperIsNotSet(): void
    {
        $manager = new GeoserverDataSourceManager();
        try {
            $manager->initialize();
            $this->fail();
        } catch (InitializationException $e) {
            $this->assertTrue(true);
        }
    }

}
