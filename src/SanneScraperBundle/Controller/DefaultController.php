<?php

namespace SanneScraperBundle\Controller;

use SanneScraperBundle\Services\ApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/generate")
     */
    public function generateAction()
    {
        $url = 'http://listography.com/2271185865';
        $em = $this->getDoctrine()->getManager();

        // clean out the old data before crawling
        $em->createQuery('DELETE FROM SanneScraperBundle:Statistic')->execute();

        /* @var $scraper SanneScraperBundle\Scrapers\SanneScraper */
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
        $api = $this->get('sanne.api');
        
        $years = $api->getYears();

        if (empty($years)) {
            return $this->render('SanneScraperBundle:Default:nostats.html.twig');
        }

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
     * @Route("/")
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

        if (empty($results)) {
            return $this->render('SanneScraperBundle:Default:nostats.html.twig');
        }

        return $this->render('SanneScraperBundle:Default:stats.html.twig', ['results' => $results]);
    }

    /**
     * truncate the stats table.
     *
     * @Route("truncate")
     *
     * @return type
     */
    public function flushAction()
    {
        $this->api = $this->container->get('sanne.scraper');
        //TODO error handling
        $this->api->truncate();

        return $this->render('SanneScraperBundle:Default:truncated.html.twig');
    }
}
