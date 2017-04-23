<?php

namespace SanneScraperBundle;

use PHPUnit_Framework_TestCase;
use SanneScraperBundle\Services\ApiService;

class ApiServiceTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorExpectingException()
    {
        $this->expectException(\Exception::class);
        $api = new ApiService();
    }
}
