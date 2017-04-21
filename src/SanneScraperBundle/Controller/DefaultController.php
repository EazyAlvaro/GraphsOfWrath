<?php

namespace SanneScraperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller {

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('SanneScraperBundle:Default:stats.html.twig');
    }

    /**
     * @Route("/generate")
     */
    public function generateAction()
    {
        $url = "http://listography.com/2271185865";
        $em = $this->getDoctrine()->getManager();
        
        // clean out the old data before crawling
        $em->createQuery('DELETE FROM SanneScraperBundle:Statistic')->execute();
          
        /** @var $scraper SanneScraperBundle/Scrapers/SanneScraper $scraper */
        $scraper = $this->get('sanne.scraper');
        $scraper->setURL($url);
        $scraper->load();
        $scraper->start();

        return $this->render('SanneScraperBundle:Default:generate.html.twig');
    }

    /**
     * @route("test")
     */
    public function testAction()
    {
        $results = $this->getDoctrine()
                ->getRepository("SanneScraperBundle:Statistic")
                ->findByYear(2012);
        
        return $this->render('SanneScraperBundle:Default:stat.html.twig', array(
            'results' => $results
        ));
    }

    /**
     * @Route("/stats")
     */
    public function statsAction()
    {
        return $this->render('SanneScraperBundle:Default:stats.html.twig');
    }

}
