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

if ( ! function_exists('get_masa_pajak')){
	
	function get_masa_pajak($bulan, $lang = "id", $full=false){

		$shortMonth = date('F', mktime(0, 0, 0, $bulan, 10));

		if($lang == "id"){
			if($full == true){
				$shortMonthArr = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			}
			else{
				$shortMonthArr = array("", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des");
			}
			$shortMonth    = $shortMonthArr[$bulan];
		}

		return $shortMonth;
	}
}

if ( ! function_exists('get_nama_cabang')){

	function get_nama_cabang($kode_cabang){

		$CI = get_instance();
		$CI->load->model('cabang_mdl');

		$cabang      = $CI->cabang_mdl->get_by_id($kode_cabang);

		if($cabang){
			$nama_cabang = $cabang->NAMA_CABANG;
		}
		else{
			$nama_cabang = "";
		}

		return $nama_cabang;
	}
}

if ( ! function_exists('get_list_cabang')){

	function get_list_cabang(){

		$CI = get_instance();
		$CI->load->model('cabang_mdl');

		$list_cabang = $CI->cabang_mdl->get_all();

		return $list_cabang;
	}
}

if ( ! function_exists('get_og_id')){

	function get_og_id($kode_cabang){

		$CI = get_instance();
		$CI->load->model('cabang_mdl');

		$organization_id = $CI->cabang_mdl->get_og_id($kode_cabang);

		return $organization_id;
	}
}

if ( ! function_exists('get_daftar_pajak')){

	function get_daftar_pajak($kelompok_pajak = "", $detail=false){

		$CI = get_instance();
		$CI->load->model('pajak_mdl');

		if($detail){
			$pajak      = $CI->pajak_mdl->get_daftar_pajak_detail($kelompok_pajak);
		}
		else{
			$pajak      = $CI->pajak_mdl->get_daftar_pajak($kelompok_pajak);
		}

		$nama_pajak = ($pajak) ? $pajak : '';

		return $nama_pajak;

	}
}

if ( ! function_exists('list_month')){

	function list_month($lang="id"){

		$monthArr = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

		if($lang == "eng"){
			$monthArr = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		}

		return $monthArr;

	}
}


if ( ! function_exists('subtr_npwp')){

	function subtr_npwp($npwp, $lenght=15){
	    $first  = substr($npwp, 0, 1);
	    
	    if($lenght == 15){
	        $subtr_npwp = substr($npwp, 0, 2).".".substr($npwp, 2, 3).".".substr($npwp, 5, 3).".".substr($npwp, 8, 1)."-".substr($npwp, 9, 3).".".substr($npwp, 12, 3);
	    }
	    elseif($lenght == 16){
	    	if($first != 0){
	        	$subtr_npwp = substr($npwp, 0, 2).".".substr($npwp, 2, 3).".".substr($npwp, 5, 3).".".substr($npwp, 8, 1)."-".substr($npwp, 9, 3).".".substr($npwp, 12, 3);
	    	}
	    	else{
				$npwp = substr($npwp, 1);
				$subtr_npwp = substr($npwp, 0, 2).".".substr($npwp, 2, 3).".".substr($npwp, 5, 3).".".substr($npwp, 8, 1)."-".substr($npwp, 9, 3).".".substr($npwp, 12, 3);
	    	}
	    }
	    else{
	        if($first != "0"){
	            $subtr_npwp = "0".substr($npwp, 0, 1).".".substr($npwp, 1, 3).".".substr($npwp, 4, 3).".".substr($npwp, 7, 1)."-".substr($npwp, 8, 3).".".substr($npwp, 11, 3);
	        }
	        else{
	            $subtr_npwp = substr($npwp, 0, 2).".".substr($npwp, 2, 3).".".substr($npwp, 5, 3).".".substr($npwp, 8, 1)."-".substr($npwp, 9, 3).".".substr($npwp, 12, 2)."0";
	        }
	    }
		return $subtr_npwp;
	}
}

if ( ! function_exists('format_npwp')){

	function format_npwp($npwp, $format=true){
		$exponent = false;
		$null     = false;
		$npwp     = simtax_trim($npwp, "space");
	    
	    if(strpos($npwp, 'E+') !== false){
	        $exponent = true;
	    }	    
	    if($npwp == "-" || $npwp == "0" || $npwp == "000000000000000" || $npwp == "00.000.000.0-000.000"){
	        $null = true;
	    }
	    
	    if($exponent){
    		$result = $npwp;
	        
	    }
	    else{
	        
	        $lenght = strlen($npwp);
	        
	    	if($lenght >= 14 && $lenght <= 16){
	    	    $result = subtr_npwp($npwp, $lenght);
	    	}
	    	else{
	    		if($null){
	    			$result = "00.000.000.0-000.000";
	    		}else{
	    			$result = $npwp;
	    		}
	    	}
	    }
	    
	    if($format == false){
			$fix  = preg_replace("/[^0-9]/", "", $result);
			$npwp = $fix;
	    }
	    else{
	        $npwp = $result;
	    }
	    
		return $npwp;

	}
}

if ( ! function_exists('simtax_trim')){

	function simtax_trim($string, $type=""){

		$replace1 = str_replace(",", "", $string);
		$replace2 = str_replace(".", "", $replace1);
		$replace3 = preg_replace('/\s+/', '', $replace2);
		
		$type     = ($type != "") ? strtolower($type) : "";
		
		switch ($type) {
			case 'comma':
				$replace = str_replace(",", "", $string);
			break;
			case 'point':
				$replace = str_replace(".", "", $string);
			break;
			case 'space':
				$replace = preg_replace('/\s+/', '', $string);
			break;
			default:
				$replace = $replace3;
			break;
		}

		return $replace;

	}
}

if ( ! function_exists('simtax_trim_tarif')){

	function simtax_trim_tarif($string, $type=""){

		$replace1 = str_replace(",", ".", $string);
		
		$type     = ($type != "") ? strtolower($type) : "";
		
		switch ($type) {
			case 'comma':
				$replace = str_replace(",", ".", $string);
			break;
			default:
				$replace = $replace1;
			break;
		}

		return $replace;

	}
}

if ( ! function_exists('qrcode_update_history')){

	function qrcode_update_history($table, $action="UPDATE", $params=""){

		$CI    = get_instance();

		if($action == "CREATE"){
			$query = $CI->qrcode->create_history($table, $params);
		}
		else{
			$query = $CI->qrcode->update_history($table, $params);
		}

	}
}

if ( ! function_exists('horizontal_loop_excel')){
	
	function horizontal_loop_excel($start, $lenght){
		$letter = $start;
		$step   = ($lenght) ? $lenght : 1000;
		for($i = 0; $i < $lenght; $i++) {
			$letters[] = $letter;
		    $letter++;
		}
		return $letters;
	}
}

?>