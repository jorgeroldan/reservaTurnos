<?php
include ('slots_model.php');

class CharlasModel extends SlotsModel{
	public function getOcupado($id, $vacantes){
		$anotados = $this->anotados($id, "REC_Charla__c");
		return $anotados < $vacantes;
	}


	public function ocuparTurno($email, $turno, $year){

		$query = "SELECT Id from Contact WHERE Email = '{$email}' AND ((RecordType.DeveloperName = 'Postulante') OR (RecordType.DeveloperName = 'Prospecto')) AND Generaci_n__c = '{$year}'";
		//$query = "SELECT Id from Contact WHERE Email = '{$email}' AND ((RecordType.DeveloperName = 'Postulante') OR (RecordType.DeveloperName = 'Prospecto')) AND ubicacion = '$ubicacion' AND Generaci_n__c = '2017'";
		$response = $this->mySforceConnection->query($query);
		//print_r($response);
		$queryResult = new QueryResult($response);

		if($queryResult->size <= 0){
			$this->logAndMail($email, $turno);
			return false;
		}

		$id = $queryResult->records[0]->Id;

		$sObject1 = new stdclass();
		$sObject1->Id = $id;
		$sObject1->REC_Charla__c = $turno;
		$response = $this->mySforceConnection->update(array ($sObject1), 'Contact');
		
		if ($response[0]->success == 1) {
			$this->enviarConfirmacion($id, "00XE0000001ERkv");
			return true;
		}

		$this->logAndMail($email, $turno);
		return false;
	}
}
?>

