<?php
function captcha($x,$y)	{
	$possible = '23456789abcdefghijkmnpqrstvwxyz';
	$code = '';
	for ($i=0;$i<5;$i++){ 
		$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
	}

	$space = $x / (strlen($code)+1);	
	$img = imagecreate($x,$y);	
	$bg = imagecolorallocate($img,247,247,247);
	$border = imagecolorallocate($img,102,102,102);
	$colors[] = imagecolorallocate($img,102,102,102); //distrubi e lettere
	imagefilledrectangle($img,1,1,$x-2,$y-2,$bg);
	imagerectangle($img,0,0,$x-1,$y-1,$border);
	for ($i=0; $i< strlen($code); $i++)	{
		$color = $colors[$i % count($colors)];
		imagettftext($img,18+rand(0,4),-30+rand(0,60),($i+0.3)*$space,20+rand(0,10),$color,'arial.ttf',$code[$i]);
	}
	for($i=0;$i<($x*$y)/7;$i++){
		$x1 = rand(3,$x-3);
		$y1 = rand(3,$y-3);
		$x2 = $x1-2-rand(0,8);
		$y2 = $y1-2-rand(0,8);
		imagefilledellipse($img, mt_rand(0,$x), mt_rand(0,$y), 1, 1, $colors[rand(0,count($colors)-1)]);
	}
	header("Content-type: image/png");
	imagepng($img);
	$_SESSION['captcha']=$code;
}
session_start();
captcha(115,40);
?>7694db