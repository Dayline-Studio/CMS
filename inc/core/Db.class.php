<?php

class Db {

    private static $sql;
    
    public static function init($sql = array()) {
        self::$sql = self::connect($sql['host'],$sql['db'],$sql['user'],$sql['pw']);
    }
    
    public static function connect($host,$db,$user,$pw) {
        $handler = new PDO ("mysql:host=$host;dbname=$db;charset=utf8",$user,$pw);
        return $handler;
    }
    
    public static function query($qry, $params = array(), $fetchmode = PDO::FETCH_ASSOC) {
        $stmt = self::$sql->prepare($qry);
        $stmt->setFetchMode($fetchmode);
        if (!is_array($params)) $params = array();
        return $stmt->execute($params) ? $stmt->fetchAll() : false;   
    }
    
    public static function npquery($qry, $fetchmode = PDO::FETCH_ASSOC) {
        $stmt = self::$sql->prepare($qry);
        $stmt->setFetchMode($fetchmode);
        if ($stmt->execute()) {
            return strpos($qry,'LIMIT 1') ? $stmt->fetch() : $stmt->fetchAll();
        }
        return false;
    }
    
    public static function nrquery($qry, $params = array(), $fetchmode = PDO::FETCH_ASSOC) {
        $stmt = self::$sql->prepare($qry);
        $stmt->setFetchMode($fetchmode);
        if (!is_array($params)) $params = array();
        return $stmt->execute($params) ? true : false;   
    }
    
    public static function closeConnection() {
        self::$sql = null;
    }
}