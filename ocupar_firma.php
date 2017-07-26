<?php
require_once ('models/FirmaModel.php');
require_once ('../data.php');

$model = new FirmaModel(); 
$model->connect($user,$pass);

if($model->ocuparTurno($_POST['email'], $_POST['id']))
	echo "Turno reservado con éxito. Muchas gracias!";
else
	echo "El turno no pudo ser reservado. Nuestro equipo se pondrá en contacto para confirmar tu turno!";

?>