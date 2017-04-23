<?php

namespace SanneScraperBundle;

use SanneScraperBundle\Services\ApiService;
use Codeception\Test\Unit;

class ApiServiceTest extends Unit
{
    public function testConstructorExpectingException()
    {
        $this->expectException(\Exception::class);
        $api = new ApiService();
    }

    public function testConstructorExpectingNoFailure()
    {
        //$em = $this->getModule('Doctrine2')->em;

        $container = $this->getModule('Symfony')->container;

        //\MyDb::createEntityManager();
    }
}
