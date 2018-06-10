<div class="main">
  <div class="message-history">

	<?php
		session_start();
		if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
			header("Location: /login.php");
			die();
		}
		require_once('sanitize.php');
		$username = $_SESSION["username"];
		$accessLevel = $_SESSION["accessLevel"];
		$title    = $_SESSION["title"];
		$docID    = $_SESSION["docID"];
		
		$isAdmin = $arr_Admins = $arr_Users = "";
		$port = "http://".$_SERVER{'SERVER_NAME'}.":5984";

		if($_SERVER["REQUEST_METHOD"] == "GET"){
			if(!empty(trim($_GET["id"]))){
				$docID = fCleanString($_GET["id"], 64);
				$_SESSION["docID"] = $docID;
				
				$url = $port."/board/".$docID;		
				$json = file_get_contents($url);
				$data = json_decode($json,true);
						
				$arr_Admins = $data['admin'];
				$arr_Users = $data['users'];
								
				$url = $port.'/users/_design/user/_view/username';
				$json = file_get_contents($url);
				$data = json_decode($json,true);
				
				$arr_AllU = $data['rows'];
				
				$arrayUsers = array();
				foreach($arr_AllU as $items){
					$arrayUsers[] = $items['key'];
				}
			}
		}
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			if(!empty(trim($_POST["delete"]))){

				$docID = fCleanString($_POST["delete"], 64);
				$_SESSION["docID"] = $docID;

				$port = "http://".$_SERVER{'SERVER_NAME'}.":5984";
				$url = $port."/board/".$docID;
				
				$json = file_get_contents($url);
				$data = json_decode($json,true);
				
				$arr_Admins = $data['admin'];
				$isAdmin=0;

				foreach($arr_Admins as $admin){
					if($username==$admin){
						$isAdmin=1;
					}
				}

				if($accessLevel==1 || $isAdmin==1){
					// Delete the board's messages
					$url = $port."/messages/_design/messages/_view/messages?key=%22".$docID."%22";
					$json = file_get_contents($url);
					$data = json_decode($json,true);
					$data = $data['rows'];
					$arrayDocs = array();
					foreach($data as $item){
						fDeleteDoc($port, 'messages', $item['id']);
					}
					
					// Delete the board
					fDeleteDoc($port, 'board', $docID);
					
					header("Location: /index.php");
					die();
				}
			}
			
			if(!empty($_POST['add_users']) || !empty($_POST['add_admins'])) {
				$add_users = $_POST['add_users'];
				$add_admins = $_POST['add_admins'];
				fAddUsers($port, 'board', $docID, $add_users, $add_admins);
				
				header("Location: /index.php?board=Edit%20Board&title=$title&load=edit_board.php&id=$docID");
				die();
			}
			
			if(!empty($_POST['remove_users']) || !empty($_POST['remove_admins'])) {
				$remove_users = $_POST['remove_users'];
				$remove_admins = $_POST['remove_admins'];
				fRemoveUsers($port, 'board', $docID, $remove_users, $remove_admins);
				
				header("Location: /index.php?board=Edit%20Board&title=$title&load=edit_board.php&id=$docID");
				die();
			}
		}
	?>
	 
	 	<div style="margin:0px auto; width: 300px; max-width:100%;">
	 	<?php if(!empty($arr_Users[0]) || !empty($arr_Admins[0])){
	 	?>
		<h2>Remove Users</h2>
		<form action="edit_board.php?id=<?php echo $docID; ?>" method="POST">
			<?php
				if(!empty($arr_Admins[0])){
					echo "
					<div class=\"form-group\">
						<label>Board Admins</label><br>";
						foreach($arr_Admins as $admin){
							echo "<input type=\"checkbox\" name=\"remove_admins[]\" value=\"$admin;\"><label>$admin</label><br>";
						}
					echo "
						<br>
					</div>";
				}
				if(!empty($arr_Users[0])){
					echo "
					<div class=\"form-group\">
						<label>Board Users</label><br>";
						foreach($arr_Users as $user){
							echo "<input type=\"checkbox\" name=\"remove_users[]\" value=\"$user;\"><label>$user</label><br>";
						}
					echo "<br>Warning: removing all users will make this board public
						<br>
					</div>";
				}
				else{
					$warningMessage = "<br>Warning: adding users will make this board private";
				}
				?>

			<div class="form-group">
				<input type="submit" class="btn btn-danger" value="Remove Existing Users">
			</div>
		</form>
		<br><br>
		<?php } ?>
		<h2>Add Users</h2>
		<form action="edit_board.php?id=<?php echo $docID; ?>" method="POST">
			<?php
				$arrayDiff = array_diff($arrayUsers, $arr_Admins);
				if(!empty($arrayDiff)){
					echo "
					<div class=\"form-group\">
						<label>Board Admins</label><br>";
						foreach($arrayDiff as $admin){
							echo "
								<input type=\"checkbox\" name=\"add_admins[]\" value=\"$admin;\"><label>$admin</label><br>";
						}
					echo "
						<br>
					</div>";
				}
				
				$arrayDiff = array_diff($arrayUsers, $arr_Users);
				if(!empty($arrayDiff)){
					echo "
					<div class=\"form-group\">
						<label>Board Users</label><br>";
						foreach($arrayDiff as $user){
							echo "
								<input type=\"checkbox\" name=\"add_users[]\" value=\"$user;\"><label>$user</label><br>";
						}
					echo "$warningMessage
						<br>
					</div>";
				}
				?>

			<div class="form-group">
				<input type="submit" class="btn btn-success" value="Add New Users">
			</div>
		</form>
		<br><br>
		<h2>Delete Board</h2>
		<form action="edit_board.php" method="POST">
			<div class="form-group">
				<input type="hidden" name="delete" value="<?php echo $docID; ?>">
				<input type="submit" class="btn btn-danger" value="Delete Board">
			</div>
		</form>
		
		
	</div>

  </div>
</div>
