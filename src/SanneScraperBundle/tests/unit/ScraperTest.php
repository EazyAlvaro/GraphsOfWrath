<?php
namespace SanneScraperBundle;

use SanneScraperBundle\Scrapers\SanneScraper;
use \Exception;

class ScraperTest extends \PHPUnit_Framework_TestCase
{
    private $scraper;
    
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
        $this->assertEquals(1, $this->scraper->determineType('/boeken/i')) ;
        $this->assertEquals(2, $this->scraper->determineType('/films/i')) ;
        
        $this->expectException(\Exception::class);
        $this->assertEquals(2, $this->scraper->determineType('/explosion/i')) ;
    }
}
