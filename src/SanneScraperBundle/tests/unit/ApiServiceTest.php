<?php

namespace SanneScraperBundle;

use SanneScraperBundle\Services\ApiService;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\AbstractQuery;

class ApiServiceTest extends Unit
{
    private $emMock;

    private $queryMock;

    public function setUp()
    {
        $this->emMock = $this->createMock(EntityManager::class);

        $this->queryMock = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getResult'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    public function testGetYears()
    {
        $resultArr = [
          ['year' => 2009],
          ['year' => 2010],
          ['year' => 2011],
        ];

        $this->queryMock->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($resultArr));

        $this->emMock->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($this->queryMock));

        $api = new ApiService($this->emMock);

        $flatYears = $api->getYears();

        $this->assertEquals([2009, 2010, 2011], $flatYears);
    }

    public function testDetermineType()
    {
        $api = new ApiService(($this->emMock));

        $this->assertEquals('books', $api->determineType(['type' => 1]));
        $this->assertEquals('movies', $api->determineType(['type' => 2]));
        $this->assertEquals('unknown', $api->determineType(['type' => 0]));
        $this->assertEquals('unknown', $api->determineType(['type' => null]));

        $this->expectException(\InvalidArgumentException::class);
        $api->determineType([]);
    }

    public function testDetermineColor()
    {
        $api = new ApiService(($this->emMock));

        $actual1 = $api->determineColor(25, 25, 1);
        $this->assertEquals('rgba(0,0,10,0.4)', $actual1);

        $actual2 = $api->determineColor(20, 11, 1);
        $this->assertEquals('rgba(0,0,127,0.4)', $actual2);

        $actual3 = $api->determineColor(20, 1, 1);
        $this->assertEquals('rgba(0,0,255,0.4)', $actual3);

        $actual4 = $api->determineColor(20, 11, 2);
        $this->assertEquals('rgba(127,0,0,0.4)', $actual4);
    }

    public function testDetermineColorExpectingIAException1()
    {
        $api = new ApiService(($this->emMock));

        $this->expectException(\InvalidArgumentException::class);
        $api->determineColor(0, 25, 1);
    }

    public function testDetermineColorExpectingIAException2()
    {
        $api = new ApiService(($this->emMock));

        $this->expectException(\InvalidArgumentException::class);
        $api->determineColor(25, 0, 1);
    }

    public function testGetConfigExpectingRed()
    {
        $api = new ApiService(($this->emMock));

        $input = [
            'year' => '2017',
            'type' => 2,
            'data' => '[2,2,1,0,3,0,2,2,0,0,1,2]',
        ];

        $actual = $api->getConfig($input, 20, 11);

        $expected = [
            'borderWidth' => 5,
            'label' => 'movies 2017',
            'data' => [2, 2, 1, 0, 3, 0, 2, 2, 0, 0, 1, 2],
            'borderColor' => ['rgba(127,0,0,1)'],
            'backgroundColor' => ['rgba(127,0,0,0.4)'],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testGetConfigExpectingBlue()
    {
        $api = new ApiService(($this->emMock));

        $input = [
            'year' => '2017',
            'type' => 1,
            'data' => '[2,2,1,0,3,0,2,2,0,0,1,2]',
        ];

        $actual = $api->getConfig($input, 20, 11);

        $expected = [
            'borderWidth' => 5,
            'label' => 'books 2017',
            'data' => [2, 2, 1, 0, 3, 0, 2, 2, 0, 0, 1, 2],
            'borderColor' => ['rgba(0,0,127,1)'],
            'backgroundColor' => ['rgba(0,0,127,0.4)'],
        ];

        $this->assertEquals($expected, $actual);
    }
}
