<?php
include('config.php');
?>
<!DOCTYPE html>
<html>

<head>

  <meta charset="UTF-8">

  <title>CSA - Staff</title>
  <link rel="stylesheet" type="text/css" href="css/style.css">

    <script src="js/prefixfree.min.js"></script>

</head>

<body>


<div id="alert"><b>Meldung:</b> An alle BFtoolnutzer ohne Gruppenmitgliedschaft! Bitte stellt eine Gruppenanfrage, zur Hilfe gibts hier ein kleines Video. <a href="#" class="link" title="2,8 MB groß">Download</a><div id="x"><a href="#" class="x">&nbsp;Registrieren&nbsp;</a></div></div>
  <div class="body"></div>
		<div class="grad"></div>
		<div class="header">
			<div><img id="cc-m-imagesubtitle-image-11331278025" src="http://u.jimdo.com/www71/o/s2db6a28250e267bc/img/ic8753465714960b3/1424941870/std/image.png" alt="" title="" class="" data-src-width="750" data-src-height="749"></div>
		</div>
		<br>
		<div class="login">
		<form action="sign_up.php" method="post">
            <input type="text" name="username" placeholder="Benutzername" /><br />
            <input type="password" name="password" placeholder="Passwort" /><br />
            <input type="password" name="passverif" placeholder="Passwort" /><br /><br />
            <input type="text" name="email" placeholder="Email Adresse" /><br /><br />
            <input type="text" name="avatar" placeholder="Avatar (nicht n&ouml;tig)" /><br />
            <input type="submit" value="Sign up" />
		</div>
    </form>
		</div>

  <script src='http://codepen.io/assets/libs/fullpage/jquery.js'></script>

</body>

</html>