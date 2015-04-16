<?php

/**
 * Class based on TCPDF to print PDF files from WMS services
 * Uses code from Anthony Martin (https://github.com/anthonymartin/GeoLocation.php)
 * licensed under CC 3.0 license: http://creativecommons.org/licenses/by/3.0/
 *
 * @author     Marti Pericay <marti@pericay.com>
 * @author     Mcrit <catala@mcrit.com>
 * @copyright  (c) 2014-2015 by Marti Pericay and MCRIT
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (unless otherwise stated)
 * 
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License.
 */

class wms2PDF extends TCPDF {
	
	private $servers;
	private $legends;
	private $layers;
	private $bbox;
	protected $mapHeight;
	protected $mapWidth;
	protected $size = 1024;
	protected $forcedScale = false;
	protected $epsg = 4326;
	protected $geographic = true;	
	protected $pageHeight;
	protected $pageWidth;
	public $ratio = 1;
	public $pageOrientation = 'L';
	public $pageSize = 'A4';
	public $mapTitle = false;
	public $config = array(
		/* extra graphical parameters (the others are located in tcpdf_config.php) */
		"boxGap"=> 5,
		/* print.php outputs directly the PDF or stores the file and sends the filename? */
		"directOutput"=>false,
		/* show the logo? */
		"showLogo"=>true,
		/* show the north arrow? */
		"showNorth"=>true,
		/* show the reference system? */
		"showEpsg"=>true,
		/* show the numeric scale? */
		"showScale"=>true,
		/* show the left inferior coords? */
		"showCoords"=>false,
		/* show the legend? */
		"showLegend"=>true,
		/* show a personal text? */
		"customText"=>false,
		/* logo URL */
		"logoUrl"=>"img/cerdanyola.jpg",
		/* if a legend fails, throw error or ignore the error? */
		"ignoreLegendErrors"=>false
	);
	public $locale = array(
		/* showEpsg */
		"epsg"=> "Sistema de referència EPSG:",
		/* showScale */
		"scale"=> "Escala aproximada del mapa 1:",
		/* showCoords */
		"coords"=> "Coordenades de la cantonada inferior\nesquerra del mapa: "
	);
	
	public function setMapDimensions() {
		//only for landscape (legend on right)
		$this->mapHeight = $this->pageHeight; 
		$this->mapWidth = $this->pageHeight;
	}
	
	public function buildPage() {
		// add a page
		$this->AddPage($this->pageOrientation, $this->pageSize);
		
		$this->pageHeight = $this->getRemainingHeight();
		$this->pageWidth = $this->getTotalWidth();
		
		$this->setMapDimensions();
	
		//we need to pass image width and height to recalculate bbox
		$this->recalculateBbox($this->mapHeight, $this->mapWidth);
	
		// the Image() method recognizes the alpha channel embedded on the image:
		$this->writeMap($this->mapHeight, $this->mapWidth);
		$this->setPageMark();
	
		//write the boxes
		$this->SetLineStyle(array('color'=>array(50, 50, 50)));
		$this->SetLineWidth(0.3);
		// write the first cell (Map cell)
		$this->MultiCell($this->mapWidth, $this->mapHeight, '', 1, 'J', 0, 0, '', '', true, 0, false, true, 0);
		
		$this->writeRightBlock();
		
		$this->writeProfileExtraItems();
	
		return true;
	}	
	
	public function writeRightBlock() {
		
		// write the splitter
		$this->MultiCell($this->config["boxGap"], $this->pageHeight, '', 0, 'J', 0, 0, '', '', false, 0, false, true, 0);
	
		//Get current write position: we will draw the legend from here
		$x = $this->GetX();
		$y = $this->GetY();
	
		// write the second cell
		$this->MultiCell(0, $this->pageHeight, '', 1, 'C', 0, 1, '', '', true, 0, false, true, 0);

		/* --- START LEGEND BLOCK ---*/
		//fixed elements: write north and texts 
		$fixedSpaceUsed = 0;
		if($this->config["showNorth"]) {
			$northHeight = 13;
			$this->writeNorth($x + 5, $this->pageHeight - $northHeight, $this->mapWidth);
			$fixedSpaceUsed += $northHeight;
		}
	
		//fixed elements: write logo (46pt above bottom)
		if($this->config["showLogo"]) {
			$logoHeight = 20;
			$this->writeLogo($this->config["logoUrl"], $x + 12, $this->pageHeight - $logoHeight - $fixedSpaceUsed);
			$fixedSpaceUsed += $logoHeight;
		}
		
		$fixedSpaceUsed += 8; //margin
	
		//reduce the page break by the 46pt (if the legend doesn't fit, we must not write over logo and north)
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM + $fixedSpaceUsed * PDF_IMAGE_SCALE_RATIO);
		//dynamic elements: write Legend and Title
		$this->SetXY($x,$y); //return to the beginning of the legend to start writing dynamically

		if($title = $this->mapTitle) $this->writeTitle($title, 15);
		if($this->config["showLegend"]) $this->writeLegend($this->getRemainingHeight());
		/* --- END LEGEND BLOCK ---*/		
	}
		
	public function overwriteConfig($options) {
		if($options) $this->config = array_merge($this->config, array_intersect_key((array) $options, $this->config));
		return true;
	}
	
	public function loadConfig($params) {
		//whether size is sent, or we use default value
		$this->size = array_key_exists("size", $params) ? $params->size : $this->size;
		//whether epsg is sent, or we use default value
		$this->epsg = array_key_exists("epsg", $params) ? $params->epsg : $this->epsg;
		//whether title is sent, or we use default value
		$this->mapTitle = array_key_exists("title", $params) ? $params->title : $this->mapTitle;
		//whether projected coordinates are sent, or we use default value
		$this->geographic = array_key_exists("geographic", $params) ? $params->geographic : $this->geographic;
		
		//is there a forced scale? 
		//TODO: we can't do it if geographic coordinates 
		if(array_key_exists("scale", $params)) $this->forcedScale = $params->scale;
		//we set the servers info and calculate bbox, width and height
    	$this->setServers($params->servers);
    	if(array_key_exists("config", $params)) $this->overwriteConfig($params->config);
	}	
	
	public function setServers($servers) {
		
		//TODO: instead of die, output error
		if(!$servers) outputError("No WMS services provided in JSON POST data (printData->map->servers)");
		
		// set legends and titles
		for($i=0; $i<count($servers); $i++) {
            // special WKT 
			if($servers[$i]->type == "wkt") { 
				// do special stuff
				// create URL
				if(!$servers[$i]->url) {
					$servers[$i]->url = "http://dev.geodata.es/wms56/highlight/wkt/wkt?FORMAT=image%2Fpng&TRANSPARENT=true&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&STYLES=&EXCEPTIONS=application%2Fvnd.ogc.se_inimage&SRS=EPSG%3A25831";
				}
			}
			$this->servers[$i] = $servers[$i];

			// legends and titles
			 if(isset($servers[$i]->layers)) {
	             for($j=0; $j < count($servers[$i]->layers); $j++) {
	             	if(isset($servers[$i]->layers[$j]->legend)) {
		                $this->layers[]=$servers[$i]->layers[$j]->title;
		                $this->legends[]=$servers[$i]->layers[$j]->legend;
	             	}
	             }
			 }
        }
		
		return true;
	}
	
	public function recalculateBbox($imageHeight, $imageWidth) {
		$servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
			$bbox = split(",", get_url_parameter($servers[$i]->url, "BBOX"));
			if(!$this->bbox) $this->bbox = correctBbox($bbox, $imageHeight, $imageWidth, $this->forcedScale, $this->geographic);
			$servers[$i]->url = set_url_parameter($servers[$i]->url, "BBOX", join(",",$this->bbox));
			$servers[$i]->url = set_url_parameter($servers[$i]->url, "WIDTH", $this->size);
			$servers[$i]->url = set_url_parameter($servers[$i]->url, "HEIGHT", intval($this->size * $imageHeight / $imageWidth));
        }
	}
		
	public function getRemainingHeight() {
		return ($this->getPageHeight() - $this->getBreakMargin() - $this->GetY());
	}
	
	public function getTotalWidth() {
		$margins = $this->getMargins();
		return ($this->getPageWidth() - $margins['right'] - $margins['left']);
	}
	
	public function getRatio() {
		return ($this->ratio);
	}	
	
	public function writeLogo($src, $x, $y) {
		//TODO: 50 is hard-coded width for Progess
		$this->Image($src, $x, $y, 50);
	}
	
	public function writeProfileExtraItems() {
		// to draw profile-specific items (labels, extra texts, ...) 
		// code to be added inside every layout class
	}	
	
	/* draws a nice box with a north arrow, a reference system and the scale */
	public function writeNorth($x, $y, $imageWidth) {
		$size = $this->getFontSizePt();
		if($this->config["showNorth"]) $this->Image('img/north2.jpg', $x, $y, 7);
		$this->SetFontSize(9);
		if($this->config["showEpsg"]) $this->Text($x + 10, $y + 3, $this->locale["epsg"]. $this->epsg);
		//TODO: we can't print geographic coordinates
		if($this->config["showScale"] && $scale = $this->getScale($imageWidth)) $this->Text($x + 10, $y + 8, $this->locale["scale"].$scale);
		else if($this->config["showCoords"]) $this->Text($x + 10, $y + 8, $this->locale["coords"].$this->bbox[0].", ".$this->bbox[2]);
		if($this->config["customText"]) $this->Text($x + 10, $y + 13, $this->config["customText"]);
		//reset default font size
		$this->SetFontSize($size);
	}
	
	/* gets scale value by measuring horizontal distance and image width */
	public function getScale($imageWidth) {
		
		if($this->geographic) {
            $geo = new GeoLocation();
            $left = $geo->fromDegrees($this->bbox[3], $this->bbox[0]);
		    $right = $geo->fromDegrees($this->bbox[3], $this->bbox[2]);
		    $distance = $left->distanceTo($right, 'kilometers') * 1000;	//km to meters
		} else {
			$distance = $this->bbox[2]-$this->bbox[0];
		}
		

        $scale = intval(1000 * ($distance) / ($imageWidth));
        return significant($scale,2);

	}
	
	/* draws the title */
	public function writeTitle($title, $height) {
		$size = $this->getFontSizePt();
		$this->SetFontSize(15);
		$this->Cell(0, $height, $title, 0, 2, 'C', 0, '', 1);
		//reset default font size
		$this->SetFontSize($size);
	}	
	
	public function writeLegend($availableHeight = 0) {
		$legends = $this->legends;
	    $layers = $this->layers;

		$html = '';
		$height = 0;
		
        for ($j=count($layers)-1; $j>=0; $j--) {
        	$writeLegend = $legends[$j] ? true : false;
        	if($this->config['ignoreLegendErrors']) {
        		//if legend URL doesn't exist, don't write it
        		if(!@getimagesize($legends[$j])) $writeLegend = false;
        	}
        	list($imageWidth, $imageHeight) = getimagesize($legends[$j]);
        	$height += 5 + $imageHeight/PDF_IMAGE_SCALE_RATIO;
        	if($availableHeight && ($height > $availableHeight)) {
        		// no room for more legend
        		array_splice($this->legends, -(count($layers) - $j - 1));
        		array_splice($this->layers, -(count($layers) - $j - 1));
        		$this->writeHTMLCell(0, 0, $this->GetX() + 5, $this->GetY(), $html, 0, 0, 0, true, 'L', true);
        		$this->writeExtraPage();
        		return;
        	}
        	
        	//if legend URL exists or we don't want to check, draw the name and legend
        	if($writeLegend) $html .= $layers[$j].'<br><img src="'.$legends[$j].'"><br>';
        }
        
        $this->writeHTMLCell(0, 0, $this->GetX() + 5, $this->GetY(), $html, 0, 0, 0, true, 'L', true);
	}
	
	
	public function writeExtraPage() {
		$this->SetAutoPageBreak(FALSE);
		$this->AddPage($this->pageOrientation, $this->pageSize);

		//write the box
		$this->MultiCell($this->pageWidth, $this->mapHeight, '', 1, 'J', 0, 0, '', '', true, 0, false, true, 0);
		
		$this->SetXY(20,10);
		$this->writeTitle('Llegenda', 15);
		$this->Ln(5);
		
		$this->setEqualColumns(3, 57);
		$this->writeLegend();
		
	}	
	
	public function writeMap($height, $width) {
		
		$servers = $this->servers;
		for($i=0; $i<count($servers); $i++) {
			if(isset($servers[$i]->opacity)) {
				// set specific opacity
				$this->SetAlpha($servers[$i]->opacity);
			}
			if($servers[$i]->type == "wkt" && $servers[$i]->wkt) {
				$servers[$i]->url .= $this->writeWKT($servers[$i]->ftype, $servers[$i]->wkt);
			}
			$this->Image($servers[$i]->url, PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $width, $height, '', '', '', false, 1024);
			// restore full opacity
			$this->SetAlpha(1);
        }
	}
	
	public function writeWKT($type, $wkt) {
		switch($type) {
			case "polygon":
				//layer 2 (third one) for polygons
				$url = "&map_layer[2]=FEATURE+WKT+%22".urlencode($wkt)."%22+END";
				$url .= "&LAYERS=polygon";
				break;			
			case "line":
				//layer 1 (second one) for lines
				$url = "&map_layer[1]=FEATURE+WKT+%22".urlencode($wkt)."%22+END";
				$url .= "&LAYERS=line";
				break;
			case "point":
			default:
				//layer 0 (first one) for points
				$url = "&map_layer[0]=FEATURE+WKT+%22".urlencode($wkt)."%22+END";
				$url .= "&LAYERS=point";
				break;
		}
		
		return $url;
	}
	
	/* override error to output error messages */
	/* TODO: shouldn't do that in production environment */
	public function Error($msg) {
   		// unset all class variables
   		$this->_destroy(true);

  		//output Error
   		outputError($msg);
	}
}
?>