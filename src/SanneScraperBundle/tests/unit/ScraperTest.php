<?php

namespace SanneScraperBundle;

use SanneScraperBundle\Scrapers\SanneScraper;

class ScraperTest extends \PHPUnit_Framework_TestCase
{
    private $scraper;

    /**
     * @todo eventually figure out how to do DI for this
     */
    protected function setUp()
    {
        $this->scraper = new SanneScraper();
    }

    protected function tearDown()
    {
    }

    // tests
    public function testDetermineType()
    {
        $this->assertEquals(1, $this->scraper->determineType('boeken 1234'));
        $this->assertEquals(2, $this->scraper->determineType('films 1234'));
        $this->expectException(\InvalidArgumentException::class);
        $this->scraper->determineType('exception');
    }
}
