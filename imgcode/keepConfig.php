<?php

require "ImgCode.class.php";
$set = array(
    'width' => 160,
    'height' => 35,
    'fontColor' => array(0, 0, 0),
    'fontSize' => 30, //数字大小
    'fontLen' => 6, //数字个数
);
$config = new Config($set);
if (!isset($_SESSION))
    session_start();
$_SESSION['imgcode']['C']=$config->getConfig();
$_SESSION['imgcode']['S']=$config->getImgSet();
$_SESSION['imgcodestr']=$_SESSION['imgcode']['C']['codestr'];