<?php

header('Content-Type: text/html; charset=UTF-8');

$correu = $_GET['correu'];
$nomtrobada = $_GET['nomtrobada'];
$date = date('d-m-Y', strtotime($_GET['date']));
$time = $_GET['time'];
$convidats = $_GET['convidats'];

//Generar #CODI
$any = date("y");
$mes = date('m');
$dia = date("d");
$llmaj = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$llmin = "abcdefghijklmnopqrstuvwxyz";
$arrayllmaj = str_split($llmaj);
$arrayllmin = str_split($llmin);
$num = "1234567890";
$sym = "#$%&*+/?@";
$arraynum = str_split($llmaj);
$arraysym = str_split($llmin);
$randIndexllmaj = array_rand($arrayllmaj);
$randIndexllmin = array_rand($arrayllmin);
$randIndexnum = array_rand($arraynum);
$randIndexsym = array_rand($arraysym);
$codi = $any . $mes . $dia . $arrayllmaj[$randIndexllmaj] . $arraynum[$randIndexnum] . $arraysym[$randIndexsym] . $arrayllmin[$randIndexllmin];

$nomtrobadasenseespais = str_replace(" ", "", $nomtrobada);
$enllac = "https://agora.ajudem.cat/" . $nomtrobadasenseespais . $codi;
$notierror = "S'ha produ&iuml;t un error. Revisa la configuraci&oacute; o posa't en contacte amb nosaltres.";
$notitotb = "La reuni&oacute; ha estat creada correctament. Pots accedir fent <a href='.$enllac.'>clic aqu&iacute;</a>. Tamb&eacute; t'hem enviat un missatge a tu i als convidats; si no el trobeu, reviseu la carpeta de correu brossa!";

$from = "Àgora <agora@ajudem.cat>";
$to = $correu.", ".$convidats;
$subject = "📹 Trobada programada: " . $nomtrobada ;
$message = file_get_contents("mailtrobadacreada.html");
$message = str_replace("XXXXXX", $correu, $message);
$message = str_replace("YYYYYY", $nomtrobada, $message);
$message = str_replace("ZZZZZZ", $date, $message);
$message = str_replace("AAAAAA", $time, $message);
$message = str_replace("GGGGGG", $enllac, $message);
$headers = "Content-type: text/html; charset=UTF-8" . "\r\n" . "From:" . $from . "\r\n" . 'Reply-To: '.$correu;

$notierror = urldecode($notierror);
$notitotb = urlencode($notitotb);

if (mail($to,$subject,$message, $headers)) {
	$type = "success";
	header("Location: https://ajudem.cat/trobat/crea-una-trobada/?noti=".$notitotb."&type=".$type);
}else{
	$type = "danger";
	header("Location: https://ajudem.cat/trobat/crea-una-trobada/?noti=".$notierror."&type=".$type);
}
?>