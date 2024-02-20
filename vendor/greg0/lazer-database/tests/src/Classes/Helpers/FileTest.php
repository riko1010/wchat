<?php

declare(strict_types=1);

namespace Lazer\Test\Classes\Helpers;

use Lazer\Classes\Helpers\File;
use Lazer\Classes\LazerException;
use PHPUnit\Framework\TestCase;
use Lazer\Test\VfsHelper\Config as TestHelper;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectExceptionMessageMatches;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-04-03 at 12:57:13.
 */
class FileTest extends TestCase {

    use TestHelper;
    use ExpectExceptionMessageMatches;

    /**
     * @var File
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->setUpFilesystem();
        $this->object = new File;
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::table
     */
    public function testTable()
    {
        $object = $this->object->table('users');
        $this->assertInstanceOf('Lazer\Classes\Helpers\File', $object);
        return $object;
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::getPath
     * @depends testTable
     */
    public function testGetPathIfTypeIsNull($object)
    {
        $this->expectException(LazerException::class);
        $this->expectExceptionMessageMatches('#Please specify the type of file in class: [a-zA-Z0-9]+#');
        $object->getPath();
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::getPath
     * @depends testTable
     */
    public function testGetPath($object)
    {
        $object->setType('data');
        $this->assertSame(LAZER_DATA_PATH . 'users.data.json', $object->getPath());
        return $object;
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::get
     * @depends testTable
     */
    public function testGetDataReturnArray($object)
    {
        $object->setType('data');
        $this->assertIsArray($object->get());
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::get
     * @depends testTable
     */
    public function testGetConfigReturnObject($object)
    {
        $object->setType('config');
        $this->assertIsObject($object->get());
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::get
     * @depends testTable
     */
    public function testGetConfigReturnArray($object)
    {
        $object->setType('config');
        $this->assertIsArray($object->get(true));
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::put
     * @depends testGetPath
     */
    public function testSaveToFile($object)
    {
        $this->assertIsInt($object->put('test'));
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::put
     */
    public function testCreateFile()
    {
        $object = $this->object->table('foo');
        $object->setType('bar');
        $this->assertFalse($this->root->hasChild('foo.bar.json'));
        $this->assertIsInt($object->put('test'));
        $this->assertTrue($this->root->hasChild('foo.bar.json'));
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::exists
     * @depends testGetPath
     */
    public function testExists($object)
    {
        $this->assertTrue($object->exists());
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::remove
     */
    public function testRemove()
    {
        $object = $this->object->table('users');
        $object->setType('data');
        $this->assertTrue($this->root->hasChild('users.data.json'));
        $this->assertTrue($object->remove());
        $this->assertFalse($this->root->hasChild('users.data.json'));
    }

    /**
     * @covers \Lazer\Classes\Helpers\File::remove
     */
    public function testRemoveFileNotExists()
    {
        $this->expectException(LazerException::class);
        $this->expectExceptionMessage('Data: File does not exists');
        $object = $this->object->table('ghost');
        $object->setType('data');
        $this->assertFalse($this->root->hasChild('ghost.data.json'));
        $object->remove();
    }

}
