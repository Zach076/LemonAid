<?php
session_start();
if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
	header("Location: /login.php");
	die();
}
$username    = $_SESSION["username"];
$accessLevel = $_SESSION["accessLevel"];

$heading = "LEMON<span style=\"color:#ff1a1a;\">AID</span>";

$port = "http://".$_SERVER{'SERVER_NAME'}.":5984";
require_once('sanitize.php');

if($_SERVER["REQUEST_METHOD"] == "GET"){
	if(!empty(trim($_GET["board"])) && !empty(trim($_GET["title"])) && !empty(trim($_GET["load"]))){
		$board = fCleanString($_GET["board"], 50);
		$title = fCleanString($_GET["title"], 50);
		$loadPage = fCleanString($_GET["load"], 50);
		$docID = fCleanString($_GET["id"], 64);
		$heading = $board."&nbsp;<span class=\"channel-menu_prefix\">#$title</span>";
		
		$_SESSION["board"] = $board;
		$_SESSION["title"] = $title;
	}
}

?>
<!DOCTYPE html>
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
		<div id="menu-container" onclick="openNav()" style="float:left;">
			<div id="menu-bar"></div>
			<div id="menu-bar"></div>
			<div id="menu-bar"></div>
		</div>
		<span class="channel-menu_name" style="float:left"><?php echo $heading; ?></span>
		<?php
		
		$adminArray = fGetDoc($port.'/board/'.$docID)['admin'];
		$message = "<a href=\"index.php?board=Edit%20Board&title=$title&load=edit_board.php&id=$docID\"><span class=\"channel-menu_name\" style=\"float:right; background-color:green; padding:0px 10px;\">Edit</span></a>";
		$sendMessage = 0;
		if(isset($loadPage) && ($loadPage=='messages.php')){
			if(!empty($adminArray[0])){
				foreach($adminArray as $admin){
					if($username==$admin){
						$sendMessage = 1;
					}
				}
			}
			if($sendMessage==1 || $accessLevel==1){
				echo $message;
			}
		}
		?>
			
		
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div id="navbar" class="sidenav">
					<h2 class="title" style="margin-bottom:0px;">LEMON<span style="color:#ff1a1a;">AID</span></h2>
					<p style="margin-top:5px;">A Community Aid Network</p>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">x</a>
                    
                    <div id="links">
						<?php 
						
						$categories = array('News', 'Discussion', 'Resources', 'Groups');

						foreach($categories as $cat){
							$Items = fGetDoc($port."/board/_design/board/_view/board?key=%22$cat%22");
							$Items = $Items['rows'];
							
							echo "<a href=\"index.php?board=Create%20Board&title=$cat&load=create_board.php&cat=$cat\">$cat</a>";
							echo "<ul>\n";
							foreach($Items as $Item){
								$canView = false;
								$admins = $Item['value']['admins'];
								if(!empty($admins[0])){
									foreach($admins as $admin){
										if($username==$admin){
											$canView=true;
										}
									}
								}
								$users = $Item['value']['users'];
								if(!empty($users[0])){
									foreach($users as $user){
										if($username==$user){
											$canView=true;
										}
									}
								}else{$canView=true;}
								
								if($canView==true){
									$title=$Item['value']['title'];
									$id=$Item['id'];

									$class="";
									if($_SESSION["board"]==$cat && $_SESSION["title"]==$title){
										$class= "class=\" active\"";
									}
									
									echo "<li><a $class href=\"index.php?board=$cat&title=$title&load=messages.php&cat=messages&id=$id\">$title</a></li>\n";
								}
								else if($accessLevel==1){
									$title=$Item['value']['title'];
									$id=$Item['id'];
									echo "<li><a href=\"index.php?board=$cat&title=$title&load=messages.php&cat=messages&id=$id\" style=\"color: #663300; \">$title</a></li>\n";
								}
							}
							echo "</ul>\n";
						}
						?>
                    </div>
					<hr>
					
					<a href="/logout.php">Logout</a>

                <script>
                function openNav() {
                    document.getElementById("navbar").style.width = "250px";
                }
                function closeNav() {
                    document.getElementById("navbar").style.width = "0";
                }
                </script>
            </div>
			</div>
        </nav>

    </div>
	<?php
		if(isset($loadPage) && !empty($loadPage)){
			if($loadPage=='messages.php'){
				include('messages.php');
			}
			else if($loadPage=='create_board.php'){
				include('create_board.php');
			}
			else if($loadPage=='edit_board.php'){
				include('edit_board.php');
			}
			else{
				echo "loadPage: $loadPage<br>";
			}
		}
		else{
			include('empty.php');
		}
	?>
    
    <script type="text/javascript">
        var header = document.getElementById("links");
        var btns = header.getElementsByTagName("A");
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener("click", function() {
            var current = document.getElementsByClassName("active");
            current[0].className = current[0].className.replace(" active", "");
            this.className += " active";
            });
        }
    </script>
    
  </body>
</html>
