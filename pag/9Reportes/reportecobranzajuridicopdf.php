<?php 
session_start(); 
require_once("../dompdf/dompdf_config.inc.php");
include '../conexion.php';
conectarse();
$fe=$_GET['fec'];
$desde=$_GET['des'];
$hasta=$_GET['has'];
error_reporting(0);

$codigo='<html>
  <head>

    <title>Saitel - Sistema Juridico</title>

    <link href="../../css/ayudas.css" rel="stylesheet">
    <link href="../dashboard.css" rel="stylesheet">

  </head>

  <body>';

  $codigo.='
  <DIV STYLE="position:absolute; top:0px; left:50px; width:50; height:70; visibility:visible z-index:1"> 
      <IMG SRC="../../img/logosfondo.png" border=0  width=200; height=50;> 
      </DIV> 
      <h3 align=right>'.$fe.'</h3>
      <br><br>
      Ing.';

//---------Nombre del Gerente-----------------
$consulta="select (select nombre || ' ' || apellido from tbl_empleado where id_rol=12 and estado=true order by id_empleado desc limit 1) as gerente;";
 $resultado=pg_query($consulta) or die (pg_last_error());

 if(pg_num_rows($resultado)!=0){
  while($fila=pg_fetch_array($resultado)){
  $codigo.='<br>
      '.$fila[0].'<br>
      GERENTE GENERAL DE SAITEL.<br>
      Presente.-<br><br><br>
      Saludos Cordiales:<br>';   
    }
  }

$consultaj="select distinct usuario as us, ve.id_sucursal,ve.nombre
from tbl_gestion_cobranzas gc, vta_empleado ve where ve.alias=gc.usuario 
and (gc.fecha_periodo between'".$desde."' and '".$hasta."')  and gestion_final not in ('') and usuario not in('','cliente') order by us desc;";
 $resultadoj=pg_query($consultaj) or die (pg_last_error());

 if(pg_num_rows($resultadoj)!=0){
 	while($filaj=pg_fetch_array($resultadoj)){
 		
 		$consultau="select get_mes(gc.fecha_periodo),
		date_part('year',gc.fecha_periodo),
		(select  count(*)
		from vta_instalacion i, tbl_prefactura p, tbl_gestion_cobranzas g
		where p.id_instalacion=i.id_instalacion
		and p.id_prefactura=g.id_gestion
		and i.id_sucursal=".$filaj[1]." and (p.periodo between'".$desde."' and '".$hasta."') and g.usuario not in('cliente')),
		(select count(*) from tbl_gestion_cobranzas where usuario='".$filaj[0]."' and fecha_periodo between'".$desde."' and '".$hasta."') usuario1,
		(select count(*) from tbl_gestion_cobranzas where usuario2='".$filaj[0]."' and fecha_periodo between'".$desde."' and '".$hasta."') usuario2,
		(select nombre||' '||apellido as usuario from vta_empleado where alias='".$filaj[0]."')
		from tbl_gestion_cobranzas gc
		where gc.fecha_periodo between'".$desde."' and '".$hasta."' limit 1;";
		 $resultadou=pg_query($consultau) or die (pg_last_error());
		 if(pg_num_rows($resultadou)!=0){
		 	while($filau=pg_fetch_array($resultadou)){ 
		 		$codigo.='<br>
			      El presente tiene como finalidad informarle sobre lo relacionado con las gestiones realizadas del mes de '.$filau[0].' del año '.$filau[1].'.<br><br>
			      Por parte de <strong>'.$filau[5].'</strong>. De '.$filau[2].' clientes que estan Adeudando el mes antes mencionado, su persona realizó el seguimiento a '.$filau[3].' clientes en la Gestion1 y a '.$filau[4].' en la Gestion2 de lo cual:<br><br>
			      En la Gestion 1:<br>';   
		 	}
		 }

		 //---------Gestion uno-----------------
		 $consultag1="select (select count(gestion1) from tbl_gestion_cobranzas where usuario='".$filaj[0]."' and fecha_periodo between '".$desde."' and '".$hasta."' and gestion1 not in('')) as llamados_g1, 
		 round(count(gc.gestion1)::numeric/(select count(gestion1) from tbl_gestion_cobranzas where usuario='".$filaj[0]."' and fecha_periodo between '".$desde."' and '".$hasta."' and gestion1 not in(''))::numeric,2)*100,
		 c.requerimiento, count(gc.gestion1)
		 from tbl_conclusiones c, tbl_gestion_cobranzas gc
		 where (gc.fecha_periodo between '".$desde."' and '".$hasta."') 
		 and gc.gestion1=c.requerimiento 
		 and gc.usuario='".$filaj[0]."'
		 group by c.requerimiento,gc.gestion1
		 order by c.requerimiento;";
		 $resultadog1=pg_query($consultag1) or die (pg_last_error());
		 if(pg_num_rows($resultadog1)!=0){
				while($filag1=pg_fetch_array($resultadog1)){
					$codigo.='*'.$filag1[1].'%: '.$filag1[2].'; ('.$filag1[3].')<br>';   
			    }
			}

		 //---------Gestion dos-----------------
		 $codigo.='<br>En la Gestion 2:<br>';

		 $consultag2="select (select count(gestion2) from tbl_gestion_cobranzas where usuario2='".$filaj[0]."' and fecha_periodo between '".$desde."' and '".$hasta."' and gestion2 not in('')) as llamados_g2, 
		 round(count(gc.gestion2)::numeric/(select count(gestion2) from tbl_gestion_cobranzas where usuario2='".$filaj[0]."' and fecha_periodo between '".$desde."' and '".$hasta."' and gestion2 not in(''))::numeric,2)*100,
		 c.requerimiento, count(gc.gestion2)
		 from tbl_conclusiones c, tbl_gestion_cobranzas gc
		 where (gc.fecha_periodo between '".$desde."' and '".$hasta."') 
		 and gc.gestion2=c.requerimiento 
		 and gc.usuario2='".$filaj[0]."'
		 group by c.requerimiento,gc.gestion2
		 order by c.requerimiento;";
		 $resultadog2=pg_query($consultag2) or die (pg_last_error());
		 if(pg_num_rows($resultadog2)!=0){
		 		while($filag2=pg_fetch_array($resultadog2)){
		 			$codigo.='*'.$filag2[1].'%: '.$filag2[2].'; ('.$filag2[3].')<br>';   
		 		}
		 	}
 	}
 }









 //---------Final del Documento-----------------
$codigo.='<br>Es cuanto puedo comunicar.<br><br>';
$codigo.='Atentamente.<br><br><br><br><br><br>';

$consultaf="select nombre || ' ' || apellido from tbl_empleado where id_rol=8 and estado=true order by id_empleado desc limit 1";
 $resultadof=pg_query($consultaf) or die (pg_last_error());

 if(pg_num_rows($resultadof)!=0){
  while($filaf=pg_fetch_array($resultadof)){
  $codigo.='Abg.'.$filaf[0].'<br>
  '.$filaf[1].'';   
    }
  }

    $codigo=utf8_decode($codigo);
    $dompdf= new DOMPDF();
    $dompdf->load_html($codigo);
    $dompdf->set_paper("A4","portrait");
    $dompdf->render();
    //$dompdf->stream("Suspencion.pdf");
    $dompdf->stream('ReporteCobranzasJuridico.pdf',array('Attachment'=>0));
pg_close();
 ?>
