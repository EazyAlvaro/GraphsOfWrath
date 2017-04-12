<?php
namespace SanneScraperBundle\Scrapers;

use mysqli;
use Exception;
use \DOMDocument;

/**
 * @todo abstract away mysqli from this
 */
class BaseScraper {
    private $targetURL; //primaire URL waar het zoeken begint
    private $pageDOM;   //DOMDocument() van de hoofdpagina
    private $ex = "Not Implemented.";
    private $resultData = array(); //result of the scraper 
    public $i; // MySQLi Object
    
    /**
     * @param DOMDocument $dom het DOM document dat we gaan doorzoeken
     * @param string $tag naam van het DOM tag type dat we gaan bekijken
     * @param string $attribute attribuut naam. Niet(!) de attribuut
     * @param boolean $allowEmpty Append the return array if element is empty ?
     * @return Array {attribute,value}
     */
    public function crawlDom($dom  , $tag , $attribute , $allowEmpty = true ) {
       $result = array();
       $elements =  $dom->getElementsByTagName($tag);
       
       foreach($elements as $element) {
           $attrValue = $element->getAttribute($attribute);
           $elementValue = $element->nodeValue;
           
            if (!$allowEmpty ){
                if(!empty($elementValue)) {
                    $result[] = array($attrValue,$elementValue);
                }
           } else {
               $result[] = array($attrValue,$elementValue);
           }
       }
       return $result;
    }
    
    /** 
     * Filter crawler/scraper gegens op een bepaalde waarde
     * "Does what it says on the tin"  
     */
    public function filterCrawlToAttribute($haystack, $needle) {
        $output = array();
        
        foreach ($haystack as $straw) {
            if($straw[0] == $needle) {
                $output[] = $straw;
            }
        }
        return $output;
    }
    
    /** 
     * vraag het DOM Object op
     * @throws Exception "DOM not loaded"
     * @returns DOMDocument Het DOM van de ingelaadde webpagina
     */
    public function getDOM() {
        if (empty($this->pageDOM)) {
            throw new Exception("DOM not loaded.");
        }
        return $this->pageDOM;
    }
    
    /** 
     * @return string The URL
     * @throws Exception "URL not set"
     */
    public function getURL() {
         if (empty($this->targetURL)) {
            throw new Exception("URL not set.");
        }
        return $this->targetURL;
    }
 
    /** 
     * Haal ruwe HTML op en zet ze in de local DOM object
     * @todo error handling voor als de pagina onbereikbaar is e.d.
     */
    public function load() {
        //haal pagecontent op van de opgegeven URL
        $fetch = file_get_contents($this->targetURL);    
        try {
        $doc = new \DOMDocument();
        } catch(Exception $ex) {
            die($ex->getMessage());
        }
        
        // voert de fetch in en maak er een DOM tree van
        // Silenced omdat er bijna altijd wel fouten in webpagina's zitten  
        @$doc->loadHTML($fetch);
        
        $this->pageDOM = $doc;
    }
    
    /**
     * Set URL van de te crawlen/scrapen webpagina.
     * @param string $url De URL van de te crawlen/scrapen webpagina.
     */
    public function setURL($url) {
        $this->targetURL = $url;
    }
    
    /**
     * Start het proces van url's scrapen, daar DOM objecten van maken, DB vullen en afbeeldingen genereren. 
     */
    public function start() {
        //Bouw de URL array op
        $this->buildUrlArr();
        //ga alle pagina's door voor hun DOM Objecten
        $this->buildDomArr();
        //en sla ze op in de database + afbeeldingen maken
        $this->save();
    }
    
    /**
     * verwerk en save de gegevens in de database (Voor extending classes)
     * @throws Exception Not Implemented
     */
    public function save() {
        throw new Exception("Not Implemented");
    }
}
