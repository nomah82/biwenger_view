<?php
require_once('constantes.php');
require_once('lib.php');
$ligas=getMisLigas();
$primeraLiga=null;
foreach($ligas as $k=>$v)
{
	if ($primeraLiga==null) $primeraLiga=$k;
	print "<a href='?l=$k'>$v</a><br>";
}


if (isset($_GET["l"]))
	$liga=$_GET["l"];
else $liga=$primeraLiga;

$page=consultarMercado($liga);
$obj=json_decode($page);

function posColor()
{
	$posColor=array("#DEF0E5","#BFE4CB","#A1D7B0","#82CB96", "#63BE7B");
	return $posColor;
}

function negColor()
{
	$negColor=array("#FBDEE1", "#FAC1C3","#F9A3A6","#F88688", "#F8696B");
	return $negColor;
}




function getColor($min, $max, $v, $inverso=0)
{
	$posColor=array(1,2,3,4,5);
	$negColor=array(-1,-2,-3,-4,-5);
	
	if ($v>0)
		$colores=$posColor;
	else {
		$colores=$negColor;
		$v=$v * -1;
		
	}
	
	if ($inverso)
		$colores=array_reverse($colores);
	
	if ($v<$min)
		return 0;
	else if ($v>$max)
		return $colores[count($colores)-1];
	else {
		$max=$max-$min;
		$v=$v-$min;
		
		$index=(int)round((($v * (count($colores)-1))/$max));
		return $colores[$index];
	}
}


function pintaFilas($amigable, $name, $usuario="")
{
		$posColor=posColor();
		$negColor=negColor();
		obtenerDatosJugador($amigable, $name, $jugador);
		$datos=array();
		$colores=array();
		$pesos=array();
		$datos[]="<a href='info_jugador.php?name=$name&amigable=$amigable'>$name</a> $usuario";$colores[]=0;$pesos[]=0;
		$datos[]=$jugador->Precio;$colores[]=0;$pesos[]=0;
		$datos[]=$jugador->IncrementoUltimoDia;$colores[]=getColor(50000, 200000, $jugador->IncrementoUltimoDia);$pesos[]=0.1;
		
		$tendencia=$jugador->BiTendenciaUlt5Dias($r2);
		$datos[]=$tendencia;$colores[]=getColor(50000, 200000, $tendencia);$pesos[]=0.1;
		$datos[]=$r2;$colores[]=getColor(0.4, 1, $r2);$pesos[]=0.1;
		
		$tendencia=$jugador->CoTendenciaUlt5Dias($r2);
		$datos[]=$tendencia;$colores[]=getColor(50000, 200000, $tendencia);$pesos[]=0.1;
		$datos[]=$r2;$colores[]=getColor(0.4, 1, $r2);$pesos[]=0.1;
		$datos[]=$jugador->PuntosTemporadaAnterior;$colores[]=getColor(80, 200, $jugador->PuntosTemporadaAnterior);$pesos[]=0.1;
		$ratioPrecio=round($jugador->RadioDiffPrecio(),2);
		$datos[]=$ratioPrecio;$colores[]=getColor(0.2, 1, $ratioPrecio);$pesos[]=0.1;

		$ratioMax=round($jugador->RatioPrecioMax(),2);
		$datos[]=$ratioMax;$colores[]=getColor(0.1, 0.8, $ratioMax,1);$pesos[]=0.1;
		
		$graf=$jugador->GraficaDibujoUltimo();
		$datos[]=$graf;$colores[]=colorUltGrafica($graf);$pesos[]=0.1;

		$puntuacion=0;
		$i=0;
		foreach($colores as $c)
		{
			$puntuacion+=$c * $pesos[$i];
			$i++;
		}
		$datos[]=round($puntuacion,3);
		$colores[]=0;
		$pesos[]=0;
		
		print "<tr>";
		$i=0;
		foreach($datos as $d)
		{
			$alineacion="";
			if ($i>0) $alineacion="text-align:right;";
			
			if ($colores[$i]==0) $color="white";
			else if ($colores[$i]>0) $color=$posColor[$colores[$i]-1];
			else if ($colores[$i]<0) $color=$negColor[abs($colores[$i])-1];
			
			
			print "<td style='$alineacion background-color:".$color."'>$d</td>";
			$i++;
		}
		print "</tr>";
}

$cabecera=array("Jugador", "Precio", "Ult.Inc.", "Tend.Bi", "R2.Bi", "Tend.Co", "R2.Co", "P.2016", "BivsCo", "PMax", "Graf.", "Puntuación");
print "<table border='1'>";
print "<tr>";
foreach ($cabecera as $scab)
    print "<td>$scab</td>";


print "</tr>";
foreach($obj->data as $jugadorJson)
{
    if ($jugadorJson->type=="sale")
    {
        $user="";
        if (!empty($jugadorJson->user))
		{
            $user="(".$jugadorJson->user->name.")";
        }
        $name=$jugadorJson->player->name;
        $amigable=$jugadorJson->player->slug;
        $id=$jugadorJson->player->id;
		pintaFilas($amigable, $name, $user);

    }
}
pintaFilas("serantes", "Serantes");
print "</table>";

print "<h1>Mis Jugadores</h1>";


$page=consultarMisJugadores($liga);


$obj=json_decode($page);

print "<table border='1'>";
foreach($obj->data->players as $jugadorJson)
{
	$name=$jugadorJson->name;
	$amigable=$jugadorJson->slug;
	$id=$jugadorJson->id;
	pintaFilas($amigable, $name);
}
print "</table>";


function colorUltGrafica($s)
{
	if (empty($s)) return 0;
	$codigos=array();
	$codigos["\\\\"]=0;
	$codigos["\\_"]=3;
	$codigos["\\/"]=6;
	$codigos["_\\"]=2;
	$codigos["__"]=5;
	$codigos["_/"]=7;
	$codigos["/\\"]=1;
	$codigos["/_"]=4;
	$codigos["//"]=8;
	
	return getColor(0, 5, $codigos[$s]-4);
}

?>
