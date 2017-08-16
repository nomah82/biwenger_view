<?php
require_once('partido.php');

class Jugador
{
	public function GraficaDibujoUltimo()
	{
		$a1=array_slice($this->yBi, count($this->yBi)-6,3);
		$a2=array_slice($this->yBi, count($this->yBi)-3,3);
		if ((count($a1)==3) && (count($a2)==3))
		{
			$rtn1=linear_regression(array(1,2,3), $a1);
			$rtn2=linear_regression(array(1,2,3), $a2);
			
			$s="";
			
			if (abs($rtn1["m"])<10000) $s.="_";
			else if ($rtn1["m"]>0) $s.="/";
			else if ($rtn1["m"]<0) $s.="\\";
			
			if (abs($rtn2["m"])<10000) $s.="_";
			else if ($rtn2["m"]>0) $s.="/";
			else if ($rtn2["m"]<0) $s.="\\";
			return $s;
			
		}
		else return "";
	}
	
	public function RadioDiffPrecio()
	{
		$mBi=array_sum(array_slice($this->yBi, count($this->yBi)-2,2))/2;
		$mCo=array_sum(array_slice($this->yCoCortado, count($this->yCoCortado)-2,2))/2;
		
		return ($mCo/$mBi)-1;
	}
	
	public function RatioPrecioMax()
	{
		$a=$this->yCo;
		$max=0;
		foreach($a as $v)
		{
			if ($v>$max)
				$max=$v;
		}
		$mCo=array_sum(array_slice($this->yCoCortado, count($this->yCoCortado)-2,2))/2;
		if ($max>0)
			return ($mCo/$max);
		else return 0;
	}

    public function BiTendenciaUlt5Dias(&$r2)
    {
		if (count($this->yBi)>=6)
		{
			$rtn=linear_regression(array(1,2,3,4,5,6), array_slice($this->yBi, count($this->yBi)-6,6));
			$r2=round($rtn["r"],3);
			return (int)$rtn["m"];
		}
		else return 0;
    }
    
    public function CoTendenciaUlt5Dias(&$r2)
    {
		if (count($this->yCoCortado)>=6)
		{
			$rtn=linear_regression(array(1,2,3,4,5,6), array_slice($this->yCoCortado, count($this->yCoCortado)-6,6));
			$r2=round($rtn["r"],3);
			return (int)$rtn["m"];
		}
		else return 0;
    }
    
    public $xBi;
    public $yBi;
    public $yCoCortado;

    public $yCo;
    public $xCo;
    
    public $IncrementoUltimoDia;
    public $Precio;
    public $PuntosTemporadaAnterior;
    public $UrlPromunio;
    
    public $PartidosTemporadaAnterior;
	public $Nombre;
}


?>