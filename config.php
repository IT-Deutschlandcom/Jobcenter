<?php
//We start sessions
session_start();

/******************************************************
------------------Required Configuration---------------
Please edit the following variables so the members area
can work correctly.
******************************************************/

//We log to the DataBase
mysql_connect('localhost', 'root', '1234');
mysql_select_db('csa');

//Webmaster Email
$mail_webmaster = 'webmaster@csa.net';

//Top site root URL
$url_root = 'http://localhost/';

/******************************************************
-----------------Optional Configuration----------------
******************************************************/

//Home page file name
$url_home = 'index.php';

//Design Name
$design = 'default';
?>