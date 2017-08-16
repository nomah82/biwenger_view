<?php
require_once('constantes.php');
require_once('jugador.php');

function SetPartidosTemporadaAnterior($obj, $jugador)
{
    $jugador->PartidosTemporadaAnterior=array();
    foreach ($obj->data->reports as $j)
    {
        $partido=new Partido();
        $partido->Eventos=array();
        
        $txteventoar=array();
        if (isset($j->events))
        {
            foreach($j->events as $evento)
            {
                $eventoDto=new Evento();
                
                $txtevento=$evento->type;
                if ($txtevento=='6') $txtevento='T';//tarjeta amarilla
                else if ($txtevento=='3') $txtevento='A';//asistencia
                else if ($txtevento=='5') $txtevento='E';//entra al campo
                
                $eventoDto->Tipo=$txtevento;
                $eventoDto->Minuto=null;
                if (isset($evento->metadata))
                    $eventoDto->Minuto=$evento->metadata;
                $partido->Eventos[]=$eventoDto;
            }
        }
       
        if (isset($j->points))
        {
            $partido->Puntos=$j->points;
        }
        
        if (isset($j->status))
        {
            $partido->EstadoPartido=$j->status->status;
        }
        $jugador->PartidosTemporadaAnterior[]=$partido;
    }
}

function obtenerDatosJugador($urlAmigable, $nombre, &$jugador)
{
    $jugador=new Jugador();
    
    //datos provenientes de promunio
    $prom=consultaGraficaPromunio($urlAmigable, $nombre, $urlPromunio, $xprom, $yprom);
    $jugador->yCo=$yprom;
    $jugador->xCo=$xprom;
    $jugador->UrlPromunio=$urlPromunio;
	$jugador->Nombre=$nombre;

    //datos de biwenger
    $jsonBi=consultaJugador($urlAmigable);
    $obj=json_decode($jsonBi);
    $jugador->IncrementoUltimoDia=$obj->data->priceIncrement;
    $jugador->Precio=$obj->data->price;
    $jugador->PuntosTemporadaAnterior=$obj->data->pointsLastSeason;
    $jugador->EstadoActual=$obj->data->status;
    
    if (isset($obj->data->prices))
    {
        $x=array();
        $y=array();
        foreach ($obj->data->prices as $precio)
        {
            $x[]=$precio[0];
            $y[]=$precio[1];
        }
        $ypromCortado=array_slice($yprom, count($yprom) - count($y), count($y));
        $x=array_reverse($x);
        $y=array_reverse($y);
        
        $jugador->xBi=$x;
        $jugador->yBi=$y;
        $jugador->yCoCortado=$ypromCortado;

    }

    SetPartidosTemporadaAnterior($obj, $jugador);    
}

function consultaJugador($urlAmigable)
{
    $fichero="./consultados/".$urlAmigable."_".date("Ymd");
    if (file_exists($fichero))
        return file_get_contents($fichero);
    else {
        $c = curl_init('https://cf.biwenger.com/api/v1/players/la-liga/'.$urlAmigable.'?callback=jsonp_153156716&fields=*,team,fitness,owner,reports(points,home,events,status(status,statusText),match(*,round,home,away),star),prices,competition,seasons,news,threads,lastTeamNews&lang=es&score=1&season=2017');
        curl_setopt($c, CURLOPT_VERBOSE, 1);
        curl_setopt($c, CURLOPT_COOKIE, getCookie());
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            ':authority:cf.biwenger.com',
            ':method:GET',
            'referer:http://app.biwenger.com/market',
            ':path:/api/v1/players/la-liga/'.$urlAmigable.'?callback=jsonp_153156716&fields=*,team,fitness,owner,reports(points,home,events,status(status,statusText),match(*,round,home,away),star),prices,competition,seasons,news,threads,lastTeamNews&lang=es&score=1&season=2017',
            ':scheme:https',
            'accept:*/*',
            
            'accept-language:es-ES,es;q=0.8'
        ));
        $page = curl_exec($c);
        $page=substr($page, 0, -1);
        $page=substr($page, strpos($page,"(")+1);
        curl_close($c);
        if ($page)
            file_put_contents($fichero, $page);
        return $page;
    }
}

function consultarMercado($liga)
{
    $fichero="./consultados/".$liga."_".date("Ymd")."_mercado";
    if (file_exists($fichero))
        return file_get_contents($fichero);
    else {
        $c = curl_init('http://app.biwenger.com/api/v1/market');
        curl_setopt($c, CURLOPT_VERBOSE, 1);
        curl_setopt($c, CURLOPT_COOKIE, getCookie());
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            'Accept:application/json, text/plain, */*',
            'Authorization:'.getAutorizacion(),
            'Referer:http://app.biwenger.com/market',
            'X-Lang:es',
            'X-League:'.$liga,
            'X-Version:513'
        ));

        $page = curl_exec($c);
        curl_close($c);
        if ($page)
            file_put_contents($fichero, $page);
        return $page;
    }
}


function consultarMisJugadores($liga)
{
    $fichero="./consultados/".$liga."_".date("Ymd")."_mis_jugadores";
    if (file_exists($fichero))
        return file_get_contents($fichero);
    else {
        $c = curl_init('http://app.biwenger.com/api/v1/user?fields=*,lineup(type,playersID,reservesID),players(*,fitness,team,owner),market(*,-userID),offers,-trophies');
        curl_setopt($c, CURLOPT_VERBOSE, 1);
        curl_setopt($c, CURLOPT_COOKIE, getCookie());
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            'Accept:application/json, text/plain, */*',
            'Authorization:'.getAutorizacion(),
            'Referer:http://app.biwenger.com/market',
            'X-Lang:es',
            'X-League:'.$liga,
            'X-Version:513'
        ));

        $page = curl_exec($c);
        curl_close($c);
        if ($page)
            file_put_contents($fichero, $page);
        return $page;
    }
}

function consultaGraficaPromunio($urlAmigable, $nombre, &$urlPromunio, &$xprom, &$yprom)
{
	$prom="";
    $fichero="./consultados/".$urlAmigable."_".date("Ymd")."_promunio";
    if (file_exists($fichero))
    {
        $prom=file_get_contents($fichero);
        $urlPromunio=$fichero;
    }
    else {
            $idComunio=getIdComunio($nombre, $urlPromunio);
			if ($idComunio!=null)
			{
				$prom = file_get_contents('http://www.promunio.com/ajax/obtenerChart.php?id_comunio='.$idComunio.'&jugador=0');
				file_put_contents($fichero, $prom);
			}
    }
    
    $prom=str_replace("\r\n", "\n", $prom);
    $arprom=explode("\n", $prom);

    $xprom=array();
    $yprom=array();
    $dfechaProm=new DateTime();

    foreach($arprom as $sprom)
    {
        if (strpos($sprom, "categories.push")!==false)
        {
            $xprom[]=$dfechaProm->format('Y-m-d');
            $dfechaProm->modify('-1 day');
        }
        else if (strpos($sprom, "precios.push")!==false)
            $yprom[]=str_replace(");","",str_replace("precios.push(","",trim($sprom)));
    }
    $xprom=array_reverse($xprom);
    return $prom;
}

function grafica1d($canvas,$titulo, $x, $y, $ytitulo, $y2=null, $y2titulo=null)
{    
    $tipo="line";    
    print '<canvas id="'.$canvas.'" height="450" width="900"></canvas>';
    
    
    print '<script>
        var config = {
            type: "line",
            data: {
                labels: [' . "'".implode("', '", $x)."'" .'],
                datasets: [';
				
					print '{
						label: "'.$ytitulo.'",
						fill: false,
						backgroundColor: window.chartColors.blue,
						borderColor: window.chartColors.blue,
						data: ['. implode(", ", $y) .'],
					}';
					
					if ($y2!=null)
					{
						print ',{
							label: "'.$y2titulo.'",
							fill: false,
							backgroundColor: window.chartColors.red,
							borderColor: window.chartColors.red,
							data: ['. implode(", ", $y2) .'],
						}';

					}
					
					

				print ']
            },
            options: {
                responsive: false,
                tooltips: {
                    mode: "index",
                    intersect: false,
                },
                hover: {
                    mode: "nearest",
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "Fecha"
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "Precio"
                        }
                    }]
                }
            }
        };

        //window.onload = function() {
            var ctx = document.getElementById("'.$canvas.'").getContext("2d");
            window.myLine = new Chart(ctx, config);
        //};
    </script>';
    
}

function getIdComunio($name, &$outurl)
{
	$ch = curl_init();
	$fields="jugador_seleccionado=$name"."&buscar_navbar=1";
	curl_setopt($ch,CURLOPT_URL, 'http://www.promunio.com/ajax/buscarJugador.php');
	curl_setopt($ch,CURLOPT_POST, 2);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//execute post
	$url = curl_exec($ch);
	
	//close connection
	curl_close($ch);
    
    //<ul><li><a class="link" href="http://www.promunio.com/comunio/jugadores/270/vigaray" title="Vigaray"><i class="eqs-alaves margin-equipo"></i><span class="margin-jugador">Vigaray</span></a></li><li><a class="link" href="http://www.promunio.com/comunio/jugadores/1202/garay" title="Garay"><i class="eqs-valencia margin-equipo"></i><span class="margin-jugador">Garay</span></a></li></ul>
	$xml=new SimpleXmlElement($url);
    $total=count($xml->li);
    
    if ($total>1)
    {
        foreach ($xml->li as $j)
        {
            if (((string)$j->a['title'])==$name)
                $jugador=$j;
        }
    }
    else $jugador=$xml->li;
    $url=$jugador->a['href'];

	$outurl=$url;
	if ($url)
	{
		$idComunio=file_get_contents($url);
		$cortar="cargarChart(";
		$idComunio = substr($idComunio, strpos($idComunio, $cortar)+strlen($cortar));
		$idComunio = substr($idComunio, 0, strpos($idComunio,","));
		return $idComunio;
	}
	else return null;
}


/**
 * linear regression function
 * @param $x array x-coords
 * @param $y array y-coords
 * @returns array() m=>slope, b=>intercept
 */
function linear_regression($x, $y) {

  // calculate number points
  $n = count($x);
  
  // ensure both arrays of points are the same size
  if ($n != count($y)) {

    trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
  
  }

  // calculate sums
  $x_sum = array_sum($x);
  $y_sum = array_sum($y);

	$xx_sum = 0.0;
	$xy_sum = 0.0;
	$sumCodeviates = 0.0;
	$sumOfXSq = 0.0;
	$sumOfYSq = 0.0;
  
  for($i = 0; $i < $n; $i++) {
  
    $xy_sum+=($x[$i]*$y[$i]);
    $xx_sum+=($x[$i]*$x[$i]);
	
	
	$sumOfXSq += ($x[$i] * $x[$i]);
	$sumOfYSq += ($y[$i] * $y[$i]);
	
	$sumCodeviates+=($x[$i]*$y[$i]);
  }
  
  $RNumerator = ($n * $sumCodeviates) - ($x_sum * $y_sum);
  $RDenom = ($n * $sumOfXSq - ($x_sum * $x_sum)) * ($n * $sumOfYSq - ($y_sum * $y_sum));
  
  if ($RDenom>0)
	$dblR = $RNumerator / sqrt($RDenom);
else $dblR=0;
  $rsquared = $dblR * $dblR;
  
  // calculate slope
  $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
  
  // calculate intercept
  $b = ($y_sum - ($m * $x_sum)) / $n;
    
  // return result
  return array("m"=>$m, "b"=>$b, "r"=>$rsquared);
}


?>

