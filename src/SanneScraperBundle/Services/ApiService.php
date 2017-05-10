<?php

namespace SanneScraperBundle\Services;

use Doctrine\ORM\EntityManager;

class ApiService
{
    /**
     * @var EntityManager Doctrine Entity Manager
     */
    private $em;

    public function __construct(EntityManager $em = null)
    {
        if (!$em) {
            throw new \Exception('Entity Manager missing');
        }

        $this->em = $em;
    }

    /**
     * @return array [2009, 2010, etc]
     */
    public function getYears()
    {
        $query = $this->em->createQuery(
            'SELECT DISTINCT s.year FROM SanneScraperBundle:Statistic s ORDER BY s.year ASC'
        );

        $years = $query->getResult();

        $flatYears = [];

        foreach ($years as $year) {
            $flatYears[] = $year['year'];
        }

        return $flatYears;
    }
    
    public function getMovieDataByYear(int $year)
    {
        $query = $this->em->createQuery(
            "SELECT DISTINCT s.data FROM SanneScraperBundle:Statistic s where s.year = $year and s.type = 2"
        );
         
        return $query->getResult();
    }
    
    public function getBookDataByYear(int $year)
    {
        $query = $this->em->createQuery(
            "SELECT DISTINCT s.data FROM SanneScraperBundle:Statistic s where s.year = $year and s.type = 1"
        );
         
        return $query->getResult();
    }
    
}
