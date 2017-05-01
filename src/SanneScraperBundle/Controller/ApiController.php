<?php

namespace SanneScraperBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    private $api;

    /**
     * This exists only because you can't seem to call this->container inside 
     * constructors
     */
    private function setUp()
    {
        $this->api = $this->container->get('sanne.api');
    }
    
    
    /**
     * @Route("/sanne/get/years")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function yearsAction()
    {
        //TODO error handling
        //$this->api = $this->container->get('sanne.api');

        $this->setUp();
        
        return new JsonResponse(json_encode($this->api->getYears()));
    }

   
    /**
     * @Route("/sanne/movies/{year}")
     */
    public function getMovieDataAction(int $year)
    {
        $this->setUp();
        
        return new JsonResponse(
                $this->api->getMovieDataByYear($year)[0]['data'] 
        );
    }
    
    /**
     * @Route("/sanne/books/{year}")
     */
    public function getBookDataAction(int $year)
    {
         $this->setUp();
        
        return new JsonResponse(
                $this->api->getBookDataByYear($year)[0]['data']
        );
    }
    
    
    
    
    /**
     * @Route("/sanne/flush")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function flushAction()
    {
        $this->api = $this->container->get('sanne.api');
        //TODO error handling
        return new JsonResponse(json_encode(['success' => $this->api->flush()]));
    }
}
