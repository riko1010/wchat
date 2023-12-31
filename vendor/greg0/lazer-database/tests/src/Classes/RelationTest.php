<?php

declare(strict_types=1);

namespace Lazer\Test\Classes;

use Lazer\Classes\Database;
use Lazer\Classes\Relation;
use Lazer\Test\VfsHelper\Config as TestHelper;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase {

    use TestHelper;

    /**
     * @var Database
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->setUpFilesystem();
        $this->object = new Relation();
    }


    public function testDummy()
    {
       $this->markTestSkipped('TODO tests for relation');
    }


}
