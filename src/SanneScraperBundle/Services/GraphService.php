<?php

namespace SanneScraperBundle\Services;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;

class GraphService
{
    /**
     * Generates a line-graph and saves it.
     *
     * @param array $data Array with [desc (graph title),url (target file), type(1=books,2=movies), year, months (incidents per month) ]
     * @param float $zoom image magnification factor
     */
    public function buildGraph($data = null, $zoom = 1)
    {
        $graph = new Graph($zoom * 350, $zoom * 250);
        $graph->SetScale('textlin');
        $graph->img->SetMargin(30, 30, 30, 60);
        $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
        $graph->title->Set($data['desc']);

        $lineplot = new LinePlot($data['months']);
        $lineplot->SetColor('blue');
        $lineplot->SetWeight(5);

        //Sla de afbeelding op
        $graph->Add($lineplot);
        $graph->Stroke(_IMG_HANDLER);
        $graph->img->Headers();
        $graph->img->Stream($data['url']);
    }
}
