<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ppn_wapu extends CI_Controller {

	public function __construct()
	{

		parent::__construct();
		
		if (!$this->ion_auth->logged_in())
		{
			redirect('/', 'refresh');
		}

		$this->load->model('Ppn_wapu_mdl');
		$this->load->model('cabang_mdl');
		$this->load->model('Pph_badan_mdl');

		/*ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(e_all);*/
	}

	function input_url_doc(){


		$this->template->set('title', 'Archive Link');
		$data['subtitle']   = "Archive Link";
		$data['activepage'] = "ppn_wapu";
		
		$data['stand_alone'] = true;
		$group_pajak         = get_daftar_pajak("PPNWAPU"); // PPH, PPH21, PPNMASA, PPNWAPU
		
		$list_pajak          = array();
	
		foreach ($group_pajak as $key => $value) {
			$list_pajak[] = $value->JENIS_PAJAK;
		}

		$data['nama_pajak']  = $list_pajak;
		
		$this->template->load('template', 'administrator/archive_link',$data);

	}

	function show_rekonsiliasi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Rekonsiliasi PPN WAPU');
			$data['subtitle']	= "Rekonsiliasi PPN WAPU";				
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/rekonsiliasi',$data);

		}
	}
	
	function load_rekonsiliasi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
				
			$hasil    = $this->Ppn_wapu_mdl->get_rekonsiliasi();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];

			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;
						$checked	= ($row['IS_CHEKLIST']==1)?"checked":"";
						$checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='checkbox".$row['RNUM']."' class='checklist' type='checkbox' ".$checked." data-toggle='confirmation-singleton' data-singleton='true' data-id='".$row['PAJAK_LINE_ID']."'>
										<label for='checkbox".$row['RNUM']."'>&nbsp;</label>
									</div>";
						$result['data'][] = array(
									'checkbox'			        => $checkbox,
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'pembetulan_ke' 			=> $row['PEMBETULAN_KE'],
									'pembetulan' 				=> $row['PEMBETULAN'],
									'jumlah_ppnbm' 				=> number_format($row['JUMLAH_PPNBM'],2,'.',','),
									'jumlah_ppn' 				=> number_format($row['JUMLAH_POTONG'],2,'.',','),
									'alamat_wp' 		        => $row['ALAMAT_WP'],
									'kode_pajak' 	            => $row['KODE_PAJAK'],
									'dpp' 	                    => number_format($row['JML_DPP'],2,'.',','),
									'tarif' 	                => $row['TARIF'],
									'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'uraian'					=> $row['URAIAN'],
									'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
									'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
									'new_tarif'					=> $row['NEW_TARIF'],
									'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
									'invoice_num'				=> $row['INVOICE_NUM'],
									'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",
									'vendor_id'					=> $row['VENDOR_ID'],
									'vendor_site_id'			=> $row['VENDOR_SITE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'invoice_accounting_date'	=> ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])):"",
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'masa_pajak'				=> $row['MASA_PAJAK'],
									'tahun_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'digit_tahun'				=> ($row['DIGIT_TAHUN']) ? $row['DIGIT_TAHUN'] : $row['DGT_THN'],
									'tgl_tagih'					=> ($row['TGL_TAGIH']) ? date("d/m/Y", strtotime($row['TGL_TAGIH'])):"",
									'tgl_setor_ppn'				=> ($row['TGL_SETOR_SSP']) ? date("d/m/Y", strtotime($row['TGL_SETOR_SSP'])):"",
									'tgl_setor_ppnbm'			=> ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y", strtotime($row['TGL_SETOR_PPNBM'])):"",
									'kode_lampiran'				=> $row['KODE_LAMPIRAN'],
									'kode_transaksi'			=> $row['KODE_TRANSAKSI'],
									'kode_status'				=> $row['KODE_STATUS'],
									'kode_dokumen'				=> ($row['KODE_DOKUMEN']) ? $row['KODE_DOKUMEN']: $row['KD_DOK'],
									'kode_cabang'				=> $row['KD_CAB'],
									'organization_id'			=> $row['ORGANIZATION_ID'],
									'currency_code'				=> $row['INVOICE_CURRENCY_CODE']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }
	
	/*Awal Detail Rekonsiliasi================================================================================*/
	function load_detail_summary()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
			
			$hasil    = $this->Ppn_wapu_mdl->get_detail_summary();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			$totselisih	= 0;
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	
						$totselisih = $totselisih + $row['JUMLAH_POTONG'];
						$result['data'][] = array(									
									'no'				        => $row['RNUM'],
									'vendor_name'	        	=> $row['VENDOR_NAME'],
									'npwp1'			    		=> $row['NPWP1'],
									'address_line1'				=> $row['ADDRESS_LINE1'],									
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],									
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",									
									'dpp'						=> $row['DPP'],									
									'jumlah_potong' 			=> number_format($row['JUMLAH_POTONG'],2,'.',','),
									'keterangan' 				=> $row['KETERANGAN'],
									'totselisih' 				=> number_format($totselisih,2,'.',',')
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);
		$query->free_result();
    }

    function load_detail_summary_pusat()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
			
			$hasil    = $this->Ppn_wapu_mdl->get_detail_summary_pusat();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			$totselisih	= 0;
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	
						$totselisih = $totselisih + $row['JUMLAH_POTONG'];
						$result['data'][] = array(									
									'no'				        => $row['RNUM'],
									'vendor_name'	        	=> $row['VENDOR_NAME'],
									'npwp1'			    		=> $row['NPWP1'],
									'address_line1'				=> $row['ADDRESS_LINE1'],									
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],									
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",									
									'dpp'						=> $row['DPP'],									
									'jumlah_potong' 			=> number_format($row['JUMLAH_POTONG'],2,'.',','),
									'keterangan' 				=> $row['KETERANGAN'],
									'totselisih' 				=> number_format($totselisih,2,'.',',')
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);
		$query->free_result();
    }

    function load_detail_summary_kompilasi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
			
			$hasil    = $this->Ppn_wapu_mdl->get_detail_summary_kompilasi();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			$totselisih	= 0;
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	
						$totselisih = $totselisih + $row['JUMLAH_POTONG'];
						$result['data'][] = array(									
									'no'				        => $row['RNUM'],
									'nama_cabang'			    => $row['NAMA_CABANG'],
									'vendor_name'	        	=> $row['VENDOR_NAME'],
									'npwp1'			    		=> $row['NPWP1'],
									'address_line1'				=> $row['ADDRESS_LINE1'],									
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],									
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",									
									'dpp'						=> $row['DPP'],									
									'jumlah_potong' 			=> number_format($row['JUMLAH_POTONG'],2,'.',','),
									'keterangan' 				=> $row['KETERANGAN'],
									'totselisih' 				=> number_format($totselisih,2,'.',',')
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);
		$query->free_result();
    }
	 
	function load_total_detail_summary()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}	
		$ii			  = 0;
		$result['jml_tidak_dilaporkan']	= 0; $result['jml_tgl_akhir'] = 0; $result['jml_import_csv'] = 0;		
		$result['total'] = 0; 
		if($permission === true)
		{
			$hasil    = $this->Ppn_wapu_mdl->get_total_detail_summary();			
			foreach($hasil->result_array() as $row)	{
						$ii++;	
						$result['total'] = $result['total'] + $row['JUMLAH_POTONG'];
						
						if ($row['KETERANGAN']=='Tidak Dilaporkan'){
							$result['jml_tidak_dilaporkan'] = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Tanggal 26 - 31 Bulan ini'){
							$result['jml_tgl_akhir'] = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Import CSV'){
							$result['jml_import_csv'] = $row['JUMLAH_POTONG'];
						}						
				}
		}
		echo json_encode($result);
		$hasil->free_result();
    }	 
	 /*Akhir Detail Rekonsiliasi================================================================================*/
	
	function load_total_detail_summary_pusat()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}	
		$ii			  = 0;
		$result['jml_tidak_dilaporkan']	= 0; $result['jml_tgl_akhir'] = 0; $result['jml_import_csv'] = 0;		
		$result['total'] = 0; 
		if($permission === true)
		{
			$hasil    = $this->Ppn_wapu_mdl->get_total_detail_summary_pusat();			
			foreach($hasil->result_array() as $row)	{
						$ii++;	
						$result['total'] = $result['total'] + $row['JUMLAH_POTONG'];
						
						if ($row['KETERANGAN']=='Tidak Dilaporkan'){
							$result['jml_tidak_dilaporkan'] = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Tanggal 26 - 31 Bulan ini'){
							$result['jml_tgl_akhir'] = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Import CSV'){
							$result['jml_import_csv'] = $row['JUMLAH_POTONG'];
						}						
				}
		}
		echo json_encode($result);
		$hasil->free_result();
    }
	
    function show_koreksi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_koreksi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Koreksi');
			$data['subtitle']	= "Koreksi PPN WAPU";
			$this->template->load('template', 'ppn_wapu/koreksippnwapu/index',$data);

		}
	}

    function load_koreksi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_koreksi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil    = $this->Ppn_wapu_mdl->get_koreksi();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];

			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;
						$result['data'][] = array(
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'alamat_wp' 		        => $row['ADDRESS_LINE1'],
									'kode_pajak' 	            => $row['KODE_PAJAK'],
									'dpp' 	                    => number_format($row['DPP'],2,'.',','),
									'tarif' 	                => $row['TARIF'],
									'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'uraian'					=> $row['URAIAN'],
									'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
									'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
									'new_tarif'					=> $row['NEW_TARIF'],
									'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
									'invoice_num'				=> $row['INVOICE_NUM'],
									'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> $row['TANGGAL_FAKTUR_PAJAK'],
									'vendor_id'					=> $row['VENDOR_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'gl_account'				=> $row['GL_ACCOUNT'],
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> $row['BULAN_PAJAK'],
									'tahun_pajak'				=> $row['TAHUN_PAJAK'],
									'masa_pajak'				=> $row['MASA_PAJAK'],
									'pembetulan'				=> $row['PEMBETULAN']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

    function show_approv()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_approv", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Approv');
			$data['subtitle']	= "Pelaporan PPN WAPU";
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/approval',$data);

		}
	}

    function load_approv()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_approv", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil    = $this->Ppn_wapu_mdl->get_approv();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];	
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;					
						$result['data'][] = array(
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'pembetulan_ke' 			=> $row['PEMBETULAN_KE'],
									'pembetulan' 				=> $row['PEMBETULAN'],
									'jumlah_ppnbm' 				=> number_format($row['JUMLAH_PPNBM_PPN'],2,'.',','),
									'alamat_wp' 		        => $row['ALAMAT_WP'],
									'kode_pajak' 	            => $row['KODE_PAJAK'],
									'dpp' 	                    => number_format($row['JUMLAH_DPP_PPN'],2,'.',','),
									'tarif' 	                => $row['TARIF'],
									'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'uraian'					=> $row['URAIAN'],
									'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
									'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
									'new_tarif'					=> $row['NEW_TARIF'],
									'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
									'invoice_num'				=> $row['INVOICE_NUM'],
									'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",
									'vendor_id'					=> $row['VENDOR_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'invoice_accounting_date'	=> ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])):"",
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'tahun_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'masa_pajak'				=> $row['MASA_PAJAK'],
									'digit_tahun'				=> $row['DGT_THN'],
									'tgl_tagih'					=> ($row['TGL_TAGIH']) ? date("d/m/Y", strtotime($row['TGL_TAGIH'])):"",
									'tgl_setor_ppn'				=> ($row['TGL_SETOR_SSP']) ? date("d/m/Y", strtotime($row['TGL_SETOR_SSP'])):"",
									'tgl_setor_ppnbm'			=> ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y", strtotime($row['TGL_SETOR_PPNBM'])):"",
									'jumlah_ppn'				=> number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
									'kode_lampiran'				=> $row['KODE_LAMPIRAN'],
									'kode_transaksi'			=> $row['KODE_TRANSAKSI'],
									'kode_status'				=> $row['KODE_STATUS'],
									'kode_dokumen'				=> $row['KODE_DOKUMEN'],
									'kode_cabang'				=> $row['KD_CAB'],
									'currency_code'				=> $row['INVOICE_CURRENCY_CODE']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

    function save_rekonsiliasi()
	{
		$data = $this->Ppn_wapu_mdl->action_save_rekonsiliasi();

		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}
	
    function submit_to_apprv()
	{
		$data	= $this->Ppn_wapu_mdl->do_submit_to_apprv();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function submit_rekonsiliasi()
	{
		$data	= $this->Ppn_wapu_mdl->action_submit_rekonsiliasi();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function check_rekonsiliasi()
	{
		$data	= $this->Ppn_wapu_mdl->action_check_rekonsiliasi();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}
	

	function delete_rekonsiliasi()
	{
		$data	= $this->Ppn_wapu_mdl->action_delete_rekonsiliasi();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function save_koreksi()
	{
		$data	= $this->Ppn_wapu_mdl->action_save_koreksi();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function delete_koreksi()
	{
		$data	= $this->Ppn_wapu_mdl->action_delete_koreksi();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function save_approv()
	{
		$data	= $this->Ppn_wapu_mdl->action_save_approv();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function get_start()
	{
		$data	= $this->Ppn_wapu_mdl->action_get_start();			
		if($data){
			if($data->num_rows()>0){
				$row	                 = $data->row();       	
				$result['status']        = strtoupper($row->STATUS); 
				$result['status_period'] = strtoupper($row->STATUS_PERIOD);
				//$result['keterangan'] 	 = $row->KETERANGAN;
			} else {
				$result['status']        = "-------------"; 
				$result['status_period'] = " ----------";
				//$result['keterangan'] 	 = "";
			}
			$result['isSuccess'] 	 = 1;
		} else {
			$result['isSuccess'] 	 = 0;
		}
		echo json_encode($result);
		$data->free_result();  
	}

	function get_start_pusat()
	{
		$data	= $this->Ppn_wapu_mdl->action_get_start_pusat();			
		if($data){
			if($data->num_rows()>0){
				$row	                 = $data->row();       	
				$result['status']        = strtoupper($row->STATUS); 
				$result['status_period'] = strtoupper($row->STATUS_PERIOD);
				//$result['keterangan'] 	 = $row->KETERANGAN;
			} else {
				$result['status']        = "-------------"; 
				$result['status_period'] = " ----------";
				//$result['keterangan'] 	 = "";
			}
			$result['isSuccess'] 	 = 1;
		} else {
			$result['isSuccess'] 	 = 0;
		}
		echo json_encode($result);
		$data->free_result();  
	}

	function show_closing()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_closing", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Closing PPN WAPU');
			$data['subtitle']	= "Closing";
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/closing',$data);

		}
	}

	function load_closing()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_closing", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

	      	$hasil		= $this->Ppn_wapu_mdl->get_closing();
			$rowCount	= $hasil['jmlRow'] ;
			$query 		= $hasil['query'];

			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;
						if ($row['STATUS']=="Open") {
							$st ="<span class='label label-success'>".$row['STATUS']."</span>";
						} else {
							$st ="<span class='label label-danger'>".$row['STATUS']."</span>";
						}
						$result['data'][] = array(
									'no'			=> $row['RNUM'],
									'nama_pajak'	=> $row['NAMA_PAJAK'],
									'masa_pajak'	=> $row['MASA_PAJAK'],
									'bulan_pajak'	=> $row['BULAN_PAJAK'],
									'tahun_pajak'	=> $row['TAHUN_PAJAK'],
									'pembetulan_ke'	=> $row['PEMBETULAN_KE'],
									'params'		=> $row['STATUS'],
									'cabang'		=> $row['KODE_CABANG'],
									'status' 		=> $st
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

    function save_closing()
	{
		$data	= $this->Ppn_wapu_mdl->action_save_closing();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function show_download()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_download", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Download & Cetak');
			$data['subtitle']	= "Download & Cetak PPN WAPU";
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/download',$data);
		
		}
	}

	function load_download()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_download", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil    = $this->Ppn_wapu_mdl->get_download();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];	
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;					
						$result['data'][] = array(
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'kode_cabang' 				=> $row['KODE_CABANG'],
									'pembetulan_ke' 			=> $row['PEMBETULAN_KE'],
									'pembetulan' 				=> $row['PEMBETULAN'],
									'jumlah_ppnbm' 				=> number_format($row['JUMLAH_PPNBM_PPN'],2,'.',','),
									'alamat_wp' 		        => $row['ALAMAT_WP'],
									'kode_pajak' 	            => $row['KODE_PAJAK'],
									'dpp' 	                    => number_format($row['JUMLAH_DPP_PPN'],2,'.',','),
									'tarif' 	                => $row['TARIF'],
									'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'uraian'					=> $row['URAIAN'],
									'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
									'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
									'new_tarif'					=> $row['NEW_TARIF'],
									'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
									'invoice_num'				=> $row['INVOICE_NUM'],
									'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",
									'vendor_id'					=> $row['VENDOR_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'invoice_accounting_date'	=> ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])):"",
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'tahun_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'masa_pajak'				=> $row['MASA_PAJAK'],
									'digit_tahun'				=> $row['DGT_THN'],
									'tgl_tagih'					=> ($row['TGL_TAGIH']) ? date("d/m/Y", strtotime($row['TGL_TAGIH'])):"",
									'tgl_setor_ppn'				=> ($row['TGL_SETOR_SSP']) ? date("d/m/Y", strtotime($row['TGL_SETOR_SSP'])):"",
									'tgl_setor_ppnbm'			=> ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y", strtotime($row['TGL_SETOR_PPNBM'])):"",
									'jumlah_ppn'				=> number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
									'kode_lampiran'				=> $row['KODE_LAMPIRAN'],
									'kode_transaksi'			=> $row['KODE_TRANSAKSI'],
									'kode_status'				=> $row['KODE_STATUS'],
									'kode_dokumen'				=> $row['KODE_DOKUMEN'],
									'kode_cabang'				=> $row['KD_CAB'],
									'currency_code'				=> $row['INVOICE_CURRENCY_CODE']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

    function export_format_csv_rekon() {
        $this->load->helper('csv_helper');
		$pajak   	= ($_REQUEST['tax'])? $_REQUEST['tax']:"";
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $data       = $this->Ppn_wapu_mdl->get_format_csv_rekon();
		$title = array("Line Id","Kode Lampiran", "Kode Transaksi", "Kode Status","Kode Dokumen", "NPWP Lawan Transaksi", "Nama Lawan Transaksi","Kode Cabang", "Digit Tahun", "No Seri FP / No Nota Retur","Tanggal Faktur", "Masa Pajak","Tahun Pajak","Pembetulan","Tanggal Tagih","Tanggal Setor PPN","Tanggal Setor PPN BM","Invoice Number", "Tanggal GL", "Mata Uang","Jumlah DPP","Jumlah PPN","Jumlah PPN BM");
        array_push($export_arr, $title);
        if (!empty($data)) {         
			foreach($data->result_array() as $row)	{	
				//$npwp = str_replace(".","",$row['NPWP1']);
				//$npwp = $row['NPWP1'];
				$npwp = ($row['NPWP1'] == "") ? "" : format_npwp($row['NPWP1']);
				array_push($export_arr, array($row['PAJAK_LINE_ID'],$row['KODE_LAMPIRAN'], $row['KODE_TRANSAKSI'], $row['KODE_STATUS'], $row['KD_DOK'], $npwp, $row['VENDOR_NAME'], $row['KD_CAB'], $row['DGT_THN'], ($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR_PAJAK'] : $row['NO_FAKTUR'], ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "", ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "", ($row['TANGGAL_FAKTUR_PAJAK'] != "" ) ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "", $row['PEMBETULAN'], ($row['TGL_TAGIH']) ? date("d/m/Y",strtotime($row['TGL_TAGIH'])) : "", ($row['TGL_SETOR_SSP']) ? date("d/m/Y",strtotime($row['TGL_SETOR_SSP'])) : "", ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y",strtotime($row['TGL_SETOR_PPNBM'])) : "", $row['INVOICE_NUM'], ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])) : "", $row['INVOICE_CURRENCY_CODE'], $row['JML_DPP'], $row['JUMLAH_POTONG'], ($row['JUMLAH_PPNBM']) ? $row['JUMLAH_PPNBM'] : 0 ));
			}
        }
       convert_to_csv($export_arr, 'Format Buku Bantu '.$_REQUEST['tax'].' '.$date.'.csv', ';');
    }

    /*function export_format_csv() {
        $this->load->helper('csv_helper');		
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $data       = $this->Ppn_wapu_mdl->get_format_csv();
		$title = array("Kode Lampiran", "Kode Transaksi", "Kode Status","Kode Dokumen", "NPWP Lawan Transaksi", "Nama Lawan Transaksi","Kode Cabang", "Digit Tahun", "No Seri FP atau Nota Retur","Tanggal Faktur", "Masa Pajak","Tahun Pajak","Pembetulan","Tanggal Tagih","Tanggal Setor PPN","Tanggal Setor PPN BM","Jumlah DPP","Jumlah PPN","Jumlah PPN BM");
        array_push($export_arr, $title);
        if (!empty($data)) {         
			foreach($data->result_array() as $row)	{	
				//$npwp = str_replace(".","",$row['NPWP']);
				$npwp = $row['NPWP1'];
				array_push($export_arr, array($row['KODE_LAMPIRAN'], $row['KODE_TRANSAKSI'], $row['KODE_STATUS'], $row['KD_DOK'], $npwp, $row['VENDOR_NAME'], $row['KD_CAB'], ($row['DIGIT_TAHUN']) ? $row['DIGIT_TAHUN'] : $row['DGT_THN'], ($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR'] : $row['BAYAR'], ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "", ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "", $row['TAHUN_PAJAK'], $row['PEMBETULAN'], ($row['TGL_TAGIH']) ? date("d/m/Y",strtotime($row['TGL_TAGIH'])) : "", ($row['TGL_SETOR_SSP']) ? date("d/m/Y",strtotime($row['TGL_SETOR_SSP'])) : "", ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y",strtotime($row['TGL_SETOR_PPNBM'])) : "", $row['DPP'], $row['JUMLAH_POTONG'], ($row['JUMLAH_PPNBM']) ? $row['JUMLAH_PPNBM'] : 0 ));
			}
        }
       convert_to_csv($export_arr, 'PPN WAPU '.$date.'.csv', ';');
    }*/

    /*function export_format_csv() {
        $this->load->helper('csv_helper');		
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $kode_cabang = $this->session->userdata('kd_cabang');
		$nama_pajak  = ($_REQUEST['tax'])? strtoupper($_REQUEST['tax']):"";
        $bulan_pajak  = $_REQUEST['month'];
        $tahun_pajak  = $_REQUEST['year'];
        $pembetulan  = $_REQUEST['pem'];
        $get_pajak_header_id = $this->Ppn_wapu_mdl->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

        $data       = $this->Ppn_wapu_mdl->get_format_csv();
		$title = array("Kode Lampiran", "Kode Transaksi", "Kode Status","Kode Dokumen", "NPWP Lawan Transaksi", "Nama Lawan Transaksi","Kode Cabang", "Digit Tahun", "No Seri FP atau Nota Retur","Tanggal Faktur", "Masa Pajak","Tahun Pajak","Pembetulan","Tanggal Tagih","Tanggal Setor PPN","Tanggal Setor PPN BM","Jumlah DPP","Jumlah PPN","Jumlah PPN BM");
        array_push($export_arr, $title);
        if (!empty($data)) {
			$resCheckData = $this->Ppn_wapu_mdl->check_duplicate_faktur($pajak_header_id);
			$lastInv    = "";
			$duplicateNoFaktur = array();
			foreach ($resCheckData as $key => $value) {
				$duplicateNoFaktur['NO_FAKTUR_PAJAK'][]   = ($value['NO_FAKTUR_PAJAK']) ? $value['NO_FAKTUR']: $value['BAYAR'];
				$duplicateNoFaktur['JUMLAH_POTONG_PPN'][] = $value['JUMLAH_POTONG_PPN'];
				$duplicateNoFaktur['JUMLAH_DPP'][]        = $value['JUMLAH_DPP'];
				$duplicateNoFaktur['JUMLAH_PPNBM_PPN'][]  = $value['JUMLAH_PPNBM_PPN'];
			}

			$pushData = true;
			$i = 0;
			$j = 0;
			$invoiceLiineNumArr = array();
			$dataNya = array();
			foreach($data->result_array() as $row)	{	
				//$npwp = str_replace(".","",$row['NPWP']);
				$npwp = $row['NPWP1'];

				$ppnNya     = $row['JUMLAH_POTONG'];
				$invoice_line_num     = $row['INVOICE_LINE_NUM'];
				$dppNya     = ($row['DPP']) ? $row['DPP'] : 0;
				$ppnbmNya     = ($row['JUMLAH_PPNBM']) ? $row['JUMLAH_PPNBM'] : 0;
				$no_fakturNya = ($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR'] : $row['BAYAR'];
				$pushData = false;
				if(in_array($no_fakturNya, $duplicateNoFaktur['NO_FAKTUR_PAJAK'])){
					if($no_fakturNya != $lastInv){
						$ppnNya      = $duplicateNoFaktur['JUMLAH_POTONG_PPN'][$j];
						$dppNya      = $duplicateNoFaktur['JUMLAH_DPP'][$j];
						$ppnbmNya    = $duplicateNoFaktur['JUMLAH_PPNBM_PPN'][$j];
						$pushData = true;
						$j++;
					}
					$lastInv = ($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR'] : $row['BAYAR'];
				}
				else{
					$pushData = true;
				}
				if($pushData){
					$invoiceLiineNumArr[$i] = $invoice_line_num;
					array_push($dataNya, array($row['KODE_LAMPIRAN']
												,$row['KODE_TRANSAKSI']
												,$row['KODE_STATUS']
												,$row['KD_DOK']
												,$npwp
												,$row['VENDOR_NAME']
												,$row['KD_CAB']
												,($row['DIGIT_TAHUN']) ? $row['DIGIT_TAHUN'] : $row['DGT_THN']
												,$no_fakturNya
												// ,($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR'] : $row['BAYAR']
												,($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
												,($row['BULAN_PAJAK'] !="") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])):""
												,$row['TAHUN_PAJAK']
												,$row['PEMBETULAN']
												,($row['TGL_TAGIH']) ? date("d/m/Y",strtotime($row['TGL_TAGIH'])) : ""
												,($row['TGL_SETOR_SSP']) ? date("d/m/Y",strtotime($row['TGL_SETOR_SSP'])) : ""
												,($row['TGL_SETOR_PPNBM']) ? date("d/m/Y",strtotime($row['TGL_SETOR_PPNBM'])) : ""
												,($dppNya) ? $dppNya : 0
												,$ppnNya
												,($ppnbmNya) ? $ppnbmNya : 0 ));
											$i++;
				}
			}
			krsort($invoiceLiineNumArr);
			foreach ($invoiceLiineNumArr as $key => $value) {
				$export_arr[] = $dataNya[$key];
			}
        }
       convert_to_csv($export_arr, 'PPN WAPU '.$date.'.csv', ';');
    }*/

    function export_format_csv() {
        $this->load->helper('csv_helper');		
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $data       = $this->Ppn_wapu_mdl->get_format_csv();
		$title = array("Kode Lampiran", "Kode Transaksi", "Kode Status","Kode Dokumen", "NPWP Lawan Transaksi", "Nama Lawan Transaksi","Kode Cabang", "Digit Tahun", "No Seri FP atau Nota Retur","Tanggal Faktur", "Masa Pajak","Tahun Pajak","Pembetulan","Tanggal Tagih","Tanggal Setor PPN","Tanggal Setor PPN BM","Jumlah DPP","Jumlah PPN","Jumlah PPN BM");
        array_push($export_arr, $title);
        if (!empty($data)) {
			foreach($data->result_array() as $row)	{	
				//$npwp = str_replace(".","",$row['NPWP1']);
				$npwp = format_npwp($row['NPWP1'], false);
				//$npwp = $row['NPWP1'];

				array_push($export_arr, array($row['KODE_LAMPIRAN']
												,$row['KODE_TRANSAKSI']
												,$row['KODE_STATUS']
												,$row['KODE_DOKUMEN']
												,$npwp
												,$row['VENDOR_NAME']
												,$row['KD_CAB']
												,$row['DGT_THN']
												//,($row['DIGIT_TAHUN']) ? $row['DIGIT_TAHUN'] : $row['DGT_THN']
												,($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR'] : $row['BAYAR']
												,($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
												,($row['TANGGAL_FAKTUR_PAJAK'] !="") ? date("m", strtotime($row['TANGGAL_FAKTUR_PAJAK'])):""
												,($row['TANGGAL_FAKTUR_PAJAK'] !="") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])):""
												,$row['PEMBETULAN']
												,($row['TGL_TAGIH']) ? date("d/m/Y",strtotime($row['TGL_TAGIH'])) : ""
												,($row['TGL_SETOR_SSP']) ? date("d/m/Y",strtotime($row['TGL_SETOR_SSP'])) : ""
												,($row['TGL_SETOR_PPNBM']) ? date("d/m/Y",strtotime($row['TGL_SETOR_PPNBM'])) : ""
												,($row['JUMLAH_DPP_PPN']) ? $row['JUMLAH_DPP_PPN'] : 0
												,$row['JUMLAH_POTONG_PPN']
												,($row['JUMLAH_PPNBM_PPN']) ? $row['JUMLAH_PPNBM_PPN'] : 0 ));
			}
        }
       convert_to_csv_PPH21($export_arr, 'PPN WAPU '.$date.'.csv', ';');
    }

    function load_master()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_master();
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];		
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;
					$result['data'][] = array(
								'no'				=> $row['RNUM'],
								'vendor_id'			=> $row['VENDOR_ID'],
								'vendor_site_id'	=> $row['VENDOR_SITE_ID'],
								'organization_id'	=> $row['ORGANIZATION_ID'],
								'nama_wp'			=> $row['VENDOR_NAME'],
								'alamat_wp'			=> $row['ADDRESS_LINE1'],
								'npwp' 				=> $row['NPWP']
								);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}		
		echo json_encode($result);

    }

    function load_master_kode_pajak()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_master_kode_pajak();
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];		
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;
					$result['data'][] = array(
								'no'				=> $row['RNUM'],								
								'tax_code'			=> $row['TAX_CODE'],
								'jenis_23'			=> $row['JENIS_23'],
								'tax_rate'			=> $row['TAX_RATE'],
								'description'		=> $row['DESCRIPTION']
								);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}		
		echo json_encode($result);

    }

    function import_CSV()
	{
		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		
		if (!empty($_FILES['file_csv']['name'])){

			$path 	= $_FILES['file_csv']['name'];
			$ext  	= pathinfo($path, PATHINFO_EXTENSION);
			$pajak 	= str_replace(" ","_",$this->input->post('uplPph'));			
			$file_name = "fileCSV_".$pajak;
			
			$kode_cabang = $this->session->userdata('kd_cabang');
			$nama_pajak  = $this->input->post('uplPph');
			$bulan_pajak = $this->input->post('import_bulan');
			$tahun_pajak = $this->input->post('import_tahun');
			$pembetulan  = $this->input->post('import_pembetulanKe');


			$pajak_header_id = $this->Ppn_wapu_mdl->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan);
			$pajak_header_id = $pajak_header_id->PAJAK_HEADER_ID;
			/*echo json_encode ($pembetulan);
								die();*/  
			if ($ext=='csv'){

				if($upl = $this->_upload('file_csv', 'importCsv/ppn_wapu/', $file_name, 'csv', $ext)){

					$row = 1;
					$handle = fopen("./uploads/importCsv/ppn_wapu/".$file_name.".".$ext, "r");
											
					$dataCsv  = array();
					while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

						$id_lines					= $data[0];
						$kode_lampiran		        = $data[1];
						$kode_transaksi		        = $data[2];
						$kode_status		        = $data[3];
						$kode_dokumen		        = $data[4];
						$npwp		       		    = $data[5];
						$nama_wp		        	= $data[6];
						$digit_tahun		        = str_replace(',','',$data[8]);
						$no_faktur_pajak			= $data[9];
						$tgl_faktur		        	= ($data[10]) ? date("y-m-d",strtotime(str_replace('/','-',$data[10]))) : "";
						$tgl_tagih		        	= ($data[14]) ? date("y-m-d",strtotime(str_replace('/','-',$data[14]))) : "";
						$tgl_setor_ppn		        = ($data[15]) ? date("y-m-d",strtotime(str_replace('/','-',$data[15]))) : "";
						$tgl_setor_ppnbm		    = ($data[16]) ? date("y-m-d",strtotime(str_replace('/','-',$data[16]))) : "";

						if($row > 1){

 							 /*  echo json_encode ($dataCsv);
								die();    */
							$hasil	= $this->Ppn_wapu_mdl->add_csv($kode_lampiran, $kode_transaksi, $kode_status, $kode_dokumen, $digit_tahun, $no_faktur_pajak, $tgl_faktur, $tgl_tagih, $tgl_setor_ppn, $tgl_setor_ppnbm, $pajak_header_id, $id_lines, $npwp, $nama_wp);

							//echo $hasil;die();
							
							if ($hasil){
								$st =1;
							} else {
								$st	= 0;
							} 						
						}
						$row++;
					}	
				
				}
			} else {
				$st = 3;
			}
		} else {
			$st	= 2 ;
		}		
		echo $st;
	}

	function insert_CSV()
	{
		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		
		if (!empty($_FILES['file_csv']['name'])){

			$path 	= $_FILES['file_csv']['name'];
			$ext  	= pathinfo($path, PATHINFO_EXTENSION);
			$pajak 	= str_replace(" ","_",$this->input->post('uplPph'));			
			$file_name = "fileCSV_".$pajak;
			
			$kode_cabang = $this->session->userdata('kd_cabang');
			$nama_pajak  = $this->input->post('uplPpn');
			$bulan_pajak = $this->input->post('insert_bulan');
			$bulan 		 = $this->input->post('insert_bulan');
			$tahun_pajak = $this->input->post('insert_tahun');
			$pembetulan  = $this->input->post('insert_pembetulanKe');


			$pajak_header_id = $this->Ppn_wapu_mdl->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan);
			$masa_pajak = $pajak_header_id->MASA_PAJAK;
			$kode_cabang = $pajak_header_id->KODE_CABANG;
			$tahun_pajak = $pajak_header_id->TAHUN_PAJAK;
			$pajak_header_id = $pajak_header_id->PAJAK_HEADER_ID;
			
			/*echo $masa_pajak;
								die();*/  
			if ($ext=='csv'){

				if($upl = $this->_upload('file_csv', 'importCsv/ppn_wapu/', $file_name, 'csv', $ext)){

					$row = 1;
					$handle = fopen("./uploads/importCsv/ppn_wapu/".$file_name.".".$ext, "r");
											
					$dataCsv  = array();
					while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

						$id_lines		        	= $data[0];
						$kode_lampiran		        = $data[1];
						$kode_transaksi		        = $data[2];
						$kode_status		        = $data[3];
						$kode_dokumen		        = $data[4];
						$npwp		       		    = $data[5];
						$nama_wp		        	= $data[6];
						$digit_tahun		        = str_replace(',','',$data[8]);
						$no_faktur_pajak			= $data[9];
						$tgl_faktur		        	= ($data[10]) ? date("Y-m-d",strtotime(str_replace('/','-',$data[10]))) : "";
						$bulan_pajak		       	= $data[11];
						//$tahun_pajak		        = $data[12];
						$tgl_tagih		        	= ($data[14]) ? date("Y-m-d",strtotime(str_replace('/','-',$data[14]))) : "";
						$tgl_setor_ppn		        = ($data[15]) ? date("Y-m-d",strtotime(str_replace('/','-',$data[15]))) : "";
						$tgl_setor_ppnbm		    = ($data[16]) ? date("Y-m-d",strtotime(str_replace('/','-',$data[16]))) : "";
						$invoice_num		    	= $data[17];
						$invoice_accounting_date    = ($data[18]) ? date("Y-m-d",strtotime(str_replace('/','-',$data[18]))) : "";
						$invoi_currency_code		= $data[19];
						$dpp						= simtax_trim($data[20]);
						$jumlah_potong		    	= simtax_trim($data[21]);
						$jumlah_ppnbm		    	= simtax_trim($data[22]);
						//$source_data		    	= $data[23];

						if($row > 1){

 							 /*  echo json_encode ($dataCsv);
								die();    */
						if($npwp == "" && $nama_wp == "" && $no_faktur_pajak == ""){
						}
						 else{

							if ($id_lines == "")
							{
								$hasil	= $this->Ppn_wapu_mdl->tambah_csv($kode_lampiran, $kode_transaksi, $kode_status, $kode_dokumen, $npwp, $nama_wp, $digit_tahun, $no_faktur_pajak, $tgl_faktur, $tgl_tagih, $tgl_setor_ppn, $tgl_setor_ppnbm, $pajak_header_id, $jumlah_potong, $jumlah_ppnbm, $masa_pajak, $tahun_pajak, $kode_cabang,$bulan, $invoice_num, $invoi_currency_code, $dpp, $invoice_accounting_date);
							}
							else
							{
								$hasil	= $this->Ppn_wapu_mdl->add_csv($kode_lampiran, $kode_transaksi, $kode_status, $kode_dokumen, $digit_tahun, $no_faktur_pajak, $tgl_faktur, $tgl_tagih, $tgl_setor_ppn, $tgl_setor_ppnbm, $pajak_header_id, $id_lines, $npwp, $nama_wp, $jumlah_potong, $dpp, $invoice_num);

							}
							

							//echo $hasil;die();
							
							if ($hasil){
								$st =1;
							} else {
								$st	= 0;
							}
						  } 						
						}
						$row++;
					}	
				
				}
			} else {
				$st = 3;
			}
		} else {
			$st	= 2 ;
		}		
		echo $st;
	}

	function show_pembetulan()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_pembetulan", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Pembetulan');
			$data['subtitle']	= "Pembetulan";
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/pembetulan',$data);

		}
	}

	function load_pembetulan()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_pembetulan", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil    = $this->Ppn_wapu_mdl->get_pembetulan();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];		
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;
						$result['data'][] = array(
									'no'				=> $row['RNUM'],
									'pajak_header_id'	=> $row['PAJAK_HEADER_ID'],
									'status_period'		=> $row['STATUS_PERIOD'],
									'bulan_pajak'		=> $row['BULAN_PAJAK'],
									'nama_pajak' 		=> $row['NAMA_PAJAK'],
									'masa_pajak' 		=> $row['MASA_PAJAK'],
									'tahun_pajak' 		=> $row['TAHUN_PAJAK'],
									'pembetulan_ke' 	=> $row['PEMBETULAN_KE'],
									'kode_cabang' 		=> $row['KODE_CABANG'],
									'nama_cabang' 		=> $row['NAMA_CABANG']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

	function save_pembetulan()
	{
		$data	= $this->Ppn_wapu_mdl->action_save_pembetulan();
		if($data && ($data=="Close" || $data=="CLOSE") ){
			echo '1';
		} else if($data && ($data=="Open" || $data=="OPEN")) {
			echo '2';
		} else if ($data && $data=="3") {
			echo '3';
		} 
		else {
			echo "0";
		}
	}

	function delete_pembetulan()
	{
		$data	= $this->Ppn_wapu_mdl->action_delete_pembetulan();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function show_view()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_view", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'View PPN WAPU');
			$data['subtitle']	= "View Status";
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/view',$data);

		}
	}

	function load_view()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_view", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil    = $this->Ppn_wapu_mdl->get_view();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];	
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;					
						$result['data'][] = array(
									'no'				=> $row['RNUM'],
									'pajak_header_id'	=> $row['PAJAK_HEADER_ID'],
									'kode_cabang'		=> $row['KODE_CABANG'],
									'nama_pajak'		=> $row['NAMA_PAJAK'],
									'bulan_pajak'		=> $row['BULAN_PAJAK'],
									'masa_pajak'		=> $row['MASA_PAJAK'],
									'tahun_pajak'		=> $row['TAHUN_PAJAK'],
									'creation_date' 	=> $row['CREATION_DATE'],
									'user_name' 		=> $row['USER_NAME'],
									'status' 		    => $row['STATUS'],
									'tgl_submit_sup' 	=> $row['TGL_SUBMIT_SUP'],
									'tgl_approve_sup' 	=> $row['TGL_APPROVE_SUP'],
									'tgl_approve_pusat' => $row['TGL_APPROVE_PUSAT'],
									'pembetulan_ke' 	=> $row['PEMBETULAN_KE'],
									'ttl_jml_potong' 	=> number_format($row['TTL_JML_POTONG'],2,'.',',')
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

    function load_rekonsiliasi_detail()
	{
      	$hasil	=$this->Ppn_wapu_mdl->get_rekonsiliasi_detail();
		$rowCount	= $hasil['jmlRow'] ;
		$query 		= $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;
					$checked	= ($row['IS_CHEKLIST']==1)?"checked":"";
					$checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
									<input id='checkbox".$row['RNUM']."' class='checklist' type='checkbox' ".$checked." disabled data-id='".$row['PAJAK_LINE_ID']."'>
									<label for='checkbox".$row['RNUM']."'>&nbsp;</label>
								</div>";
					$result['data'][] = array(
								'checkbox'			        => $checkbox,
								'no'				        => $row['RNUM'],
								'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
								'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
								'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
								'nama_wp'			        => $row['VENDOR_NAME'],
								'npwp' 				        => $row['NPWP1'],
								'pembetulan_ke' 			=> $row['PEMBETULAN_KE'],
								'pembetulan' 				=> $row['PEMBETULAN'],
								'jumlah_ppnbm' 				=> number_format($row['JUMLAH_PPNBM'],2,'.',','),
								'alamat_wp' 		        => $row['ADDRESS_LINE1'],
								'kode_pajak' 	            => $row['KODE_PAJAK'],
								'dpp' 	                    => number_format($row['JML_DPP'],2,'.',','),
								'tarif' 	                => $row['TARIF'],
								'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
								'uraian'					=> $row['URAIAN'],
								'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
								'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
								'new_tarif'					=> $row['NEW_TARIF'],
								'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
								'invoice_num'				=> $row['INVOICE_NUM'],
								'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
								'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
								'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",
								'vendor_id'					=> $row['VENDOR_ID'],
								'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
								'invoice_accounting_date'	=> ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])):"",
								'akun_pajak'				=> $row['AKUN_PAJAK'],
								'nama_pajak'				=> $row['NAMA_PAJAK'],
								'bulan_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
								'tahun_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
								'masa_pajak'				=> $row['MASA_PAJAK'],
								'organization_id'			=> $row['ORGANIZATION_ID'],
								'digit_tahun'				=> $row['DGT_THN'],
								'tgl_tagih'					=> ($row['TGL_TAGIH']) ? date("d/m/Y", strtotime($row['TGL_TAGIH'])):"",
								'tgl_setor_ppn'				=> ($row['TGL_SETOR_SSP']) ? date("d/m/Y", strtotime($row['TGL_SETOR_SSP'])):"",
								'tgl_setor_ppnbm'			=> ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y", strtotime($row['TGL_SETOR_PPNBM'])):"",
								'jumlah_ppn'				=> number_format($row['JUMLAH_POTONG'],2,'.',','),
								'kode_lampiran'				=> $row['KODE_LAMPIRAN'],
								'kode_transaksi'			=> $row['KODE_TRANSAKSI'],
								'kode_status'				=> $row['KODE_STATUS'],
								'kode_dokumen'				=> $row['KODE_DOKUMEN'],
								'kode_cabang'				=> $row['KD_CAB'],
								'currency_code'				=> $row['INVOICE_CURRENCY_CODE']
								);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_summary_rekonsiliasi1()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_summary_rekonsiliasi(1);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-success'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_summary_rekonsiliasi0()
	{
      	$hasil	=$this->Ppn_wapu_mdl->get_summary_rekonsiliasi(0);
		$rowCount	= $hasil['jmlRow'] ;
		$query 		= $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-danger'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_tot_rekonsiliasi()
	{
		$data	= $this->Ppn_wapu_mdl->action_tot_rekonsiliasi();
		if($data){
			if($data->num_rows()>0){
				$row	                 = $data->row();       	
				$result['total']        = number_format($row->JML_POTONG,2,'.',','); 
			} else {
				$result['total']        = number_format(0,2,'.',',');
			}
			$result['isSuccess'] 	 = 1;
		} else {
			$result['isSuccess'] 	 = 0;
		}
		echo json_encode($result);
		$data->free_result(); 
	}

	function load_history()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_history();
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'			=> $row['RNUM'],
						'action_code'	=> $row['ACTION_CODE'],
						'action_date'	=> $row['ACTION_DATE'],
						'user_name'		=> $row['USER_NAME'],
						'catatan'		=> $row['CATATAN']
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_detail_tot_rekonsiliasi()
	{
		$data	= $this->Ppn_wapu_mdl->action_detail_tot_rekonsiliasi();
		if($data){
			if($data->num_rows()>0){
				$row	                 = $data->row();       	
				$result['total']        = number_format($row->JML_POTONG,2,'.',','); 
			} else {
				$result['total']        = number_format(0,2,'.',',');
			}
			$result['isSuccess'] 	 = 1;
		} else {
			$result['isSuccess'] 	 = 0;
		}
		echo json_encode($result);
		$data->free_result(); 
	}

	function load_detail_summary_rekonsiliasi0()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_detail_summary_rekonsiliasi(0);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-danger'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_detail_summary_rekonsiliasi1()
	{
      	$hasil	=$this->Ppn_wapu_mdl->get_detail_summary_rekonsiliasi(1);
		$rowCount	= $hasil['jmlRow'] ;
		$query 		= $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-success'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function show_compilasi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Kompilasi');
			$data['subtitle']	= "Kompilasi PPN WAPU";
			$data['activepage']	= "ppn_wapu";

			$data['kantor_cabang'] = "pusat";
			$data['list_cabang']   = $this->cabang_mdl->get_all();
			
			$this->template->load('template', 'ppn_wapu/compilasi',$data);

		}
	}

    function load_kompilasi()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_view", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
			$hasil    = $this->Ppn_wapu_mdl->get_kompilasi();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];	
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;
						$checked	= ($row['IS_CHEKLIST']==1)?"checked":"";
						$checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='checkbox".$row['RNUM']."' disabled class='checklist' type='checkbox' ".$checked." data-toggle='confirmation-singleton' data-singleton='true' data-id='".$row['PAJAK_LINE_ID']."'>
										<label for='checkbox".$row['RNUM']."'>&nbsp;</label>
									</div>";
						$result['data'][] = array(
									'checkbox'			        => $checkbox,
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'pembetulan_ke' 			=> $row['PEMBETULAN_KE'],
									'pembetulan' 				=> $row['PEMBETULAN'],
									'jumlah_ppnbm' 				=> number_format($row['JUMLAH_PPNBM_PPN'],2,'.',','),
									'jumlah_ppn' 				=> number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
									'alamat_wp' 		        => $row['ALAMAT_WP'],
									'kode_pajak' 	            => $row['KODE_PAJAK'],
									'dpp' 	                    => number_format($row['JUMLAH_DPP_PPN'],2,'.',','),
									'tarif' 	                => $row['TARIF'],
									'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'uraian'					=> $row['URAIAN'],
									'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
									'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
									'new_tarif'					=> $row['NEW_TARIF'],
									'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
									'invoice_num'				=> $row['INVOICE_NUM'],
									'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",
									'vendor_id'					=> $row['VENDOR_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'invoice_accounting_date'	=> ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])):"",
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'masa_pajak'				=> $row['MASA_PAJAK'],
									'tahun_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'digit_tahun'				=> $row['DGT_THN'],
									'tgl_tagih'					=> ($row['TGL_TAGIH']) ? date("d/m/Y", strtotime($row['TGL_TAGIH'])):"",
									'tgl_setor_ppn'				=> ($row['TGL_SETOR_SSP']) ? date("d/m/Y", strtotime($row['TGL_SETOR_SSP'])):"",
									'tgl_setor_ppnbm'			=> ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y", strtotime($row['TGL_SETOR_PPNBM'])):"",
									'kode_lampiran'				=> $row['KODE_LAMPIRAN'],
									'kode_transaksi'			=> $row['KODE_TRANSAKSI'],
									'kode_status'				=> $row['KODE_STATUS'],
									'kode_dokumen'				=> $row['KODE_DOKUMEN'],
									'kode_cabang'				=> $row['KD_CAB'],
									'currency_code'				=> $row['INVOICE_CURRENCY_CODE']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);
    }

    function export_format_csv_compilasi() {


    	$this->load->helper('csv_helper');		
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $data       = $this->Ppn_wapu_mdl->get_format_csv_compilasi();
        $title = array("Kode Lampiran", "Kode Transaksi", "Kode Status","Kode Dokumen", "NPWP Lawan Transaksi", "Nama Lawan Transaksi","Kode Cabang", "Digit Tahun", "No Seri FP atau Nota Retur","Tanggal Faktur", "Masa Pajak","Tahun Pajak","Pembetulan","Tanggal Tagih","Tanggal Setor PPN","Tanggal Setor PPN BM","Invoice Number","Mata Uang","Jumlah DPP","Jumlah PPN","Jumlah PPN BM");
        array_push($export_arr, $title);
        if (!empty($data)) {
        foreach($data->result_array() as $row)	{	
				//$npwp = str_replace(".","",$row['NPWP']);
        		$npwp = format_npwp($row['NPWP1'], false);
				//$npwp = $row['NPWP'];

				array_push($export_arr, array(
						$row['KODE_LAMPIRAN']
						,$row['KODE_TRANSAKSI']
						,$row['KODE_STATUS']
						,$row['KODE_DOKUMEN']
						,$npwp
						,$row['VENDOR_NAME']
						,$row['KD_CAB']
						,($row['DIGIT_TAHUN']) ? $row['DIGIT_TAHUN'] : $row['DGT_THN']
						,($row['NO_FAKTUR_PAJAK']) ? $row['NO_FAKTUR_PAJAK']: $row['NO_FAKTUR']
						,($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
						,($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
						,($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
						,$row['PEMBETULAN']
						,($row['TGL_TAGIH']) ? date("d/m/Y",strtotime($row['TGL_TAGIH'])) : ""
						,($row['TGL_SETOR_SSP']) ? date("d/m/Y",strtotime($row['TGL_SETOR_SSP'])) : ""
						,($row['TGL_SETOR_PPNBM']) ? date("d/m/Y",strtotime($row['TGL_SETOR_PPNBM'])) : ""
						,$row['INVOICE_NUM']
						,$row['INVOICE_CURRENCY_CODE']
						,($row['JUMLAH_DPP_PPN']) ? $row['JUMLAH_DPP_PPN'] : 0
						,$row['JUMLAH_POTONG_PPN']
						,($row['JUMLAH_PPNBM_PPN']) ? $row['JUMLAH_PPNBM_PPN'] : 0 ));
					}

        }
       convert_to_csv($export_arr, 'Format Tambah Data '.$date.'.csv', ';');
    }

    function export_format_ntpn_compilasi() {


       $this->load->helper('csv_helper');		
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $data       = $this->Ppn_wapu_mdl->get_format_ntpn_compilasi();
		$title = array("Nama Rekanan", "NPWP Rekanan", "Kode dan Nomor Seri Faktur Pajak","Tanggal Faktur Pajak", "Tanggal Setor SSP", "NTPN","PPN (rupiah)","PPNBM (rupiah)");
        array_push($export_arr, $title);
        if (!empty($data)) {
        foreach($data->result_array() as $row)	{	
				//$npwp = str_replace(".","",$row['NPWP']);
				$npwp = $row['NPWP'];

				array_push($export_arr, array(
					$row['VENDOR_NAME']
					,$npwp
					,$row['NO_FAKTUR_PAJAK']
					,date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK']))
					,($row['TGL_SETOR_SSP']) ? date("d/m/Y",strtotime($row['TGL_SETOR_SSP'])) : ""
					,$row['NTPN']
					,$row['JUMLAH_POTONG_PPN']
					,($row['JUMLAH_PPNBM_PPN']) ? $row['JUMLAH_PPNBM_PPN'] : 0 ));
				}
        }
       convert_to_csv($export_arr, 'Format Tambah Data NTPN '.$date.'.csv', ';');
    }

    function import_compilasi_CSV()
	{
		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		
		if (!empty($_FILES['file_csv']['name'])){
			$path 	    = $_FILES['file_csv']['name'];
			$ext  	    = pathinfo($path, PATHINFO_EXTENSION);
			$pajak 	    = str_replace(" ","_",$this->input->post('uplPph'));			
			$file_name  = "fileKompilasiCSV_".$pajak;
			$jnspajak	= rawurldecode($this->uri->segment(3));
			$bulan	    = rawurldecode($this->uri->segment(4));
			$tahun	    = rawurldecode($this->uri->segment(5));
			$pembetulan	= rawurldecode($this->uri->segment(6));
			$header_id  = $this->Ppn_wapu_mdl->get_header_id($bulan, $tahun, $pembetulan);
			
			 if ($ext=='csv'){
				if($upl = $this->_upload('file_csv', 'importCsv/ppn_wapu/', $file_name, 'csv', $ext)){			
					$row = 1;
					$handle = fopen("./uploads/importCsv/ppn_wapu/".$file_name.".".$ext, "r");	
					$dataCsv  = array();
					while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {						
						if($row > 1){	
								$dataCsv = array(
									'bulan'             => $data[0],
									'tahun'	            => $data[1],
									'pajak'    	        => $data[2],
									'nama'              => $data[3],
									'npwp'              => $data[4],
									'alamat'            => $data[5],
									'kode_pajak'        => $data[6],					
									'dpp'          		=> $data[7],
									'tarif'             => $data[8],
									'jumlah_potong'     => $data[9],
									'invoice_number'    => $data[10],
									'no_bukti_potong'   => $data[11],
									'no_faktur'         => $data[12],
									'tgl_faktur'        => $data[13],
									'akun_beban'        => $data[14],
									'pembetulan'        => $data[15],
									'keterangan'        => $data[16],
									'header_id'     	=> $header_id
								);
							
							if($data[15] != "PEMBETULAN"){
								if ($pembetulan != $data[15]){
									echo 4; exit();
								}
							}
							
							$hasil	= $this->Ppn_wapu_mdl->add_kompilasi_csv($dataCsv);							
							if ($hasil){
								$st =1;								
							} else {
								$st	= 0;
							} 		
						}
						$row++;
					}
						
				}
			} else {
				$st = 3;
			} 
		} else {
			$st	= 2 ;
		}			
		echo $st; 
	}

	function load_summary_kompilasi1()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_summary_kompilasi(1);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'nama_cabang'		=> $row['NAMA_CABANG'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-success'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_summary_kompilasi0()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_summary_kompilasi(0);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'nama_cabang'		=> $row['NAMA_CABANG'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-danger'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_tot_kompilasi()
	{
		$data1	= $this->Ppn_wapu_mdl->action_tot_kompilasi(1);
		$data0	= $this->Ppn_wapu_mdl->action_tot_kompilasi(0);
		if($data1 && $data0){
			if ($data1){
				if($data1->num_rows()>0){
					$row	                 = $data1->row();       	
					$result['total1']        = $row->JML_POTONG; 
				} else {
					$result['total1']        = 0; 
				}
			}
			
			if ($data0){
				if($data0->num_rows()>0){
					$row	                 = $data0->row();       	
					$result['total0']        = $row->JML_POTONG; 
				} else {
					$result['total0']        = 0; 
				}
			}

			$result['isSuccess'] 	 = 1;
		} else {
			$result['isSuccess'] 	 = 0;
		}
		echo json_encode($result);
		$data1->free_result(); 
		$data0->free_result(); 
	}

	function show_ntpn()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_ntpn", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Daftar Nominatif');
			$data['subtitle']	= "Daftar Nominatif";				
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/nominatif',$data);

		}
	}

	function load_ntpn()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/show_ntpn", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil	= $this->Ppn_wapu_mdl->get_ntpn();
			$rowCount	= $hasil['jmlRow'] ;
			$query 		= $hasil['query'];	
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;					
						$result['data'][] = array(
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date('d/m/Y', strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'vendor_id'					=> $row['VENDOR_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> $row['BULAN_PAJAK'],
									'jumlah_ppn'				=> number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
									'jumlah_ppnbm'				=> number_format($row['JUMLAH_PPNBM'],2,'.',','),
									'tgl_setor_ssp'				=> ($row['TGL_SETOR_SSP']) ? date('d/m/Y', strtotime($row['TGL_SETOR_SSP'])) : "",
									'ntpn'						=> $row['NTPN']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

	function import_CSV_Nominatif()
	{
		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		
		if (!empty($_FILES['file_csv']['name'])){

			$path 	= $_FILES['file_csv']['name'];
			$ext  	= pathinfo($path, PATHINFO_EXTENSION);
			$pajak 	= str_replace(" ","_",$this->input->post('uplPph'));			
			$file_name = "fileCSV_".$pajak;
			
			$kode_cabang = $this->session->userdata('kd_cabang');
			$nama_pajak  = $this->input->post('uplPph');
			$bulan_pajak = $this->input->post('import_bulan');
			$tahun_pajak = $this->input->post('import_tahun');
			$pembetulan  = $this->input->post('import_pembetulanKe');


			$pajak_header_id = $this->Ppn_wapu_mdl->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan);
			$pajak_header_id = $pajak_header_id->PAJAK_HEADER_ID;
			if ($ext=='csv'){

				if($upl = $this->_upload('file_csv', 'importCsv/ppn_wapu/', $file_name, 'csv', $ext)){

					$handle = fopen("./uploads/importCsv/ppn_wapu/".$file_name.".".$ext, "r");
											
					$row = 1;
					$totalData = 0;
					$emptyNTPN = FALSE;
					$emptyTglSSP = FALSE;
					$ntpn_kurang_18 = FALSE;
					$ntpn_lebih_18 = FALSE;
					$ssp_thn_eror = FALSE;
					$ssp_bln_eror = FALSE;
					while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

						if($row > 1){

							$no_faktur[] = $data[2];

							if($data[4] !=""){
								$tanggalNya = explode("/",$data[4]);
								$tglsetorssp[] = $tanggalNya[2]."-".$tanggalNya[1]."-".$tanggalNya[0];

								/*if($tanggalNya[2] != $tahun_pajak){
									$ssp_thn_eror = TRUE;
								}*/
								/*elseif(ltrim($tanggalNya[1],'0') != $bulan_pajak){
									$ssp_bln_eror = TRUE;
								}*/
							}

							$ntpn[] = $data[5];

							if ($data[5] == ""){
								$emptyNTPN = TRUE;
							}

							if ($data[4] == ""){
								$emptyTglSSP = TRUE;
							}

							if (strlen($data[5]) < 16 ){
								$ntpn_kurang_18 = TRUE;
							}

							if (strlen($data[5]) > 16 ){
								$ntpn_lebih_18 = TRUE;
							}
													
							$totalData++;
						}
						$row++;
					}


					$dups = array();
					$ntpn = array_filter($ntpn);
					foreach(array_count_values($ntpn) as $val => $c){
	   					 if($c > 1){
	   					 	$dups[] = $val;
	   					 }
	   				}


	   				if($emptyNTPN === TRUE){
						echo '4';
						die();
	   				}
	   				elseif($emptyTglSSP === TRUE){
						echo '6';
						die();
	   				}
					elseif(count($dups) > 0){
						echo '5';
						die();
					}
					elseif($ntpn_kurang_18 === TRUE){
						echo '7';
						die();
					}
					elseif($ntpn_lebih_18 === TRUE){
						echo '8';
						die();
					}
					/*elseif($ssp_bln_eror === TRUE){
						echo '9';
						die();
					}*/
					/*elseif($ssp_thn_eror === TRUE){
						echo '10';
						die();
					}*/
					else{

						for ($i=0; $i < $totalData; $i++) { 

							$hasil	= $this->Ppn_wapu_mdl->add_csv_nominatif($tglsetorssp[$i],$ntpn[$i],$no_faktur[$i],$pajak_header_id);

							if ($hasil){
								$st =1;
							} else {
								$st	= 0;
							} 
						}
						
					}
				
				}
			} else {
				$st = 3;
			}
		} else {
			$st	= 2 ;
		}		
		echo $st;
	}

	/*function export_csv_nominatif() {
        $this->load->helper('csv_helper');
		$pajak   	= ($_REQUEST['tax'])? $_REQUEST['tax']:"";
		$date	    = date("Y-m-d H:i:s");
        $export_arr = array();
        $data       = $this->Ppn_wapu_mdl->get_format_csv_nominatif();
		$title = array("Nama Rekanan","NPWP Rekanan", "Kode dan Nomor Seri Faktur Pajak", "Tanggal Faktur Pajak","Tanggal Setor SSP", "NTPN","PPN (Rupiah)","PPNBM (Rupiah)");
        array_push($export_arr, $title);
        if (!empty($data)) {         
			foreach($data->result_array() as $row)	{	
				$npwp = str_replace(".","",$row['NPWP']);
				array_push($export_arr, array($row['VENDOR_NAME'],$npwp, $row['NO_FAKTUR_PAJAK'], ($row['TANGGAL_FAKTUR_PAJAK']) ? date('d/m/Y', strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "", ($row['TGL_SETOR_SSP']) ? date('d/m/Y', strtotime($row['TGL_SETOR_SSP'])) : "", $row['NTPN'], $row['JUMLAH_POTONG'], $row['JUMLAH_PPNBM']));
			}
        }
       convert_to_csv($export_arr, 'Format Nominatif '.$_REQUEST['tax'].' '.$date.'.csv', ';');
    }*/

    /*function export_csv_nominatif() {
        $this->load->helper('csv_helper');
        $export_arr = array();
        $kode_cabang = $this->session->userdata('kd_cabang');
		$nama_pajak   	= ($_REQUEST['tax'])? $_REQUEST['tax']:"";
		$bulan_pajak  = $_REQUEST['month'];
        $tahun_pajak  = $_REQUEST['year'];
        $pembetulan  = $_REQUEST['ke'];
		$date	    = date("Y-m-d H:i:s");

		$get_pajak_header_id = $this->Ppn_wapu_mdl->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

        $data       = $this->Ppn_wapu_mdl->get_format_csv_nominatif();
		$title = array("Nama Rekanan","NPWP Rekanan", "Kode dan Nomor Seri Faktur Pajak", "Tanggal Faktur Pajak","Tanggal Setor SSP", "NTPN","PPN (Rupiah)","PPNBM (Rupiah)");
        array_push($export_arr, $title);
        if (!empty($data)) {
        	$resCheckData = $this->Ppn_wapu_mdl->check_duplicate_faktur($pajak_header_id);
			$lastInv    = "";
			$duplicateNoFaktur = array();
			foreach ($resCheckData as $key => $value) {
				$duplicateNoFaktur['NO_FAKTUR_PAJAK'][]   = $value['NO_FAKTUR_PAJAK'];
				$duplicateNoFaktur['JUMLAH_POTONG_PPN'][] = $value['JUMLAH_POTONG_PPN'];
				$duplicateNoFaktur['JUMLAH_DPP'][]        = $value['JUMLAH_DPP'];
				$duplicateNoFaktur['JUMLAH_PPNBM_PPN'][]  = $value['JUMLAH_PPNBM_PPN'];
			}

			$pushData = true;
			$i = 0;
			$j = 0;
			$invoiceLiineNumArr = array();
			$dataNya = array();         
			foreach($data->result_array() as $row)	{	
				$npwp = str_replace(".","",$row['NPWP']);
				$ppnNya     = $row['JUMLAH_POTONG'];
				$invoice_line_num     = $row['INVOICE_LINE_NUM'];
				$dppNya     = ($row['DPP']) ? $row['DPP'] : 0;
				$ppnbmNya     = ($row['JUMLAH_PPNBM']) ? $row['JUMLAH_PPNBM'] : 0;
				$no_fakturNya = $row['NO_FAKTUR_PAJAK'];
				$pushData = false;
				if(in_array($no_fakturNya, $duplicateNoFaktur['NO_FAKTUR_PAJAK'])){
					if($no_fakturNya != $lastInv){
						$ppnNya      = $duplicateNoFaktur['JUMLAH_POTONG_PPN'][$j];
						$dppNya      = $duplicateNoFaktur['JUMLAH_DPP'][$j];
						$ppnbmNya    = $duplicateNoFaktur['JUMLAH_PPNBM_PPN'][$j];
						$pushData = true;
						$j++;
					}
					$lastInv = $row['NO_FAKTUR_PAJAK'];
				}
				else{
					$pushData = true;
				}
				if($pushData){
					$invoiceLiineNumArr[$i] = $invoice_line_num;
				array_push($dataNya, array(
					$row['VENDOR_NAME']
					,$npwp
					,$row['NO_FAKTUR_PAJAK']
					,($row['TANGGAL_FAKTUR_PAJAK']) ? date('d/m/Y', strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
					,($row['TGL_SETOR_SSP']) ? date('d/m/Y', strtotime($row['TGL_SETOR_SSP'])) : ""
					,$row['NTPN']
					,$ppnNya
					,($ppnbmNya) ? $ppnbmNya : 0 ));
				$i++;
				}
			}
			krsort($invoiceLiineNumArr);
			foreach ($invoiceLiineNumArr as $key => $value) {
				$export_arr[] = $dataNya[$key];
			}
        }
       convert_to_csv($export_arr, 'Format Nominatif '.$_REQUEST['tax'].' '.$date.'.csv', ';');
    }*/

    function export_csv_nominatif() {
        $this->load->helper('csv_helper');
        $export_arr = array();
        $date	    = date("Y-m-d H:i:s");
        $data       = $this->Ppn_wapu_mdl->get_format_csv_nominatif();
		$title = array("Nama Rekanan","NPWP Rekanan", "Kode dan Nomor Seri Faktur Pajak", "Tanggal Faktur Pajak","Tanggal Setor SSP", "NTPN","PPN (Rupiah)","PPNBM (Rupiah)");
        array_push($export_arr, $title);
        if (!empty($data)) {
			foreach($data->result_array() as $row)	{	
				$npwp = str_replace(".","",$row['NPWP']);
				array_push($export_arr, array(
					$row['VENDOR_NAME']
					,$npwp
					,$row['NO_FAKTUR_PAJAK']
					,($row['TANGGAL_FAKTUR_PAJAK']) ? date('d/m/Y', strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : ""
					,($row['TGL_SETOR_SSP']) ? date('d/m/Y', strtotime($row['TGL_SETOR_SSP'])) : ""
					,$row['NTPN']
					,$row['JUMLAH_POTONG_PPN']
					,($row['JUMLAH_PPNBM_PPN']) ? $row['JUMLAH_PPNBM_PPN'] : 0 ));
				}
        }
       convert_to_csv($export_arr, 'Format Nominatif '.$_REQUEST['tax'].' '.$date.'.csv', ';');
    }

	function load_summary_approv1()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_summary_approv(1);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-success'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_summary_approv0()
	{
		$hasil    = $this->Ppn_wapu_mdl->get_summary_approv(0);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;					
					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-danger'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
			}
			
			$query->free_result();
			
			$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
			$result['recordsTotal']		= $rowCount;
			$result['recordsFiltered'] 	= $rowCount;
			
		} else {
			$result['data'] 			= "";
			$result['draw']				= "";
			$result['recordsTotal']		= 0;
			$result['recordsFiltered'] 	= 0;
		}	
		echo json_encode($result);
    }

    function load_tot_approv()
	{
		$data	= $this->Ppn_wapu_mdl->action_tot_approv();
		if($data){
			if($data->num_rows()>0){
				$row	                 = $data->row();       	
				$result['total']        = number_format($row->JML_POTONG,2,'.',','); 
			} else {
				$result['total']        = number_format(0,2,'.',',');
			}
			$result['isSuccess'] 	 = 1;
		} else {
			$result['isSuccess'] 	 = 0;
		}
		echo json_encode($result);
		$data->free_result(); 
	}

	function cek_data_csv_compilasi()
	{
		$data	= $this->Ppn_wapu_mdl->get_format_csv_compdata();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function cek_data_csv()
	{
		$data	= $this->Ppn_wapu_mdl->get_format_csv();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function approv_pusat()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/approv_pusat", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('/');
		}
		else{

			$this->template->set('title', 'Approve Pusat');
			$data['subtitle']	= "Pelaporan PPN WAPU";
			$data['activepage']	= "ppn_wapu";
			$this->template->load('template', 'ppn_wapu/appr_pusat',$data);
		}
	}

	function load_approv_pusat()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_wapu/approv_pusat", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$hasil	= $this->Ppn_wapu_mdl->get_approv_pusat();
			$rowCount	= $hasil['jmlRow'] ;
			$query 		= $hasil['query'];	
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;					
						$result['data'][] = array(
									'no'				        => $row['RNUM'],
									'pajak_header_id'	        => $row['PAJAK_HEADER_ID'],
									'pajak_line_id'			    => $row['PAJAK_LINE_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'nama_wp'			        => $row['VENDOR_NAME'],
									'npwp' 				        => $row['NPWP1'],
									'pembetulan_ke' 			=> $row['PEMBETULAN_KE'],
									'pembetulan' 				=> $row['PEMBETULAN'],
									'jumlah_ppnbm' 				=> number_format($row['JUMLAH_PPNBM_PPN'],2,'.',','),
									'alamat_wp' 		        => $row['ALAMAT_WP'],
									'kode_pajak' 	            => $row['KODE_PAJAK'],
									'dpp' 	                    => number_format($row['JUMLAH_DPP_PPN'],2,'.',','),
									'tarif' 	                => $row['TARIF'],
									'jumlah_potong' 		    => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'uraian'					=> $row['URAIAN'],
									'new_kode_pajak'			=> $row['NEW_KODE_PAJAK'],
									'new_dpp'					=> number_format($row['NEW_DPP'],2,'.',','),
									'new_tarif'					=> $row['NEW_TARIF'],
									'new_jumlah_potong'			=> number_format($row['NEW_JUMLAH_POTONG'],2,'.',','),
									'invoice_num'				=> $row['INVOICE_NUM'],
									'invoice_line_num'			=> $row['INVOICE_LINE_NUM'],
									'no_faktur_pajak'			=> $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak'		=> ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])):"",
									'vendor_id'					=> $row['VENDOR_ID'],
									'no_bukti_potong'			=> $row['NO_BUKTI_POTONG'],
									'invoice_accounting_date'	=> ($row['INVOICE_ACCOUNTING_DATE']) ? date("d/m/Y",strtotime($row['INVOICE_ACCOUNTING_DATE'])):"",
									'akun_pajak'				=> $row['AKUN_PAJAK'],
									'nama_pajak'				=> $row['NAMA_PAJAK'],
									'bulan_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("n", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'tahun_pajak'				=> ($row['TANGGAL_FAKTUR_PAJAK'] != "") ? date("Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : "",
									'masa_pajak'				=> $row['MASA_PAJAK'],
									'digit_tahun'				=> $row['DGT_THN'],
									'tgl_tagih'					=> ($row['TGL_TAGIH']) ? date("d/m/Y", strtotime($row['TGL_TAGIH'])):"",
									'tgl_setor_ppn'				=> ($row['TGL_SETOR_SSP']) ? date("d/m/Y", strtotime($row['TGL_SETOR_SSP'])):"",
									'tgl_setor_ppnbm'			=> ($row['TGL_SETOR_PPNBM']) ? date("d/m/Y", strtotime($row['TGL_SETOR_PPNBM'])):"",
									'jumlah_ppn'				=> number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
									'kode_lampiran'				=> $row['KODE_LAMPIRAN'],
									'kode_transaksi'			=> $row['KODE_TRANSAKSI'],
									'kode_status'				=> $row['KODE_STATUS'],
									'kode_dokumen'				=> $row['KODE_DOKUMEN'],
									'kode_cabang'				=> $row['KD_CAB'],
									'currency_code'				=> $row['INVOICE_CURRENCY_CODE']
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);

    }

    function save_approv_pusat()
	{
		$data	= $this->Ppn_wapu_mdl->action_save_approv_pusat();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}


    private function _upload($field_name, $folder_name, $file_name, $allowed_types, $ext){
        //file upload destination
        $upload_path = './uploads/';
        $config['upload_path'] = $upload_path.$folder_name;
        //allowed file types. * means all types
        $config['allowed_types'] = $allowed_types;
        //allowed max file size. 0 means unlimited file size
        $config['max_size'] = '0';
        //max file name size
        $config['max_filename'] = '355';
        //whether file name should be encrypted or not
        $config['encrypt_name'] = FALSE;
        $config['file_name'] = $file_name;
        //store image info once uploaded
        $image_data = array();
        //check for errors
        $is_file_error = FALSE;
        //check if file was selected for upload
        if (!$_FILES) {
            $is_file_error = TRUE;
        }
        //if file was selected then proceed to upload
        if (!$is_file_error) {
			
			if (file_exists(FCPATH.$upload_path.$folder_name."/".$file_name.".".$ext)){
				unlink($upload_path.$folder_name."/".$file_name.".".$ext);
				
			} 			
			//load the preferences
			$this->load->library('upload', $config);
			//check file successfully uploaded. 'image_name' is the name of the input
			if (!$this->upload->do_upload($field_name)) {
				//if file upload failed then catch the errors
				$is_file_error = TRUE;
			} else {
				//store the file info
				$image_data = $this->upload->data();

				if($image_data){
					return true;
				}

			}
        }

        return false;
	}

	function get_selectAll()
	{
		$data	= $this->Ppn_wapu_mdl->action_get_selectAll();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function cek_row_rekonsiliasi()
	{
		$data	= $this->Ppn_wapu_mdl->action_cek_row_rekonsiliasi();
		$result['st'] = 0;
		if($data){
			$ii=0;
			$records	= "";
			foreach($data->result_array() as $row)	{
					$ii++;
					if ($row['IS_CHEKLIST']==1){
						if(!$row['TANGGAL_FAKTUR_PAJAK'] || $row['TANGGAL_FAKTUR_PAJAK']=="" || !$row['VENDOR_NAME'] || $row['VENDOR_NAME']=="" || !$row['NO_FAKTUR_PAJAK'] || $row['NO_FAKTUR_PAJAK']=="" || !$row['NPWP1'] || $row['NPWP1']=="" ){
							$records .= $ii.", " ;
							$result['st'] = 1;
						}
					}
					$result['data'] ="Nomor ".$records." Tanggal Faktur/Nama WP/NPWP/Nomor Faktur Pajak Masih Kosong!";					
			}
		} 
		echo json_encode($result);
		$data->free_result();  
	}

	function load_summary_rekonsiliasiAll1()
	{
      	$bulan 		= $_POST['_searchBulan'];
      	$tahun 		= $_POST['_searchTahun'];
      	$pajak 		= $_POST['_searchPph'];
      	$pembetulan = $_POST['_searchPembetulan'];
		$step		= $_POST['_step'];	
		
		$hasil_currency	=$this->Ppn_wapu_mdl->get_currency1($bulan, $tahun, $pajak, $pembetulan, $step);
		$rowCount	= $hasil_currency['jmlRow'] ;
		$queryC 	= $hasil_currency['query'];	
		$ii = 0;
		
		if ($rowCount>0) {
		foreach($queryC->result_array() as $rowC)	
			{
					$dibayarkan			= 0;
					$tidakDibayarkan	= 0;
					$ii++;
					$hasil	=$this->Ppn_wapu_mdl->get_summary_rekonsiliasiAll1($bulan, $tahun, $pajak, $pembetulan,$step);
					$query1 		= $hasil['queryExec'];	
					
					foreach($query1->result_array() as $row)	
					{
						if ($row['PENGELOMPOKAN']=="Dilaporkan"){
							$dibayarkan = $row['JML_POTONG'];
						} else {
							$tidakDibayarkan = $row['JML_POTONG'];
						}						
					}
												
						$saldoAkhir	= $rowC['SALDO_AWAL'] + ( $rowC['MUTASI_DEBIT'] -  $rowC['MUTASI_KREDIT'] );
						$selisih	= $saldoAkhir - $dibayarkan;
						
						 if ($step=="REKONSILIASI") {
							$result['data'][] = array(
										'no'				    => $ii,								
										'saldo_awal'	        => '<input type="text" class="form-control input-sm text-right" id="saldoAwal" name="saldoAwal" placeholder="Saldo Awal" value="'.number_format($rowC['SALDO_AWAL'],2,'.',',').'">',
										
										'mutasi_debet'	        => '<input type="text" class="form-control input-sm text-right" id="mutasiDebet" name="mutasiDebet" placeholder="Mutasi Debet" value="'.number_format($rowC['MUTASI_DEBIT'],2,'.',',').'">',
										
										'mutasi_kredit'	        =>  '<input type="text" class="form-control input-sm text-right" id="mutasiKredit" name="mutasiKredit" placeholder="Mutasi Kredit" value="'.number_format($rowC['MUTASI_KREDIT'],2,'.',',').'">',
										
										'saldo_akhir'	        => '<input type="text" class="form-control input-sm text-right" id="saldoAkhir" name="saldoAkhir" placeholder="Saldo Akhir" value="'.number_format($saldoAkhir,2,'.',',').'">',
										
										'jumlah_dibayarkan'	    => '<input type="text" class="form-control input-sm text-right" id="jmlDibayarkan" name="jmlDibayarkan" placeholder="Jumlah DIbayarkan" value="'.number_format($dibayarkan,2,'.',',').'">',
										
										'selisih'	            => '<input type="text" class="form-control input-sm text-right" id="selisih" name="selisih" placeholder="Selisih" disabled value="'.number_format($selisih,2,'.',',').'">',
										
										'tidak_dilaporkan'	    => '<input type="text" class="form-control input-sm text-right" id="tidakDilaporkan" name="tidakDilaporkan" placeholder="Tidak Dilaporkan" value="'.number_format($tidakDibayarkan,2,'.',',').'">'
									);
						 } else {
							 $result['data'][] = array(
										'no'				    => $ii,								
										'saldo_awal'	        => number_format($rowC['SALDO_AWAL'],2,'.',','),
										
										'mutasi_debet'	        => number_format($rowC['MUTASI_DEBIT'],2,'.',','),
										
										'mutasi_kredit'	        => number_format($rowC['MUTASI_KREDIT'],2,'.',','),
										
										'saldo_akhir'	        => number_format($saldoAkhir,2,'.',','),
										
										'jumlah_dibayarkan'	    => number_format($dibayarkan,2,'.',','),
										
										'selisih'	            => number_format($selisih,2,'.',','),
										
										'tidak_dilaporkan'	    => number_format($tidakDibayarkan,2,'.',',')
									);
						 }
								
					}
								
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}				
			
		echo json_encode($result);
    }

    function load_summary_rekonsiliasiAll1_pusat()
	{
      	$bulan 		= $_POST['_searchBulan'];
      	$tahun 		= $_POST['_searchTahun'];
      	$pajak 		= $_POST['_searchPph'];
      	$pembetulan = $_POST['_searchPembetulan'];
      	$pilihCabang = $_POST['_searchCabang'];
		$step		= $_POST['_step'];	
		
		$hasil_currency	=$this->Ppn_wapu_mdl->get_currency1_pusat($bulan, $tahun, $pajak, $pembetulan,$pilihCabang, $step);
		$rowCount	= $hasil_currency['jmlRow'] ;
		$queryC 	= $hasil_currency['query'];	
		$ii = 0;
		
		if ($rowCount>0) {
		foreach($queryC->result_array() as $rowC)	
			{
					$dibayarkan			= 0;
					$tidakDibayarkan	= 0;
					$ii++;
					$hasil	=$this->Ppn_wapu_mdl->get_summary_rekonsiliasiAll1_pusat($bulan, $tahun, $pajak, $pembetulan,$pilihCabang,$step);
					$query1 		= $hasil['queryExec'];	
					
					foreach($query1->result_array() as $row)	
					{
						if ($row['PENGELOMPOKAN']=="Dilaporkan"){
							$dibayarkan = $row['JML_POTONG'];
						} else {
							$tidakDibayarkan = $row['JML_POTONG'];
						}						
					}
												
						$saldoAkhir	= $rowC['SALDO_AWAL'] + ( $rowC['MUTASI_DEBIT'] -  $rowC['MUTASI_KREDIT'] );
						$selisih	= $saldoAkhir - $dibayarkan;
						
						 if ($step=="REKONSILIASI") {
							$result['data'][] = array(
										'no'				    => $ii,								
										'saldo_awal'	        => '<input type="text" class="form-control input-sm text-right" id="saldoAwal" name="saldoAwal" placeholder="Saldo Awal" value="'.number_format($rowC['SALDO_AWAL'],2,'.',',').'">',
										
										'mutasi_debet'	        => '<input type="text" class="form-control input-sm text-right" id="mutasiDebet" name="mutasiDebet" placeholder="Mutasi Debet" value="'.number_format($rowC['MUTASI_DEBIT'],2,'.',',').'">',
										
										'mutasi_kredit'	        =>  '<input type="text" class="form-control input-sm text-right" id="mutasiKredit" name="mutasiKredit" placeholder="Mutasi Kredit" value="'.number_format($rowC['MUTASI_KREDIT'],2,'.',',').'">',
										
										'saldo_akhir'	        => '<input type="text" class="form-control input-sm text-right" id="saldoAkhir" name="saldoAkhir" placeholder="Saldo Akhir" value="'.number_format($saldoAkhir,2,'.',',').'">',
										
										'jumlah_dibayarkan'	    => '<input type="text" class="form-control input-sm text-right" id="jmlDibayarkan" name="jmlDibayarkan" placeholder="Jumlah DIbayarkan" value="'.number_format($dibayarkan,2,'.',',').'">',
										
										'selisih'	            => '<input type="text" class="form-control input-sm text-right" id="selisih" name="selisih" placeholder="Selisih" disabled value="'.number_format($selisih,2,'.',',').'">',
										
										'tidak_dilaporkan'	    => '<input type="text" class="form-control input-sm text-right" id="tidakDilaporkan" name="tidakDilaporkan" placeholder="Tidak DIlaporkan" value="'.number_format($tidakDibayarkan,2,'.',',').'">'
									);
						 } else {
							 $result['data'][] = array(
										'no'				    => $ii,								
										'saldo_awal'	        => number_format($rowC['SALDO_AWAL'],2,'.',','),
										
										'mutasi_debet'	        => number_format($rowC['MUTASI_DEBIT'],2,'.',','),
										
										'mutasi_kredit'	        => number_format($rowC['MUTASI_KREDIT'],2,'.',','),
										
										'saldo_akhir'	        => number_format($saldoAkhir,2,'.',','),
										
										'jumlah_dibayarkan'	    => number_format($dibayarkan,2,'.',','),
										
										'selisih'	            => number_format($selisih,2,'.',','),
										
										'tidak_dilaporkan'	    => number_format($tidakDibayarkan,2,'.',',')
									);
						 }
								
					}
								
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}				
			
		echo json_encode($result);
    }

    function load_summary_kompilasi()
	{
		$bulan 		= $_POST['_searchBulan'];
      	$tahun 		= $_POST['_searchTahun'];
      	$pajak 		= $_POST['_searchPph'];
      	$pembetulan = $_POST['_searchPembetulan'];
		$pilihCabang		= $_POST['_searchCabang'];	
		
		$hasil_currency	=$this->Ppn_wapu_mdl->get_currency_kompilasi($bulan, $tahun, $pajak, $pembetulan, $pilihCabang);
		$rowCount	= $hasil_currency['jmlRow'] ;
		$queryC 	= $hasil_currency['query'];	
		$ii = 0;
		
		if ($rowCount>0) {
		foreach($queryC->result_array() as $rowC)	
			{
					$kdcabang			= $rowC['KODE_CABANG'] ;
					$dibayarkan			= 0;
					$tidakDibayarkan	= 0;
					$ii++;
					$hasil	=$this->Ppn_wapu_mdl->get_summary_rekonsiliasi_kompilasi($bulan, $tahun, $pajak, $pembetulan,$kdcabang);
					$query1 		= $hasil['queryExec'];	
					
					foreach($query1->result_array() as $row)	
					{
						if ($row['PENGELOMPOKAN']=="Dilaporkan"){
							$dibayarkan = $row['JML_POTONG'];
						} else {
							$tidakDibayarkan = $row['JML_POTONG'];
						}						
					}
												
						$saldoAkhir	= $rowC['SALDO_AWAL'] + ( $rowC['MUTASI_DEBIT'] -  $rowC['MUTASI_KREDIT'] );					
						$selisih	= $saldoAkhir - $dibayarkan;
						
							 $result['data'][] = array(
										'no'				    => $ii,								
										'cabang'				=> $rowC['NAMA_CABANG'],								
										'saldo_awal'	        => number_format($rowC['SALDO_AWAL'],2,'.',','),
										
										'mutasi_debet'	        => number_format($rowC['MUTASI_DEBIT'],2,'.',','),
										
										'mutasi_kredit'	        => number_format($rowC['MUTASI_KREDIT'],2,'.',','),
										
										'saldo_akhir'	        => number_format($saldoAkhir,2,'.',','),
										
										'jumlah_dibayarkan'	    => number_format($dibayarkan,2,'.',','),									
										
										'tidak_dilaporkan'	    => number_format($tidakDibayarkan,2,'.',','),
										'selisih'			    => number_format($selisih,2,'.',',')
									);
						
								
					}
								
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}				
			
		echo json_encode($result);
	}

	function load_total_detail_summary_kompilasi()
	{

    	$bulan 		= $_POST['_searchBulan'];
      	$tahun 		= $_POST['_searchTahun'];
      	$pajak 		= $_POST['_searchPph'];
      	$pembetulan = $_POST['_searchPembetulan'];
		$pilihCabang		= $_POST['_searchCabang'];	
		
		$hasil_currency	=$this->Ppn_wapu_mdl->get_currency_kompilasi($bulan, $tahun, $pajak, $pembetulan, $pilihCabang);
		$rowCount	= $hasil_currency['jmlRow'] ;
		$queryC 	= $hasil_currency['query'];	
		$ii = 0;
		
		if ($rowCount>0) {
		foreach($queryC->result_array() as $rowC)	
			{
					$cabang							= $rowC['KODE_CABANG'] ;
					$jml_tidak_dilaporkan			= 0;
					$jml_tgl_akhir					= 0;
					$total							= 0;
					$ii++;
					$hasil	=$this->Ppn_wapu_mdl->get_total_detail_summary_kompilasi($bulan, $tahun, $pajak, $pembetulan,$cabang);
					$query 		= $hasil['queryExec'];
					/*echo json_encode ($cabang);
								die();*/	
					
					foreach($query->result_array() as $row)	
					{

						if ($row['KETERANGAN']=='Tidak Dilaporkan'){
							$jml_tidak_dilaporkan = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Tanggal 26 - 31 Bulan ini'){
							$jml_tgl_akhir = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Import CSV'){
							$jml_import_csv = $row['JUMLAH_POTONG'];
						}						
					}
												
						$total					= $jml_tgl_akhir + $jml_import_csv;
						$total_tdk_dilaporkan	= $jml_tidak_dilaporkan + $jml_tgl_akhir;
						
							 $result['data'][] = array(
										'no'				    		=> $ii,

										'cabang'						=> $rowC['NAMA_CABANG'],
										
										'jml_tidak_dilaporkan'	       	=> number_format($jml_tidak_dilaporkan,2,'.',','),
										
										'jml_tgl_akhir'	    			=> number_format($jml_tgl_akhir,2,'.',','),									
										
										'jml_import_csv'	    		=> number_format($jml_import_csv,2,'.',','),
										'total'			    			=> number_format($total,2,'.',','),

										'total_tdk_dilaporkan'			=> number_format($total_tdk_dilaporkan,2,'.',',')
									);
								
					}
								
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}				
			
		echo json_encode($result);
    }

    function load_detail_summary_kompilasi_cabang()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("pph/show_compilasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
			
			$hasil    = $this->Ppn_wapu_mdl->get_detail_summary_kompilasi_cabang();
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			$totselisih	= 0;
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	
						$totselisih = $totselisih + $row['JUMLAH_POTONG'];
						$result['data'][] = array(									
									'no'				        => $row['RNUM'],
									'nama_cabang'			    => $row['NAMA_CABANG'],														
									'jumlah_potong' 			=> number_format($row['JUMLAH_POTONG'],2,'.',',')
									);
				}
				
				$query->free_result();
				
				$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']		= $rowCount;
				$result['recordsFiltered'] 	= $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
		}

		echo json_encode($result);
		$query->free_result();
    }

    function save_saldo_awal()
	{
		$data	= $this->Ppn_wapu_mdl->action_save_saldo_awal();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

    function cek_data_csv_nominatif()
	{
		$data	= $this->Ppn_wapu_mdl->cek_format_csv_nominatif();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}
	
	function cetak_summary_wapu_pdf()
	{

		$shortMonthArr 	= array("", "Januari"
								  , "Februari"
								  , "Maret"
								  , "April"
								  , "Mei"
								  , "Juni"
								  , "Juli"
								  , "Agustus"
								  , "September"
								  , "Oktober"
								  , "November"
								  , "Desember");
								  
		$masa			= $shortMonthArr[$_REQUEST['bulan']];

		$tahun 				= $_REQUEST['tahun'];
		$total_spt 			= 0;
		$total_ppn 			= 0;
		$selisih 			= 0;
		$tgl_cetak 			= '20';
		$bulan_cetak 		= $shortMonthArr[$_REQUEST['bulan'] + 1];
		$tahun_cetak 		= $_REQUEST['tahun'];
		//$nama_dvp			= 'AGUS WAHYUDI';
		$employee_num_dvp 	= '274096976';
		$bulan				= $_REQUEST['bulan'];
		$pembetulan_ke		= $_REQUEST['pembetulanKe'];
		$pilihCabang		= $_REQUEST['pilihCabang'];
		
		ob_start();

		//$this->load->library('fpdf');		
		
		define('FPDF_FONTPATH',$this->config->item('fonts_path')); 
		//$this->load->library('pdf_html');		
		//$pdf = new PDF_HTML();
		require('fpdf.php');
		$pdf = new fpdf();
		$pdf->SetFont('Arial','B',6);
		$pdf->AddPage();

		$header = array('NO', 'NAMA CABANG/UNIT', 'PPN WAPU (SPT)','','SELISIH');	

		$pdf->Cell(0,0,'PT. PELABUHAN INDONESIA II (PERSERO)',0,0,'L');
		$pdf->Ln(5);
		$pdf->Cell(0,0,'e-SPT KOMPILASI',0,0,'L');
		$pdf->Ln(10);
		$pdf->Cell(0,0,"REKAPITULASI SETORAN PPN WAPU MASA ".strtoupper($masa)." ".$tahun,0,0,'C');
		$pdf->Ln(10);
						
		//header
		// Column widths
		
		$w = array(5, 28, 90, 28, 28);
		// Header
		$pdf->Cell($w[0],5,$header[0],'TL',0,'C');
		$pdf->Cell($w[1],5,$header[1],'TL',0,'C');
		$pdf->Cell($w[2],5,$header[2],'TLR',0,'C');
		$pdf->Cell($w[3],5,$header[3],'TLR',0,'C');
		$pdf->Cell($w[4],5,$header[4],'TLR',0,'C');
		$pdf->Ln();
		//header kosong
		$pdf->Cell($w[0],5,'','LR',0,'C');
		$pdf->Cell($w[1],5,'','LR',0,'C');
		$pdf->Cell($w[2],5,'YANG DIPUNGUT OLEH PEMUNGUT LAINNYA (SELAIN BENDAHARAWAN )','LR',0,'C');
		$pdf->Cell($w[3],5,'REKAP SSP CABANG','LR',0,'C');
		$pdf->Cell($w[4],5,'Faktur Sebelumnya','LR',0,'C');
		$pdf->Ln();
		
		//get detail
			$hasil		= $this->Ppn_wapu_mdl->get_summary_wapu($tahun,$bulan,$pembetulan_ke,$pilihCabang);
			$query 		= $hasil['query'];
			$row1		= $query->row();		
			
			$ii	=	0;
			$numrow = 1;
			foreach($query->result_array() as $row)	{
					$ii++;
					/*
					$data = array(
								'no'			=> $row['RNUM'],
								'nama_pajak'	=> $row['NAMA_CABANG'],
								'masa_pajak'	=> $row['PPN']
								);
								
					*/
					// Data
					$pdf->SetFont('','');
					$pdf->Cell($w[0],6,$numrow,1,0,'C');
					$pdf->Cell($w[1],6,$row['NAMA_CABANG'],1);
					$pdf->Cell($w[2],6,($row['DISP_SPT']) ? $row['DISP_SPT'] : '-',1,0,'R');
					$pdf->Cell($w[3],6,($row['DISP_PPN']) ? $row['DISP_PPN'] : '-',1,0,'R');
					$pdf->Cell($w[4],6,($row['DISP_SELISIH']) ? $row['DISP_SELISIH'] : '-',1,0,'R');
					$total_spt = $total_spt + $row['SPT'];
					$total_ppn = $total_ppn + $row['PPN'];
					$selisih   = $selisih + $row['SELISIH'];
					$pdf->Ln();
					$numrow++;
					
					// Closing line
					//$pdf->Cell(array_sum($w),0,'','T');		
			}

			$nama_dvp  = strtoupper($row1->NAMA_PETUGAS_PENANDATANGAN);
			$dvp  	   = strtoupper($row1->JABATAN_PETUGAS_PENANDATANGAN);

			/*for ($i=2; $i < 12 ; $i++) {
				$j=$i;

				$pdf->SetFont('','');
					$pdf->Cell($w[0],6,$j=$i,1,0,'C');
					$pdf->Cell($w[1],6,$row['NAMA_CABANG'],1);
					$pdf->Cell($w[2],6,"-",1,0,'R');
					$pdf->Cell($w[3],6,"-",1,0,'R');
					$pdf->Cell($w[4],6,"-",1,0,'R');
					$pdf->Ln(); 
				# code...
			}*/
			$pdf->SetFont('','B');
			$pdf->Cell($w[0],6,'','LBT',0,'L');
			$pdf->Cell($w[1],6,'JUMLAH DISETOR','BT','C');
			$pdf->Cell($w[2],6,number_format($total_spt),1,0,'R');
			$pdf->Cell($w[3],6,number_format($total_ppn),1,0,'R');
			$pdf->Cell($w[3],6,number_format($selisih),1,0,'R');
		//end get detail	
			
		//penanda tangan	
		$pdf->Ln(8);
		$pdf->Cell(130);
		$pdf->Cell(30,10, 'Jakarta, '.$tgl_cetak." ".$bulan_cetak." ".$tahun_cetak,0,0,'C');
		$pdf->Ln(4);
		$pdf->Cell(130);
		$pdf->Cell(30,10, 'DVP PAJAK',0,0,'C');
		$pdf->Ln(20);
		$pdf->Cell(130);
		$pdf->Cell(30,10, $nama_dvp,0,0,'C');
		$pdf->Ln(4);
		$pdf->Cell(130);
		$pdf->Cell(30,10, 'NIPP. '.$employee_num_dvp,0,0,'C');
		//end tanda tangan
		
		//footer
		//$pdf->SetY(-35);
		//$pdf->SetFont('Arial','I',8);
		//$pdf->Cell(0,10,'Page '.$pdf->PageNo().'/{nb}',0,0,'C');		
		//end footer
		
		$pdf->Output();		
		ob_end_flush(); 
		//echo $this->fpdf->Output('hello_world.pdf','D');// Name of PDF file		
	}

	function cetak_summary_wapu()
	{

		$tahun 			= $_REQUEST['tahun'];
		$bulan 			= $_REQUEST['bulan'];
		$pembetulanKe 	= $_REQUEST['pembetulanKe'];
		/*$pilihCabang 	= $_REQUEST['pilihCabang'];*/

		$shortMonthArr 	= array("", "Januari"
								  , "Februari"
								  , "Maret"
								  , "April"
								  , "Mei"
								  , "Juni"
								  , "Juli"
								  , "Agustus"
								  , "September"
								  , "Oktober"
								  , "November"
								  , "Desember");
								  
		$masa			= $shortMonthArr[$_REQUEST['bulan']];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak PPN MASA BULANAN")
								->setSubject("Cetakan")
								->setDescription("Cetak PPN MASA BULANAN")
								->setKeywords("MASA");
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$style_col = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$style_col2 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_colhead = array(
			'font'     => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_colhead1 = array(
			'font'     => array('bold' => true), // Set font nya jadi bold
		);

		$style_colhsl = array(
			'font'     => array('bold' => true), // Set font nya jadi bold
		);		
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_nama = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_rowbold = array(
			'font'     => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		   	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		//buat header cetakan
		//logo IPC
		$excel->setActiveSheetIndex(0)->setCellValue('A1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A2', "e-SPT KOMPILASI
"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A5', "REKAPITULASI SETORAN PPN WAPU MASA"." ".strtoupper($masa)." ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		
		// Buat header tabel nya pada baris ke 3
		
		$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "NAMA CABANG/UNIT "); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "PPN WAPU (SPT)"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('C8', "YANG DIPUNGUT OLEH PEMUNGUT LAINNYA"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('C9', "(SELAIN BENDAHARAWAN )"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D8', "REKAP SSP CABANG "); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E7', "SELISIH"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('E8', "Faktur Sebelumnya"); // Set kolom E3 dengan tulisan "ALAMAT"
		
		$excel->getActiveSheet()->mergeCells('A5:E5');
		$excel->getActiveSheet()->mergeCells('A7:A9');		
		$excel->getActiveSheet()->mergeCells('B7:B9');		
		
		$excel->getActiveSheet()->getStyle('A1')->applyFromArray($style_colhead1);
		$excel->getActiveSheet()->getStyle('A2')->applyFromArray($style_colhead1);
		$excel->getActiveSheet()->getStyle('A5:E5')->applyFromArray($style_colhead);
		$excel->getActiveSheet()->getStyle('A7:A9')->applyFromArray($style_rowbold);
		$excel->getActiveSheet()->getStyle('B7:B9')->applyFromArray($style_rowbold);
		$excel->getActiveSheet()->getStyle('C7:C9')->applyFromArray($style_rowbold);
		$excel->getActiveSheet()->getStyle('D7:D9')->applyFromArray($style_rowbold);
		$excel->getActiveSheet()->getStyle('E7:E9')->applyFromArray($style_rowbold);
		
		/*$excel->getActiveSheet()->getStyle('A7:A8')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('C7:C8')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D7:D8')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($style_col2);*/

		
		//get detail				
			$queryExec	= " select skc.kode_cabang
                             , case skc.nama_cabang
                                when 'Kantor Pusat' then skc.nama_cabang
                               else 'Cabang ' || skc.nama_cabang
                               end nama_cabang     
                             , pajak.disp_ppn 
                             , pajak.ppn
                             , pajak.disp_spt
                             , pajak.spt
                             , pajak.selisih
                             , pajak.disp_selisih
                             from simtax_kode_cabang skc 
                        ,(    select rownum rnum
                             , sph.kode_cabang
                             , (select trim(to_char(sum(jumlah_potong))) ttl from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1) DISP_PPN
                             , (select sum(jumlah_potong) from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1) PPN        
                             , (select trim(to_char(sum(jumlah_potong))) ttl from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1 
                                and to_char(TANGGAL_FAKTUR_PAJAK,'MON-YYYY') = sph.MASA_PAJAK || '-' || sph.TAHUN_PAJAK) DISP_SPT  
                             , (select sum(jumlah_potong) ttl from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1 
                                and to_char(TANGGAL_FAKTUR_PAJAK,'MON-YYYY') = sph.MASA_PAJAK || '-' || sph.TAHUN_PAJAK) SPT 
                             , nvl((select sum(jumlah_potong) ttl from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1 
                                and to_char(TANGGAL_FAKTUR_PAJAK,'MON-YYYY') = sph.MASA_PAJAK || '-' || sph.TAHUN_PAJAK),0)
                                - 
                                nvl((select sum(jumlah_potong) from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1),0) SELISIH       
                             , trim(to_char(nvl((select sum(jumlah_potong) ttl from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1 
                                and to_char(TANGGAL_FAKTUR_PAJAK,'MON-YYYY') = sph.MASA_PAJAK || '-' || sph.TAHUN_PAJAK),0)
                                - 
                                nvl((select sum(jumlah_potong) from simtax_pajak_lines
                                where pajak_header_id = sph.pajak_header_id
                                and nvl(IS_CHEKLIST,0) = 1),0))) DISP_SELISIH
                        from simtax_pajak_headers sph
                        where nama_pajak = 'PPN WAPU'
                        and tahun_pajak = ".$tahun."
						and bulan_pajak = ".$bulan."
						and pembetulan_ke = ".$pembetulanKe."
                    	and status = 'APPROVED BY PUSAT') pajak
                        where skc.KODE_CABANG = pajak.kode_cabang (+)
                        and skc.kode_cabang in ('000','010','020','030','040','050','060','070','080','090','100','110','120')
                        union all
                                                     select '991', '' ,null,null,null,null,null,null from dual
                                                     union all
                                                     select '992', '' ,null,null,null,null,null,null from dual
                                                     union all
                                                     select '993', '' ,null,null,null,null,null,null from dual
                                                    order by 1
							";							
			
			$query 		= $this->db->query($queryExec);
			//$row1		= $query->row();

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 10; // Set baris pertama untuk isi tabel adalah baris ke 4
			$disp_spt = 0;								
			$disp_ppn = 0;								
			$disp_selisih = 0;									
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['DISP_SPT']);	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['DISP_PPN']);
				$selisih = $row['DISP_PPN'] - $row['DISP_SPT'];
				/*$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['DISP_SELISIH']);*/
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $selisih);

				$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
										
				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_nama);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
												
				$disp_spt  		= $disp_spt + $row['DISP_SPT'];								
				$disp_ppn   	= $disp_ppn + $row['DISP_PPN'];								
				$disp_selisih  	= $disp_selisih + ($row['DISP_SELISIH']*-1);
				
				$no++;
				$numrow++; // Tambah 1 setiap kali looping					
			}

			$cabang		= $this->session->userdata('kd_cabang');
			$queryTtd	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = 'DVP Pajak'
                            and nama_pajak = 'PPN WAPU'
                            and document_type = 'SPT Summary' 
                            and kode_cabang ='".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
			$query1 	= $this->db->query($queryTtd);
			$rowCount 	= $query1->num_rows();

		if($rowCount > 0){
			$rowb1		= $query1->row();

			$ttd 					= $rowb1->URL_TANDA_TANGAN;
			$petugas_ttd			= $rowb1->NAMA_PETUGAS_PENANDATANGAN;
			$jabatan_petugas_ttd	= $rowb1->JABATAN_PETUGAS_PENANDATANGAN;

		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$logo = $ttd; // Provide path to your logo file
		if(file_exists($logo)){
			$objDrawing->setPath($logo);  //setOffsetY has no effect
			$objDrawing->setCoordinates('D30');
			$objDrawing->setHeight(80); // logo height
			$objDrawing->setWorksheet($excel->getActiveSheet());
		}

		$excel->setActiveSheetIndex(0)->setCellValue('D29', $jabatan_petugas_ttd);		
		$excel->getActiveSheet()->getStyle('D29')->applyFromArray($style_colhead);

		$excel->setActiveSheetIndex(0)->setCellValue('D34', $petugas_ttd);		
		$excel->getActiveSheet()->getStyle('D34')->applyFromArray($style_colhead);
		}

				$excel->setActiveSheetIndex(0)->setCellValue('D28', "Jakarta,"." ".$masa." ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"

				$excel->getActiveSheet()->mergeCells('D30:D33');

				$excel->getActiveSheet()->getStyle('D28')->applyFromArray($style_colhead);
				$excel->getActiveSheet()->getStyle('D35')->applyFromArray($style_colhead);

		//end get detail
		//total
		$excel->setActiveSheetIndex(0)->setCellValue('A26', "JUMLAH DISETOR");
		$excel->getActiveSheet()->mergeCells('A'.$numrow.':B'.$numrow);		
		$excel->setActiveSheetIndex(0)->setCellValue('C26', $disp_spt);	
		$excel->setActiveSheetIndex(0)->setCellValue('D26', $disp_ppn);	
		$excel->setActiveSheetIndex(0)->setCellValue('E26', $disp_selisih);

		$excel->getActiveSheet()->getStyle('A26')->applyFromArray($style_colhead);
		$excel->getActiveSheet()->getStyle('C26')->applyFromArray($style_colhsl);
		$excel->getActiveSheet()->getStyle('D26')->applyFromArray($style_colhsl);
		$excel->getActiveSheet()->getStyle('E26')->applyFromArray($style_colhsl);

		$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
		
		//setahun
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(30); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(50); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("SPT SUMMARY PPN WAPU");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="SPT SUMMARY PPN WAPU.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function load_total_detail()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("pph/show_rekonsiliasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}	
		$ii			  = 0;
		$result['jml_tidak_dilaporkan']	= 0; $result['jml_tgl_akhir'] = 0; $result['jml_import_csv'] = 0;		
		$result['total'] = 0; 
		if($permission === true)
		{
			$hasil    = $this->Ppn_wapu_mdl->get_total_detail();			
			foreach($hasil->result_array() as $row)	{
						$ii++;	
						$result['total'] = $result['total'] + $row['JUMLAH_POTONG'];
						
						if ($row['KETERANGAN']=='Tidak Dilaporkan'){
							$result['jml_tidak_dilaporkan'] = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Tanggal 26 - 31 Bulan ini'){
							$result['jml_tgl_akhir'] = $row['JUMLAH_POTONG'];
						}
						if ($row['KETERANGAN']=='Import CSV'){
							$result['jml_import_csv'] = $row['JUMLAH_POTONG'];
						}						
				}
		}
		echo json_encode($result);
		$hasil->free_result();
    }		
		
}
