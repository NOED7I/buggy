<?php
use \Tx\DB;
class DBTest extends TestCase{

    public function testInsert(){
        $r = DB::exec('insert into fuck values(null, "fuck" )');
        $this->assertTrue(\Tx\b($r)===true);
    }
    public function testGetAll(){
        $r = DB::getAll('select * from fuck');
        $this->assertTrue(\Tx\b($r)===true);
    }
    public function testGetRow(){
        $r = DB::getRow('select * from fuck limit 1');
        $this->assertTrue(\Tx\b($r)===true);
    }
    public function testGetCol(){
        $r = DB::getCol('select name from fuck');
        $this->assertTrue(\Tx\b($r)===true);
    }
    public function testGetCell(){
        $r = DB::getCell('select name from fuck limit 1');
        $this->assertTrue(\Tx\b($r)===true);
    }
    public function testUpdate(){
        $r = DB::exec('UPDATE fuck SET name="test" WHERE id=1');
        $this->assertTrue(\Tx\b($r)===true);
    }
}

