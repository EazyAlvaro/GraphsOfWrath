<?php

namespace SanneScraperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ApiController extends Controller
{
    /**
     * //FIXME broken right now, dont use.
     *
     * @Route("/sanne/get/years")
     */
    public function getYears()
    {
        $em = $this->getDoctrine()->getManager();

        $em->createQuery('SELECT DISTINCT year FROM SanneScraperBundle:Statistic')
                ->getResult();
    }
}
