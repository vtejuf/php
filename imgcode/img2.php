<?php
require "ImgCode.class.php";
if (!isset($_SESSION))
    session_start();

ImgCode::showImg('two', $_SESSION['imgcode']);
