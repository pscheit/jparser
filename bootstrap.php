<?php

define( 'PLUG_COMPILED', true );

require_once __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

/**
 * Import top-level PLUG class
 */ 
import('PLUG.core.PLUG'); 

/**
 * Initialize PLUG environment
 */
PLUG::init();

?>