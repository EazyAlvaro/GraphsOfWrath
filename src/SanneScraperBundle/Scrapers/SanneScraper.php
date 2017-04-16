<?php
namespace SanneScraperBundle\Scrapers;

use SanneScraperBundle\Scrapers\BaseScraper;
use \Exception as Exception;
use SanneScraperBundle\Services\GraphService;
use DOMDocument;
use SanneScraperBundle\Entity\Statistic;
use Doctrine\ORM\EntityManager;


/**
 * This service crawls the target site (intende use, a specific listography page)
 * to scrape data, save it in the DB, and generate graph images from that data.
 * 
 * @todo actually save the graph data, now we only save data on the stored asset
 */
class SanneScraper extends BaseScraper{ 
    private $urlArr =  array(); // URL's die uit de targetURL zijn gescraped
    private $domArr = array();  // DOM objecten die daar weer uit gescraped zijn.
    
    /**
     * @var EntityManager Doctrine Entity Manager
     */
    private $em; 
    
    /**
     * @var GraphService 
     */
    private $graph;

    /**
     * @todo look into ContainerAwareInterface and ContainerAwareTrait
     * @param \Doctrine\ORM\EntityManager $em
     * @param GraphService $graph
     */
    function __construct(EntityManager $em = null, GraphService $graph = null) {
        $this->em = $em;
        $this->graph = $graph;
    }
    
    /**
     * Vul $domArr met DOM objecten, op basis van de URL's in $urlArr
     * @throws Exception URL array not loaded
     * @todo error handling
     */
    public function buildDomArr() {
        if (empty($this->urlArr)) {
            throw new Exception("URL array not loaded");
        }             
        foreach ($this->urlArr as $url) {
            $tempSanne = new SanneScraper();
            $tempSanne->setURL($url);
            $tempSanne->load();
            $this->domArr[] = $tempSanne->getDOM();
        }
    }    
    
    /**
     * Vul $urlArr array met relevante URL's uit de Listography userpagina
     * @todo error handling
     */
    public function buildUrlArr() {
        $dom = $this->getDOM();
        $nodes = $dom->getElementsByTagName('a');
                
        foreach ($nodes as $node)  {
            $href = $node->getAttribute('href');           
            // is de URL is en subset van de HREF ?
            // nb: er zijn ook links naar login/signup/etc.
            if (strstr($href, $this->getURL())) {
                $this->urlArr[] = $href; 
            }
        }
        //elimineer alle doubleringen (double records)
        $this->urlArr = array_unique($this->urlArr);
    }
    
    /**
     * Bepaalt het typenummer op basis van de pagina naam
     * @param string $pageName Naam van de pagina. in een vorm zoals "(Boeken 2013)"
     * @return int Het typenummer. 1 = Boeken , 2 = Films
     * @throws Exception
     */
    private function determineType($pageName) {
        (int) $type = 0;
        if (preg_match("/boeken/i", $pageName)) {
            $type = 1;
        } else if (preg_match("/films/i", $pageName)) {
            $type = 2;
        }

        if ($type === 0) {
            throw new Exception("List type not found!");
        }
        return $type;
    }
    
    /**
     * Doorzoekt alle span tags/nodes voor de "box-subtitle" span, en bepaalt uit diens inhoud de pagina naam
     * @param DOMDocument $dom het Document object waar de pagina naam uit gezocht wordt.
     * @return string naam van de pagina. voorbeeld: "(Boeken 2013)"
     */
    private function determinePageName($dom = null) {
        // alle <span> elementen met een ingestelde "class" EN inhoud
        $haystack = $this->crawlDom($dom, "span", "class", false);

        //inhoud van <span class="box-subtitle"> , naam van de lijst
        $rawName = $this->filterCrawlToAttribute($haystack, "box-subtitle");

        //verwijder dubbele/overtollige spaces met een regex/pattern
        $pageName = preg_replace('!\s+!', ' ', $rawName[0][1]);
        
        return $pageName;
    }
    
    
    /**
     * Bepaalt per maand hoeveel boeken er genoteerd staan o.b.v (DD/MM) format
     * @param DOMDocument $dom het te scrapen DOM Document
     * @return array Een int array die boeken per maand vertegenwoordigd
     * @throws Exception DOM parameter empty/invalid
     */
    public function scrapeYData($dom = null) {
        if (empty($dom)) {
            throw new Exception("DOM parameter empty/invalid");
        }
        
        $ydata = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $haystack = $this->crawlDom($dom, "li", "", false);

        foreach ($haystack as $li) {
            //vind de (DD/MM) datumnotering
            preg_match('(\(\d{2}/\d{2}\))', $li[1], $matches);
            $matches = explode("/", $matches[0]);
            $month = $matches[1];
            //Door datumnoteringen die '(DD/MM)' in de titel staan zouden  
            //er False Positives kunnen komen
            if ((int) $month < 13) {
                //verhoog de positie in de array die correspondeert met de maand
                $ydata[(int) $month - 1]++; //cast als int om de 0 uit '01' te strippen
            } else {
                echo "offset bug ! (possible false positive)maand: " . $month . " $li[1]<br>";
            }
        }
        return $ydata;
    }
      
    /**
     * Bepaalt de statistieken, slaat ze op in de database en maakt de Statistieken
     * @throws Exception DOM's not loaded
     */
    public function save() {
        if (empty($this->domArr)) {
            throw new Exception("DOM's not loaded");
        }

        //Elke iteratie is een webpage , doe rustig aan.
        foreach ($this->domArr as $dom) {
            $data = $this->buildDataArr($dom);
            $this->graph->buildGraph($data);
            $this->saveDB($data);
        }
    }
    
    /**
     * 
     * Returns an array based on the DOMDocument contents that looks like the following:
     *  [
     *      desc, // string. ex: "(Films 2017)" 
     *      url , // string. asset path. ex: "img/sanne/2_2017.png" 
     *      type, // int. '1' (Books), '2' (movies)
     *      year, // int. 
     *      months // array. results per month, ex: [0 => 2, 1 => 4, etc => etc]
     * ]
     * 
     * @param DOMDocument $dom
     * @return array
     */
    public function buildDataArr(DOMDocument $dom)
    {
        $pageName = $this->determinePageName($dom);
        $type = $this->determineType($pageName);

        //Zoek of er waardes van 4 digits (een jaartal) achter staan
        $matches = array();
        preg_match('(\d{4})', $pageName, $matches);
        $year = $matches[0];

        //bepaal de bestandsnaam van de grafiekafbeelding
        $filename = "img/sanne/" . $type . "_" . $year . ".png";

        $ydata = $this->scrapeYData($dom);

        $data = array(
            "desc" => $pageName,
            "url" => $filename,
            "type" => (int) $type,
            "year" => (int) $year,
            "months" => $ydata
        );
        
        return $data;
    }

    /** 
     * Persist the data to the DB
     * 
     * @param array $data Array met [desc (grafiektitel),url (doelbestand), type(1=boeken,2=film), year, months (grafiekgegevens per maand)] 
     */
    private function saveDB($data) {
        $statistic = new Statistic();
        
        $statistic->setDesc($data['desc'])
                ->setType($data['type'])
                ->setUrl($data['url'])
                ->setYear($data['year'])
                ->setData( json_encode($data['months']))
        ;
        
        $this->em->persist($statistic);
        
        $this->em->flush();
    }
 
}

