<?php

if(!isset($_POST['credential']))  || if(!isset($_POST['g_csrf_token'])){
    header('location: index.php');
    exit;
}

$cookie = $_COOKIE['g_csrf_token']??'';

if($_POST['g_csrf_token'] != $cookie){
    header('location: index.php');
    exit;

}
