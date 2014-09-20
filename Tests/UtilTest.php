<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 6/17/13
 * Time: 8:04 PM */
include_once __DIR__ . '/../Base.php';

use CPath\Framework\Request\Common\CLIRequest;

class UtilTest extends PHPUnit_Framework_TestCase {

    public function testCLI()
    {
        $_SERVER['argv'] = array('index.php', 'GET', '/my/path');
        $CLI = CLIRequest::fromRequest(true, false);

        $this->assertEquals('GET', $CLI->getMethodName());
        $this->assertEquals('/my/path', $CLI->getPath());

        $_SERVER['argv'] = array('index.php', 'my', 'path');
        $CLI = CLIRequest::fromRequest(true, false);

        $this->assertEquals('CLI', $CLI->getMethodName());
        $this->assertEquals('/my/path', $CLI->getPath());
    }

//    public function testJSON() {
//        $json = Util::toJSON(array('key'=>'val','arr'=>array('key2'=>'val2')));
//        $this->assertTrue(is_array($json));
//    }
//
//    public function testXML() {
//        $XML = Util::toXML(array('key'=>'val','arr'=>array('key2'=>'val2')));
//        $this->assertTrue($XML instanceof \SimpleXMLElement);
//    }
}
