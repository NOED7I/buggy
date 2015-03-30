<?php
use \Tx\v as v;
class IsTest extends TestCase{
    public function testIsBankCard(){
        $r =  \Tx\isBankCard(6228480031561499810);
        $this->assertTrue($r===true);
        $r =  \Tx\isBankCard(6228480031561499811);
        $this->assertTrue($r===false);
    }

    public function testIsChineseID(){
        $r =  \Tx\isChineseID("140622198609202912");
        $this->assertTrue($r===true);
        $r =  \Tx\isChineseID("140622190609202912");
        $this->assertTrue($r===false);
    }

    public function testIsEmail(){
        $r =  \Tx\isEmail("a@a.com");
        $this->assertTrue($r===true);
        $r =  \Tx\isEmail("aa.com");
        $this->assertTrue($r===false);
    }

    public function testIsChineseWords(){
        $r =  \Tx\isChineseWords("丑小鸭");
        $this->assertTrue($r===true);
        $r =  \Tx\isChineseWords("hi");
        $this->assertTrue($r===false);
    }
}

