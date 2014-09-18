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
		$this->mapWidth = $this->getTotalWidth();
	}
	
	public function writeRightBlock() {
		
	}

	
}