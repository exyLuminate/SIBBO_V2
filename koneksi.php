<?php
$db_host = 'localhost';
$db_user = 'root'; // Default Laragon
$db_pass = '';     // Default Laragon
$db_name = 'sibbo';

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if(mysqli_connect_errno()){
	echo 'Gagal melakukan koneksi ke Database : '.mysqli_connect_error();
    die();
}
?>