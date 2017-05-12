<?php

namespace SanneScraperBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
     * @fixme uses a lazy query to get the first year, could be done cleaner
     *
     * @route("/stats/new")
     *
     * @return Response
     */
    public function singleStatAction()
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
     * All years for books and movies, overlaid.
     *
     * @route("/stats/new/all")
     */
    public function allNewStatsAction()
    {
        /* i'm just using years as a lazy check if we have ANY data */
        $api = $this->get('sanne.api');
        $years = $api->getYears();

        if (empty($years)) {
            return $this->render('SanneScraperBundle:Default:nostats.html.twig');
        }

        return $this->render('SanneScraperBundle:Default:allstats.html.twig');
    }

    /**
     * All years for books and movies, overlaid.
     *
     * @route("/stats/new/total")
     */
    public function totalsAction()
    {
        return $this->render('SanneScraperBundle:Default:totals.html.twig');
    }

    /**
     * This is where i experiment. Subject to change at all times.
     *
     * @route("test")
     */
    public function testAction()
    {
        return $this->render('SanneScraperBundle:Default:allstats.html.twig');
    }

    /**
     * Show all pre-generated stats .png images in a single page.
     *
     * @Route("/")
     * @Route("/stats/old")
     *
     * @return Response
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
     * @return Response
     */
    public function flushAction()
    {
        $this->api = $this->container->get('sanne.scraper');
        //TODO error handling
        $this->api->truncate();

        return $this->render('SanneScraperBundle:Default:truncated.html.twig');
    }
}
