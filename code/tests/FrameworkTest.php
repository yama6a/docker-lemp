<?php

namespace Tests;

require_once __DIR__.'/../vendor/autoload.php';
include __DIR__.'/../mysql.php';

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class FrameworkTest extends TestCase
{
    protected Connection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        if(!isset($this->conn)){
            $this->conn = getDbConnection();
        }

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->conn->close();
    }
}
