<?php

    $enableAll  = '{"Read":true,"Create":true,"Update (owner)":true,"Update (other)":true,"Delete (owner)":true,"Delete (other)":true}';
    $disableAll = '{"Read":false,"Create":false,"Update (owner)":false,"Update (other)":false,"Delete (owner)":false,"Delete (other)":false}';
    $readOnly   = '{"Read":true,"Create":false,"Update (owner)":false,"Update (other)":false,"Delete (owner)":false,"Delete (other)":false}';
    
    // The objective is to initialize first SuperAdmin role and Public role with:
    // https://jbase-shahfazliz.c9.io/auth/role/init.php
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/DBEvents.php');
    $db     = new DBEvents('auth', 'role');
    $result = $db-> postData(array('Name'=>'SuperAdmin','Auth'=>$enableAll,'Role'=>$enableAll));
    $result = $db-> postData(array('Name'=>'RegisteredUser'));
    $result = $db-> postData(array('Name'=>'Public'));
    $db-> close();
    
    // Next Objective is to initialize first Auth entry as SuperAdmin 
    // Username and passrowd is superadmin
    // so make sure to change username and password later
    require_once($_SERVER["DOCUMENT_ROOT"].'/auth/APIAuth.php');
    $db     = new APIAuth();
    $result = $db-> postData(array('Username'=>'superadmin', 'Password'=>'superadmin', 'Roles'=>'SuperAdmin'));
    $db-> close();
?>