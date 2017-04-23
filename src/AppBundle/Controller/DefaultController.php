<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use SanneScraperBundle\Scrapers\SanneScraper;

class DefaultController extends Controller {

    /**
     * @Route("/sanne", name="homepage")
     */
    public function indexAction(Request $request) {
        $url = "http://listography.com/2271185865";

        $scraper = new SanneScraper();
        $scraper->flush();
        $scraper->setURL($url);
        $scraper->load();
        $scraper->start();
        
        return NULL;

    }

}
