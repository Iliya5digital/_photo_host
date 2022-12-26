<?php
function alphaID($in, $to_num=false)
{
	$index = "bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";

	$base  = strlen($index);

	if($to_num)
	{
		$in  = strrev($in);
		$out = 0;
		$len = strlen($in) - 1;
		
		for($t=0; $t<=$len; $t++)
		{
			$bcpow = bcpow($base, $len - $t);
			
			$out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
		}
		
		$out = sprintf('%F', $out);
		$out = substr($out, 0, strpos($out, '.'));
	} else
	{
		$out = "";
		for ($t = floor(log($in, $base)); $t>=0; $t--)
		{
			$bcp = bcpow($base, $t);
			$a   = floor($in / $bcp) % $base;
			$out = $out . substr($index, $a, 1);
			$in  = $in - ($a * $bcp);
		}
		
		$out = strrev($out);
	}

	return $out;
}

function size($bytes)
{
	$units = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
	
	for($i=1; $i<=9; $i++)
	{
		if($bytes*$i <= pow(1024, $i)) return round($bytes/pow(1024, $i-1), 1).' '.$units[$i-1].'B';
	}
}

function time_ago($timestamp)
{
	global $lang;
	
	$seconds = time() - $timestamp + 1;
	
	$units = array
	(
		'years_ago'		=>	60*60*24*7*30*12,
		'months_ago'	=>	60*60*24*7*30,
		'weeks_ago'		=>	60*60*24*7,
		'days_ago'		=>	60*60*24,
		'hours_ago'		=>	60*60,
		'minutes_ago'	=>	60,
		'seconds_ago'	=>	1
	);
	
	foreach($units as $unit=>$size)
	{
		if($seconds >= $size)
		{
			$n = round($seconds/$size);
			
			return sprintf($lang[$unit][isset($lang[$unit][$n])?$n:'n'], $n);
		}
	}
}

if(!defined('LANG')) define('LANG', 'eng');