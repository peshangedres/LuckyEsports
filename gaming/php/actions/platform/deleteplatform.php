<?php

function DeletePlatform(MessageService &$messageService, $platformId){
	global $loggedInUser, $_FILES, $ip, $userAgent, $platformData, $platformDbInterface, $userData;

	$platformId = intval(trim($platformId));

	//Authorize user
	if($loggedInUser === false){
		return "NOT_LOGGED_IN";
	}

	if(IsAdmin($loggedInUser) === false){
		return "NOT_AUTHORIZED";
	}

	$iconUrl = "";
	$platformWithIdExists = false;
	foreach($platformData->PlatformModels as $i => $platformModel){
		if($platformModel->Id == $platformId){
			$platformWithIdExists = true;
			$iconUrl = $platformModel->IconUrl;
			break;
		}
	}

	if(!$platformWithIdExists){
		return "UNKNOWN_PLATFORM";
	}

	$platformDbInterface->SoftDelete($platformId);

	$messageService->SendMessage(LogMessage::UserLogMessage(
		"PLATFORM_SOFT_DELETED", 
		"Platform $platformId soft deleted", 
		$loggedInUser->Id)
	);
	$userData->LogAdminAction($loggedInUser->Id);

	return "SUCCESS_PLATFORM_SOFT_DELETED";
}

function PerformAction(MessageService &$messageService, &$loggedInUser){
	global $_POST;
	
	if($loggedInUser !== false){
		$platformId = (isset($_POST[FORM_DELETEPLATFORM_PLATFORM_ID])) ? $_POST[FORM_DELETEPLATFORM_PLATFORM_ID] : "";

		return DeletePlatform($messageService, $platformId);
	}
}

?>
