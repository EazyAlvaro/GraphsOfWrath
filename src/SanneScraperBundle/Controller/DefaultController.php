<?php

namespace SanneScraperBundle\Controller;

use SanneScraperBundle\Services\ApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
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
        $url = 'http://listography.com/2271185865';
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
     * This is where i experiment. Subject to change at all times.
     *
     * @route("test")
     */
    public function testAction()
    {
        //TODO use dependency inj once done prototyping
        $api = new ApiService($this->getDoctrine()->getManager());

        $years = $api->getYears();
        
        $results = $this->getDoctrine()
                ->getRepository('SanneScraperBundle:Statistic')
                ->findByYear($years[0]);

        return $this->render('SanneScraperBundle:Default:stat.html.twig', [
            'results' => $results,
            'years' => $years,
            ]
        );
    }

    /**
     * Show all pre-generated stats .png images in a single page.
     *
     * @Route("/stats")
     */
    public function statsAction()
    {
        $query = $this->getDoctrine()
                ->getRepository('SanneScraperBundle:Statistic')
                ->createQueryBuilder('s')
                ->orderBy('s.year', 'ASC')
                ->getQuery();
        $results = $query->getResult();

        return $this->render('SanneScraperBundle:Default:stats.html.twig', ['results' => $results]);
    }
}
