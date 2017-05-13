<?php

namespace SanneScraperBundle\Services;

use Doctrine\ORM\EntityManager;
use Prophecy\Exception\InvalidArgumentException;

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
        return [
            'borderWidth' => 5,
            'label' => $this->determineType($record).' '.$record['year'],
            'data' => json_decode($record['data']),
            'borderColor' => [$this->determineColor($total, $iteration, $record['type'], true)],
            'backgroundColor' => [$this->determineColor($total, $iteration, $record['type'], false)],
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

    public function getYearlyTotalsConfig()
    {
        $books = [];
        $movies = [];

        foreach ($this->getAllData() as $record) {
            $total = $this->getTotalFromDataString($record['data']);

            switch ($record['type']) {
                case 1:
                    $books[] = $total;
                    break;
                case 2:
                    $movies[] = $total;
                    break;
            }
        }

        $output = [
            $this->getConfigForTotals($books, 1),
            $output[] = $this->getConfigForTotals($movies, 2),
        ];

        return $output;
    }

    public function getConfigForTotals(array $totals, $type)
    {
        return [
            'borderWidth' => 5,
            'label' => $this->determineType(['type' => $type]),
            'data' => $totals,
            'borderColor' => [$this->determineColor(1, 1, $type, true)],
            'backgroundColor' => [$this->determineColor(1, 1, $type, false)],
        ];
    }

    /**
     * @param string $jsonString JSON encoded array with ints for every month.
     *                           example: "[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]"
     *
     * @return int
     */
    public function getTotalFromDataString(string $jsonString): int
    {
        if (!json_decode($jsonString)) {
            throw new \InvalidArgumentException();
        }

        $dataArray = json_decode($jsonString);

        return array_sum($dataArray);
    }

    public function getMonthlyAverageConfig()
    {
        $data = $this->getAllData();

        $inputBooks = [];
        $inputMovies = [];

        foreach ($data as $record) {
            $recordArray = json_decode($record['data']);

            switch ($record['type']) {
                case 1: $inputBooks[] = $recordArray; break;
                case 2: $inputMovies[] = $recordArray; break;
            }
        }

        $bookAverages = $this->determineAverages($inputBooks);
        $moviesAverages = $this->determineAverages($inputMovies);
        $booksConfig = $this->getConfigForTotals($bookAverages, 1);
        $moviesConfig = $this->getConfigForTotals($moviesAverages, 2);

        return [$booksConfig, $moviesConfig];
    }

    public function determineAverages(array $input): array
    {
        $inputCount = count($input);
        $dataSize = count($input[0]);
        $output = array_fill(0, $dataSize, 0);

        foreach ($input as $yearData) {
            if ($dataSize != count($yearData)) {
                throw new InvalidArgumentException('Data size  mismatch');
            }
        }

        foreach ($input as $yearData) {
            for ($i = 0; $i < $dataSize; ++$i) {
                $output[$i] += $yearData[$i];
            }
        }

        foreach ($output as $key => $value) {
            $output[$key] = $value / $inputCount;
        }

        return $output;
    }
}
