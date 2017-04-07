<?php

namespace SanneScraperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SanneScraperBundle\Scrapers\SanneScraper;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('SanneScraperBundle:Default:index.html.twig');
    }
    
    
    /**
     * @Route("/generate")
     */
    public function generateAction()
    {
        $url = "http://listography.com/2271185865";

        $scraper = new SanneScraper();
        $scraper->flush();
        $scraper->setURL($url);
        $scraper->load();
        $scraper->start();
        
        return $this->render('SanneScraperBundle:Default:index.html.twig');
    }
    
    
      /**
     * @Route("/stats")
     */
    public function statsAction()
    {
        return $this->render('SanneScraperBundle:Default:stats.html.twig');
    }
    
}
