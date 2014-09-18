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

class polinya extends wms2PDF {
	
	public function writeProfileExtraItems() {
	
		//for Polinya: Planol sense valor normatiu
		$this->SetFontSize(9);
		$this->writeHTMLCell(83, 0, 115, 15, '<div style="background-color:#fff;color:black;">&nbsp;Plànol sense valor normatiu, vàlid a efectes informatius&nbsp;</div>');
		
	}
	
}