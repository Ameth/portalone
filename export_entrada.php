<?php 
if(!isset($_GET['ID'])||$_GET['ID']==""){exit();}
// include autoloader
require_once 'dompdf-master/autoload.inc.php';
require_once("includes/conexion.php");

$ConEmp="EXEC usp_rep_Empresa";
$SQLEmp=sqlsrv_query($conexion,$ConEmp);
$rowEmp=sqlsrv_fetch_array($SQLEmp);

$ConEnt="EXEC usp_rep_EntradasFromOrdenesCompras '".base64_decode($_GET['ID'])."', '".$_GET['type']."'";
$SQLEnt=sqlsrv_query($conexion,$ConEnt);
//$rowEnt=sqlsrv_fetch_array($SQLSolPed);

$Datos="";
$i=1;
$SubTotal=0;
$SubTotalIVA=0;
$Total=0;
while($rowEnt=sqlsrv_fetch_array($SQLEnt)){
	$Datos.="<tr class='TextoCuerpo'>
      <td align='center'>".$i."</td>
      <td>".$rowEnt['ItemCode']."</td>
      <td>".LSiqmlName($rowEnt['ItemName'])."</td>
      <td align='right'>".number_format($rowEnt['Quantity'],2)."</td>
      <td align='center'>".utf8_encode($rowEnt['UnitMsr'])."</td>
      <td>".utf8_encode($rowEnt['FreeTxt'])."</td>
	  <td align='right'>".number_format($rowEnt['Price'],2)."</td>
	  <td align='right'>".number_format($rowEnt['DiscPrcnt'],2)."</td>
	  <td align='right'>".number_format($rowEnt['LineTotal'],2)."</td>
    </tr>";
	$i++;
	$SubTotal=$SubTotal+$rowEnt['LineTotal'];
	$SubTotalIVA=$SubTotalIVA+$rowEnt['LineVat'];
}
$Total=$SubTotal+$SubTotalIVA;
$SQLEnt=sqlsrv_query($conexion,$ConEnt);
$rowEnt=sqlsrv_fetch_array($SQLEnt);
// reference the Dompdf namespace
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$HTML1='<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
	#header{
		 position: fixed; 
		 left: 0px; 
		 top: 0px; 
		 right: 0px; 
		 height: 140px; 
		 padding-bottom: 15px;
	}
	#footer{
		position: fixed; 
		left: 0px; 
		bottom: 0px; 
		right: 0px; 
		height: 80px;
	}
	#page{
		margin: 190px 0px 120px 0px;
	}
	.Cabecera{
		font: normal;
		font-family: sans-serif;
		font-size: 11px;
	}
	.TextoCabecera{
		font: normal;
		font-family: sans-serif;
		font-size: 10px;
	}
	.TextoCuerpo{
		font: normal;
		font-family: sans-serif;
		font-size: 9px;
	}
	.TextoCodigo{
		font-family: Courier;
		color: red;
		font-size: 13px;
		font-weight: bold;
	}
	.Titulo{
		font: normal;
		font-family: sans-serif;
		font-size: 16px;
	}
	.TablaIDInvenObras{
		border: 1px solid;
		border-color: black;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
	}
	.TablaIDInvenObras tbody tr td{
		border: 0px;
		border-color: #ffffff;
		padding: 0.3em;
	} 
	.TablaCabecera{
		border: 1px solid;
		border-color: black;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
	}
	.TablaCabecera tbody tr td{
		border: 0px;
		border-color: #ffffff;
		padding: 0.3em;
	}
	.TablaCuerpo{
		border: 1px solid;
		border-color: black;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
	}
	.TablaCuerpo tbody tr th{
		border: 1px solid;
		border-left-color: black;
		border-right-color: black;
		border-bottom-color: black;
		border-top-color: black;
	}
	.TablaCuerpo tbody tr td{
		border: 1px solid;
		border-left-color: black;
		border-right-color: black;
		border-bottom-color: black;
		border-top-color: black;
	}
	.TablaComentario{
		border: 1px solid;
		border-color: black;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		height: 80px;
	}
	.TablaComentario tbody tr td{
		border: 0px;
		border-color: #ffffff;
		padding: 0.3em;
	}
	.TablaPie{
		border: 1px solid;
		border-color: black;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
	}
	.TablaPie tbody tr th{
		border: 0px;
		border-color: #ffffff;
	}
	.TablaPie tbody tr td{
		border: 0px 0px 0px 0px;
	}
	.TablaFinal{
		border: 0px;
	}
	.TablaFinal tbody tr td{
		border: 0px;
		border-color: #ffffff;
	}
	.Tabla3 {
		padding: 1px;
		margin: 1px;
		border: 1px solid rgba(189, 183, 181, 0.5);
		border-collapse: collapse;
	}
	.Tabla3 tr th{
		background-color: #f2f0ed;	
		border: 1px solid rgba(189, 183, 181, 0.5);
		vertical-align: bottom;
		font-family: sans-serif;
		font-size: 9px;
		padding: 0.5rem 0.25rem;
	}
	.Tabla3 tr td{
		border: 1px solid rgba(189, 183, 181, 0.5);
		padding: 0.25rem;
		font: normal;
		font-family: sans-serif;
		font-size: 9px;
		vertical-align: middle;
	}
</style>
<title>['.$rowEnt['DocNum'].'] Reporte - Entrada</title>
</head>

<body>
<div id="header">
<table width="100%" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td rowspan="2" width="18%"><img src="../dist/css/logo_ac.png" width="116" height="109" alt=""/>
			</td>
			<td rowspan="2" class="Cabecera" width="25%" valign="middle">'.$rowEmp['CompnyName'].'<br>NIT: '.$rowEmp['TaxIdNum'].'<br>'.$rowEmp['CompnyAddr'].'<br>Tel: '.$rowEmp['Phone1'].'<br>www.aconstruir.co</td>
			<td rowspan="2" width="20%">&nbsp;</td>
			<td width="34%" class="Titulo" valign="middle"><strong>ENTRADA POR COMPRAS</strong>
			</td>
		</tr>
		<tr class="Cabecera">
			<td>
				<table width="100%" border="1" class="TablaIDInvenObras">
					<tbody>
						<tr>
							<td><strong>Id InvenObras:</strong>
							</td>
							<td class="TextoCodigo">'.$rowEnt['IdInvenObras'].'</td>
						</tr>
						<tr>
							<td><strong>No. SAP B1:</strong>
							</td>
							<td class="TextoCodigo">'.$rowEnt['DocNum'].'</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
	<br>	
<table width="100%" border="1" class="TablaCabecera">
  <tbody>
    <tr class="TextoCabecera">
      <td width="15%" style="font-weight: bold;">Proveedor:</td>
      <td width="30%">'.utf8_encode($rowEnt['CardName']).'</td>
      <td width="20%" style="font-weight: bold;">Fecha de entrega:</td>
      <td width="35%">'.$rowEnt['DocDate']->format('Y-m-d').'</td>
    </tr>
    <tr class="TextoCabecera">
      <td width="15%" style="font-weight: bold;">Código:</td>
      <td width="30%">'.$rowEnt['CardCode'].'</td>
      <td width="20%" style="font-weight: bold;">Fecha de vencimiento:</td>
      <td width="35%">'.$rowEnt['DocDueDate']->format('Y-m-d').'</td>
    </tr>
    <tr class="TextoCabecera">
      <td width="15%" style="font-weight: bold;">Núm. Remisión:</td>
      <td width="30%">'.$rowEnt['NumAtCard'].'</td>
      <td width="20%" style="font-weight: bold;">Proyecto:</td>
      <td width="35%">'.utf8_encode($rowEnt['NomProyecto']).'</td>
    </tr>
	<tr class="TextoCabecera">
      <td width="15%" style="font-weight: bold;">Almacén:</td>
      <td width="30%">'.utf8_encode($rowEnt['NomAlmacen']).'</td>
      <td width="20%" style="font-weight: bold;">Orden de Compra:</td>
      <td width="35%">'.$rowEnt['BaseEntry'].'</td>
    </tr>
  </tbody>
</table>
	</div>
	<br>	
<div id="page">
<br>
<table width="100%" border="1" cellspacing="0" class="Tabla3">
        <tbody>
          <tr class="TextoCuerpo" style="font-weight: bold;">
            <th align="center">#</th>
            <th align="center">CÓDIGO</th>
            <th align="center">DESCRIPCIÓN</th>
            <th align="center">CANT</th>
            <th align="center">UND</th>
            <th align="center">TEXTO LIBRE</th>
            <th align="center">PRECIO UNIT.</th>
			<th align="center">% DESC</th>
            <th align="center">TOTAL</th>
          </tr>';
$HTML2='</tbody>
      </table>
 <br> 
 <table width="100%" border="1" class="TablaComentario">
  <tbody>
	<tr class="TextoCuerpo">
	  <td valign="top" width="80%"><strong>Comentarios</strong></td>
	  <td valign="top" width="10%">SUBTOTAL:</td>
	  <td valign="top" width="10%" align="right">'.number_format($SubTotal,2).'</td>
	</tr>
	<tr class="TextoCuerpo">
	  <td valign="top" width="80%" rowspan="2">'.$rowEnt['Comments'].'</td>
	  <td valign="top" width="10%">IVA:</td>
	  <td valign="top" width="10%" align="right">'.number_format($SubTotalIVA,2).'</td>
	</tr>
	<tr class="TextoCuerpo" style="font-weight: bold;">
	  <td valign="top" width="10%">TOTAL:</td>
	  <td valign="top" width="10%" align="right" style="border-top: 1px !important; border-top-color: black;">'.number_format($Total,2).'</td>
	</tr>
  </tbody>
</table>
<br>
</div>
<div id="footer">

<table width="100%" border="1" class="TablaPie">
  <tbody>
	<tr>
	  <th>&nbsp;</th>
	  <th>&nbsp;</th>
	  <th>&nbsp;</th>
	  <th>&nbsp;</th>
	</tr>
	<tr align="center" class="TextoCuerpo">
	  <td width="15%">Recibido por:</td>
	  <td width="25%" align="left" style="border-bottom-color: black; border-bottom: 1px;">&nbsp;</td>
	  <td width="15%">Fecha y hora:</td>
	  <td width="25%" align="left" style="border-bottom-color: black; border-bottom: 1px;">&nbsp;</td>
	</tr>
  </tbody>
</table>
<br>
<table width="100%" border="1" class="TablaFinal">
        <tbody>
          <tr class="TextoCuerpo">
            <td width="33%">Realizado por: '.utf8_encode($rowEnt['NomUser']).'</td>
			<td width="33%" align="center">Impreso por: Portal de proveedores</td>
            <td align="right" width="33%">Página 1 de 1</td>
          </tr>
        </tbody>
      </table>
      
</div>
</body>
</html>';
//InsertarLog("Descarga de entrada");
sqlsrv_close($conexion);
//echo $HTML1.$Datos.$HTML2;/*
// instantiate and use the dompdf class
$dompdf->loadHtml($HTML1.$Datos.$HTML2);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('letter', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("Entrada_".$rowEnt['IdInvenObras'].".pdf",array("Attachment" => false));
exit(0);
?>