<html>
<head>
<script src="Chart.min.js"></script>
    <script src="Chart.bundle.js"></script>
    <script src="utils.js"></script>
</head>
<body>
<?php
require_once("lib.php");
$gname=null;
$gamigable=null;

if (isset($_GET["name"]))
	$gname=$_GET["name"];
if (isset($_GET["amigable"]))
	$gamigable=$_GET["amigable"];

obtenerDatosJugador($gamigable, $gname, $jugador);


if ($jugador->IncrementoUltimoDia > 0) $inc="<span style='color:green'>".$jugador->IncrementoUltimoDia."</span>";
else if ($jugador->IncrementoUltimoDia < 0) $inc="<span style='color:red'>".$jugador->IncrementoUltimoDia."</span>";
else $inc="";

print "<h2>$gname ".$jugador->Precio." $inc</h2>";
print $jugador->UrlPromunio . "<br>";
print "Temporada pasada: ".$jugador->PuntosTemporadaAnterior."<br>";
print "Estado: ".$jugador->EstadoActual."<br>";

print "Tendencia B: ".$jugador->BiTendenciaUlt5Dias($rB)." ($rB)<br>";
print "Tendencia C: ".$jugador->CoTendenciaUlt5Dias($rC)." ($rC)<br>";

grafica1d($gamigable."1","Precio ".$gname, $jugador->xBi, $jugador->yBi, "Biwenger", $jugador->yCoCortado, "Comunio");
grafica1d($gamigable."2","Precio ".$gname, $jugador->xCo, $jugador->yCo, "Comunio");

print "<table border='1'>";
print "<tr>";

foreach($jugador->PartidosTemporadaAnterior as $partido)
{
    $p=substr($partido->EstadoPartido,0,2);
    if (empty($p))
        $p=$partido->Puntos;
	$c=$partido->getColor();
    
	print "<td width='20px' style='background-color:$c'>$p</td>";
}
print "</tr>";

print "</table>";
?>
</body>
</html>
