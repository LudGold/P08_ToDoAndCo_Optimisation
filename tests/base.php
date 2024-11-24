<?php

namespace App\tests;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}