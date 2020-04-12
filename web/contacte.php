<?php
header('Content-Type: text/html; charset=UTF-8');

$nomusuari = $_GET['nomusuari'];
$correu = $_GET['correu'];
$tel = $_GET['tel'];
$assumpte = $_GET['assumpte'];
$cos = $_GET['cos'];

$from = "ajudem.cat <contacte@ajudem.cat>";
$to = "t@ajudem.cat";
$subject = "📥 Nou formulari rebut: " . $assumpte;
$message = "Nom: " . $nomusuari . "<br>Correu: ".$correu."<br>Telèfon: ".$tel."<br>Assumpte: ".$assumpte."<br>Missatge: ".$cos;
$headers = "Content-type: text/html; charset=UTF-8" . "\r\n" . "From:" . $from . "\r\n" . 'Reply-To: '. $correu;
mail($to,$subject,$message, $headers);

header("Location: https://ajudem.cat/contacte-enviat/");
?>
