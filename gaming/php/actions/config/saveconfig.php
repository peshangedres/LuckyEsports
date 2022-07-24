<?php

function SaveConfig(MessageService &$messageService, $key, $newValue){
	global $configData, $dictionary, $loggedInUser, $userData;

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	if (!isset($configData->ConfigModels[$key])) {
		//Invalid configuration key
		return;
	}

	if ($configData->ConfigModels[$key]->Editable != true) {
		//Some configuration settings cannot be set via this interface for security reasons.
		return;
	}

	if ($newValue == $configData->ConfigModels[$key]->Value) {
		return;
	}

	if(!UserHasPermissionLevel($loggedInUser, $configData->ConfigModels[$key]->RequiredPermissionsWrite)){
        return "INSUFFICIENT_PERMISSIONS";
    }

	$configData->UpdateConfig($key, $newValue, $loggedInUser->Id, "");

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"CONFIG_UPDATED", 
		"Config value edited: $key = '$newValue'", 
		$loggedInUser->Id)
	);
	$userData->LogAdminAction($loggedInUser->Id);
	return "SUCCESS";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if(IsAdmin($loggedInUser) !== false){
		$overallActionResult = "NO_CHANGE";
		foreach($_POST as $postKey => $value){
		    if(startsWith($postKey, "CONFIG_VALUE_")){
                $key = substr($postKey, strlen("CONFIG_VALUE_"));
                $actionResult = SaveConfig($messageService, $key, $value);
                if($actionResult != ""){
                    $overallActionResult = $actionResult;
                }
            }
		}
		return $overallActionResult;
	}
}

?>