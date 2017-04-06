<?php
namespace SanneScraperBundle\Scrapers;

use SanneScraperBundle\Scrapers\BaseScraper;
use \Exception as Exception;
//App::import('Vendor', 'jpgraph/jpgraph');
//App::import('Vendor', 'jpgraph/jpgraph_line');
//use Amenadiel\JpGraph\Graph;
//use Amenadiel\JpGraph\Plot;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;


class SanneScraper extends BaseScraper{ 
    private $urlArr =  array(); // URL's die uit de targetURL zijn gescraped
    private $domArr = array();  // DOM objecten die daar weer uit gescraped zijn.
    
    function __construct() {
       $this->setDB("localhost", "root", "1q2w", "listograb" );
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

            //toevoegen voor later gebruik
            $this->resultData[] = $data;
            
            $this->buildGraph($data);
            $this->saveDB($data);
        }
    }
     
    /** 
     * 
     * @param array $data Array met [desc (grafiektitel),url (doelbestand), type(1=boeken,2=film), year, months (grafiekgegevens per maand)] 
     */
    private function saveDB($data) {
        
        if($this->i->connect_errno) {
            throw new Exception("Failed to connect to mysqli");
        }
            
        $query = 'INSERT INTO stats (`desc`, `url`, `type`, `year`) VALUES ( ?  , ? , ? , ?) ';
        $statement = $this->i->stmt_init();
        
        if ($statement->prepare($query)) {
            
           $statement->bind_param(
                    'ssii', 
                    $data['desc'], //string 
                    $data['url'],  //string
                    $data['type'], //int
                    $data['year'] //int
            );
            
            $feedback = $statement->execute();  
            if(!$feedback) {
                echo  mysqli_error($this->i);
                throw new Exception("Failed to insert record");
            } else {
                echo $data['desc']." Saved in DB<br>";
            }
        } else {
           echo mysqli_error($this->i);
        }
       echo  mysqli_error($this->i);       
    }
    
    /**
     * Maakt een lijngrafiek van $resultData en slaat deze op 
     * @param array $data Array met [desc (grafiektitel),url (doelbestand), type(1=boeken,2=film), year, months (grafiekgegevens per maand)]
     * @param double $zoom vergrotingsfactor voor het formaat van de afbeeldingen
     */
    private function buildGraph($data = null, $zoom = 1) {
        $graph = new Graph($zoom*350, $zoom*250);    
        $graph->SetScale("textlin");
        $graph->img->SetMargin(30,30,30,60);
        $graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
        $graph->title->Set($data["desc"]);
         
        $lineplot = new LinePlot($data["months"]);
        $lineplot->SetColor("blue");
        $lineplot->SetWeight(5);
            
        //Sla de afbeelding op
        $graph->Add($lineplot);
        $graph->Stroke(_IMG_HANDLER);
        $graph->img->Headers();
        $graph->img->Stream($data["url"]);
    }   
}

