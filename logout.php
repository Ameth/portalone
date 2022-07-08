<?php 
if (isset($_SESSION)) {
  session_destroy();
}
$parametros_cookies = session_get_cookie_params(); 
setcookie(session_name(),0,1,$parametros_cookies["path"]);
setcookie ("JWT", "", time() - 3600);
if(isset($_GET['data'])&&$_GET['data']!=""){
?>
<!DOCTYPE html>
<html lang="es">
	<head>
	</head>
	<body onload="Enviar();">
		<form name="form" id="form" method="post" action="login.php">
			<input type="hidden" name="data" value="OK"> 
		</form>
		<script language="javascript">
		 function Enviar(){
			 //alert('Hola');
			document.getElementById('form').submit();
		}
		</script>  
	</body>
</html>
<?php
}else{
	isset($_GET['return_url']) ? header('Location:login.php?return_url='.$_GET['return_url']) : header('Location:login.php');
//	$isReturn = isset($_GET['return_url']) ? 'var isReturn=true;' : 'var isReturn=false;';
//	
//	echo "<script>
//	function send(){
//		".$isReturn."
//		var pag=window.location.pathname+window.location.search;
//		if(isReturn){
//			window.location.href='login.php'+window.location.search
//		}else{
//			window.location.href='login.php'
//		}
//	}
//	send();
//	</script>";
}

?>