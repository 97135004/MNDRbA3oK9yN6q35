<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('dateFormat')){
	
	function dateFormat($dateTime, $format = "", $lang="id"){

		$timestamp  = strtotime($dateTime);
		$date       = date("d", $timestamp);
		$time       = date("H:i", $timestamp);
		$day        = date("l", $timestamp);
		$month      = date("F", $timestamp);
		$shortMonth = date("M", $timestamp);
		$year       = date("Y", $timestamp);

		if($lang == "id"){
			$dayArr   = array("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
			$day      = $dayArr[date("w", $timestamp)];

			$monthArr = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			$month    = $monthArr[date("n", $timestamp)];

			$shortMonthArr = array("", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des");
			$shortMonth    = $shortMonthArr[date("n", $timestamp)];
		}

		switch ($format) {
			case '1':
				$dateString = $date.", ".$month." ".$year;
			break;
			case '2':
				$dateString = $day.", ".$month." ".$year;
			break;
			case '3':
				$dateString = $date."-".$month."-".$year;
			break;
			case 'shortmonth':
				$dateString = $date." ".$shortMonth." ".$year;
			break;
			case 'datetime':
				$dateString = $date." ".$month." ".$year." ".$time;
			break;
			case 'monthonly':
				$dateString = $month;
			break;
			case 'datetime2':
				if($lang == "id"){
					$dateString = $date." ".$month." ".$year." pukul ". $time;
				}
				else{
					$dateString = $date." ".$month." ".$year." at ". $time;
				}
			break;
			case 'pdf':
				$month		= date("n", $timestamp);
				$dateString = $month."/".$date."/".$year;
			break;
			default:
				$dateString = $date." ".$month." ".$year;
			break;
		}

		return $dateString;
	}
}

?>