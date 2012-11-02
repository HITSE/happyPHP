<?php

class Restaurant{
    function __construct(){
    }

    static function getDetail($rname) {     
        $rname = trim($rname);
        if (empty($rname)){
            return false;
        }
        $sql = DB::sql('select * from restaurant where name = :rname', array(':rname' => $rname));
        var_dump($sql);
        return false;
        if (count($sql) > 0){
            $r = $sql[0];
            $rid =  $sql['id'];
            if (empty($rid)){
                $sql = DB::sql('select * from queue where rid = :rid and status = "queueing"', array(
                            ':rid' => $rid));
                $queueNum = count($sql);
                if ($queueNum > 0)
                    $r['queueNum'] = $queueNum; 
                return $r;
            }
        }
        return false;
    }

    static function getBasicInfo($rname) {
        $r = array();
        $temp = getDetail($rname);
        if($temp != false){
            $r['id'] = $temp['id'];
            $r['name'] = $temp['name'];
            $r['phone'] = $temp['phone'];
            return $r;
        }
        return false;
    }    

    static function getAllDetail(){
        $num;
        $sql = DB::sql('select * from restaurant');
        $num = count($sql);
        if ($num > 0){
            $i = 0;
            $r = array();
            for ($i = 0; $i < $num; $i++){
               $temp = $this->getDetail($sql[0]['name']);
               if ($tmep != false)
                   $r[i] = $temp;
            }
            if (count($r) > 0)
                return $r;
        }
        return false;   
    }
}

