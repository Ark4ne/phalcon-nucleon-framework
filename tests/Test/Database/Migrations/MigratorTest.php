<?php

namespace Test\Database\Migrations;

use Neutrino\Cli\Output\Decorate;
use Neutrino\Constants\Services;
use Neutrino\Database\Migrations\Migrator;
use Neutrino\Database\Migrations\Prefix\TimestampPrefix;
use Neutrino\Database\Migrations\Storage\StorageInterface;
use Neutrino\Debug\Reflexion;
use Phalcon\Db\Adapter;
use Phalcon\Db\Dialect;
use Test\TestCase\TestCase;

class MigratorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        global $listeners;

        $listeners = [];
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

        global $listeners;

        $listeners = [];
    }

    public function testNotes()
    {
        $migrator = new Migrator($this->createMock(StorageInterface::class), new TimestampPrefix());

        Reflexion::invoke($migrator, 'note', "my first message");
        Reflexion::invoke($migrator, 'note', "my second message");

        $this->assertEquals([
            "my first message",
            "my second message"
        ], $migrator->getNotes());
    }

    public function testStorage()
    {
        $storage = $this->createMock(StorageInterface::class);

        $storage
            ->expects($this->once())
            ->method('storageExist')
            ->willReturn(true);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $this->assertEquals($storage, $migrator->getStorage());
        $this->assertTrue($migrator->storageExist());
    }

    public function testPaths()
    {
        $migrator = new Migrator($this->createMock(StorageInterface::class), new TimestampPrefix());

        $migrator->path('path_1');
        $migrator->path('path_1');
        $migrator->path('path_2');

        $this->assertEquals(['path_1', 'path_2'], $migrator->paths());
    }

    public function testGetMigrationName()
    {
        $migrator = new Migrator($this->createMock(StorageInterface::class), new TimestampPrefix());

        $this->assertEquals('file_123', $migrator->getMigrationName(BASE_PATH . '/migrations/file_123.php'));
    }

    public function testGetMigrationsFiles()
    {
        $migrator = new Migrator($this->createMock(StorageInterface::class), new TimestampPrefix());

        $files = $migrator->getMigrationFiles(BASE_PATH);

        $this->assertEquals([], $files);

        $files = $migrator->getMigrationFiles(BASE_PATH . '/migrations/migrations_dir_1');

        $this->assertEquals([
            '1511272551_CreateOne' => BASE_PATH . '/migrations/migrations_dir_1/1511272551_CreateOne.php',
            '1511272584_CreateTwo' => BASE_PATH . '/migrations/migrations_dir_1/1511272584_CreateTwo.php',
            '1511272593_UpdateOne' => BASE_PATH . '/migrations/migrations_dir_1/1511272593_UpdateOne.php',
        ], $files);

        $files = $migrator->getMigrationFiles([
            BASE_PATH . '/migrations/migrations_dir_1',
            BASE_PATH . '/migrations/migrations_dir_2'
        ]);

        $this->assertEquals([
            '1511272551_CreateOne'  => BASE_PATH . '/migrations/migrations_dir_1/1511272551_CreateOne.php',
            '1511272584_CreateTwo'  => BASE_PATH . '/migrations/migrations_dir_1/1511272584_CreateTwo.php',
            '1511272593_UpdateOne'  => BASE_PATH . '/migrations/migrations_dir_1/1511272593_UpdateOne.php',
            '1511272605_CreateTree' => BASE_PATH . '/migrations/migrations_dir_2/1511272605_CreateTree.php',
            '1511272613_UpdateTwo'  => BASE_PATH . '/migrations/migrations_dir_2/1511272613_UpdateTwo.php',
            '1511272620_UpdateTree' => BASE_PATH . '/migrations/migrations_dir_2/1511272620_UpdateTree.php',
        ], $files);
    }

    public function testGetMigrationsForRollback()
    {

        $storage = $this->createMock(StorageInterface::class);

        $storage->expects($this->once())->method('getLast')->willReturn($getLast = [
            ['migration' => 'get_last']
        ]);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $result = Reflexion::invoke($migrator, 'getMigrationsForRollback', []);

        $this->assertEquals($getLast, $result);

        $storage->expects($this->once())->method('getMigrations')->with(1)->willReturn($getMigrations = [
            ['migration' => 'get_migrations']
        ]);

        $result = Reflexion::invoke($migrator, 'getMigrationsForRollback', ['step' => 1]);

        $this->assertEquals($getMigrations, $result);

    }

    public function testRun()
    {
        $this->mockService(Services::DB, Adapter::class, true)
            ->method('getDialect')->willReturn($this->createMock(Dialect::class));

        $storage = $this->createMock(StorageInterface::class);

        $storage->method('getRan')->willReturn([]);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $migrator->run([
            BASE_PATH . '/migrations/migrations_dir_1',
            BASE_PATH . '/migrations/migrations_dir_2'
        ]);

        global $listeners;

        $this->assertCount(6, $listeners);
        $this->assertEquals([
            \CreateOne::class . '::up'  => 1,
            \CreateTwo::class . '::up'  => 1,
            \CreateTree::class . '::up' => 1,
            \UpdateOne::class . '::up'  => 1,
            \UpdateTwo::class . '::up'  => 1,
            \UpdateTree::class . '::up' => 1,
        ], $listeners);
    }

    public function testRollback()
    {
        $this->mockService(Services::DB, Adapter::class, true)
            ->expects($this->exactly(1))
            ->method('getDialect')->willReturn($this->createMock(Dialect::class));

        $storage = $this->createMock(StorageInterface::class);

        $storage->method('getLast')->willReturn([
            ['migration' => '1511367165_DownOne']
        ]);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $migrator->rollback([
            BASE_PATH . '/migrations'
        ]);

        global $listeners;

        $this->assertCount(1, $listeners);
        $this->assertEquals([
            \DownOne::class . '::down' => 1,
        ], $listeners);
    }

    public function testRollbackNoFile()
    {
        $storage = $this->createMock(StorageInterface::class);

        $storage->expects($this->once())->method('getLast')->willReturn([]);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $migrator->rollback([
            BASE_PATH . '/migrations'
        ]);

        $this->assertEquals([
            Decorate::info('Nothing to rollback.')
        ], $migrator->getNotes());
    }

    public function testReset()
    {

        $this->mockService(Services::DB, Adapter::class, true)
            ->expects($this->exactly(1))
            ->method('getDialect')->willReturn($this->createMock(Dialect::class));

        $storage = $this->createMock(StorageInterface::class);

        $storage->method('getRan')->willReturn([
            ['migration' => '1511367165_DownOne']
        ]);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $migrator->reset([
            BASE_PATH . '/migrations'
        ]);

        global $listeners;

        $this->assertCount(1, $listeners);
        $this->assertEquals([
            \DownOne::class . '::down' => 1,
        ], $listeners);
    }

    public function testResetNoFile()
    {
        $storage = $this->createMock(StorageInterface::class);

        $storage->method('getRan')->willReturn([]);

        $migrator = new Migrator($storage, new TimestampPrefix());

        $migrator->reset([
            BASE_PATH . '/migrations'
        ]);

        $this->assertEquals([
            Decorate::info('Nothing to rollback.')
        ], $migrator->getNotes());
    }
}
