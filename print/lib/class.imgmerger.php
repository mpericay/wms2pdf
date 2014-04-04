<?php

//************************************************************************
//
// OGC Client
// ============================================
//
// Copyright (c) 2008 by Geodata Sistemas S.L.
// http://www.geodata.es
// Written by Xose Pérez and Martí Pericay
//
// Image merger
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
//
//************************************************************************

class ImageMerger {

  var $ready = false;
  var $image = null;
  var $tmpdir = null;
  var $width = 0;
  var $height = 0;
  var $gd;

  // ---------------------------------------------------------------------------

  function ImageMerger($tmpdir) {

    // check GD support
    if(function_exists("gd_info")){
      $this->gd = gd_info();
      $this->ready = true;
      $this->tmpdir = $tmpdir;
    } else {
    	die("No GD support");
    }

  }
  
  // ---------------------------------------------------------------------------
  
  function load_image($file,$x=0,$y=0,$maxWidth=0,$maxHeight=0) {

	// check ready state
    if (!$this->ready) return false;
    
    // load image
    list($width, $height, $type, $atr) = @getimagesize($file);

    switch ($type) {
      case 1: // GIF
        if ($this->gd["GIF Read Support"]) $tmp = imagecreatefromgif($file);
        break;
      case 2: // JPEG
        if ($this->gd["JPEG Support"]) $tmp = imagecreatefromjpeg($file);
        break;
      case 3: // PNG
        if ($this->gd["PNG Support"]) $tmp = imagecreatefrompng($file);
        break;
      default:
        return false;
    }

    if ($tmp) {

      // check if destination image exists
      if (!$this->image) {
          if ($maxWidth > 0 && $maxHeight > 0)
            $this->image = $this->_create_image($maxWidth, $maxHeight);
           else
            $this->image = $this->_create_image($width, $height);
      }

      // transform source image to palette mode
      @imagetruecolortopalette($tmp, false, 256);
      
      // merge new file (transparency only works for PNG files!!)
      if ($maxWidth > 0 && $maxHeight > 0)
        @imagecopymerge($this->image, $tmp, $x, $y, 0, 0, $maxWidth, $maxHeight, 100);
      else
        @imagecopymerge($this->image, $tmp, $x, $y, 0, 0, $this->width, $this->height, 100);
      
      // use source palette in order to not to loose colours
      @imagecolormatch($this->image, $tmp);
      
      return true;

    }
    
    return false;

  }
  
  function add_icon($file,$data,$bbox) {
		
    // check ready state
    if (!$this->ready) return false;
    
    // load image
    list($width, $height, $type, $atr) = getimagesize($file);
    switch ($type) {
      case 1: // GIF
        if ($this->gd["GIF Read Support"]) $tmp = imagecreatefromgif($file);
        break;
      case 2: // JPEG
        if ($this->gd["JPG Support"]) $tmp = imagecreatefromjpeg($file);
        break;
      case 3: // PNG
        if ($this->gd["PNG Support"]) $tmp = imagecreatefrompng($file);
        break;
      default:
        return false;
    }

    if ($tmp) {

      
      $data = explode(",",$data);
      $bbox = explode(",",$bbox);
      
      // merge new file (transparency only works for PNG files!!)
      if (($data[0] >= $bbox[0] && $data[0] <= $bbox[2]) && ($data[1] >= $bbox[1] && $data[1] <= $bbox[3]))
      {
      	//calculate the relative position of the marker in the image
      	$dist_x = ($data[0] - $bbox[0]) * $this->width / ($bbox[2] - $bbox[0]);
      	$dist_y = $this->height - ($data[1] - $bbox[1]) * $this->height / ($bbox[3] - $bbox[1]);
      	
      	imagecopy($this->image, $tmp, $dist_x, $dist_y-$height, 0, 0, $width, $height);
      }
      
      return true;

    }
    
    return false;

  }    
  
  function get_image($quality = 75) {

    // check ready state
    if (!$this->ready) return false;

    // dump image as jpg and return file
    $output = secure_tmpname('.jpg','tmp',$this->tmpdir);
    //imagetruecolortopalette($this->image, false, 256);
    
    if (@imagejpeg($this->image, $output, $quality)) return $output;
    else return false;
  }
  
  //************************************************************************
  // Private functions
  //************************************************************************

  function _create_image($width, $height) {
    $image = @imagecreatetruecolor($width, $height);
	$white = @imagecolorallocate($image, 255, 255, 255);
    
    @imagefill($image, 0, 0, $white);
    $this->width = $width;
    $this->height = $height;
    return $image;
  }

}

?>