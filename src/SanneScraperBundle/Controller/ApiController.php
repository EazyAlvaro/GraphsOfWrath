<?php

namespace SanneScraperBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    private $api;

    /**
     * @Route("/sanne/get/years")
     */
    public function yearsAction()
    {
        //TODO error handling
        $this->api = $this->container->get('sanne.api');
        return new JsonResponse(json_encode($this->api->getYears()));
    }
}
