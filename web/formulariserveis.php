<?php
header('Content-Type: text/html; charset=UTF-8');
$servername = "";
$username = "";
$password = "";
$dbname = "";


$nomusuari = $_GET['nomusuari'];
$genere = $_GET['genere'];
$correu = $_GET['correu'];
$empresa = $_GET['empresa'];
$web = $_GET['web'];
$codipostal = $_GET['codipostal'];
$nomservei = $_GET['nomservei'];
$descripccioservei = $_GET['descripccioservei'];
$categoria = $_GET['categoria'];
$tel = $_GET['tel'];
$whats = $_GET['whats'];
$telegram = $_GET['telegram'];

$nfoto = rand(1, 20);
$foto = "/fotos/categories/".$categoria."/".$nfoto.".jpg";

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
$randIndexllmaj1 = array_rand($arrayllmaj);
$randIndexllmin1 = array_rand($arrayllmin);
$randIndexnum1 = array_rand($arraynum);
$randIndexsym1 = array_rand($arraysym);

$codi = $any . $mes . $dia . $arrayllmaj[$randIndexllmaj] . $arraynum[$randIndexnum] . $arraysym[$randIndexsym] . $arrayllmin[$randIndexllmin];

$codieliminar = $arrayllmaj[$randIndexllmaj] . $arraynum[$randIndexnum] . $arraysym[$randIndexsym] . $arrayllmin[$randIndexllmin] . $arrayllmaj[$randIndexllmaj1] . $arraynum[$randIndexnum1] . $arraysym[$randIndexsym1] . $arrayllmin[$randIndexllmin1] . $any . $mes . $dia;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

$nomusuari= $conn->real_escape_string($nomusuari);
$foto= $conn->real_escape_string($foto);
$genere= $conn->real_escape_string($genere);
$correu= $conn->real_escape_string($correu);
$empresa= $conn->real_escape_string($empresa);
$web= $conn->real_escape_string($web);
$codipostal= $conn->real_escape_string($codipostal);
$nomservei= $conn->real_escape_string($nomservei);
$descripccioservei= $conn->real_escape_string($descripccioservei);
$categoria= $conn->real_escape_string($categoria);
$tel= $conn->real_escape_string($tel);
$whats= $conn->real_escape_string($whats);
$telegram= $conn->real_escape_string($telegram);
$codi= $conn->real_escape_string($codi);


$sql = "INSERT INTO serveis (codi, codieliminar, nomusuari, foto, genere, correu, empresa, web, codipostal, nomservei, descripccioservei, categoria, tel, whats, telegram) VALUES ('".$codi."', '".$codieliminar."', '".$nomusuari."', '".$foto."', '".$genere."', '".$correu."', '".$empresa."', '".$web."', '".$codipostal."', '".$nomservei."', '".$descripccioservei."', '".$categoria."', '".$tel."', '".$whats."', '".$telegram."')";

if ($conn->query($sql) === TRUE) {
	$from = "ajudem.cat <serveis@ajudem.cat>";
	$to = $correu;
	$subject = "🔔 Validació de servei #" . $codi ;
	$premessage = file_get_contents("correuvalidar.php");

	$message = str_replace("XXXXXX", "https://ajudem.cat/validarservei.php?codi=" . $codi, $premessage);
	$message = str_replace("YYYYYY", "https://ajudem.cat/eliminarservei.php?codi=" . $codieliminar, $message);
	$message = str_replace("ZZZZZZ", "https://ajudem.cat/verifica", $message);


	$headers = "Content-type: text/html; charset=UTF-8" . "\r\n" . "From:" . $from . "\r\n" . 'Reply-To: noreply@ajudem.cat';
	mail($to,$subject,$message, $headers);

	header("Location: https://ajudem.cat/servei-introduit/");

} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
