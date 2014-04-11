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
	
	public function writeLegend() {
	    $servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
             for($j=0; $j < sizeof($servers[$i]->layers); $j++) {
                 $layers[]=$servers[$i]->layers[$j]->title;
                 $legends[]=$servers[$i]->layers[$j]->legend;
             }
        }

		$html = '';
        for ($j=count($layers)-1; $j>=0; $j--) {
        	//if legend URL exists, draw the name and legend
        	if($legends[$j]) $html .= $layers[$j].'<br><img src="'.$legends[$j].'"><br><br>';
        }
        
        $this->writeHTMLCell(0, 0, $this->GetX() + 5, $this->GetY(), $html, 0, 0, 0, true, 'L', true);
	}
	
	public function writeMap($height, $width, $size = 1024) {
		//$pdf->Image('http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
		
		$servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
			/*$layers = array();
            for($j=0; $j < sizeof($servers[$i]->layers); $j++) {
                $layers[]=$servers[$i]->layers[$j]->name;
            }
            $wms = implode(",", $layers);*/
			$ratio = 1;
			$bbox = split(",", get_url_parameter($servers[$i]->url, "BBOX"));
			$bbox = correctBbox($bbox, $ratio);
			$url = set_url_parameter($servers[$i]->url, "BBOX", $bbox);
			$url = set_url_parameter($url, "WIDTH", $size);
			$url = set_url_parameter($url, "HEIGHT", $size);
			//print_r("antic ".$servers[$i]->url." nou ".$url);die();
			$this->Image($url, PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
        }
        //$this->Image('http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
	}
}
?>