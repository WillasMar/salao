<?php

    require 'environment.php';

    global $db;
    $config = array();

    if(ENVIRONMENT == 'development'){
        define("BASE_URL", "http://localhost/salao/");
        $config['dbname'] = 'salao';
        $config['host'] = 'localhost';
        $config['dbuser'] = 'root';
        $config['dbpass'] = '';
        $config['port'] = '3308';

    }else{
        define("BASE_URL", "http://localhost/salao/");
        $config['dbname'] = 'salao';
        $config['host'] = 'localhost';
        $config['dbuser'] = 'root';
        $config['dbpass'] = '';
        $config['port'] = '3308';
    }

    $dsn = "mysql:dbname=".$config['dbname'].";host=".$config['host'].";port=".$config['port'].";charset=utf8";
    $user = $config['dbuser'];
    $pass = $config['dbpass'];

    try{
        $db = new PDO($dsn, $user, $pass);

    }catch(PDOException $e){
        echo "Falha: ".$e->getMessage();
        exit;
    }