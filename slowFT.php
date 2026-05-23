<?php
function slowFT($array){
    $FT=[];
    $zero=array_sum($array)/count($array);
    $FT[0]=[$zero,null];
    for($freq=1;$freq<floor(count($array)/2)-1;$freq++){
        $phase=get_phase($array,$freq);
        $size=get_size($array,$freq,$phase);
        $FT[$freq]=[$size,$phase];
    }
    return $FT;
}
function customsin($count,$freq,$phase,$size,$x){
    return $size*sin(2*pi()*($freq*$x/$count-$phase));
}
function getenergy($freq, $phase, $size, $array) {
    $energy = 0;
    for ($x = 0; $x < count($array); $x++) {
        $energy += ($array[$x] - customsin(count($array), $freq, $phase, $size, $x)) ** 2;
    }
    return $energy;
}
function get_phase($array,$freq){
    $min=0;
    $max=1;
    for($i=0;$i<15;$i++){
        $mid1=$min+($max-$min)*1/3;
        $mid2=$min+($max-$min)*2/3;
        $energy1=getenergy($freq,$min,1,$array);
        $energy2=getenergy($freq,$mid1,1,$array);
        $energy3=getenergy($freq,$mid2,1,$array);
        $energy4=getenergy($freq,$max,1,$array);
        $sum1=$energy1+$energy2;
        $sum2=$energy2+$energy3;
        $sum3=$energy3+$energy4;
        if($sum1<=$sum2 && $sum1<=$sum3){
            $max=$mid1;
        }elseif($sum2<=$sum1 && $sum2<=$sum3){
            $min=$mid1;
            $max=$mid2;
        }else{
            $min=$mid2;
        }
    }
    return ($min+$max)/2;
}
function get_size($array, $freq, $phase) {
    $min = 0;
    $max = 200000000;
    for ($i = 0; $i < 80; $i++) {
        $m1 = $min + ($max - $min) / 3;
        $m2 = $min + ($max - $min) * 2 / 3;
        $energy1 = getenergy($freq,$phase,$m1,$array);
        $energy2 = getenergy($freq,$phase,$m2,$array);
        if ($energy1 < $energy2) {
            $max = $m2;
        } else {
            $min = $m1;
        }
    }
    return ($min + $max) / 2;
}