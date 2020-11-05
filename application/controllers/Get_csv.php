<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Get_csv extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Get_csv_mdl', 'get_csv');
	}

	public function index()
	{

		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}

		$row = 1;
		$handle = fopen("data.csv", "r");
		
		$dataCsv  = array();
	    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

	    	/*$num = count($data);
	        echo "<p> $num fields in line $row: <br /></p>\n";
	        $row++;
	        for ($c=0; $c < $num; $c++) {
	            echo $data[$c] . "<br />\n";
	        }*/

	    	if($row > 1){
		        $dataCsv = array(
						'PAJAK_HEADER_ID'  => 56,
						'PAJAK_LINE_ID'    => $row,
						'KODE_FORM'        => $data[0],
						'MASA_PAJAK'       => $data[1],
						'BULAN_PAJAK'      => 9,
						'TAHUN_PAJAK'      => $data[2],
						'PEMBETULAN_KE'    => $data[3],
						'NPWP'             => $data[4],
						'NAMA_WP'          => $data[5],
						'ALAMAT_WP'        => $data[6],
						'NO_BUKTI_POTONG'  => $data[7],
						'TGL_BUKTI_POTONG' => $data[8],
						/*'nilai_bruto_1'  => $data[9],
						'tarif_1'          => $data[10],
						'pph_1'            => $data[11],*/
						'KODE_CABANG'      => $data[12],
						'NAMA_PAJAK'       => $data[13]
					);

	        	$this->get_csv->add($dataCsv);

	        }


	        $row++;
	    }

	    // echo "<pre>";
	    // print_r($dataCsv);
	    // echo "</pre>";

	}

}

/* End of file Get_csv.php */
/* Location: ./application/controllers/Get_csv.php */