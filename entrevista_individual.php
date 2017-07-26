<?php
require_once ('models/EIModel.php');
require_once ('../data.php');

$model = new EIModel(); 
$model->connect($user, $pass);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>ENSEÑÁ POR ARGENTINA: ENTREVISTAS INDIVIDUALES.</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script src="jquery.validate.js"></script>

	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

	<style>
	.boton{
		background-color: rgba(0, 128, 0, 0.53);
		color: white;
		text-align: center;

		-webkit-border-radius: 20px;
		-moz-border-radius: 20px;
		border-radius: 20px;
		border: 3px solid rgba(230, 23, 23, 0.33);

		cursor: pointer;
	}

	.vacia{
		background-color: red;
		color: white;
		text-align: center;

		-webkit-border-radius: 20px;
		-moz-border-radius: 20px;
		border-radius: 20px;
		border: 3px solid rgba(230, 23, 23, 0.33);
	}

	#cargando{
		color:purple;
	}

	input[type='email']{
		width:300px;
	}

	span.etapa{
		font-style: bolder;
		font-style: italic;
		color: hotpink;
		text-decoration: underline;
	}

	span.indicacion{
		color: red;
		font-weight: bolder;
		border: 2px solid #F00;
		-webkit-border-radius: 50px;
		-moz-border-radius: 50px;
		border-radius: 50px;
		font-size: larger;
	}
	</style>
</head>
<body>

<div class="container">
	<div class="row">
		<div class="col-lg-12 col-md-12"><h2>ENSEÑÁ POR ARGENTINA: ENTREVISTAS INDIVIDUALES</h2></div>
	</div>
	<div class="row">
		<div class="col-lg-12 col-md-12"><h4>Completando tus datos en el siguiente formulario quedarás registrado para una <span class="etapa">ENTREVISTA INDIVIDUAL</span>.<br>
			Tené en cuenta que, si bien podés cambiar el turno en el que te registraste, los turnos tienen un cupo limitado y son dinámicos.</h4></div>
	</div>
	<br/><br/>

	<form name="datos" id="datos" method="post" action="">

		<div class="row">
			<div class="col-lg-4 col-md-4"><span class="indicacion">&nbsp;1&nbsp;</span>&nbsp;Ingrese sus datos</div>
			<div class="col-lg-4 col-md-4">
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<span class="indicacion">&nbsp;a&nbsp;</span>&nbsp;dirección de correo electrónico: 
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<span class="indicacion">&nbsp;b&nbsp;</span>&nbsp;Nombre: 
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<span class="indicacion">&nbsp;c&nbsp;</span>&nbsp;Apellido: 
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-4">
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<input type="email" name="email" id="email" placeholder="email" required style="width:100%" <?php if (isset($_POST["email"])) echo 'value="'.$_POST["email"].'"'; ?> />
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<input type="text" name="nombre" id="nombre" placeholder="nombre" required style="width:100%" <?php if (isset($_POST["nombre"])) echo 'value="'.$_POST["nombre"].'"'; ?> >
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<input type="text" name="apellido" id="apellido" placeholder="apellido" required style="width:100%" <?php if (isset($_POST["apellido"])) echo 'value="'.$_POST["apellido"].'"'; ?> >
					</div>
				</div>
			</div>
		</div>
		<br/>

		<div class="row">
		<div class="col-lg-2 col-md-2 col-lg-offset-4 col-md-offset-4">
			<input type="submit" id="buscar" class="boton" value="BUSCAR TURNOS"/>
		</div>

	</form>

</div>

	<div class="row busqueda">
		<div class="col-lg-12 col-md-12"><span class="indicacion">&nbsp;2&nbsp;</span>&nbsp;Seleccione un turno de la siguiente lista. Al hacerlo habrá seleccionado un lugar, fecha y rango horario para la entrevista individual.</div>
	</div>
	<br/>	


	<div class="row busqueda">
		<div class="col-lg-6 col-md-6"><b>LUGAR</b></div>
		<div class="col-lg-2 col-md-2"><b>FECHA</b></div>
		<div class="col-lg-2 col-md-2"><b>HORA</b></div>
		<div class="col-lg-2 col-md-2"><b>SELECCIONAR</b></div>
	</div>
	<br/>

	<?php
	$individuales = array();
	if(isset($_POST["email"]))
	{
		$individuales = $model->getVacantes("Entrevista Individual", $_POST["email"]);
	}

	$i=0;
	foreach ($individuales as $key => $value) {
		$lugar = $value->Lugar__c;
		$vacantes = $value->Vacantes__c;
		$fecha = $model->getFecha($value->Inicio__c);
		$turno = $model->getHora($value->Inicio__c) . " - " . $model->getHora($value->Fin__c);
		$libre = $model->getOcupado($value->Id, $vacantes);
		$id = $value->Id;
		echo '<div class="row busqueda">
				<div class="col-lg-6 col-md-6" id="lugar'.$i.'">'.$lugar.'</div>
				<div class="col-lg-2 col-md-2" id="fecha'.$i.'">'.$fecha.'</div>
				<div class="col-lg-2 col-md-2" id="turno'.$i.'">'.$turno.'</div>
				<div class="col-lg-2 col-md-2">';
		if($libre){
			echo '<input class="seleccionador" type="checkbox" id="'.$id.'" value="'.$id.'">';
		}
		else{
			echo '<span style="color:red">OCUPADO</span>';
		}
		echo '</div></div>';
		$i++;
	}

	if (count($individuales)==0) {
		echo '<div class="row">
				<div class="col-lg-12 col-md-12 vacia busqueda">No se encontraron opciones para su email. Por favor, pongase en contacto con seleccion@ensenaporargentina.org</div>';
	}

	?>

<br/>

<div class="row seleccion">
	<div class="col-lg-3 col-lg-offset-1 col-md-3 col-lg-offset-1"><span class="indicacion">&nbsp;3&nbsp;</span>&nbsp;Envianos el turno que seleccionaste.</div>
	<br/>	

	<div class="col-lg-2">
		<div id="boton" class="boton">ACEPTAR</div>
	</div>
	<div class="col-lg-2" id="cargando"><ul class="fa-ul"><li><i class="fa-li fa fa-spinner fa-spin"></i>ocupando...</li></ul></div>
</div>
	
</div>

<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="jquery.validate.js"></script>

<script>
$(document).ready(function(){
	<?php 
		if(!isset($_POST['email']))
			echo '$(".busqueda").hide();';

		if (!isset($individuales)||count($individuales)==0) {
			echo '$(".seleccion").hide();';
		}
		else {echo '$(".seleccion").show();';}
		;
	?>

	$("#cargando").hide();

	$(".seleccionador").click(function(){
		var i = 0;
		$(".seleccionador").each(function(a, b){
			if(b.checked){
				i++;
				if(i>1){
					alert("Solo se puede seleccionar un turno!");
					b.checked = false;
				}
			}
		});

	});

	$("#boton").click(function(){
		var i = 0;
		$(".seleccionador").each(function(a,b){
			if(b.checked){
				var email = $("#email").val();
				$("#cargando").show();

				$.ajax({
					type: "POST",
					url: "ocupar_ei.php",
					data: { email: email, id: b.id }
				})
				.done(function( msg ) {
					$("#cargando").hide();
					alert(msg);
				});

				i++;
			}
		})
		if(i==0){
			alert("Debe seleccionar un turno!");
		}
	})
});
</script>

</body>
</html>


