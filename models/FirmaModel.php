<?php
require_once ('slots_model.php');

class FirmaModel extends SlotsModel{
	public function getOcupado($id, $vacantes){
		$anotados = $this->anotados($id, "SEL_Firma__c");
		return $anotados < $vacantes;
	}


	public function ocuparTurno($email, $turno){
		$query = "SELECT Id from Contact WHERE Email = '{$email}' AND ((RecordType.DeveloperName = 'Postulante') OR (RecordType.DeveloperName = 'Prospecto')) AND Generaci_n__c = '2016'";
		$response = $this->mySforceConnection->query($query);
		$queryResult = new QueryResult($response);

		if($queryResult->size != 1){
			$this->logAndMail($email, $turno);
			return false;
		}

		$id = $queryResult->records[0]->Id;

		$sObject1 = new stdclass();
		$sObject1->Id = $id;
		$sObject1->SEL_Firma__c = $turno;
		$response = $this->mySforceConnection->update(array ($sObject1), 'Contact');
		
		
		if ($response[0]->success == 1) {
		//	$this->enviarConfirmacion($id, "00XE0000001ERkv"); TODO: Cambiar template
			return true;
		}

		$this->logAndMail($email, $turno);
		return false;
	}
}
?>

