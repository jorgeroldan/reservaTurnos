<?php
//ini_set("soap.wsdl_cache_enabled", "0");

require_once ('Force.com/soapclient/SforceEnterpriseClient.php');
include("../../data.php");
//require_once("../data.php");

$WSDL_PATH = "Force.com/soapclient/enterprise.wsdl.xml";

public $generacion = 2018;

abstract class SlotsModel{

protected $mySforceConnection = null;
protected $mySoapClient = null;
protected $mylogin = null;

abstract public function getOcupado($id, $vacantes);
abstract public function ocuparTurno($email, $turno, $year);

public function connect($user,$pass){
	$this->mySforceConnection = new SforceEnterpriseClient();
	//$this->mySoapClient = $this->mySforceConnection->createConnection("enterprise_ensena.wsdl");
	$this->mySoapClient = $this->mySforceConnection->createConnection("enterprise_ensena.wsdl.xml");
	//$this->mylogin = $this->mySforceConnection->login("user", "token");
	$this->mylogin = $this->mySforceConnection->login($user,$pass);
}

/*
	@provincia: Buenos Aires, Córdoba, Salta
*/
public function getVacantes($tipo, $email){
	$query = "SELECT Id, REC_proceso_de_entrevistas__c from Lead WHERE RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion' AND Email = '$email'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);
	if(count($queryResult->records)==0 || !isset($queryResult->records[0]->REC_proceso_de_entrevistas__c)){
		return array();
	}
	$provincia = $queryResult->records[0]->REC_proceso_de_entrevistas__c;

	$query = "SELECT Id from Campaign WHERE RecordType.DeveloperName = 'Reclutamiento' AND IsActive = True AND Provincia__c = '$provincia'";//limit 1
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);
	$campaignId = $queryResult->records[0]->Id;

	$query = "SELECT Id, Inicio__c, Fin__c, Lugar__c, Vacantes__c from Acci_n_de_Reclutamiento__c WHERE Campa_a__c = '$campaignId' AND Activa__c = True AND Tipo__c = '$tipo'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);
	return $queryResult->records;
}

protected function anotados($id, $tipo){
	$query = "SELECT COUNT(Id) from Lead WHERE ((RecordType.DeveloperName = 'Postulante') OR (RecordType.DeveloperName = 'Prospecto')) AND Generaci_n__c = '$generacion' AND $tipo = '$id'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	$retorno = 0;
	foreach ($queryResult->records as $record) {
		$retorno = $record->any['expr0'];
	}

	return $retorno;
}

protected function enviarConfirmacion($id, $templateId){
	try{
		$massEmail = new MassEmailMessage();
		$massEmail->setSaveAsActivity(true);
		$massEmail->setEmailPriority(EMAIL_PRIORITY_LOW);
		$massEmail->setTemplateId($templateId);
		$massEmail->setTargetObjectIds(array ($id));
		$emailResponse = $this->mySforceConnection->sendMassEmail(array($massEmail));
	}
	catch (Exception $e) {
		echo $mySforceConnection->getLastRequest();
		echo $e->faultstring;
	}
}

/**
  *	@param tipo es el tipo de objeto (Entrevista Individual, Grupal, Final)
  *	@param id es el id del contacto.
**/
protected function crearObjeto($tipo, $id){
//Busco que el contacto $id no tenga asociado un objeto $tipo
	$query = "SELECT COUNT(Id) from $tipo WHERE Postulante__c = '$id'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	$cantidad = 0;
	foreach ($queryResult->records as $record) {
		$cantidad = $record->any['expr0'];
	}

	if(($tipo != "Entrevista_Final__c") && ($cantidad != 0)){ //El contacto ya tiene un objeto de este tipo
		return;
	}

	//Le creo el objeto
	$o = new stdclass();
	$o->Postulante__c = $id;

	$createResponse = $this->mySforceConnection->create(array($o), $tipo);
}

protected function logAndMail($email, $turno){
	$mensaje = "{$email} intentó registrarse a {$turno} pero no fue encontrado en salesforce.\r\n";
	$csv = "{$email}, {$turno}\r\n";
	mail('quieroserpexa@ensenaporargentina.org', 'Registración a Charla', $mensaje);
	file_put_contents('charla.log', date('c').": ".$mensaje, FILE_APPEND);
	file_put_contents('charla.csv', $csv, FILE_APPEND);
}

public function getFecha($dateAndTime){
	return substr($dateAndTime, 0, 10);
}
public function getHora($dateAndTime){
	$date = new DateTime($dateAndTime);
	$date->sub(new DateInterval('PT3H'));
	return $date->format('H:i');
//substr(date('F jS Y', strtotime($dateAndTime)-0.12500052631), 11, 5);
}
}

























function getOcupado($fecha, $turno, $lugar){
	$query = "SELECT Entrevista_IND_Fecha__c, Entrevista_IND_Turno__c, COUNT(Id) from Lead WHERE RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion' AND Entrevista_IND_Fecha__c = '".$fecha."' AND Entrevista_IND_Turno__c = '".$turno."' AND Entrevista_IND_Lugar__c = '".$lugar."' GROUP BY Entrevista_IND_Fecha__c, Entrevista_IND_Turno__c";

	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	$retorno = 0;
	foreach ($queryResult->records as $record) {
		$retorno = $record->any['expr0'];
	}

	//echo "cantidad = {$retorno}";
	return $retorno;
}

function getOcupadoGrupal($fecha, $turno, $lugar){

	$query = "SELECT Entrevista_GRU_Fecha__c, Entrevista_GRU_Turno__c, COUNT(Id) from Lead WHERE RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion' AND Entrevista_GRU_Fecha__c = '".$fecha."' AND Entrevista_GRU_Turno__c = '".$turno."' AND Entrevista_GRU_Lugar__c = '".$lugar."' GROUP BY Entrevista_GRU_Fecha__c, Entrevista_GRU_Turno__c";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	$retorno = 0;
	foreach ($queryResult->records as $record) {
		$retorno = $record->any['expr0'];
	}

	//echo "cantidad = {$retorno}";
	return $retorno;
}

function getOcupadoFIN($fecha, $turno, $lugar){
	$query = "SELECT Entrevista_FIN_Fecha__c, Entrevista_FIN_Turno__c, COUNT(Id) from Lead WHERE RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion' AND Entrevista_FIN_Fecha__c = '".$fecha."' AND Entrevista_FIN_Turno__c = '".$turno."' AND Entrevista_FIN_Lugar__c = '".$lugar."' GROUP BY Entrevista_FIN_Fecha__c, Entrevista_FIN_Turno__c";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	$retorno = 0;
	foreach ($queryResult->records as $record) {
		$retorno = $record->any['expr0'];
	}

	//echo "cantidad = {$retorno}<br>";
	return $retorno;
}

function getOcupadoFirma($fecha, $turno, $lugar){
	$query = "SELECT Firma_Fecha__c, Firma_Turno__c, Firma_Lugar__c, COUNT(Id) from Lead WHERE RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion' AND Firma_Fecha__c = '".$fecha."' AND Firma_Turno__c = '".$turno."' AND Firma_Lugar__c = '".$lugar."' GROUP BY Firma_Fecha__c, Firma_Turno__c, Firma_Lugar__c";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	$retorno = 0;
	foreach ($queryResult->records as $record) {
		$retorno = $record->any['expr0'];
	}

	//echo "cantidad = {$retorno}<br>";
	return $retorno;
}

function ocuparTurno($email, $fecha, $turno, $lugar){
	$query = "SELECT Id from Lead WHERE Email = '{$email}' AND RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	if($queryResult->size != 1){
		//echo "error!";
		return false;
	}

	$id = $queryResult->records[0]->Id;
	//echo "ID: {$id}<br>";

	$sObject1 = new stdclass();
	$sObject1->Id = $id;
	$sObject1->Entrevista_IND_Fecha__c = $fecha;
	$sObject1->Entrevista_IND_Turno__c = $turno;
	$sObject1->Entrevista_IND_Lugar__c = $lugar;
	$response = $this->mySforceConnection->update(array ($sObject1), 'Lead');

	if ($response[0]->success == 1) {
		return true;
	}
	return false;
}

function ocuparTurnoGrupal($email, $fecha, $turno, $lugar){
	$query = "SELECT Id from Lead WHERE Email = '{$email}' AND RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	if($queryResult->size != 1){
		//echo "error!";
		return false;
	}

	$id = $queryResult->records[0]->Id;
	//echo "ID: {$id}<br>";

	$sObject1 = new stdclass();
	$sObject1->Id = $id;
	$sObject1->Entrevista_GRU_Fecha__c = $fecha;
	$sObject1->Entrevista_GRU_Turno__c = $turno;
	$sObject1->Entrevista_GRU_Lugar__c = $lugar;
	$response = $this->mySforceConnection->update(array ($sObject1), 'Lead');

	if ($response[0]->success == 1) {
		return true;
	}
	return false;
}

function enviar_confirmacion_fin($id){
	try{
		$massEmail = new MassEmailMessage();
		$massEmail->setSaveAsActivity(true);
		$massEmail->setEmailPriority(EMAIL_PRIORITY_LOW);
		$massEmail->setTemplateId("00XE00000011UQl");
		$massEmail->setTargetObjectIds(array ($id));
		$emailResponse = $this->mySforceConnection->sendMassEmail(array($massEmail));
	}
	catch (Exception $e) {
		echo $mySforceConnection->getLastRequest();
		echo $e->faultstring;
	}
}

function enviar_confirmacion_firma($id){
	try{
		$massEmail = new MassEmailMessage();
		$massEmail->setSaveAsActivity(true);
		$massEmail->setEmailPriority(EMAIL_PRIORITY_LOW);
		$massEmail->setTemplateId("00XE00000011bjM");
		$massEmail->setTargetObjectIds(array ($id));
		$emailResponse = $this->mySforceConnection->sendMassEmail(array($massEmail));
	}
	catch (Exception $e) {
		echo $mySforceConnection->getLastRequest();
		echo $e->faultstring;
	}
}

function ocuparTurnoFIN($email, $fecha, $turno, $lugar){
	$query = "SELECT Id from Lead WHERE Email = '{$email}' AND RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '$generacion'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	if($queryResult->size != 1){
		//echo "error!";
		return false;
	}

	$id = $queryResult->records[0]->Id;
	//echo "ID: {$id}<br>";

	$sObject1 = new stdclass();
	$sObject1->Id = $id;
	$sObject1->Entrevista_FIN_Fecha__c = $fecha;
	$sObject1->Entrevista_FIN_Turno__c = $turno;
	$sObject1->Entrevista_FIN_Lugar__c = $lugar;
	$response = $this->mySforceConnection->update(array ($sObject1), 'Lead');

	if ($response[0]->success == 1) {
		$this->enviar_confirmacion_fin($id);
		return true;
	}
	return false;
}

function ocuparFirma($email, $fecha, $turno, $lugar){
	$query = "SELECT Id from Lead WHERE Email = '{$email}' AND RecordType.DeveloperName = 'Postulante' AND Generaci_n__c = '2015'";
	$response = $this->mySforceConnection->query($query);
	$queryResult = new QueryResult($response);

	if($queryResult->size != 1){
		//echo "error!";
		return false;
	}

	$id = $queryResult->records[0]->Id;
	//echo "ID: {$id}<br>";

	$sObject1 = new stdclass();
	$sObject1->Id = $id;
	$sObject1->Firma_Fecha__c = $fecha;
	$sObject1->Firma_Turno__c = $turno;
	$sObject1->Firma_Lugar__c = $lugar;
	$response = $this->mySforceConnection->update(array ($sObject1), 'Lead');

	if ($response[0]->success == 1) {
		$this->enviar_confirmacion_firma($id);
		return true;
	}
	return false;
}

?>
