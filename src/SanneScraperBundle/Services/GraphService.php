<?php

namespace SanneScraperBundle\Services;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;


class GraphService
{
    
    /**
     * Maakt een lijngrafiek van $resultData en slaat deze op 
     * @param array $data Array met [desc (grafiektitel),url (doelbestand), type(1=boeken,2=film), year, months (grafiekgegevens per maand)]
     * @param double $zoom vergrotingsfactor voor het formaat van de afbeeldingen
     */
    public function buildGraph($data = null, $zoom = 1) {
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
