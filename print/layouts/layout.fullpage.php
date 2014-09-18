<?php

/**
 * Class based on TCPDF to print PDF files from WMS services
 *
 * @author     Marti Pericay <marti@pericay.com>
 * @author     Mcrit <catala@mcrit.com>
 * @copyright  (c) 2014 by Marti Pericay and MCRIT
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
 * 
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License.
 */

class fullpage extends wms2PDF {
	
	public function setMapDimensions() {
		//only for landscape (legend on right)
		$this->mapHeight = $this->pageHeight; 
		$this->mapWidth = $this->pageWidth;
	}
	
	public function writeRightBlock() {
		
	}
	
	public function writeExtraPage() {
		$this->AddPage($this->pageOrientation, $this->pageSize);

		//write the box
		$this->MultiCell($this->pageWidth, $this->mapHeight, '', 1, 'J', 0, 0, '', '', true, 0, false, true, 0);
		
		$this->SetXY(20,10);
		$this->writeTitle('Llegenda', 15);
		$this->Ln(5);
		
		$this->setEqualColumns(3, 57);
		$this->writeLegend();
		
	}
	
	public function writeProfileExtraItems() {
		$this->writeExtraPage();
	}

	
}