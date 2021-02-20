<?php
require_once __DIR__ . "/vendor/autoload.php";

use \Mpsoft\AEWeb\AEWeb;

$empresa = "";
$token = "";

$aeweb = new AEWeb($empresa, $token);

$estado = $aeweb->GET_SesionPerfil();
//$estado = $aeweb->GET_CfdiUsocfdis(NULL, array("inicio"=>1, "registros"=>10));

echo json_encode($estado);