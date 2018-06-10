<?php
session_start();

// Already Logged In
if(isset($_SESSION["username"]) && !empty($_SESSION["username"])){
	header("Location: /index.php");
	die();
}

// Define variables and initialize with empty values
$email = $username = $password = "";
$email_err = $username_err = $password_err = "";
require_once('sanitize.php');

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = 'Please enter your username.';
    } else{
        $username = fCleanString($_POST["username"], 50);
		if(preg_match('/[^a-z_\-0-9]/i', $username))
		{
			$username = "";
			$username_err = "Username can only contain alphanumeric characters, '_' and '-'";
		}
    }
	
	// Check if password is empty
    if(empty(trim($_POST['password']))){
        $password_err = 'Please enter your password.';
    } else{
        $password = fCleanString($_POST['password'], 50);
		if(strlen($password) < 5){
			$password_err = 'Password must be at least 5 characters long.';
			$password = "";
		}
    }
	
	// Check if email is empty
    if(empty(trim($_POST['email']))){
        $email_err = 'Please enter your email.';
    } else{
        $email = fCleanString($_POST['email'], 70);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$email = "";
			$email_err = "Invalid email format";
		}
    }

	// Create New User
	if(!empty($username) && !empty($password) && !empty($email)){
		$port = "http://".$_SERVER{'SERVER_NAME'}.":5984";
		
		$url = $port."/users/_design/user/_view/email";
		$json = file_get_contents($url."?key=%22$email%22");
		$data = json_decode($json,true);
		$array = $data['rows']['0']['key'];

		if(!empty($array) && $array==$email){
			$email = "";
			$email_err = "Email is already in use";
		}else{
			$accessLevel = 0;
			$url = $port."/users/_design/user/_view/username";
			$json = file_get_contents($url."?key=%22$username%22");
			$data = json_decode($json,true);
			$array = $data['rows']['0']['key'];
			
			if($data['total_rows']==0){
				$accessLevel = 1;
			}

			if(!empty($array) && $array==$username){
				$username = "";
				$username_err = "Username is already in use";
			}else{
				echo "Creating user<br>";
				$pass_hash  = md5($password);
				
				$new_user = array(
					'Email' => $email,
					'Password' => $pass_hash,
					'Username' => $username,
					'AccessLevel' => $accessLevel,
					'Friends' => '[]',
					'Groups' => '[]',
					'Boards' => '[]'
				);
				fCreateDoc($port, $new_user, "users");

				$_SESSION["username"] = $username;
				header("Location: /index.php");
				die();
			}
		}
	}
}
?>


<html lang="en" >
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mesh Network</title>
  <script src="jquery.min.js"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="header">
		<span class="channel-menu_name" style="float:left" id="menu-title">LEMON<span style="color:#ff1a1a;">AID</span></span>
    </div>

	<div style="margin:20px auto; width: 300px; max-width:100%;">
		<h2>Create Account</h2>
		<form action="register.php" method="POST">
			<div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
				<label>Username</label>
				<input type="text" name="username" class="form-control" maxlength="50" value="<?php echo $username; ?>">
				<span class="help-block"><?php echo $username_err; ?></span>
			</div>
			<div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
				<label>Password</label>
				<input type="password" name="password" class="form-control" maxlength="50">
				<span class="help-block"><?php echo $password_err; ?></span>
			</div>
			<div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
				<label>Email</label>
				<input type="email" name="email" class="form-control" maxlength="70" value="<?php echo $email; ?>">
				<span class="help-block"><?php echo $email_err; ?></span>
			</div>
			<div class="form-group">
				<input type="submit" class="btn btn-primary" value="Create User">
				<input type="reset" class="btn btn-primary" value="Reset">
			</div>
		</form>
		<p>Already have an account? <a href="/login.php" style="color:#0065b3;">Login here</a>.</p>
		
	</div>
	
</body>
</html>
