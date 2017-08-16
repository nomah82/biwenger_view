<?php
require_once('evento.php');

class Partido
{
    public $Puntos;
    public $Eventos;
    public $EstadoPartido;
    
    public function getColor()
    {
        $c="white";
        $p=substr($this->EstadoPartido,0,2);
        if (empty($p))
        {
            $p=$this->Puntos;
            if ($p>=10) $c="darkgreen";
            else if ($p>=6) $c="green";
            else if ($p>=0) $c="orange";
            else if ($p<0) $c="indianred";
        }
        else {
            if ($p=='ok') $c="red";
            else if ($p=='di') $c="red";
            else if ($p=='in') $c="orange";
            else if ($p=='sa') $c="lime";
        }
        return $c;
    }
}
?>