<?php
function fCleanString($UserInput, $MaxLen) {
   //remove html tags
   $UserInput = strip_tags($UserInput);

   //Escape special characters - very important.
   $UserInput = htmlspecialchars($UserInput);
   
   //remove everything that is not an ascii character between 32 and 126
   $UserInput = preg_replace('/[^\x20-\x7E]/', '', $UserInput);

   //truncate to max length of database field
   return substr($UserInput, 0, $MaxLen);
}

function fCleanTextArea($UserInput, $MaxLen) {
   //remove html tags
   $UserInput = strip_tags($UserInput);
   

   //remove everything that is not an ascii character between 32 and 126, or a line break
   $UserInput = preg_replace('/[^\
   \x20-\x7E]/', '', $UserInput);
   
   $UserInput = nl2br($UserInput);

   //Escape special characters - very important.
   $UserInput = htmlspecialchars($UserInput);
   
   $UserInput = str_ireplace(array("\r","\n",'\r','\n'),'', $UserInput);

   //truncate to max length of database field
   return substr($UserInput, 0, $MaxLen);
}

function fCleanNumber($UserInput) {
   $pattern = "/[^0-9\.]/"; //replace everything except 0-9 and period
   $UserInput = preg_replace($pattern, "", $UserInput);
   return substr($UserInput, 0, 6);
}

function fGetDoc($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
	curl_setopt($ch, CURLOPT_USERPWD, 'DB_Username:DB_Password');
	$response = curl_exec($ch);
	$_response = json_decode($response, true);
	curl_close($ch);
	return  $_response;
}

function fCreateDoc($port, $array, $database) {
	$ch = curl_init();
	$payload = json_encode($array);
	
	$UUIDs = fGetDoc($port.'/_uuids');
	$UUID = $UUIDs['uuids'][0];
	 
	curl_setopt($ch, CURLOPT_URL, $port.'/'.$database.'/'.$UUID);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
	 
	curl_setopt($ch, CURLOPT_USERPWD, 'DB_Username:DB_Password'); 
	curl_exec($ch);
	curl_close($ch);
	
	return $UUID;
}

function fDeleteDoc($port, $database, $docId) {
	$json = file_get_contents($port.'/'.$database.'/'.$docId);
	$data = json_decode($json,true);
	$revision = $data['_rev'];
	
	$ch = curl_init();	 
	curl_setopt($ch, CURLOPT_URL, sprintf($port.'/%s/%s?rev=%s', $database, $docId, $revision));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
	 
	curl_setopt($ch, CURLOPT_USERPWD, 'DB_Username:DB_Password');
	$response = curl_exec($ch); 
	curl_close($ch);
	
	return $response;
}

function fAddFile($port, $database, $docId, $filePath, $fileName) {
	$json = file_get_contents($port.'/'.$database.'/'.$docId);
	$data = json_decode($json,true);
	$revision = $data['_rev'];
	
	$ch = curl_init();
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$contentType = finfo_file($finfo, $filePath);

	$payload = file_get_contents($filePath);

	curl_setopt($ch, CURLOPT_URL, sprintf($port.'/%s/%s/%s?rev=%s', $database, $docId, $fileName, $revision));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: '.$contentType,
		'Accept: */*'
	));

	curl_setopt($ch, CURLOPT_USERPWD, 'DB_Username:DB_Password');
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}

function fAddUsers($port, $database, $docId, $new_users, $new_admins) {
	$url = $port.'/'.$database.'/'.$docId;
	echo "url: $url<br>";
	$json = file_get_contents($url);
	$data = json_decode($json,true);
	
	$revision = $data['_rev'];
	$category = $data['category'];
	$title    = $data['title'];
	$admins   = $data['admin'];
	$users    = $data['users'];
	
	foreach($new_users as $new_user){
		$new_user = fCleanString(substr($new_user,0,-1), 50);
		if(!in_array($new_user, $users)){
			$users[] = $new_user;
		}
	}
	foreach($new_admins as $new_admin){
		$new_admin = fCleanString(substr($new_admin,0,-1), 50);
		if(!in_array($new_admin, $admins)){
			$admins[] = $new_admin;
		}
	}

	$updated_data = array(
		'_rev'     => $revision,
		'category' => $category,
		'title'   => $title,
		'admin' => $admins,
		'users' => $users
	);
	
	echo"<br>";
	print_r($updated_data);
	
	$ch = curl_init();
	$payload = json_encode($updated_data);
	
	curl_setopt($ch, CURLOPT_URL, $port.'/'.$database.'/'.$docId);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
	 
	curl_setopt($ch, CURLOPT_USERPWD, 'DB_Username:DB_Password');
	$response = curl_exec($ch);
	curl_close($ch);
}

function fRemoveUsers($port, $database, $docId, $removed_users, $removed_admins) {
	$url = $port.'/'.$database.'/'.$docId;
	echo "url: $url<br>";
	$json = file_get_contents($url);
	$data = json_decode($json,true);
	
	$revision = $data['_rev'];
	$category = $data['category'];
	$title    = $data['title'];
	$admins   = $data['admin'];
	$users    = $data['users'];
	
	
	$delete_users = array();
	foreach($removed_users as $removed_user){
		$removed_user = fCleanString(substr($removed_user,0,-1), 50);
		$delete_users[] = $removed_user;
	}
	
	$delete_admins = array();
	foreach($removed_admins as $removed_admin){
		$removed_admin = fCleanString(substr($removed_admin,0,-1), 50);
		$delete_admins[] = $removed_admin;
	}
	
	$users = array_diff($users, $delete_users);
	$admins = array_diff($admins, $delete_admins);

	$updated_data = array(
		'_rev'     => $revision,
		'category' => $category,
		'title'   => $title,
		'admin' => $admins,
		'users' => $users
	);
	
	$ch = curl_init();
	$payload = json_encode($updated_data);
	
	curl_setopt($ch, CURLOPT_URL, $port.'/'.$database.'/'.$docId);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
	 
	curl_setopt($ch, CURLOPT_USERPWD, 'DB_Username:DB_Password');
	$response = curl_exec($ch);
	curl_close($ch);
}

?>
