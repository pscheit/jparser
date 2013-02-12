<?php

class AutoLoadingAcceptanceTest extends \PHPUnit_Framework\TestCase {
  
  
  public function testPLUGCoreIsDefined() {
    $this->assertTrue(class_exists('PLUG'));
  }
  
  
}
?>