<?php
class wms2PDF extends TCPDF {
	
	private $servers;
	
	public function setServers($servers) {
		//TODO: instead of die, output error
		if(!$servers) die("No WMS services provided in JSON POST data (printData->map->servers)");
		// would be nice to check if JSON structure is correct
		$this->servers = $servers;
	}
		
	public function getRemainingHeight() {
		return ($this->getPageHeight() - $this->getBreakMargin() - $this->GetY());
	}
	
	/* draws a nice box with a north arrow, a reference system and the scale */
	public function writeNorth($x, $y) {
		$size = $this->getFontSizePt();
		$this->Image('img/north2.jpg', $x, $y, 7);
		$this->SetFontSize(10);
		$this->Text($x + 10, $y + 3, "Sistema de referència "."EPSG:4326");
		$this->Text($x + 10, $y + 8, "Escala del mapa ~ 1:6100");
		//reset default font size
		$this->SetFontSize($size);
	}
	
	/* draws the title */
	public function writeTitle($title, $height) {
		$size = $this->getFontSizePt();
		$this->SetFontSize(15);
		$this->Cell(0, $height, $title, 0, 2, 'C', 0, '', 1);
		//reset default font size
		$this->SetFontSize($size);
	}	
	
	public function writeLegend($x, $y, $ymax) {
	    $servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
             for($j=0; $j < sizeof($servers[$i]->layers); $j++) {
                 $layers[]=$servers[$i]->layers[$j]->title;
                 $legends[]=$servers[$i]->layers[$j]->legend;
             }
        }

		//$this->Image('http://si.progess.com:8008/geoserver/wms?LAYER=mediacions_obertes&REQUEST=GetLegendGraphic&VERSION=1.1.1&FORMAT=image/png&SERVICE=WMS');

        for ($j=count($layers)-1; $j>=0; $j--) {
        	//if legend URL exists, draw the name and legend
        	if($legends[$j]) $html .= $layers[$j].'<br><img src="'.$legends[$j].'"><br><br>';
        }
        
        $this->writeHTMLCell(0, 0, $this->GetX() + 5, $this->GetY(), $html, 0, 0, 0, true, 'L', true);
	}
}
?>