<?php
include('config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="css/style.css">
        <link href="<?php echo $design; ?>/style.css" rel="stylesheet" title="Style" />
        <title>CSA - Login</title>
    </head>
    <body>
	<div id="alert"><b>Meldung:</b> An alle BFtoolnutzer ohne Gruppenmitgliedschaft! Bitte stellt eine Gruppenanfrage, zur Hilfe gibts hier ein kleines Video. <a href="#" class="link" title="2,8 MB groß">Download</a><div id="x"><a href="#" class="x">&nbsp;Registrieren&nbsp;</a></div></div>
<?php
//If the user is logged, we log him out
if(isset($_SESSION['username']))
{
	//We log him out by deleting the username and userid sessions
	unset($_SESSION['username'], $_SESSION['userid']);
?>
<div class="message"><h1>Error! Anmeldung fehlgeschlagen!</h1><br /><br>
<a href="index.php">erneut versuchen...</a></div>
<?php
}
else
{
	$ousername = '';
	//We check if the form has been sent
	if(isset($_POST['username'], $_POST['password']))
	{
		//We remove slashes depending on the configuration
		if(get_magic_quotes_gpc())
		{
			$ousername = stripslashes($_POST['username']);
			$username = mysql_real_escape_string(stripslashes($_POST['username']));
			$password = stripslashes($_POST['password']);
		}
		else
		{
			$username = mysql_real_escape_string($_POST['username']);
			$password = $_POST['password'];
		}
		//We get the password of the user
		$req = mysql_query('select password,id from users where username="'.$username.'"');
		$dn = mysql_fetch_array($req);
		//We compare the submited password and the real one, and we check if the user exists
		if($dn['password']==$password and mysql_num_rows($req)>0)
		{
			//If the password is good, we dont show the form
			$form = false;
			//We save the user name in the session username and the user Id in the session userid
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['userid'] = $dn['id'];
?>
<div class="foot"><a href="start.php">zur Startseite</a></div>
<div class="message"><h1>Herzlich Willkommen!</h1><br />
<p>Sie haben sich erfolgreich eingeloggt wollen sie zu Startseite fortfahren? <a href="start.php">JA</a></p>
<?php
		}
		else
		{
			//Otherwise, we say the password is incorrect.
			$form = true;
			$message = 'The username or password is incorrect.';
		}
	}
	else
	{
		$form = true;
	}
	if($form)
	{
		//We display a message if necessary
	if(isset($message))
	{
		echo '<div class="message">'.$message.'</div>';
	}
	//We display the form
?>
    <div class="message"><h1>Error! Anmeldung fehlgeschlagen!</h1><br /><br>
    <a href="index.php">erneut versuchen...</a></div>
<?php
	}
}
?>
		</body>
</html>