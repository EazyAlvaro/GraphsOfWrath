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

    public function getAllData()
    {
        $query = $this->em->createQuery(
            'SELECT s.year, s.type, s.data, s.desc  FROM SanneScraperBundle:Statistic s '
        );

        return $query->getResult();
    }

    /*
     *   {
                borderWidth: 5,
                label: "Books ",
                data: bookData,
                borderColor: ['rgba(0,206,209,1)'],
                backgroundColor: ['rgba(0,206,209,0.5)']
     */

    public function getAllConfigs()
    {
        $data = $this->getAllData();
        $output = [];
        $count = count($data);
        $iteration = 1;

        foreach ($data as $record) {
            $output[] = $this->getConfig($record, $count, $iteration++);
        }

        return $output;
    }

    public function getConfig(array $record, $total, $iteration)
    {
        $label = $this->determineType($record).' '.$record['year'];

        $border = [$this->determineColor($total, $iteration, $record['type'], true)];

        $background = [$this->determineColor($total, $iteration, $record['type'], false)];

        return [
            'borderWidth' => 5,
            'label' => $label,
            'data' => json_decode($record['data']),
            'borderColor' => $border,
            'backgroundColor' => $background,
        ];
    }

    public function determineType(array $record): string
    {
        if (!is_array($record) || !array_key_exists('type', $record)) {
            throw new \InvalidArgumentException('Record is empty or does not contain \'type\' field');
        }

        switch ($record['type']) {
            default: return 'unknown';
            case 1: return 'books';
            case 2: return 'movies';
        }
    }

    public function determineColor(int $count, int $iteration, int $type, bool $border = false): string
    {
        if ($count < 1 || $iteration < 1) {
            throw new \InvalidArgumentException('count and iteration must be >1 ');
        }
        $alpha = $border ? (int) $border : '0.4';

        $step = 255 / $count;

        $iStep = --$iteration * $step;

        $color = floor(255 - $iStep);
        $red = 0;
        $green = 0;
        $blue = 0;

        switch ($type) {
            case 1: $blue = $color; break;
            case 2: $red = $color; break;
            default: $green = $color; break; // basically an error-state catch
        }

        return "rgba($red,$green,$blue,$alpha)";
    }
}
