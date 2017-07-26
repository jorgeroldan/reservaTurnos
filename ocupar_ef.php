<?php
require_once ('models/EFModel.php');
require_once ('../data.php');

$model = new EFModel(); 
$model->connect($user,$pass);

if($model->ocuparTurno($_POST['email'], $_POST['id'], $year))
	echo "Turno reservado con éxito. Muchas gracias!";
else
	echo "El turno no pudo ser reservado. Nuestro equipo se pondrá en contacto para confirmar tu turno!";

?>