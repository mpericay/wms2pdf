<?php
class wms2PDF extends TCPDF {
	
	private $servers;
	private $bbox;
	private $ratio = 1;
	private $size = 1024;
	private $forcedScale = false;
	private $geographic = true;
	public $config = array(
		/* extra graphical parameters (the others are located in tcpdf_config.php) */
		"boxGap"=> 5,
		/* print.php outputs directly the PDF or stores the file and sends the filename? */
		"directOutput"=>false,
		/* show the north arrow? */
		"showNorth"=>true,
		/* show the reference system? */
		"showEpsg"=>true,
		/* show the numeric scale? */
		"showScale"=>true,
		/* show the legend? */
		"showLegend"=>true
	);
	public $locale = array(
		/* showEpsg */
		"epsg"=> "Sistema de referència EPSG:",
		/* showScale */
		"scale"=> "Escala del mapa 1:"
	);
	
	public function overwriteConfig($options) {
		if($options) $this->config = array_merge($this->config, array_intersect_key((array) $options, $this->config));
		return true;
	}
	
	public function loadConfig($params) {
		//whether size is sent, or we use default value
		$this->size = $params->size ? $params->size : $this->size;
		//whether projected coordinates are sent, or we use default value
		$this->geographic = array_key_exists("geographic", $params) ? $params->geographic : $this->geographic;
		//is there a forced scale? 
		//TODO: we can't do it if geographic coordinates 
		if(array_key_exists("scale", $params)  && !$this->geographic) $this->forcedScale = $params->scale;
		//we set the servers info and calculate bbox, width and height
    	$this->setServers($params->servers);
    	$this->overwriteConfig($params->config);
	}	
	
	public function setServers($servers) {
		
		//TODO: instead of die, output error
		if(!$servers) die("No WMS services provided in JSON POST data (printData->map->servers)");
		$this->servers = $servers;
		
		return true;
	}
	
	public function recalculateBbox($imageHeight, $imageWidth) {
		$servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
			$bbox = split(",", get_url_parameter($servers[$i]->url, "BBOX"));
			if(!$this->bbox) $this->bbox = correctBbox($bbox, $imageHeight, $imageWidth, $this->forcedScale);
			$servers[$i]->url = set_url_parameter($servers[$i]->url, "BBOX", join(",",$this->bbox));
			$servers[$i]->url = set_url_parameter($servers[$i]->url, "WIDTH", $this->size);
			$servers[$i]->url = set_url_parameter($servers[$i]->url, "HEIGHT", $this->size);
        }
	}
		
	public function getRemainingHeight() {
		return ($this->getPageHeight() - $this->getBreakMargin() - $this->GetY());
	}
	
	public function getRatio() {
		return ($this->ratio);
	}	
	
	/* draws a nice box with a north arrow, a reference system and the scale */
	public function writeNorth($x, $y, $imageWidth) {
		$size = $this->getFontSizePt();
		if($this->config["showNorth"]) $this->Image('img/north2.jpg', $x, $y, 7);
		$this->SetFontSize(10);
		if($this->config["showEpsg"]) $this->Text($x + 10, $y + 3, $this->locale["epsg"]. "4326");
		//TODO: we can't print geographic coordinates
		if($this->config["showScale"] && !$this->geographic) $this->Text($x + 10, $y + 8, $this->locale["scale"].$this->getScale($imageWidth));
		//reset default font size
		$this->SetFontSize($size);
	}
	
	/* draws a nice box with a north arrow, a reference system and the scale */
	public function getScale($imageWidth) {

        $scale = intval(1000 * ($this->bbox[2]-$this->bbox[0]) / ($imageWidth));
        return significant($scale,2);
        /*} else if (($printData->printoptions->scalebar) == "graphic") {
                $scale = intval(1000 * ($bbox[2]-$bbox[0]) / ($map_image_width / $page_factor));
                $sign_scale = _significant($scale / $map_image_width * $scalebar_width,2);
                $scalebar_x = round($x + $w/2 - $scalebar_width/2 - $padding);

                $pdf->addPngFromFile(_create_graphic_scalebar($sign_scale,$scalebar_width,$scalebar_height),$scalebar_x,$y-$scalebar_height,$scalebar_width,$scalebar_height);
                $y = $y + $scalebar_height;
            }*/
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
             for($j=0; $j < count($servers[$i]->layers); $j++) {
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
	
	public function writeMap($height, $width) {
		//$pdf->Image('http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
		
		$servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
			/*$layers = array();
            for($j=0; $j < sizeof($servers[$i]->layers); $j++) {
                $layers[]=$servers[$i]->layers[$j]->name;
            }
            $wms = implode(",", $layers);*/
			$this->Image($servers[$i]->url, PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
        }
	}
}
?>