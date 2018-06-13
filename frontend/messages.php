<div class="main">
  <div class="message-history">

	<?php
		session_start();
		if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
			header("Location: /login.php");
			die();
		}
		$username = $_SESSION["username"];

		$port = "http://".$_SERVER{'SERVER_NAME'}.":5984";
		$board = $_SESSION["board"];
		$title = $_SESSION["title"];
		$category = $_SESSION["category"];
		$docId = $_SESSION["docId"];

		// Processing form data when form is submitted
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			require_once('sanitize.php');
			
			// Check if message is empty
			if((!empty(trim($_POST["message"])) || !empty(trim($_POST["filetoupload"]))) && !empty(trim($_GET["id"])) && !empty(trim($_GET["cat"]))){
				$message = fCleanString($_POST["message"], 50);
				$filePath = $_FILES["filetoupload"]["tmp_name"];
				$fileName = fCleanString($_FILES["filetoupload"]["name"], 64);
				$category = fCleanString($_GET["cat"], 50);
				$docId = fCleanString($_GET["id"], 64);
					
				$new_message = array(
					'type' => $category,
					'to' => $docId,
					'from' => $username,
					'time' => time(),
					'data' => $message
				);
				$UUID = fCreateDoc($port, $new_message, "messages");
				fAddFile($port, $category, $UUID, $filePath, $fileName);
			}
			
			header("Location: /index.php?board=$board&title=$title&load=messages.php&cat=messages&id=$docId");
			die();
		}
		
		// Processing form data when board is loaded
		if($_SERVER["REQUEST_METHOD"] == "GET"){
			$category = fCleanString($_GET["cat"], 50);
			$docId = fCleanString($_GET["id"], 64);
			
			$_SESSION["category"] = $category;
			$_SESSION["docId"] = $docId;

			$url = $port."/messages/_design/messages/_view/".$category."?key=%22".$docId."%22&limit=100";
			$json = file_get_contents($url);
			$data = json_decode($json,true);

			$array = $data['rows'];
			$date = date("g:i A");

			if(!empty($array)){
				foreach($array as $value){
					$from = $value['value']['from'];
					$message = $value['value']['data'];
					$date = date("g:i A M jS, Y", $value['value']['time']);
					$attachment = $value['value']['attachments'];
					$messageId = $value['id'];
					
					$file = "";
					$fileName = "";
					$fileUrl = "";
					if(!empty($attachment)){
						$fileName = key($attachment);
						$file = "/attachments/$messageId/$fileName";
						$fileUrl = "<span class=\"message_content\"><a href=\"$file\" target=\"_blank\">$fileName</a></span>";
					}
					
					echo "
					<div class=\"message\">
						<a class=\"message_username\" href=\"\">$from</a>
						<span class=\"message_timestamp\">$date</span>
						<span class=\"message_star\"></span>
						<span class=\"message_content\">$message</span>
						$fileUrl
					</div>
					";
				}
			}
			else {
				echo "
					<div class=\"message\">
						<a class=\"message_username\" href=\"\">System</a>
						<span class=\"message_timestamp\">$date</span>
						<span class=\"message_star\"></span>
						<span class=\"message_content\">There are no messages at this time</span>
					</div>
					";
			}
		}
	 ?>

  </div>
</div>
<div class="footer">
  <div class="input-box">
	<form action="/messages.php?cat=<?php echo $category."&id=".$docId; ?>" method="POST" enctype="multipart/form-data">
		<input type="text" name="message" class="input-box_text" maxlength="600" placeholder="Type your message here..."/>
		<label class="btn btn-primary">
			Upload<input type="file" name="filetoupload" hidden>
		</label>
		<input type="submit" class="btn btn-success" value="Send" style="margin-left:5px;">
	</form>
  </div>
</div>
