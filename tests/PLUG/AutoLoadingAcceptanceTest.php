<?php

class AutoLoadingAcceptanceTest extends \PHPUnit_Framework_TestCase {
  
  
  public function testPLUGCoreIsDefined() {
    $this->assertTrue(class_exists(\PLUG\core\PLUG::class));
  }
  
  
}
?>