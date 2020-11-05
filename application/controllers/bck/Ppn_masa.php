<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ppn_masa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		if (!$this->ion_auth->logged_in())
		{
			redirect('dashboard', 'refresh');
		}

		$this->load->model('ppn_masa_mdl', 'ppn_masa');
		$this->load->model('cabang_mdl', 'cabang');
		$this->load->model('supplier_mdl', 'suplier');
		
		$this->kode_cabang  = $this->session->userdata('kd_cabang');
		$this->daftar_pajak = array("PPN MASUKAN", "PPN KELUARAN");
		/*ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);*/
	}


	/* Rekonsiliasi */

	public function rekonsiliasi_masukan(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/rekonsiliasi_masukan", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{
			
			$this->data['template_page'] = "ppn_masa/rekonsiliasi";
			$this->data['title']         = 'Rekonsiliasi PM';
			$this->data['subtitle']      = "Rekonsiliasi PM";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['nama_pajak']    = $this->daftar_pajak[0];
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}

	public function rekonsiliasi_keluaran(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/rekonsiliasi_keluaran", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{
			
			$this->data['template_page'] = "ppn_masa/rekonsiliasi";
			$this->data['title']         = 'Rekonsiliasi PK';
			$this->data['subtitle']      = "Rekonsiliasi PK";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['nama_pajak']    = $this->daftar_pajak[1];
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}

    function load_rekonsiliasi($header_id="")
	{

		$nama_pajak    = $this->input->post('_searchPpn');
		$kode_cabang   = $this->kode_cabang;
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		$category      = $this->input->post('_category');

		$orderby       = ($this->input->post('_orderby')) ? $this->input->post('_orderby') : '';

		$labelFakturStandar = "EFAKTUR";
		$labelDokumenLain   = "DOKUMEN LAIN";

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak = $this->daftar_pajak[0];
			$menu_pajak = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak = $this->daftar_pajak[1];
			$menu_pajak = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
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
			$where_condition = true;
			if($header_id != ""){
				$pajak_header_id = $header_id;
				$where_condition = false;
			}
			else{
				$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
				$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
			}
			
			$start               = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length              = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw                = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords            = (isset($_POST['search'])) ? $_POST['search']['value'] : '';
			
			$hasil               = $this->ppn_masa->get_rekonsiliasi($pajak_header_id, $nama_pajak, $category, $where_condition, $start, $length, $keywords, $orderby);
			
			$rowCount            = $hasil['jmlRow'];
			$query               = $hasil['query'];
			$dokumenReturn       = "";

			if($rowCount > 0){

				$dlChecked = "";
				$fsChecked = "";

				foreach($query->result_array() as $row)	{

					$checked              = ($row['IS_CHEKLIST'] == 1) ? "checked" : "";
					$pmk_checked          = ($row['IS_PMK'] == 1) ? "checked" : "";
					$pmk_disabled         = ($row['IS_CHEKLIST'] == 0) ? " disabled" : "";
					$disabledOnViewStatus = "";
					$pmk_checkbox         = "";

					if($header_id != ""){
						$disabledOnViewStatus = " disabled";
					}

					if($row['DL_FS'] != NULL){
						if($row['DL_FS'] == "dokumen_lain"){
							$dlChecked = " checked";
							$fsChecked = "";
						}
						elseif($row['DL_FS'] == "faktur_standar"){
							$dlChecked = "";
							$fsChecked = " checked";
						}
					}
					else{
						$dlChecked = "";
						$fsChecked = " checked";
					}

					if($category == "dokumen_lain"){

						$dokumenReturn[] = $row['DL_FS'];

						$checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='checkbox".$row['RNUM']."' category-id='dokumen_lain' class='checklist-2' type='checkbox' ".$checked." data-toggle='confirmation-singleton' data-singleton='true' data-id='".$row['PAJAK_LINE_ID']."'".$disabledOnViewStatus.">
										<label for='checkbox".$row['RNUM']."'>&nbsp;</label>
									</div>";

						$dl_fs		= "<div class='radio radio-info'>
	                                    <input class='radio_dlfs' type='radio' category-id='dokumen_lain' name='radio".$row['RNUM']."' id='fakstandar-".$row['RNUM']."' data-id='".$row['PAJAK_LINE_ID']."' value='faktur_standar'".$fsChecked.">
	                                    <label for='fakstandar-".$row['RNUM']."'>".$labelFakturStandar."</label>
	                                </div>
	                                <div class='radio radio-info'>
	                                    <input class='radio_dlfs' type='radio' category-id='dokumen_lain' name='radio".$row['RNUM']."' id='doklain-".$row['RNUM']."' data-id='".$row['PAJAK_LINE_ID']."' value='dokumen_lain'".$dlChecked.">
	                                    <label for='doklain-".$row['RNUM']."'>".$labelDokumenLain."</label>
	                                </div>";
	                    $pmk_checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='pmk_checkbox".$row['RNUM']."' category-id='dokumen_lain' class='pmkchecklist-2' type='checkbox' ".$pmk_checked." data-toggle='confirmation-singleton' data-singleton='true' data-id='".$row['PAJAK_LINE_ID']."'".$pmk_disabled.">
										<label for='pmk_checkbox".$row['RNUM']."'>&nbsp;</label>
									</div>";
					}
					else{

						$checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='checkbox".$row['RNUM']."_faktur' category-id='faktur_standar' class='checklist-1' type='checkbox' ".$checked." data-toggle='confirmation-singleton' data-singleton='true' data-id='".$row['PAJAK_LINE_ID']."'".$disabledOnViewStatus.">
										<label for='checkbox".$row['RNUM']."_faktur'>&nbsp;</label>
									</div>";

						$dl_fs     = "<div class='radio radio-info'>
	                                    <input class='radio_dlfs' type='radio' category-id='faktur_standar' name='radio_f".$row['RNUM']."' id='fakstandar_f-".$row['RNUM']."' data-id='".$row['PAJAK_LINE_ID']."' value='faktur_standar'".$fsChecked.">
	                                    <label for='fakstandar_f-".$row['RNUM']."'>".$labelFakturStandar."</label>
	                                </div>
	                                <div class='radio radio-info'>
	                                    <input class='radio_dlfs' type='radio' category-id='faktur_standar' name='radio_f".$row['RNUM']."' id='doklain_f-".$row['RNUM']."' data-id='".$row['PAJAK_LINE_ID']."' value='dokumen_lain'".$dlChecked.">
	                                    <label for='doklain_f-".$row['RNUM']."'>".$labelDokumenLain."</label>
	                                </div>";

                        $pmk_checkbox	= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='pmk_checkbox".$row['RNUM']."_faktur' category-id='faktur_standar' class='pmkchecklist-1' type='checkbox' ".$pmk_checked." data-toggle='confirmation-singleton' data-singleton='true' data-id='".$row['PAJAK_LINE_ID']."'".$pmk_disabled.">
										<label for='pmk_checkbox".$row['RNUM']."_faktur'>&nbsp;</label>
									</div>";
					}

					$tgl_dok_lain = ($row['TANGGAL_DOKUMEN_LAIN']) ? date("d/m/Y",strtotime($row['TANGGAL_DOKUMEN_LAIN'])) : '';
					$tgl_fakur    = ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y",strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : '';
					$tgl_approval = ($row['TANGGAL_APPROVAL']) ? date("d/m/Y",strtotime($row['TANGGAL_APPROVAL'])) : $tgl_dok_lain;

					$kd_jenis_transaksi = ($row['KD_JENIS_TRANSAKSI'] == "") ? "" : sprintf("%02d", $row['KD_JENIS_TRANSAKSI']);
					$fg_pengganti       = ($row['FG_PENGGANTI'] != "") ? $row['FG_PENGGANTI'] : $row['FG_PENGGANTI_NEW'];

			        if($nama_pajak == $this->daftar_pajak[0]){
			        	if($category == "dokumen_lain"){
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 2;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 5;
			        	}
			        	else{
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
			        	}
			        }
			        else{
			        	if($category == "dokumen_lain"){
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 5;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
			        	}
			        	else{
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
			        	}
			        }

              		$result['data'][] = array(
							'pajak_header_id'        => $row['PAJAK_HEADER_ID'],
							'pajak_line_id'          => $row['PAJAK_LINE_ID'],
							'vendor_id'              => $row['VENDOR_ID1'],
							'organization_id'        => $row['ORGANIZATION_ID'],
							'vendor_site_id'         => $row['VENDOR_SITE_ID'],
							'is_checklist'           => $row['IS_CHEKLIST'],
							'is_pmk'                 => $row['IS_PMK'],
							'akun_pajak'             => $row['AKUN_PAJAK'],
							'masa_pajak'             => $row['MASA_PAJAK'],
							'tahun_pajak'            => $row['TAHUN_PAJAK'],
							'checkbox'               => $checkbox,
							'pmk_checkbox'           => $pmk_checkbox,
							'dl_fs'                  => $dl_fs,
							'category'               => $row['DL_FS'],
							'no'                     => $row['RNUM'],
							'jenis_transaksi'        => $val_jenis_transaksi,
							'jenis_dokumen'          => $val_jenis_dokumen,
							'kd_jenis_transaksi'     => $kd_jenis_transaksi,
							'fg_pengganti'           => $fg_pengganti,
							'no_dokumen_lain_ganti'  => ($row['NO_DOKUMEN_LAIN_GANTI'] != "") ? $row['NO_DOKUMEN_LAIN_GANTI'] : $row['FAKTUR_ASAL'],
							'no_dokumen_lain'        => $row['NO_DOKUMEN_LAIN'],
							'tanggal_dokumen_lain'   => $tgl_dok_lain,
							'no_faktur_pajak'        => $row['NO_FAKTUR_PAJAK'],
							'tanggal_faktur_pajak'   => $tgl_fakur,
							'npwp'                   => $row['NPWP1'],
							'nama_wp'                => $row['VENDOR_NAME'],
							'alamat_wp'              => $row['ADDRESS_LINE1'],
							'invoice_number'         => $row['INVOICE_NUM'],
							'mata_uang'              => $row['INVOICE_CURRENCY_CODE'],
							'dpp'                    => number_format($row['DPP'],2,'.',','),
							'jumlah_potong'          => number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
							'jumlah_ppnbm'           => number_format($row['JUMLAH_PPNBM'],2,'.',','),
							'keterangan'             => $row['KETERANGAN'],
							'fapr'                   => $row['FAPR'],
							'tanggal_approval'       => $tgl_approval,
							'is_creditable'          => ($row['IS_CREDITABLE'] != "") ? $row['IS_CREDITABLE'] : "1",
							'id_keterangan_tambahan' => $row['ID_KETERANGAN_TAMBAHAN'],
							'fg_uang_muka'           => ($row['FG_UANG_MUKA'] != "") ? $row['FG_UANG_MUKA'] : 0,
							'uang_muka_dpp'          => ($row['UANG_MUKA_DPP'] != "") ? $row['UANG_MUKA_DPP'] : 0,
							'uang_muka_ppn'          => ($row['UANG_MUKA_PPN'] != "") ? $row['UANG_MUKA_PPN'] : 0,
							'uang_muka_ppnbm'        => ($row['UANG_MUKA_PPNBM'] != "") ? $row['UANG_MUKA_PPNBM'] : 0,
							'referensi'              => $row['REFERENSI'],
							'faktur_asal'            => $row['FAKTUR_ASAL'],
							'tanggal_faktur_asal'    => $row['TANGGAL_FAKTUR_ASAL'],
							'dpp_asal'               => $row['DPP_ASAL'],
							'ppn_asal'               => $row['PPN_ASAL'],
							'ntpn'                   => $row['NTPN'],
							'keterangan_gl'          => $row['KETERANGAN_GL']
							);
				}

				$query->free_result();

				if($category == "dokumen_lain" && !array_filter($dokumenReturn)){
					$result['data']            = "";
					$result['draw']            = "";
					$result['recordsTotal']    = 0;
					$result['recordsFiltered'] = 0;
				}
				else{
					$result['draw']            = $draw;
					$result['recordsTotal']    = $rowCount;
					$result['recordsFiltered'] = $rowCount;
				}
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

    	echo json_encode($result);
    }

    function load_total_summary($isChecklist){
		
		if($this->input->post('_searchCabang')){
			$kode_cabang = $this->input->post('_searchCabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}
		
		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		$category      = $this->input->post('_category');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak = $this->daftar_pajak[0];
			$menu_pajak = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak = $this->daftar_pajak[1];
			$menu_pajak = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/view_status", $this->session->userdata['menu_url'])){
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
			
			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

			$start               = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length              = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw                = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			
			$hasil               = $this->ppn_masa->get_total_summary($pajak_header_id, $nama_pajak, $isChecklist, $category, $start, $length);
			$rowCount            = $hasil['jmlRow'];
			$query               = $hasil['query'];

			if($rowCount > 0){

				foreach($query->result_array() as $row)	{

					$result['data'][] = array(
						'no'				=> $row['RNUM'],
						'pengelompokan'	    => $row['PENGELOMPOKAN'],
						'jml_potong'		=> "<h5><span class='label label-success'>".number_format($row['JML_POTONG'],2,'.',',')."</span></h5>"
						);
				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

    	echo json_encode($result);

    }


	function load_summary_rekonsiliasiAll1()
	{

		if($this->input->post('_searchCabang')){
			$kode_cabang = $this->input->post('_searchCabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		$category      = $this->input->post('_category');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak = $this->daftar_pajak[0];
			$menu_pajak = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak = $this->daftar_pajak[1];
			$menu_pajak = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/download_cetak", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/download_kompilasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_supervisor", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_pusat", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/view_status", $this->session->userdata['menu_url'])){
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
			if($kode_cabang == "all"){
				$kode_cabang = "";
			}

			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);

			if($kode_cabang == ""){
				$pajak_header_id     = array();
				foreach ($get_pajak_header_id as $key => $value) {
					$pajak_header_id[] = $value['PAJAK_HEADER_ID'];
				}
			}
			else{
				$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
			}

			$hasil_currency = $this->ppn_masa->get_currency($pajak_header_id, $category);
			$rowCount       = $hasil_currency['jmlRow'] ;
			$queryC         = $hasil_currency['query'];

			if($category == "Rekonsiliasi"){
				$status = "rekonsiliasi";
			}
			else{
				if($kode_cabang == "all"){
					$status = "download_kompilasi";
				}
				else{
					$status = "download";
				}
			}

			if ($rowCount>0) {
				
				foreach($queryC->result_array() as $rowC){

					$get_total_faktur       = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "total_faktur", $status);
					$total_faktur           = $get_total_faktur['query']->row()->JUMLAH_POTONG;
					$get_total_doklain      = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "total_doklain", $status);
					$total_doklain          = $get_total_doklain['query']->row()->JUMLAH_POTONG;
					$get_dibayarkan         = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak,"", $status);
					$dibayarkan             = $get_dibayarkan['query']->row()->JUMLAH_POTONG;
					$di_kreditkan           = 0;
					$tdk_di_kreditkan       = 0;
					$dipungut_sendiri       = 0;
					$dipungut_oleh_pemungut = 0;
					$tidak_pungut           = 0;
					$dibebaskan             = 0;
					$pmk78                  = 0;

					if($nama_pajak == "PPN MASUKAN"){
						// $get_z_percent        = $this->ppn_masa->get_z_percent($tahun_pajak-1);
						// $z_percent            = $get_z_percent->KOREKSI_PM;
						// $get_ppn_impor        = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "ppn_impor", $status);
						// $ppn_impor            = $get_ppn_impor['query']->row()->JUMLAH_POTONG;
						$di_kreditkan         = $dibayarkan;
						$get_tdk_di_kreditkan = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "tidak_di_kreditkan", $status);
						$tdk_di_kreditkan     = $get_tdk_di_kreditkan['query']->row()->JUMLAH_POTONG;
						$get_pmk78            = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "pmk", $status);
						$total_pmk            = $get_pmk78['query']->row()->JUMLAH_POTONG;
						$koreksi_pm           = $get_pmk78['query']->row()->KOREKSI_PM;
						$pmk78                = abs($koreksi_pm-$total_pmk);
					}
					else{
						$get_dipungut_sendiri       = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "dipungut_sendiri", $status);
						$dipungut_sendiri           = $get_dipungut_sendiri['query']->row()->JUMLAH_POTONG;
						$get_dipungut_oleh_pemungut = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "dipungut_oleh_pemungut", $status);
						$dipungut_oleh_pemungut     = $get_dipungut_oleh_pemungut['query']->row()->JUMLAH_POTONG;
						$get_tidak_pungut           = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "tidak_pungut", $status);
						$tidak_pungut               = $get_tidak_pungut['query']->row()->JUMLAH_POTONG;
						$get_dibebaskan             = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "dibebaskan", $status);
						$dibebaskan                 = $get_dibebaskan['query']->row()->JUMLAH_POTONG;
					}

					$saldoAkhir	= $rowC['SALDO_AWAL'] + ( $rowC['MUTASI_DEBIT'] -  $rowC['MUTASI_KREDIT'] );
		
					if ($saldoAkhir < 0 || $dibayarkan < 0){
						$selisih	= $saldoAkhir-$dibayarkan;
					} else {
						$selisih	= $saldoAkhir-$dibayarkan;
					}

					if($category == 'Rekonsiliasi'){

						$result['data'][] = array(
									'saldo_awal'        => '<input type="text" class="form-control input-sm text-right" id="saldoAwal" name="saldoAwal" placeholder="Saldo Awal" value="'.number_format($rowC['SALDO_AWAL'],2,'.',',').'">',
									
									'mutasi_debet'      => '<input type="text" class="form-control input-sm text-right" id="mutasiDebet" name="mutasiDebet" placeholder="Mutasi Debet" value="'.number_format($rowC['MUTASI_DEBIT'],2,'.',',').'">',
									
									'mutasi_kredit'     =>  '<input type="text" class="form-control input-sm text-right" id="mutasiKredit" name="mutasiKredit" placeholder="Mutasi Kredit" value="'.number_format($rowC['MUTASI_KREDIT'],2,'.',',').'">',
									
									'saldo_akhir'       => '<input type="text" class="form-control input-sm text-right" id="saldoAkhir" name="saldoAkhir" placeholder="Saldo Akhir" readonly value="'.number_format($saldoAkhir,2,'.',',').'">',
									
									'jumlah_dibayarkan' => '<input type="text" class="form-control input-sm text-right" id="jmlDibayarkan" name="jmlDibayarkan" placeholder="Jumlah DIbayarkan" readonly value="'.number_format($dibayarkan,2,'.',',').'">',
									
									'selisih'           => '<input type="text" class="form-control input-sm text-right" id="selisih" name="selisih" placeholder="Selisih" value="'.number_format($selisih,2,'.',',').'">',
									
									'ppn_beban'         => '<input type="text" class="form-control input-sm text-right" id="ppn_beban" name="ppn_beban" placeholder="PPN Di Bebankan" value="'.number_format($dibebaskan,2,'.',',').'">',
									
									'ppn_dipungut'         => '<input type="text" class="form-control input-sm text-right" id="ppn_dipungut" name="ppn_dipungut" placeholder="PPN Di Pungut Sendiri" value="'.number_format($dipungut_sendiri,2,'.',',').'">',
									
									'ppn_tdk_dipungut'         => '<input type="text" class="form-control input-sm text-right" id="ppn_tdk_dipungut" name="ppn_tdk_dipungut" placeholder="PPN Tidak Di Pungut Sendiri" value="'.number_format($tidak_pungut,2,'.',',').'">',

									'di_kreditkan'  => '<input type="text" class="form-control input-sm text-right" id="di_kreditkan" name="di_kreditkan" readonly value="'.number_format($di_kreditkan,2,'.',',').'">',

									'not_creditable'  => '<input type="text" class="form-control input-sm text-right" id="not_creditable" name="not_creditable" readonly value="'.number_format($tdk_di_kreditkan,2,'.',',').'">',

									'total_faktur'  => '<input type="text" class="form-control input-sm text-right" id="total_faktur" name="total_faktur" readonly value="'.number_format($total_faktur,2,'.',',').'">',

									'total_doklain'  => '<input type="text" class="form-control input-sm text-right" id="total_doklain" name="total_doklain" readonly value="'.number_format($total_doklain,2,'.',',').'">',

									'pmk'               => '<input type="text" class="form-control input-sm text-right" id="pmk" name="pmk" readonly value="'.number_format($pmk78,2,'.',',').'">',
									
									'ppn_dipungut_oleh_pemungut'         => '<input type="text" class="form-control input-sm text-right" id="ppn_dipungut_oleh_pemungut" name="ppn_dipungut_oleh_pemungut" placeholder="PPN Di Pungut Oleh Pemungut" value="'.number_format($dipungut_oleh_pemungut,2,'.',',').'">'
								);
					}
					else{

						 $result['data'][] = array(
									'saldo_awal'                 => number_format($rowC['SALDO_AWAL'],2,'.',','),
									'mutasi_debet'               => number_format($rowC['MUTASI_DEBIT'],2,'.',','),
									'mutasi_kredit'              => number_format($rowC['MUTASI_KREDIT'],2,'.',','),
									'saldo_akhir'                => number_format($saldoAkhir,2,'.',','),
									'jumlah_dibayarkan'          => number_format($dibayarkan,2,'.',','),
									'selisih'                    => number_format($selisih,2,'.',','),
									'ppn_beban'                  => number_format($dibebaskan,2,'.',','),
									'ppn_dipungut'               => number_format($dipungut_sendiri,2,'.',','),
									'ppn_tdk_dipungut'           => number_format($tidak_pungut,2,'.',','),
									'di_kreditkan'               => number_format($di_kreditkan,2,'.',','),
									'not_creditable'             => number_format($tdk_di_kreditkan,2,'.',','),
									'total_faktur'               => number_format($total_faktur,2,'.',','),
									'total_doklain'              => number_format($total_doklain,2,'.',','),
									'pmk'                        => number_format($pmk78,2,'.',','),
									'ppn_dipungut_oleh_pemungut' => number_format($dipungut_oleh_pemungut,2,'.',',')
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
		}
		echo json_encode($result);
    }

	function load_rincian()
	{

		if($this->input->post('_category') == "approval_cabang"){
			$kode_cabang = $this->kode_cabang;
		}
		else{
			$kode_cabang = "";
		}
		
		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_supervisor", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_pusat", $this->session->userdata['menu_url'])){
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

			if($this->input->post('_category') == "approval_cabang"){

				$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
				$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
			
				$hasil_currency = $this->ppn_masa->get_currency($pajak_header_id, $this->input->post('_category'));
				$rowCount       = $hasil_currency['jmlRow'] ;
				$queryC         = $hasil_currency['query'];	
				$ii = 0;

				if ($rowCount>0) {

					$data_header = $this->ppn_masa->get_data_header($pajak_header_id);
					$pmk78       = 0;
					$get_pmk78   = $this->ppn_masa->jumlah_ppn($pajak_header_id, $nama_pajak, "pmk");
					$total_pmk   = $get_pmk78['query']->row()->JUMLAH_POTONG;
					$koreksi_pm  = $get_pmk78['query']->row()->KOREKSI_PM;
					$pmk78       = abs($koreksi_pm-$total_pmk);

					foreach($queryC->result_array() as $rowC){

						$ii++;
						$hasil                 = $this->ppn_masa->get_rincian($kode_cabang, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
						$query1                = $hasil['queryExec'];
						
						$nama_cabang           = "";
						$jumlah_ppn_masukan    = 0;
						$jumlah_ppn_keluaran   = 0;
						$kompensasi            = 0;
						$kompensasi_sebelumnya = 0;
						$kurang_lebih          = 0;

						foreach($query1->result_array() as $row)	
						{
							$nama_cabang           = get_nama_cabang($row['KODE_CABANG']);
							$jumlah_ppn_masukan    = $row['PPN_MASUKAN'];
							$jumlah_ppn_keluaran   = $row['PPN_KELUARAN'];
							$kompensasi            = $row['KOMPENSASI_PPN'];
							$kompensasi_sebelumnya = $row['KOMPENSASI_SEBELUMNYA'];
							$kurang_lebih          = $jumlah_ppn_keluaran-($jumlah_ppn_masukan-$pmk78);
						}

						$result['data'][] = array(
								'no'                    => $ii,
								'nama_cabang'           => $nama_cabang,
								'jumlah_ppn_keluaran'   => number_format($jumlah_ppn_keluaran,0,'.',','),
								'jumlah_ppn_masukan'    => number_format($jumlah_ppn_masukan,0,'.',','),
								'pmk'                   => number_format($pmk78,0,'.',','),
								'kurang_lebih'          => number_format($kurang_lebih,0,'.',','),
								'kompensasi_sebelumnya' => number_format($kompensasi_sebelumnya,0,'.',','),
								'kompensasi'            => '<input type="text" class="form-control input-sm text-right" id="kompensasi" name="kompensasi" value="'.number_format($kompensasi,0,'.',',').'">',
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

			}
			else{
				$hasil    = $this->ppn_masa->get_rincian($kode_cabang, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
				$query1   = $hasil['queryExec'];
				$rowCount = $hasil['jmlRow'] ;
				$kompensasi            = 0;
				$kompensasi_sebelumnya = 0;

				$ii = 0;
				foreach($query1->result_array() as $row){
						
						$ii++;
						$nama_cabang           = get_nama_cabang($row['KODE_CABANG']);
						$jumlah_ppn_masukan    = $row['PPN_MASUKAN'];
						$jumlah_ppn_keluaran   = $row['PPN_KELUARAN'];
						$pmk78                 = abs($row['PMK78']);
						$pmk78                 = ceil(number_format($pmk78,0,'.',''));
						$kurang_lebih          = $jumlah_ppn_keluaran-($jumlah_ppn_masukan-$pmk78);
						$kurang_lebih          = ceil(number_format($kurang_lebih,0,'.',''));
						/*$kompensasi            = $row['KOMPENSASI_PPN'];
						$kompensasi_sebelumnya = $row['KOMPENSASI_SEBELUMNYA'];*/

						$result['data'][] = array(
								'no'                    => $ii,
								'nama_cabang'           => $nama_cabang,
								'jumlah_ppn_keluaran'   => number_format($jumlah_ppn_keluaran,0,'.','.'),
								'jumlah_ppn_masukan'    => number_format($jumlah_ppn_masukan,0,'.','.'),
								'pmk'                   => number_format($pmk78,0,'.','.'),
								'kurang_lebih'          => number_format($kurang_lebih,0,'.','.'),
								'kompensasi_sebelumnya' => number_format($kompensasi_sebelumnya,0,'.','.'),
								'kompensasi'            => number_format($kompensasi,0,'.','.')
							);
					}

					$result['draw']				= $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
					$result['recordsTotal']		= $rowCount;
					$result['recordsFiltered'] 	= $rowCount;
			}
		}
		echo json_encode($result);
    }

	function load_total_rincian()
	{	

		if($this->input->post('_category') == "approval_cabang"){
			$kode_cabang = $this->kode_cabang;
		}
		else{
			$kode_cabang = "";
		}
		
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		
		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_pusat", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']             = "";
		$result['draw']             = "";
		$result['recordsTotal']     = 0;
		$result['recordsFiltered']  = 0;
		
		$result['tot_ppn_keluaran'] = 0;
		$result['tot_ppn_masukan']  = 0;
		$result['tot_pmk']          = 0;
		$result['tot_kurang_lebih'] = 0;
		$result['kompensasi_lalu']  = 0;
		$result['pmk_tahunan']      = 0;
		$result['pbk']              = 0;
		$result['total_dibayar']    = 0;

		if($permission === true)
		{
			if($bulan_pajak == "1"){
				$tahun_pajak2 = $tahun_pajak-1;
				$bulan_pajak2 = 12;
			}
			else{
				$tahun_pajak2 = $tahun_pajak;
				$bulan_pajak2 = $bulan_pajak-1;
			}

			$kompensasi_lalu2 = 0;
			
			$hasil            = $this->ppn_masa->get_total_rincian($bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$hasi2            = $this->ppn_masa->get_total_rincian($bulan_pajak2, $tahun_pajak2, $pembetulan_ke);
			$row              = $hasil->row();
			$row2             = $hasi2->row();
			
			$get_pmk          = $this->ppn_masa->get_pmk_tahunan($bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$get_pmk2         = $this->ppn_masa->get_pmk_tahunan($bulan_pajak2, $tahun_pajak2, $pembetulan_ke);
			$row_pmk          = $get_pmk->row();
			$row_pmk2         = $get_pmk2->row();
			
			$pmk_tahunan      = ($row_pmk) ? $row_pmk->PMK : 0;
			$pbk_tahunan      = ($row_pmk) ? $row_pmk->PBK : 0;
			$pmk_tahunan2     = ($row_pmk2) ? $row_pmk2->PMK : 0;
			$pbk_tahunan2     = ($row_pmk2) ? $row_pmk2->PBK : 0;
			$kompensasi_lalu  = ($row_pmk) ? $row_pmk->KOMPENSASI_BLN_LALU : 0;
			$kompensasi_lalu2 = ($row_pmk2) ? $row_pmk2->KOMPENSASI_BLN_LALU : 0;
			
			$ppn_masukan   = $row->PPN_MASUKAN;
			$ppn_keluaran  = $row->PPN_KELUARAN;
			$pmk78         = $row->PMK78;
			$kurang_lebih  = $ppn_keluaran-($ppn_masukan-$pmk78);
			
			$ppn_masukan2  = $row2->PPN_MASUKAN;
			$ppn_keluaran2 = $row2->PPN_KELUARAN;
			$pmk782        = $row2->PMK78;
			$kurang_lebih2 = $ppn_keluaran2-($ppn_masukan2-$pmk782);

			// $total_dibayar2 = $kurang_lebih2 + $kompensasi_lalu2 - $pmk_tahunan2 - $pbk_tahunan2;

			if($kurang_lebih2 < 0){
				$kurang_bayar_lalu = $kurang_lebih2;
			}
			else{
				$kurang_bayar_lalu = 0;
			}

			$kurang_bayar_lalu = ($kompensasi_lalu != 0) ? $kompensasi_lalu : $kurang_bayar_lalu;
			
			$total_dibayar  = $kurang_lebih-$kurang_bayar_lalu-$pmk_tahunan-$pbk_tahunan;
			
			$result['tot_ppn_keluaran'] = $ppn_keluaran;
			$result['tot_ppn_masukan']  = $ppn_masukan;
			$result['tot_pmk']          = $pmk78;
			$result['tot_kurang_lebih'] = $kurang_lebih;
			$result['kompensasi_lalu']  = $kurang_bayar_lalu;
			$result['pmk_tahunan']      = $pmk_tahunan;
			$result['pbk']              = $pbk_tahunan;
			$result['total_dibayar']    = $total_dibayar;

		$hasil->free_result();

		}
		echo json_encode($result);
    }


    function load_pmk()
	{	

		if($this->input->post('_searchCabang')){
			$kode_cabang = $this->input->post('_searchCabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		$category      = $this->input->post('_category');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak         = $this->daftar_pajak[0];
			$menu_pajak         = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak         = $this->daftar_pajak[1];
			$menu_pajak         = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

		if($pajak_header_id <= 0){
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$start       = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length      = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw        = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords    = (isset($_POST['search'])) ? $_POST['search']['value'] : '';

			$hasil    = $this->ppn_masa->get_detail_summary($pajak_header_id, $category, true, $start, $length, $keywords);
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			$totselisih	= 0;

			$get_z_percent = $this->ppn_masa->get_z_percent($tahun_pajak-1);
			$row_z         = $get_z_percent['query']->result_array();
			$v_z_percent   = 95.08;

			if($tahun_pajak == "2019" && $bulan_pajak >= 3){
				$v_z_percent = 98.49;
			}
			$z_percent     = $v_z_percent/100;

			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	
						
						$dpp              = $row['DPP'];
						$ppn              = $row['JUMLAH_POTONG'];
						$spt_masa         = $ppn * $z_percent;
						$koreksi_pm       = $spt_masa - $ppn;
						$masa_tahun_pajak = ucfirst(strtolower($row['MASA_PAJAK'])) ."-".  substr($row['TAHUN_PAJAK'], 2,2);
						$cabang           = get_nama_cabang($row['KODE_CABANG']);

						if($kode_cabang == "000"){
							$z_percentField = '<a href="javascript:void(0)" class="z_percent" data-id="'.$row['PAJAK_LINE_ID'].'"data-uraian="'.$row['URAIAN_PEKERJAAN'].'" data-zpercent="'.$v_z_percent.'%" data-vendor="'.$row['VENDOR_NAME'].'" title="Click To Edit" style="color:#fff;"><i class="fa fa-edit"></i></a> <span> '.$v_z_percent.'%</span>';
						}
						else{
							$z_percentField = $v_z_percent."%";
						}

						$result['data'][] = array(
									'no'                   => $row['RNUM'],
									'vendor_name'          => $row['VENDOR_NAME'],
									'uraian_pekerjaan'     => '<a href="javascript:void(0)" class="uraian_pekerjaan" data-id="'.$row['PAJAK_LINE_ID'].'" data-uraian="'.$row['URAIAN_PEKERJAAN'].'" data-zpercent="'.$v_z_percent.'%" data-vendor="'.$row['VENDOR_NAME'].'" title="Click To Edit" style="color:#fff;"><i class="fa fa-edit"></i></a> <span> '.$row['URAIAN_PEKERJAAN'].'</span>',
									'no_faktur_pajak'      => $row['NO_FAKTUR'],
									'tanggal_faktur_pajak' => $row['TGL_FAKTUR'],
									'dpp'                  => number_format($dpp,2,'.',','),
									'jumlah_potong'        => number_format($ppn,2,'.',','),
									'z_percent'            => $z_percentField,
									'spt_masa'             => number_format($spt_masa,2,'.',','),
									'koreksi_pm'           => number_format($koreksi_pm,2,'.',','),
									'masa_tahun_pajak'     => $masa_tahun_pajak,
									'cabang'               => $cabang
									);
				}
				
				$query->free_result();
				
				$result['draw']            = $_POST['draw']=($_POST['draw'])?$_POST['draw']:0;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			} else {
				$result['data'] 			= "";
				$result['draw']				= "";
				$result['recordsTotal']		= 0;
				$result['recordsFiltered'] 	= 0;
			}
			$query->free_result();

		}

		echo json_encode($result);
    }


    function load_ntpn()
	{	
		
		$kode_cabang = $this->kode_cabang;
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');

		if($this->ion_auth->is_admin()){
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

			$start       = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length      = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw        = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords    = (isset($_POST['search'])) ? $_POST['search']['value'] : '';

			$hasil    = $this->ppn_masa->get_ntpn($bulan_pajak, $tahun_pajak, $pembetulan_ke, $start, $length, $keywords);

			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	

						$result['data'][] = array(
									'no'            => $row['RNUM'],
									'id'            => $row['ID'],
									'pembetulan'    => $row['PEMBETULAN'],
									'bulan'         => $row['BULAN'],
									'nama_bulan'    => get_masa_pajak($row['BULAN'],"id", true),
									'tahun'         => $row['TAHUN'],
									'tanggal_setor' => $row['TANGGAL_SETOR'],
									'ntpn'          => $row['NTPN'],
									'bank'          => $row['BANK'],
									'tanggal_lapor' => $row['TANGGAL_LAPOR']
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
			$query->free_result();

		}

		echo json_encode($result);
    }


    function load_detail_summary()
	{	

		if($this->input->post('_searchCabang')){
			$kode_cabang = $this->input->post('_searchCabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		$category      = $this->input->post('_category');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak         = $this->daftar_pajak[0];
			$menu_pajak         = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak         = $this->daftar_pajak[1];
			$menu_pajak         = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

		if($pajak_header_id <= 0){
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{

			$start       = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length      = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw        = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords    = (isset($_POST['search'])) ? $_POST['search']['value'] : '';

			$hasil    = $this->ppn_masa->get_detail_summary($pajak_header_id, $category, false, $start, $length, $keywords);
			$rowCount = $hasil['jmlRow'] ;
			$query    = $hasil['query'];
			$totselisih	= 0;
			if ($rowCount>0){
				$ii	=	0;
				foreach($query->result_array() as $row)	{
						$ii++;	
						$totselisih = $totselisih + $row['JUMLAH_POTONG'];
						$result['data'][] = array(									
									'no'                   => $row['RNUM'],
									'vendor_name'          => $row['VENDOR_NAME'],
									'npwp1'                => $row['NPWP1'],
									'address_line1'        => $row['ADDRESS_LINE1'],
									'no_faktur_pajak'      => $row['NO_FAKTUR_PAJAK'],
									'tanggal_faktur_pajak' => $row['TANGGAL_FAKTUR_PAJAK'],
									'no_dokumen_lain'      => $row['NO_DOKUMEN_LAIN'],
									'tanggal_dokumen_lain' => $row['TANGGAL_DOKUMEN_LAIN'],
									'dpp'                  => $row['DPP'],									
									'jumlah_potong'        => number_format($row['JUMLAH_POTONG'],2,'.',','),
									'keterangan'           => '<input type="text" class="ket_detail" name="keterangan_detail" data-id="'.$row['PAJAK_LINE_ID'].'" value="'.$row['KETERANGAN'].'">',
									'totselisih'           => number_format($totselisih,2,'.',',')
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
			$query->free_result();

		}

		echo json_encode($result);
    }


	function load_total_detail_summary()
	{	

		if($this->input->post('_searchCabang')){
			$kode_cabang = $this->input->post('_searchCabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');
		$category      = $this->input->post('_category');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak         = $this->daftar_pajak[0];
			$menu_pajak         = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak         = $this->daftar_pajak[1];
			$menu_pajak         = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		$ii = 0;
		$result['jml_tidak_dilaporkan']	= 0;
		$result['jml_tgl_akhir'] = 0;
		$result['jml_import_csv'] = 0;		
		$result['total'] = 0; 
		if($permission === true)
		{
			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

			$hasil    = $this->ppn_masa->get_total_detail_summary($pajak_header_id, $category);			
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
		$hasil->free_result();

		}
		echo json_encode($result);
    }

	function load_total_pmk()
	{	

		if($this->input->post('_searchCabang')){
			$kode_cabang = $this->input->post('_searchCabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak         = $this->daftar_pajak[0];
			$menu_pajak         = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak         = $this->daftar_pajak[1];
			$menu_pajak         = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		$ii = 0;
		$result['tot_dpp']       = 0;
		$result['tot_ppn']       = 0;
		$result['tot_z_percent'] = 0;
		$result['tot_spt']       = 0;
		$result['tot_koreksi']   = 0;

		if($permission === true)
		{
			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

			$hasil    = $this->ppn_masa->get_total_pmk($pajak_header_id);

			$total_dpp        = ($hasil) ? $hasil->DPP : 0;
			$total_ppn        = ($hasil) ? $hasil->JUMLAH_POTONG : 0;
			$total_z_percent  = ($hasil) ? round($hasil->Z_PERCENT) : 0;
			$total_spt_masa   = ($hasil) ? $hasil->SPT_MASA : 0;
			$total_koreksi_pm = ($hasil) ? $hasil->KOREKSI_PM : 0;

			$result['tot_dpp']       = $total_dpp;
			$result['tot_ppn']       = $total_ppn;
			$result['tot_z_percent'] = $total_z_percent;
			$result['tot_spt']       = $total_spt_masa;
			$result['tot_koreksi']   = number_format(round($total_koreksi_pm));

		}
		echo json_encode($result);
    }


	function save_saldo_awal()
	{

		$kode_cabang   = $this->session->userdata('kd_cabang');
		$user_name     = $this->session->userdata('identity');				
		$nama_pajak    = $this->input->post('pajak');
		$bulan_pajak   = $this->input->post('bulan');
		$tahun_pajak   = $this->input->post('tahun');
		$pembetulan_ke = $this->input->post('pembetulan');

		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;


		$saldo            =($this->input->post('vsal')) ? str_replace(",","", $this->input->post('vsal')) : 0 ;
		$mutasiD          = ($this->input->post('vmtsd')) ? str_replace(",","", $this->input->post('vmtsd')) : 0 ;
		$mutasiK          = ($this->input->post('vmtsk')) ? str_replace(",","", $this->input->post('vmtsk')) : 0 ;
		$pmk78            = ($this->input->post('pmk')) ? str_replace(",","", $this->input->post('pmk')) : 0 ;
		$ppn_beban        = ($this->input->post('ppn_beban')) ? str_replace(",","", $this->input->post('ppn_beban')) : 0 ;
		$ppn_dipungut     = ($this->input->post('ppn_dipungut')) ? str_replace(",","", $this->input->post('ppn_dipungut')) : 0 ;
		$ppn_tdk_dipungut = ($this->input->post('ppn_tdk_dipungut')) ? str_replace(",","", $this->input->post('ppn_tdk_dipungut')) : 0 ;

		$data = array(
					'SALDO_AWAL'         => $saldo,
					'MUTASI_KREDIT'      => $mutasiK,
					'MUTASI_DEBIT'       => $mutasiD,
					'PMK78'              => $pmk78,
					'PPN_BEBAN'          => $ppn_beban,
					'PPN_DIPUNGUT'       => $ppn_dipungut,
					'PPN_TIDAK_DIPUNGUT' => $ppn_tdk_dipungut
					);

		$data	= $this->ppn_masa->update_saldo($pajak_header_id, $data);

		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	/*function save_kompensasi()
	{
		$kode_cabang   = $this->session->userdata('kd_cabang');
		$user_name     = $this->session->userdata('identity');				
		$nama_pajak    = $this->input->post('pajak');
		$bulan_pajak   = $this->input->post('bulan');
		$tahun_pajak   = $this->input->post('tahun');
		$pembetulan_ke = $this->input->post('pembetulan');

		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

		$kompensasi = $this->input->post('kompensasi');

		$data = array(
					'KOMPENSASI_PPN' => $kompensasi
					);

		$data	= $this->ppn_masa->update_saldo($pajak_header_id, $data);

		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}*/

	function save_tahunan()
	{
		$bulan_pajak   = $this->input->post('bulan');
		$tahun_pajak   = $this->input->post('tahun');
		$pembetulan_ke = $this->input->post('pembetulan');

		$pmk          = ($this->input->post('pmk')) ? str_replace(",", "", $this->input->post('pmk')) : 0;
		$pbk          = ($this->input->post('pbk')) ? str_replace(",", "", $this->input->post('pbk')) : 0;
		$kompensasi   = ($this->input->post('kompensasi')) ? simtax_trim($this->input->post('kompensasi')) : 0;
		// $kurang_lebih = ($this->input->post('kurang_lebih')) ? str_replace(",", "", $this->input->post('kurang_lebih')) : 0;

		$data = array(
					'PMK'                 => $pmk,
					'PBK'                 => $pbk,
					'KOMPENSASI_BLN_LALU' => $kompensasi
					);

		$data	= $this->ppn_masa->update_tahunan($data, $bulan_pajak, $tahun_pajak, $pembetulan_ke);

		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function save_keterangan()
	{		
	
		$id  = $this->input->post('id');
		$ket = $this->input->post('ket');

		$data = array(
					'KETERANGAN_TDK_DILAPORKAN' => $ket
					);

		$data	= $this->ppn_masa->update_line($id, $data);

		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function save_uraian_pekerjaan()
	{		
		
		$id                 = $this->input->post('uraian_id');
		$uraian_pekerjaan   = $this->input->post('uraian_pekerjaan');

		$update = true;

		if($this->kode_cabang == "000"){
			$tahun              = $this->input->post('tahun_z_percent_v');
			$terutang_ppn       = $this->input->post('terutang_ppn');
			$tidak_terutang_ppn = $this->input->post('tidak_terutang_ppn');
			$update             = $this->ppn_masa->insert_z_percent($tahun, $terutang_ppn, $tidak_terutang_ppn);
		}

		$data = array(
					'URAIAN_PEKERJAAN' => $uraian_pekerjaan
					);

		$data	= $this->ppn_masa->update_line($id, $data);

		if($data && $update){
			echo '1';
		} else {
			echo '0';
		}
	}

    function load_total_rekonsiliasi(){
		
		if($this->input->post('kode_cabang')){
			$kode_cabang = $this->input->post('kode_cabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$nama_pajak    = $this->input->post('pajak');
		$bulan_pajak   = $this->input->post('bulan');
		$tahun_pajak   = $this->input->post('tahun');
		$pembetulan_ke = $this->input->post('pembetulan');
		$category      = $this->input->post('category');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak         = $this->daftar_pajak[0];
			$menu_pajak         = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak         = $this->daftar_pajak[1];
			$menu_pajak         = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/view_status", $this->session->userdata['menu_url'])){
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
			
			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

			$data                = $this->ppn_masa->get_total_rekonsiliasi($pajak_header_id, $category);

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
    	}

    	echo json_encode($result);

		$data->free_result(); 

    }

	
	function check_dlfs()
	{

		$nama_pajak  = $this->input->post('pajak');

		if($nama_pajak == $this->daftar_pajak[0]){
			$nama_pajak         = $this->daftar_pajak[0];
			$menu_pajak         = "ppn_masa/rekonsiliasi_masukan";
		}
		else{
			$nama_pajak         = $this->daftar_pajak[1];
			$menu_pajak         = "ppn_masa/rekonsiliasi_keluaran";
		}

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array($menu_pajak, $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}


		if($permission === true){

			$id                   = $this->input->post('line_id');
			$jenis_dokumen        = $this->input->post('jenis_dokumen');
			$get_pajak_line       = $this->ppn_masa->get_pajak_lines_by_id($id);
			$no_dokumen_lain      = "";
			$tanggal_dokumen_lain = "";
			$no_faktur_pajak      = "";
			$tanggal_faktur_pajak = "";

			if($jenis_dokumen == "dokumen_lain"){
				if($get_pajak_line->NO_FAKTUR_PAJAK != NULL || $get_pajak_line->NO_FAKTUR_PAJAK != NULL){
					$no_dokumen_lain      = $get_pajak_line->NO_FAKTUR_PAJAK;
					$tanggal_dokumen_lain = ($get_pajak_line->TANGGAL_FAKTUR_PAJAK) ? date("Y-m-d", strtotime($get_pajak_line->TANGGAL_FAKTUR_PAJAK)) : '' ;
				}
				else{
					$no_dokumen_lain      = $get_pajak_line->NO_DOKUMEN_LAIN;
					$tanggal_dokumen_lain = ($get_pajak_line->TANGGAL_DOKUMEN_LAIN) ? date("Y-m-d", strtotime($get_pajak_line->TANGGAL_DOKUMEN_LAIN)) : '' ;
				}
			}
			else{
				if($get_pajak_line->NO_FAKTUR_PAJAK != NULL || $get_pajak_line->NO_FAKTUR_PAJAK != NULL){
					$no_faktur_pajak      = $get_pajak_line->NO_FAKTUR_PAJAK;
					$tanggal_faktur_pajak = ($get_pajak_line->TANGGAL_FAKTUR_PAJAK) ? date("Y-m-d", strtotime($get_pajak_line->TANGGAL_FAKTUR_PAJAK)) : '' ;
				}
				else{
					$no_faktur_pajak      = $get_pajak_line->NO_DOKUMEN_LAIN;
					$tanggal_faktur_pajak = ($get_pajak_line->TANGGAL_DOKUMEN_LAIN) ? date("Y-m-d", strtotime($get_pajak_line->TANGGAL_DOKUMEN_LAIN)) : '' ;
				}
			}

			$data = array(
									'DL_FS'   => $jenis_dokumen,
									'NO_DOKUMEN_LAIN' => $no_dokumen_lain,
									'NO_FAKTUR_PAJAK' => $no_faktur_pajak
								);

			if($this->ppn_masa->update_rekonsiliasi($id, $data, $tanggal_dokumen_lain, $tanggal_faktur_pajak)){
				echo '1';
			} else {
				echo '0';
			}

		}
		else{
			echo '0';
		}
		
	}

	
	function check_rekonsiliasi($category="checklist")
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/rekonsiliasi_masukan", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/rekonsiliasi_keluaran", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === true){

			$id      = $this->input->post('line_id');
			$ischeck = $this->input->post('ischeck');

			if($category == "checklist"){
				$data = array(
								'IS_CHEKLIST' => $ischeck,
							);
			}else{
				$data = array(
								'IS_PMK' => $ischeck,
							);
			}

			$update = $this->ppn_masa->update_rekonsiliasi($id, $data);

			if($update){
				echo '1';
			} else {
				echo '0';
			}

		}
		else{
			echo '0';
		}
		
	}

	function get_selectAll()
	{
		$data	= $this->ppn_masa->action_get_selectAll();
		if($data){			
			echo '1';
		} else {			
			echo '0';
		}
	}

	function export_format_csv(){

		ini_set('memory_limit', '-1');

		$this->load->helper('csv_helper');
		
		$kode_cabang    = $this->kode_cabang;
		$nama_pajak     = $this->input->get('pajak');
		$bulan_pajak    = $this->input->get('masa');
		$tahun_pajak    = $this->input->get('tahun');
		$pembetulan_ke  = $this->input->get('pembetulan');
		$category       = $this->input->get('category');
		$dilaporkan     = $this->input->get('dilaporkan');
		
		$date           = date("d", time());
		$dokumen_lain   = array();
		$faktur_standar = array();
		
		$nama_pajak     = str_replace("%20", " ", $nama_pajak);
		$adaEfaktur     = false;
		
		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

		$data                = $this->ppn_masa->get_data_csv($pajak_header_id, $nama_pajak, $category, $dilaporkan);

        if($nama_pajak == "PPN MASUKAN"){
        	if($category == "dokumen_lain"){
        		$tileDMFM = "DK_DM";
				$titleAkunBeban = "AKUN_BEBAN";
        	}
        	else{
        		$tileDMFM = "FM";
				$titleAkunBeban = "AKUN_BEBAN";
        	}
        }
        else{
        	if($category == "dokumen_lain"){
        		$tileDMFM = "DK_DM";
				$titleAkunBeban = "AKUN_PENDAPATAN";
        	}
        	else{
        		$tileDMFM = "FK";
				$titleAkunBeban = "AKUN_PENDAPATAN";
        	}
        }

		$title_dokumen_lain = array(
                            	'PAJAK_LINE_ID',
                            	$tileDMFM,
                            	$titleAkunBeban,
                            	'JENIS_TRANSAKSI',
                            	'JENIS_DOKUMEN',
                            	'KD_JNS_TRANSAKSI',
                            	'FG_PENGGANTI',
                            	'NOMOR_DOK_LAIN_GANTI',
                            	'NOMOR_DOK_LAIN',
                            	'TANGGAL_DOK_LAIN',
                            	'MASA_PAJAK',
                            	'TAHUN_PAJAK',
                            	'NPWP',
                            	'NAMA',
                            	'ALAMAT_LENGKAP',
                            	'INVOICE_NUMBER',
                            	'MATA_UANG',
                            	'JUMLAH_DPP',
                            	'JUMLAH_PPN',
                            	'JUMLAH_PPNBM',
                            	'KETERANGAN',
								'FAPR',
								'TGL_APPROVAL',
								'NOMOR_FAKTUR_ASAL',
                            	'TANGGAL_FAKTUR_ASAL',
                            	'DPP_ASAL',
								'PPN_ASAL',
								'KETERANGAN_1'
							);

		if($nama_pajak == "PPN MASUKAN"){

			$title_faktur_standar = array(
                        				'PAJAK_LINE_ID',
										$tileDMFM,
										$titleAkunBeban,
		                            	'KD_JENIS_TRANSAKSI',
		                            	'FG_PENGGANTI',
										'NOMOR_FAKTUR',
										'MASA_PAJAK',
										'TAHUN_PAJAK',
										'TANGGAL_FAKTUR',
										'NPWP',
										'NAMA',
										'ALAMAT_LENGKAP',
										'INVOICE_NUMBER',
										'MATA_UANG',
										'JUMLAH_DPP',
										'JUMLAH_PPN',
										'JUMLAH_PPNBM',
										'IS_CREDITABLE',
										'NOMOR_FAKTUR_ASAL',
		                            	'TANGGAL_FAKTUR_ASAL',
		                            	'DPP_ASAL',
										'PPN_ASAL',
										'KETERANGAN_1'
									);
		}
		else{

			$title_faktur_standar = array(
                        				'PAJAK_LINE_ID',
                            			$tileDMFM,
                            			$titleAkunBeban,
		                            	'KD_JENIS_TRANSAKSI',
		                            	'FG_PENGGANTI',
										'NOMOR_FAKTUR',
										'MASA_PAJAK',
										'TAHUN_PAJAK',
										'TANGGAL_FAKTUR',
										'NPWP',
										'NAMA',
										'ALAMAT_LENGKAP',
										'INVOICE_NUMBER',
										'MATA_UANG',
										'JUMLAH_DPP',
										'JUMLAH_PPN',
										'JUMLAH_PPNBM',
										'ID_KETERANGAN_TAMBAHAN',
										'FG_UANG_MUKA',
										'UANG_MUKA_DPP',
										'UANG_MUKA_PPN',
										'UANG_MUKA_PPNBM',
										'REFERENSI',
										'NOMOR_FAKTUR_ASAL',
		                            	'TANGGAL_FAKTUR_ASAL',
		                            	'DPP_ASAL',
										'PPN_ASAL',
										'KETERANGAN_1'
									);
		}

    	array_push($dokumen_lain, $title_dokumen_lain);
		array_push($faktur_standar, $title_faktur_standar);
							
        if (!empty($data)) {

        	foreach($data->result_array() as $row)	{

        		$npwp = ($row['NPWP1'] == "") ? "" : format_npwp($row['NPWP1']);

				$tanggal_dokumen_lain = ($row['TANGGAL_DOKUMEN_LAIN']) ? date("d/m/Y", strtotime($row['TANGGAL_DOKUMEN_LAIN'])) : '';
				$tanggal_approval     = ($row['TANGGAL_DOKUMEN_LAIN']) ? date("Ymd", strtotime($row['TANGGAL_DOKUMEN_LAIN']))."000000" : '';
				$tanggal_faktur       = ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : '';
				$tanggal_faktur_asal  = ($row['TANGGAL_FAKTUR_ASAL']) ? date("d/m/Y", strtotime($row['TANGGAL_FAKTUR_ASAL'])) : '';
				
				$kd_jenis_transaksi   = ($row['KD_JENIS_TRANSAKSI'] == "") ? "" : sprintf("%02d", $row['KD_JENIS_TRANSAKSI']);
				$fg_pengganti         = ($row['FG_PENGGANTI'] != "") ? $row['FG_PENGGANTI'] : $row['FG_PENGGANTI_NEW'];

		        if($nama_pajak == $this->daftar_pajak[0]){
		        	if($category == "dokumen_lain"){
						$val_jenis_transaksi    = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 2;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 5;
		        	}
		        	else{
						$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
		        	}
		        }
		        else{
		        	if($category == "dokumen_lain"){
						$val_jenis_transaksi    = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 5;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
		        	}
		        	else{
						$val_jenis_transaksi    = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
		        	}
		        }

				$no_dokumen_lain_ganti = ($row['NO_DOKUMEN_LAIN_GANTI'] != "") ? $row['NO_DOKUMEN_LAIN_GANTI'] : $row['FAKTUR_ASAL'];
				
	        	array_push($dokumen_lain,
						array(
								$row['PAJAK_LINE_ID'],
								($nama_pajak == "PPN MASUKAN") ? "DM" : "DK",
								$row['AKUN_PAJAK'],
								$val_jenis_transaksi,
								$val_jenis_dokumen,
								$kd_jenis_transaksi,
								$fg_pengganti,
								$no_dokumen_lain_ganti,
								$row['NO_DOKUMEN_LAIN'],
								$tanggal_dokumen_lain,
								$row['BULAN_PAJAK'],
								$row['TAHUN_PAJAK'],
								$npwp,
								$row['VENDOR_NAME'],
								$row['ADDRESS_LINE1'],
								$row['INVOICE_NUM'],
								$row['INVOICE_CURRENCY_CODE'],
								$row['DPP'],
								$row['JUMLAH_POTONG_PPN'],
								($row['JUMLAH_PPNBM'] != "") ? $row['JUMLAH_PPNBM'] : 0,
								$row['KETERANGAN'],
								$row['FAPR'],
								$tanggal_approval,
								$row['FAKTUR_ASAL'],
								$tanggal_faktur_asal,
								$row['DPP_ASAL'],
								$row['PPN_ASAL'],
								$row['KETERANGAN_GL']
							)
					);

				if($nama_pajak == "PPN MASUKAN"){
					array_push($faktur_standar,
							array(
									$row['PAJAK_LINE_ID'],
									"FM",
									$row['AKUN_PAJAK'],
									$kd_jenis_transaksi,
									$fg_pengganti,
									$row['NO_FAKTUR_PAJAK'],
									$row['BULAN_PAJAK'],
									$row['TAHUN_PAJAK'],
									$tanggal_faktur,
									$npwp,
									$row['VENDOR_NAME'],
									$row['ADDRESS_LINE1'],
									$row['INVOICE_NUM'],
									$row['INVOICE_CURRENCY_CODE'],
									$row['DPP'],
									$row['JUMLAH_POTONG_PPN'],
									($row['JUMLAH_PPNBM'] != "") ? $row['JUMLAH_PPNBM'] : 0,
									($row['IS_CREDITABLE'] != "") ? $row['IS_CREDITABLE'] : 1,
									$row['FAKTUR_ASAL'],
									$tanggal_faktur_asal,
									$row['DPP_ASAL'],
									$row['PPN_ASAL'],
									$row['KETERANGAN_GL']
								)
						);
				}
				else{

					if($row['E_FAKTUR'] != 'keluaran'){
						array_push($faktur_standar,
							array(
									$row['PAJAK_LINE_ID'],
									"FK",
									$row['AKUN_PAJAK'],
									$kd_jenis_transaksi,
									$fg_pengganti,
									$row['NO_FAKTUR_PAJAK'],
									$row['BULAN_PAJAK'],
									$row['TAHUN_PAJAK'],
									$tanggal_faktur,
									$npwp,
									$row['VENDOR_NAME'],
									$row['ADDRESS_LINE1'],
									$row['INVOICE_NUM'],
									$row['INVOICE_CURRENCY_CODE'],
									$row['DPP'],
									$row['JUMLAH_POTONG_PPN'],
									($row['JUMLAH_PPNBM'] != "") ? $row['JUMLAH_PPNBM'] : 0,
									$row['ID_KETERANGAN_TAMBAHAN'],
									($row['FG_UANG_MUKA'] != "") ? $row['FG_UANG_MUKA'] : 0,
									($row['UANG_MUKA_DPP'] != "") ? $row['UANG_MUKA_DPP'] : 0,
									($row['UANG_MUKA_PPN'] != "") ? $row['UANG_MUKA_PPN'] : 0,
									($row['UANG_MUKA_PPNBM'] != "") ? $row['UANG_MUKA_PPNBM'] : 0,
									$row['REFERENSI'],
									$row['FAKTUR_ASAL'],
									$tanggal_faktur_asal,
									$row['DPP_ASAL'],
									$row['PPN_ASAL'],
									$row['KETERANGAN_GL']
							)
						);
					}
					else{
						$adaEfaktur = true;
					}
				}
			}
        }

		$masa_pajak = get_masa_pajak($bulan_pajak);

		if($adaEfaktur == true){
			$nama_cabang = str_replace(" ","",get_nama_cabang($kode_cabang));
			$masa_pajak = strtoupper($masa_pajak);

			$path      = "./uploads/importCsv/ppn_masa/efaktur_keluaran/".$tahun_pajak."/".$bulan_pajak."_".$masa_pajak."/";

			$file_url = $path."Ekspor_Efaktur_Keluaran_".$tahun_pajak."_".$masa_pajak."_".$pembetulan_ke."_".$nama_cabang.".csv";

		
			if(file_exists($file_url)){
				header('Content-Type: application/octet-stream');
				header("Content-Transfer-Encoding: Binary"); 
				header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
				readfile($file_url);
			}
			
		}else{

			if($dilaporkan == "tidak_dilaporkan"){
				$addedFileName = "_(Tidak di laporkan)";
			}
			elseif($dilaporkan == "pmk"){
				$addedFileName = "_(PMK)";
			}
			elseif($dilaporkan == "summary"){
				$addedFileName = "_(Summary)";
			}
			else{
				$addedFileName = "";
			}

	        if($nama_pajak == "PPN MASUKAN"){
	        	if($category == "dokumen_lain"){
	        		convert_to_csv($dokumen_lain, 'Ekspor_Dokumen_Masukan_'.$tahun_pajak.'_'.$masa_pajak.$addedFileName.'.csv', ';');
	        	}
	        	else{
	        		convert_to_csv($faktur_standar, 'Ekspor_Efaktur_Masukan_'.$tahun_pajak.'_'.$masa_pajak.$addedFileName.'.csv', ';');
	        	}
	        }
	        else{
	        	if($category == "dokumen_lain"){
	        		convert_to_csv($dokumen_lain, 'Ekspor_Dokumen_Keluaran_'.$tahun_pajak.'_'.$masa_pajak.$addedFileName.'.csv', ';');
	        	}
	        	else{
	       			convert_to_csv($faktur_standar, 'Ekspor_Efaktur_Keluaran_'.$tahun_pajak.'_'.$masa_pajak.$addedFileName.'.csv', ';');
	        	}
	        }
        }
	}

	function import_csv(){

		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		
		if (!empty($_FILES['file_csv']['name'])){
			
			$path                = $_FILES['file_csv']['name'];
			$ext                 = pathinfo($path, PATHINFO_EXTENSION);
			$nama_pajak          = $this->input->post('uplPpn');
			$bulan_pajak         = $this->input->post('uplBulan');
			$tahun_pajak         = $this->input->post('uplTahun');
			$pembetulan_ke       = $this->input->post('uplPembetulan');
			$kode_cabang         = $this->kode_cabang;
			$masa_pajak          = strtoupper(get_masa_pajak($bulan_pajak, "id"));
			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
			$kategori_dokumen    = strtoupper($this->input->post('kategori_dokumen'));
			$date                = date("Y-m-d_H:i", time());
			$st                  = "false";
			$ket                 = "";

			if($kategori_dokumen == ""){
				$ket = "Pilih Tipe Dokumen";
				$result['status']     = $st;
				$result['keterangan'] = $ket;

				echo json_encode($result);
				die();
			}

			if ($ext=='csv'){

				if($kategori_dokumen == "EFAKTUR_KELUARAN"){
					$path      = "importCsv/ppn_masa/efaktur_keluaran/".$tahun_pajak."/".$bulan_pajak."_".$masa_pajak."/";
					$pathOri   = "./uploads/".$path;
					$file_name = "Ekspor_Efaktur_Keluaran_".$tahun_pajak."_".$masa_pajak."_".$pembetulan_ke."_".str_replace(" ", "", get_nama_cabang($kode_cabang));
					if (!file_exists($pathOri)) {
					    mkdir($pathOri, 0777, true);
					}
				}
				else{
					$path      = "importCsv/ppn_masa/";
					$pathOri = "./uploads/".$path;
					$file_name = "fileCSV_".str_replace(" ","_",$nama_pajak)."_".$date;	
				}

				if($upl = $this->_upload('file_csv', $path, $file_name, 'csv', $ext)){
					$row       = 1;
					$handle    = fopen($pathOri.$file_name.".".$ext, "r");
					$updateCSV = array();

					if($kategori_dokumen == "EFAKTUR_KELUARAN"){
						$delimiter = ",";
					}
					else{
						$delimiter = ";";
					}

					$delimiter    = detectDelimiter($handle);
					$totalData    = 0;
					$totalDataNow = 0;

					while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {

						if($row > 1){

							$check_tipe                   = false;
							$tanggaldokumenlain           = "";
							$tanggalfakturpajak           = "";
							$tanggal_approval             = "";
							$tanggal_faktur_asal          = "";
							
							$updateCSV['PAJAK_HEADER_ID'] = $pajak_header_id;
							$updateCSV['USER_NAME']       = $this->session->userdata('identity');
							$updateCSV['NAMA_PAJAK']      = $nama_pajak;
							$updateCSV['MASA_PAJAK']      = strtoupper(get_masa_pajak($bulan_pajak));
							$updateCSV['BULAN_PAJAK']     = $bulan_pajak;
							$updateCSV['KODE_CABANG']     = $kode_cabang;
							$updateCSV['TAHUN_PAJAK']     = $tahun_pajak;
							$updateCSV['PEMBETULAN_KE']   = $pembetulan_ke;

							if($kategori_dokumen == "EFAKTUR_KELUARAN"){

								if(strlen($data[0]) > 5){

									$dataNew = str_replace('""', '"_NULL_"',$data[0]);
									$dataNew = ltrim($dataNew, '"');
									$dataNew = rtrim($dataNew, '"');
									$dataNew = str_replace('","','-|-',$dataNew);

									$dataNew = str_replace('-|-"','-|-',$dataNew);
									$dataNew = str_replace('"-|-','-|-',$dataNew);
									$dataNew = str_replace(',"','-|-',$dataNew);
									$dataNew = str_replace('-|-',';|;',$dataNew);
									$dataNew = str_replace('_NULL_','',$dataNew);
									$data    = explode(";|;", $dataNew);
								}

								$checkFirst = $data[0];
								if($checkFirst == "FK" || $checkFirst == "OF" || $checkFirst == "FAPR" || $checkFirst == "LT"){
									$check_tipe = true;
								}
								else{
									$ket = "File bukan bertipe EFaktur";
									break;
								}
							}
							else{
								if(count($data) > 1){
									$checkFirst = $data[1];
									if($kategori_dokumen == "DM"){
										if($checkFirst == "DM"){
											$check_tipe = true;
										}
									}
									elseif($kategori_dokumen == "DK"){
										if($checkFirst == "DK"){
											$check_tipe = true;
										}
									}
									else{
										if($checkFirst == "FM"){
											$check_tipe = true;
										}
									}
								}
								else{
									$ket = "File bukan bertipe EFaktur";
									break;
								}
							}

							if($kategori_dokumen == "EFAKTUR_KELUARAN"){
								if($checkFirst == "FK"){
									$check_tipe = true;
									$updateCSV['KD_JENIS_TRANSAKSI']     = $data[1];
									$updateCSV['FG_PENGGANTI']           = $data[2];
									$updateCSV['NO_FAKTUR_PAJAK']        = $data[3];
									$tanggalfakturpajak                  = ($data[6] != "") ? date("Y-m-d", strtotime(str_replace("/","-",$data[6]))) : "";
									$updateCSV['NPWP']                   = $data[7];
									$updateCSV['NAMA_WP']                = $data[8];
									$updateCSV['ALAMAT_WP']              = $data[9];
									$updateCSV['DPP']                    = simtax_trim($data[10]);
									$updateCSV['JUMLAH_POTONG']          = simtax_trim($data[11]);
									$updateCSV['JUMLAH_PPNBM']           = simtax_trim($data[12]);
									$updateCSV['ID_KETERANGAN_TAMBAHAN'] = $data[13];
									$updateCSV['FG_UANG_MUKA']           = $data[14];
									$updateCSV['UANG_MUKA_DPP']          = $data[15];
									$updateCSV['UANG_MUKA_PPN']          = $data[16];
									$updateCSV['UANG_MUKA_PPNBM']        = $data[17];
									$updateCSV['REFERENSI']              = $data[18];
									$updateCSV['SOURCE_DATA']            = "CSV";
									$updateCSV['E_FAKTUR']               = "keluaran";
									$updateCSV['DL_FS']                  = "faktur_standar";

									$updateNewCsv[$totalData] = $updateCSV;
									$totalData++;

									unset($updateCSV['JSON_KELUARAN']);

									$totalDataNow = $totalData;
								}
								else{

									if($row >= 4){
										$updateCSV['JSON_KELUARAN'][] = $data;
										$updateNewCsv[$totalData-1] = $updateCSV;
									}
								}
							}
							else{
								$idPajakLines = $data[0];
								$updateCSV['AKUN_PAJAK']  = $data[2];
								if($idPajakLines == ""){
									$updateCSV['SOURCE_DATA'] = "CSV";
								}
								if($checkFirst == "DM" || $checkFirst == "DK"){

									$updateCSV['DL_FS']                 = "dokumen_lain";
									$updateCSV['JENIS_TRANSAKSI']       = $data[3];
									$updateCSV['JENIS_DOKUMEN']         = $data[4];
									$updateCSV['KD_JENIS_TRANSAKSI']    = $data[5];
									$updateCSV['FG_PENGGANTI']          = $data[6];

									if($data[5] == "" && $data[8] != ""){
										$updateCSV['KD_JENIS_TRANSAKSI'] = substr($data[8], 0,2);
									}
									if($data[6] == "" && $data[8] != ""){
										$updateCSV['FG_PENGGANTI'] = substr($data[6], 2,1);
									}
									$updateCSV['NO_DOKUMEN_LAIN_GANTI'] = $data[7];
									$updateCSV['NO_DOKUMEN_LAIN']       = $data[8];
									$tanggaldokumenlain                 = ($data[9] != "") ? date("Y-m-d", strtotime(str_replace("/","-",$data[9]))) : "";
									$updateCSV['NPWP']                  = str_replace(' ', '', $data[12]);
									$updateCSV['NAMA_WP']               = $data[13];
									$updateCSV['ALAMAT_WP']             = $data[14];
									$updateCSV['DPP']                   = simtax_trim($data[17]);
									$updateCSV['INVOICE_CURRENCY_CODE'] = $data[16];
									$updateCSV['INVOICE_NUM']           = $data[15];
									if($checkFirst == "DM"){
										$updateCSV['JUMLAH_POTONG'] = simtax_trim($data[18])*-1;
									}
									else{
										$updateCSV['JUMLAH_POTONG'] = simtax_trim($data[18]);
									}
									$updateCSV['JUMLAH_PPNBM']  = $data[19];
									$updateCSV['KETERANGAN']    = $data[20];
									$updateCSV['FAPR']          = $data[21];
									$tanggal_approval           = ($data[22] != "") ? date("Y-m-d", strtotime(str_replace("/","-",$data[22]))) : $tanggaldokumenlain;
									$updateCSV['FAKTUR_ASAL']   = $data[23];
									$tanggal_faktur_asal        = ($data[24] != "") ? date("Y-m-d", strtotime(str_replace("/","-",$data[24]))) : "";
									$updateCSV['DPP_ASAL']      = $data[25];
									$updateCSV['PPN_ASAL']      = $data[26];
									$updateCSV['KETERANGAN_GL'] = $data[27];
								}
								else{
									$updateCSV['DL_FS']              = "faktur_standar";
									$updateCSV['KD_JENIS_TRANSAKSI'] = $data[3];
									$updateCSV['FG_PENGGANTI']       = $data[4];
									if($data[3] == "" && $data[5] != ""){
										$updateCSV['KD_JENIS_TRANSAKSI'] = substr($data[5], 0,2);
									}
									if($data[4] == "" && $data[5] != ""){
										$updateCSV['FG_PENGGANTI'] = substr($data[5], 2,1);
									}
									$updateCSV['NO_FAKTUR_PAJAK']       = $data[5];
									
									$tanggalfakturpajak                 = ($data[8] != "") ? date("Y-m-d", strtotime(str_replace("/","-",$data[8]))) : "";
									$updateCSV['NPWP']                  = simtax_trim($data[9], "space");
									$updateCSV['NAMA_WP']               = $data[10];
									$updateCSV['ALAMAT_WP']             = $data[11];
									$updateCSV['DPP']                   = simtax_trim($data[14]);
									$updateCSV['INVOICE_CURRENCY_CODE'] = $data[13];
									$updateCSV['INVOICE_NUM']           = $data[12];
									$updateCSV['JUMLAH_POTONG']         = simtax_trim($data[15])*-1;
									$updateCSV['JUMLAH_PPNBM']          = $data[16];
									$updateCSV['IS_CREDITABLE']         = $data[17];
									$updateCSV['FAKTUR_ASAL']           = $data[18];
									$tanggal_faktur_asal                = ($data[19] != "") ? date("Y-m-d", strtotime(str_replace("/","-",$data[19]))) : "";
									$updateCSV['DPP_ASAL']              = simtax_trim($data[20]);
									$updateCSV['PPN_ASAL']              = simtax_trim($data[21]);
									$updateCSV['KETERANGAN_GL']         = $data[22];
								}
							}
							if($check_tipe == true){
								if($kategori_dokumen != "EFAKTUR_KELUARAN"){
									if($idPajakLines == ""){
										if ($this->ppn_masa->add_rekonsiliasi($updateCSV, $tanggaldokumenlain, $tanggalfakturpajak, $tanggal_approval, $tanggal_faktur_asal)) {
											$st = "true";
										}
										else{
											$ket = "Gagal import";
										}
									}
									else{
										if ($this->ppn_masa->update_rekonsiliasi($idPajakLines, $updateCSV, $tanggaldokumenlain, $tanggalfakturpajak, $tanggal_approval, $tanggal_faktur_asal)) {
											$st = "true";
										}
										else{
											$ket = "Gagal import";
											break;
										}
									}
								}
							}
							else{
								if($kategori_dokumen == "EFAKTUR_KELUARAN"){
									$ket = "File bukan bertipe EFaktur";
									break;
								}
								elseif($kategori_dokumen == "DM" || $kategori_dokumen == "DK"){
									$ket = "File bukan bertipe Dokumen Lain";
									break;
								}
								else{
									$ket = "File bukan bertipe Faktur Masukan";
									break;
								}
							}
						}
						$row++;
					}

					if($kategori_dokumen == "EFAKTUR_KELUARAN"){

						$delete_efaktur = $this->ppn_masa->delete_efaktur_lines($pajak_header_id);
					
						for ($i=0; $i < $totalData ; $i++) {

							$json_keluaran = $updateNewCsv[$i]['JSON_KELUARAN'];

							$updateNewCsv[$i]['JSON_KELUARAN'] = $json_keluaran;

							if($this->ppn_masa->add_rekonsiliasi($updateNewCsv[$i])) {
								$st  = "true";
							}
							else{
								$ket = "Gagal import";
							}
						}
					}

				}
			}
			else {
				$ket = "File gagal di upload";
			}
		} else {
			$ket = "File bukan bertipe CSV";
		}
		$result['status']     = $st;
		$result['keterangan'] = $ket;

		echo json_encode($result);
	}

	function save_rekonsiliasi(){

		$return             = false;

		$kode_cabang            = $this->kode_cabang;
		$idPajakHeader          =  $this->input->post('idPajakHeader');
		$idPajakLines           =  $this->input->post('idPajakLines');
		$idwp                   =  $this->input->post('idwp');
		$organization_id        =  $this->input->post('organization_id');
		$vendor_site_id         =  $this->input->post('vendor_site_id');
		$fAkun                  =  $this->input->post('fAkun');
		$fNamaAkun              =  $this->input->post('fNamaAkun');
		$fTahun                 =  $this->input->post('fTahun');
		$namawp                 =  $this->input->post('namawp');
		$npwp                   =  $this->input->post('npwp');
		$alamat                 =  $this->input->post('alamat');
		$dpp                    =  $this->input->post('dpp');
		$jumlahpotong           =  $this->input->post('jumlahpotong');
		$kd_jenis_transaksi     =  $this->input->post('kd_jenis_transaksi');
		$fg_pengganti           =  $this->input->post('fg_pengganti');
		$jenis_transaksi        =  $this->input->post('jenis_transaksi');
		$nodokumenlain_ganti    =  $this->input->post('nodokumenlain_ganti');
		$nodokumenlain          =  $this->input->post('nodokumenlain');
		$tanggaldokumenlain     =  ($this->input->post('tanggaldokumenlain')) ? date("Y-m-d", strtotime(str_replace("/", "-", $this->input->post('tanggaldokumenlain')))) :'';
		$nofakturpajak          =  $this->input->post('nofakturpajak');
		$tanggalfakturpajak     =  ($this->input->post('tanggalfakturpajak')) ? date("Y-m-d", strtotime(str_replace("/", "-", $this->input->post('tanggalfakturpajak')))) :'';
		$jumlah_ppnbm           =  $this->input->post('jumlah_ppnbm');
		$jenis_dokumen          =  $this->input->post('jenis_dokumen');
		$keterangan             =  $this->input->post('keterangan');
		$fapr                   =  $this->input->post('fapr');
		$is_creditable          =  $this->input->post('is_creditable');
		$id_keterangan_tambahan =  $this->input->post('id_keterangan_tambahan');
		$fg_uang_muka           =  $this->input->post('fg_uang_muka');
		$uang_muka_dpp          =  $this->input->post('uang_muka_dpp');
		$uang_muka_ppn          =  $this->input->post('uang_muka_ppn');
		$uang_muka_ppnbm        =  $this->input->post('uang_muka_ppnbm');
		$referensi              =  $this->input->post('referensi');
		$mata_uang              =  $this->input->post('mata_uang');
		$faktur_asal            =  $this->input->post('faktur_asal');
		$tanggal_faktur_asal    =  ($this->input->post('tanggal_faktur_asal')) ? date("Y-m-d", strtotime(str_replace("/", "-", $this->input->post('tanggal_faktur_asal')))) :'';
		$dpp_asal               =  $this->input->post('dpp_asal');
		$ppn_asal               =  $this->input->post('ppn_asal');
		$tanggal_approval       =  ($this->input->post('tanggal_approval')) ? date("Y-m-d", strtotime(str_replace("/", "-", $this->input->post('tanggal_approval')))) :'';
		$ntpn                   = $this->input->post('ntpn');
		$keterangan_gl          = $this->input->post('keterangan_gl');
		$akun_pajak             = $this->input->post('akun_pajak');
		$invoice_number         = $this->input->post('invoice_number');

		$nama_pajak = $this->ppn_masa->get_data_header($idPajakHeader)->NAMA_PAJAK;

		if($nama_pajak == 'PPN MASUKAN'){
			$ppn = $jumlahpotong*-1;
		}
		else{
			$ppn = $jumlahpotong;
		}

		$data = array(
						'VENDOR_SITE_ID'         => $vendor_site_id,
						'AKUN_PAJAK'             => $akun_pajak,
						'INVOICE_NUM'            => $invoice_number,
						'DPP'                    => $dpp,
						'JUMLAH_POTONG'          => $ppn,
						'USER_NAME'              => $this->session->userdata('identity'),
						'KD_JENIS_TRANSAKSI'     => $kd_jenis_transaksi,
						'FG_PENGGANTI'           => $fg_pengganti,
						'JUMLAH_PPNBM'           => $jumlah_ppnbm,
						'JENIS_DOKUMEN'          => $jenis_dokumen,
						'JENIS_TRANSAKSI'        => $jenis_transaksi,
						'KETERANGAN'             => $keterangan,
						'IS_CREDITABLE'          => $is_creditable,
						'ID_KETERANGAN_TAMBAHAN' => $id_keterangan_tambahan,
						'FG_UANG_MUKA'           => $fg_uang_muka,
						'UANG_MUKA_DPP'          => $uang_muka_dpp,
						'UANG_MUKA_PPN'          => $uang_muka_ppn,
						'UANG_MUKA_PPNBM'        => $uang_muka_ppnbm,
						'REFERENSI'              => $referensi,
						'NO_DOKUMEN_LAIN'        => $nodokumenlain,
						'NO_FAKTUR_PAJAK'        => $nofakturpajak,
						'NO_DOKUMEN_LAIN_GANTI'  => $nodokumenlain_ganti,
						'FAPR'                   => $fapr,
						'INVOICE_CURRENCY_CODE'  => $mata_uang,
						'FAKTUR_ASAL'            => $faktur_asal,
						'DPP_ASAL'               => $dpp_asal,
						'PPN_ASAL'               => $ppn_asal,
						'NTPN'                   => $ntpn,
						'KETERANGAN_GL'          => $keterangan_gl,
						'ORGANIZATION_ID'        => $organization_id,
						'NAMA_WP'                => $namawp,
						'NPWP'                   => $npwp,
						'ALAMAT_WP'              => $alamat
					);

		if($nama_pajak == 'PPN MASUKAN'){
			$data['VENDOR_ID'] = $idwp;
		}
		else{
			$data['CUSTOMER_ID'] = $idwp;
		}
		
		if ($this->ppn_masa->update_rekonsiliasi($idPajakLines, $data, $tanggaldokumenlain, $tanggalfakturpajak, $tanggal_approval, $tanggal_faktur_asal)) {
			echo '1';
		}
		else{
			echo '0';
		}

	}

	function delete_rekonsiliasi()
	{
		$pajak_line_id   = $this->input->post('idPajakLines');

		$data = $this->ppn_masa->delete_pajak_lines($pajak_line_id);

		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}


	function save_ntpn(){
		
		$return        = false;
		
		$id            = $this->input->post('id');
		$isnewRecord   = $this->input->post('isnewRecord');
		$bulan_pajak   = $this->input->post('bulan_pajak');
		$tahun_pajak   = $this->input->post('tahun_pajak');
		$pembetulan_ke = $this->input->post('pembetulan_pajak');
		$ntpn          = $this->input->post('ntpn');
		$bank          = $this->input->post('bank');
		$tanggal_setor = ($this->input->post('tanggal_setor')) ? date("Y-m-d", strtotime(str_replace("/", "-", $this->input->post('tanggal_setor')))) :'';
		$tanggal_lapor = ($this->input->post('tanggal_lapor')) ? date("Y-m-d", strtotime(str_replace("/", "-", $this->input->post('tanggal_lapor')))) :'';

		$data = array(
						'BULAN'      => $bulan_pajak,
						'TAHUN'      => $tahun_pajak,
						'PEMBETULAN' => $pembetulan_ke,
						'NTPN'       => $ntpn,
						'NTPN'       => $ntpn,
						'BANK'       => $bank
					);

		$check = $this->ppn_masa->check_ntpn($id, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $ntpn); //add validasi ntpn 27032019
		
		if($check > 0){
			echo '2';
		}
		else{
			if($isnewRecord == "0"){
				if ($this->ppn_masa->update_ntpn($id, $data, $tanggal_setor, $tanggal_lapor)) {
					echo '1';
				}
				else{
					echo '0';
				}

			}else{
				if ($this->ppn_masa->add_ntpn($data, $tanggal_setor, $tanggal_lapor)) {
					echo '1';
				}
				else{
					echo '0';
				}
			}
		}
	}

	function delete_ntpn()
	{
		$id   = $this->input->post('id');

		$data = $this->ppn_masa->delete_ntpn($id);

		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}

	function validation_rekonsiliasi($pajak_header_id, $nama_pajak)
	{
		$data1       = $this->ppn_masa->action_cek_row_rekonsiliasi($pajak_header_id, "dokumen_lain");
		
		$masihKosong = true;
		$doklainAda  = false;
		$fakturAda   = false;
		$data_header = $this->ppn_masa->get_data_header($pajak_header_id);
		$nama_pajak  = ($data_header) ? $data_header->NAMA_PAJAK : "";

		if($data1){
			$ii=0;
			$records  = "";
			foreach($data1->result_array() as $row)	{
				$ii++;

				// if($nama_pajak == "PPN MASUKAN"){
					if ($row['IS_CHEKLIST']==1){
						if($row['KD_JENIS_TRANSAKSI']=="" || $row['VENDOR_NAME']=="" || $row['NPWP1']=="" || $row['NO_DOKUMEN_LAIN']=="" || $row['DPP']==""){
							$records .= $ii.", " ;
							$doklainAda = true;
						}
					}
					$hasilDoklain ="Nomor ".$records." Nama WP / NPWP / KD Jenis Transaksi / Nomor dokumen lain / DPP pada form Dokumen Lain Masih Kosong!";

				// }
				// else{
				// 	if ($row['IS_CHEKLIST']==1){
				// 		if($row['KD_JENIS_TRANSAKSI']=="" || $row['VENDOR_NAME']=="" || $row['NPWP1']=="" || $row['NO_DOKUMEN_LAIN']=="" || $row['DPP']=="" || $row['AKUN_PAJAK']==""){
				// 			$records .= $ii.", " ;
				// 			$doklainAda = true;
				// 		}
				// 	}
				// 	$hasilDoklain ="Nomor ".$records." Nama WP / NPWP / KD Jenis Transaksi / Nomor dokumen lain / DPP / Akun Pendapatan pada form Dokumen Lain Masih Kosong!";
				// }
			}
		} 

		$data2	= $this->ppn_masa->action_cek_row_rekonsiliasi($pajak_header_id, "faktur_standar");
		
		if($data2){
			$ii=0;
			$records  = "";
			foreach($data2->result_array() as $row)	{
				$ii++;

				// if($nama_pajak == "PPN MASUKAN"){
					if ($row['IS_CHEKLIST']==1){
						if($row['KD_JENIS_TRANSAKSI']=="" || $row['VENDOR_NAME']=="" || $row['NPWP1']=="" || $row['NO_FAKTUR_PAJAK']=="" || $row['DPP']==""){
							$records .= $ii.", " ;
							$fakturAda = true;
						}
					}
					$hasilFaktur ="Nomor ".$records." Nama WP / NPWP / KD Jenis Transaksi / Nomor faktur / DPP pada form EFAKTUR Masih Kosong!";
				// }
				// else{
				// 	if ($row['IS_CHEKLIST']==1){
				// 		if($row['KD_JENIS_TRANSAKSI']=="" || $row['VENDOR_NAME']=="" || $row['NPWP1']=="" || $row['NO_FAKTUR_PAJAK']=="" || $row['DPP']=="" || $row['AKUN_PAJAK']==""){
				// 			$records .= $ii.", " ;
				// 			$fakturAda = true;
				// 		}
				// 	}
				// 	$hasilFaktur ="Nomor ".$records." Nama WP / NPWP / KD Jenis Transaksi / Nomor faktur / DPP / Akun Pendapatan pada form EFAKTUR Masih Kosong!";
				// }
			}
		}
		
		if($fakturAda == true && $doklainAda == true){
			$result['data'] = $hasilFaktur ."<br>".$hasilDoklain;
		}
		else{
			if($fakturAda == true && $doklainAda == false){
				$result['data'] = $hasilFaktur;
			}
			elseif($doklainAda == true && $fakturAda == false){
				$result['data'] = $hasilDoklain;
			}
			else{
				$masihKosong = false;
			}
		}

		if($masihKosong){
			$result['st'] = 1;
			echo json_encode($result);
			die();
		}
		else{

			$result['st'] = 1;

			$checkDuplicate1 = $this->ppn_masa->check_duplicate_faktur_doklain($pajak_header_id, $nama_pajak, "dokumen_lain");
			$checkDuplicate2 = $this->ppn_masa->check_duplicate_faktur_doklain($pajak_header_id, $nama_pajak, "faktur_standar");

			$dokLainSama        = false;
			$fakturSama         = false;
			$keterangan_faktur  = "";
			$keterangan_doklain = "";
			if($checkDuplicate1['num_rows'] > 0){

				$dataDuplicate1 = $checkDuplicate1['query']->result_array();
				$dokLainSama = true;
				foreach ($dataDuplicate1 as $key => $value) {
					$dokLainnya[] = $value['NO_DOKUMEN_LAIN'];
				}
				$keterangan_doklain = "Nomor Dokumen Lain ada yang sama pada nomor ".implode(", ", $dokLainnya)." yang terdapat di form Dokumen Lain";
			}
			if($checkDuplicate2['num_rows'] > 0){
				$dataDuplicate2 = $checkDuplicate2['query']->result_array();
				$fakturSama = true;
				foreach ($dataDuplicate2 as $key => $value) {
					$fakturnya[] = $value['NO_FAKTUR_PAJAK'];
				}
				$keterangan_faktur = "Nomor Faktur ada yang sama pada nomor ".implode(", ", $fakturnya)." yang terdapat di form EFAKTUR";
			}

			if($fakturSama == true && $dokLainSama == true){
				$result['data'] = $keterangan_faktur ."<br>".$keterangan_doklain;
			}
			else{
				if($fakturSama == true && $dokLainSama == false){
					$result['data'] = $keterangan_faktur;
				}
				elseif($dokLainSama == true && $fakturSama == false){
					$result['data'] = $keterangan_doklain;
				}
				else{
					$result['st'] = 0;
				}
			}

			echo json_encode($result);

		}
	}

	
	function submit_rekonsiliasi()
	{

		$kode_cabang   = $this->kode_cabang;
		$username      = $this->session->userdata('identity');
		
		$nama_pajak    = $this->input->post('_searchPpn');
		$bulan_pajak   = $this->input->post('_searchBulan');
		$tahun_pajak   = $this->input->post('_searchTahun');
		$pembetulan_ke = $this->input->post('_searchPembetulan');

		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

		if($this->ppn_masa->submit_rekonsiliasi($pajak_header_id, $nama_pajak, $username)){			
			echo '1';
		} else {
			echo '0';
		}

	}

	function load_master_wp($nama_pajak)
	{
		$nama_pajak = str_replace('%20',' ',$nama_pajak);
		$hasil    = $this->ppn_masa->get_master_wp($this->kode_cabang, $nama_pajak);
		$rowCount = $hasil['jmlRow'] ;
		$query    = $hasil['query'];
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;

					if($nama_pajak == "PPN MASUKAN"){

						$result['data'][] = array(
									'no'				=> $row['RNUM'],
									'vendor_id'			=> $row['VENDOR_ID'],
									'organization_id'	=> $row['ORGANIZATION_ID'],
									'vendor_site_id'	=> $row['VENDOR_SITE_ID'],
									'nama_wp'			=> $row['VENDOR_NAME'],
									'alamat_wp'			=> $row['ADDRESS_LINE1'],
									'npwp' 				=> $row['NPWP']
									);

					}else{

						$result['data'][] = array(
									'no'              => $row['RNUM'],
									'vendor_id'       => $row['CUSTOMER_ID'],
									'organization_id' => $row['ORGANIZATION_ID'],
									'vendor_site_id'  => $row['CUSTOMER_SITE_ID'],
									'nama_wp'         => $row['CUSTOMER_NAME'],
									'alamat_wp'       => $row['ADDRESS_LINE1'],
									'npwp'            => $row['NPWP']
									);
					}
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


	// Approval Supervisor

	function approval_supervisor(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_supervisor", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{
			
			$this->data['template_page'] = "ppn_masa/approval_supervisor";
			$this->data['title']         = 'PPN Masa';
			$this->data['subtitle']      = "Approval Supervisor";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['kantor_cabang'] = "cabang";

			$this->template->load('template', $this->data['template_page'], $this->data);

		}

	}

	function approval_pusat(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_pusat", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{
			
			$this->data['template_page'] = "ppn_masa/approval_supervisor";
			$this->data['title']         = 'PPN Masa';
			$this->data['subtitle']      = "Approval Pusat";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['kantor_cabang'] = "pusat";
			$this->data['list_cabang']   = $this->cabang->get_all();
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}

	}


    function load_approval()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_supervisor", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else if(in_array("ppn_masa/approval_pusat", $this->session->userdata['menu_url'])){
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
			
			$start             = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length            = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw              = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords          = (isset($_POST['search'])) ? $_POST['search']['value'] : '';
			
			$kode_cabang       = $this->kode_cabang;
			$nama_pajak        = $this->input->post('_searchPpn');
			$bulan_pajak       = $this->input->post('_searchBulan');
			$tahun_pajak       = $this->input->post('_searchTahun');
			$pembetulan_ke     = $this->input->post('_searchPembetulan');
			$category          = $this->input->post('_category');
			$approval_category = $this->input->post('_aproval_category');

			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

			if($approval_category == "pusat"){
				
				$kode_cabang = $this->input->post('_searchCabang');
				$hasil = $this->ppn_masa->get_approval("", $kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category, $start, $length, $keywords);
			}else{
				$hasil = $this->ppn_masa->get_approval($pajak_header_id,  $kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category, $start, $length, $keywords);
			}

			$rowCount            = $hasil['jmlRow'];
			$query               = $hasil['query'];

			if($rowCount > 0){

				foreach($query->result_array() as $row)	{

					$val_fg_pengGanti   = ($row['FG_PENGGANTI'] != "") ? $row['FG_PENGGANTI'] : $row['FG_PENGGANTI_NEW'];
					$kd_jenis_transaksi = ($row['KD_JENIS_TRANSAKSI'] == "") ? "" : sprintf("%02d", $row['KD_JENIS_TRANSAKSI']);

			        if($nama_pajak == $this->daftar_pajak[0]){
			        	if($category == "dokumen_lain"){
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 2;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 5;
			        	}
			        	else{
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
			        	}
			        }
			        else{
			        	if($category == "dokumen_lain"){
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 5;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
			        	}
			        	else{
							$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
							$val_jenis_dokumen   = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
			        	}
			        }

              		$result['data'][] = array(
							'pajak_header_id'        => $row['PAJAK_HEADER_ID'],
							'pajak_line_id'          => $row['PAJAK_LINE_ID'],
							'vendor_id'              => $row['VENDOR_ID'],
							'akun_pajak'             => $row['AKUN_PAJAK'],
							'masa_pajak'             => $row['MASA_PAJAK'],
							'tahun_pajak'            => $row['TAHUN_PAJAK'],
							'kode_cabang'            => get_nama_cabang($row['KODE_CABANG']),
							'no'                     => $row['RNUM'],
							'jenis_transaksi'        => $val_jenis_transaksi,
							'jenis_dokumen'          => $val_jenis_dokumen,
							'kd_jenis_transaksi'     => $kd_jenis_transaksi,
							'fg_pengganti'           => $val_fg_pengGanti,
							'no_dokumen_lain_ganti'  => ($row['NO_DOKUMEN_LAIN_GANTI'] != "") ? $row['NO_DOKUMEN_LAIN_GANTI'] : $row['FAKTUR_ASAL'],
							'no_dokumen_lain'        => $row['NO_DOKUMEN_LAIN'],
							'tanggal_dokumen_lain'   => $row['TANGGAL_DOKUMEN_LAIN'],
							'no_faktur_pajak'        => $row['NO_FAKTUR_PAJAK'],
							'tanggal_faktur_pajak'   => $row['TANGGAL_FAKTUR_PAJAK'],
							'npwp'                   => $row['NPWP1'],
							'nama_wp'                => $row['VENDOR_NAME'],
							'alamat_wp'              => $row['ADDRESS_LINE1'],
							'invoice_number'         => $row['INVOICE_NUM'],
							'mata_uang'              => $row['INVOICE_CURRENCY_CODE'],
							'dpp'                    => number_format($row['JUMLAH_DPP'],2,'.',','),
							'jumlah_potong'          => number_format($row['JUMLAH_POTONG_PPN'],2,'.',','),
							'jumlah_ppnbm'           => number_format($row['JUMLAH_PPNBM'],2,'.',','),
							'keterangan'             => $row['KETERANGAN'],
							'fapr'                   => $row['FAPR'],
							'tanggal_approval'       => $row['TANGGAL_APPROVAL'],
							'is_creditable'          => ($row['IS_CREDITABLE'] != "") ? $row['IS_CREDITABLE'] : 1,
							'id_keterangan_tambahan' => $row['ID_KETERANGAN_TAMBAHAN'],
							'fg_uang_muka'           => ($row['FG_UANG_MUKA'] != "") ? $row['FG_UANG_MUKA'] : 0,
							'uang_muka_dpp'          => ($row['UANG_MUKA_DPP'] != "") ? $row['UANG_MUKA_DPP'] : 0,
							'uang_muka_ppn'          => ($row['UANG_MUKA_PPN'] != "") ? $row['UANG_MUKA_PPN'] : 0,
							'uang_muka_ppnbm'        => ($row['UANG_MUKA_PPNBM'] != "") ? $row['UANG_MUKA_PPNBM'] : 0,
							'referensi'              => $row['REFERENSI'],
							'faktur_asal'            => $row['FAKTUR_ASAL'],
							'tanggal_faktur_asal'    => $row['TANGGAL_FAKTUR_ASAL'],
							'dpp_asal'               => $row['DPP_ASAL'],
							'ppn_asal'               => $row['PPN_ASAL'],
							'ntpn'                   => $row['NTPN'],
							'keterangan_gl'          => $row['KETERANGAN_GL']
						);
				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

		echo json_encode($result);
    }

	function save_approval()
	{

		if($this->input->post('cabang')){
			$kode_cabang = $this->input->post('cabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}

		$username          = $this->session->userdata('identity');
		$st                = $this->input->post('st');
		$keterangan        = $this->input->post('ket');
		$nama_pajak        = $this->input->post('pasal');
		$bulan_pajak       = $this->input->post('masa');
		$tahun_pajak       = $this->input->post('tahun');
		$pembetulan_ke     = $this->input->post('pembetulan');
		$approval_category = $this->input->post('approval_category');

		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

		$data = $this->ppn_masa->save_approval($pajak_header_id, $nama_pajak, $st, $keterangan, $username, $approval_category);

		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}


	function get_start()
	{

		if($this->input->post('cabang')){
			$kode_cabang = $this->input->post('cabang');
		}
		else{
			$kode_cabang = $this->kode_cabang;
		}
		$nama_pajak    = $this->input->post('pasal');
		$bulan_pajak   = $this->input->post('masa');
		$tahun_pajak   = $this->input->post('tahun');
		$pembetulan_ke = $this->input->post('pembetulan');
		$category      = $this->input->post('approval_category');

		if($category == "pusat"){
			$data = $this->ppn_masa->action_get_start($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		}
		else{
			$data = $this->ppn_masa->action_get_start($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		}

		if($data){
			
			if($data->num_rows()>0){
				$row	                 = $data->row();       	
				$result['status']        = strtoupper($row->STATUS); 
				$result['status_period'] = strtoupper($row->STATUS_PERIOD);
			}
			else {
				$result['status']        = "-------------"; 
				$result['status_period'] = " ----------";
				$result['keterangan'] 	 = "";
			}

			$result['isSuccess'] 	 = 1;
		}
		else {
			$result['isSuccess'] 	 = 0;
		}

		echo json_encode($result);

		$data->free_result();  
	}



	/* Pembetulan */

	public function pembetulan(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/pembetulan", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{

			$this->data['template_page'] = "ppn_masa/pembetulan";
			$this->data['title']         = 'PPN Masa';
			$this->data['subtitle']      = "Pembetulan";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['list_cabang']   = $this->cabang->get_all();
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}

	function load_pembetulan()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/pembetulan", $this->session->userdata['menu_url'])){
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

			$start       = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length      = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw        = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords    = (isset($_POST['search'])) ? $_POST['search']['value'] : '';
			
			$nama_pajak    = $this->input->post('_searchPpn');
			$bulan_pajak   = $this->input->post('_searchBulan');
			$tahun_pajak   = $this->input->post('_searchTahun');
			$pembetulan_ke = $this->input->post('_searchPembetulan');
			$kode_cabang   = $this->input->post('_searchCabang');
			
			$hasil    = $this->ppn_masa->get_pembetulan($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $start, $length, $keywords);
			
			$rowCount = $hasil['jmlRow'];
			$query    = $hasil['query'];

			if($rowCount > 0){

				$jml_pembetulan = 0;
				$jml_normal       = 0;
				$jml_selisih      = 0;

				foreach($query->result_array() as $row)	{

					$pembetulan_ke       = $row['PEMBETULAN_KE']-1;
					
					$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($row['KODE_CABANG'], $row['NAMA_PAJAK'], $row['BULAN_PAJAK'], $row['TAHUN_PAJAK'], $pembetulan_ke);
					$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
					
					$get_jml_pembetulan  = $this->ppn_masa->jumlah_ppn($row['PAJAK_HEADER_ID'], $row['NAMA_PAJAK']);
					$jml_pembetulan      = $get_jml_pembetulan['query']->row()->JUMLAH_POTONG;
					
					$get_jml_normal      = $this->ppn_masa->jumlah_ppn($pajak_header_id, $row['NAMA_PAJAK']);
					$jml_normal          = $get_jml_normal['query']->row()->JUMLAH_POTONG;
					
					$jml_selisih         = $jml_normal-$jml_pembetulan;

					$result['data'][] = array(
								'no'              => $row['RNUM'],
								'pajak_header_id' => $row['PAJAK_HEADER_ID'],
								'status_period'   => $row['STATUS_PERIOD'],
								'bulan_pajak'     => $row['BULAN_PAJAK'],
								'nama_pajak'      => $row['NAMA_PAJAK'],
								'masa_pajak'      => $row['MASA_PAJAK'],
								'tahun_pajak'     => $row['TAHUN_PAJAK'],
								'pembetulan_ke'   => $row['PEMBETULAN_KE'],
								'kode_cabang'     => $row['KODE_CABANG'],
								'nama_cabang'     => $row['NAMA_CABANG'],
								'jml_pembetulan'  => number_format($jml_pembetulan,2,'.',','),
								'jml_normal'      => number_format($jml_normal,2,'.',','),
								'selisih'         => number_format($jml_selisih,2,'.',',')
								);
				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}

    	}

    	echo json_encode($result);

    }
	
	function open_pembetulan()
	{

		$nama_pajak    = $this->input->post('fjenisPajak');
		$bulan_pajak   = $this->input->post('fbulan');
		$tahun_pajak   = $this->input->post('ftahun');
		$pembetulan_ke = $this->input->post('fpembetulanKe')-1;
		$kode_cabang   = $this->input->post('fcabang');
		
		$pajak_header = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);

		if($pajak_header){

			$period_id       = $this->ppn_masa->get_period_by_id($pajak_header->PERIOD_ID);
			
			if(strtolower($period_id->STATUS) == "close"){

				$header_id_max  = $this->ppn_masa->get_header_id_max($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak);
				$savePembetulan = $this->ppn_masa->action_save_pembetulan($header_id_max);

				if($savePembetulan){
					echo '1';
				}
				else{
					echo '0';
				}
			}
			else{
				echo '2';
			}
		}
		else{
			echo '3';
		}

	}
	
	function delete_pembetulan()
	{
		$data	= $this->ppn_masa->action_delete_pembetulan();
		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}


	/* Download dan Cetak */

	public function download_cetak(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/download_cetak", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{

			$this->data['template_page'] = "ppn_masa/download";
			$this->data['title']         = "Download dan Cetak PPN Masa";
			$this->data['subtitle']      = "Download dan Cetak PPN Masa";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['kantor_cabang'] = "cabang";
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}

	public function download_kompilasi(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/download_kompilasi", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('');
		}
		else{

			$this->data['template_page'] = "ppn_masa/download";
			$this->data['title']         = "Kompilasi PPN Masa";
			$this->data['subtitle']      = "Kompilasi PPN Masa";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['kantor_cabang'] = "pusat";
			$this->data['list_cabang']   = $this->cabang->get_all();
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}


    function load_download()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/download_cetak", $this->session->userdata['menu_url'])){
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

			$start             = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length            = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw              = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords          = (isset($_POST['search'])) ? $_POST['search']['value'] : '';
			

			$nama_pajak        = $this->input->post('_searchPpn');
			$bulan_pajak       = $this->input->post('_searchBulan');
			$tahun_pajak       = $this->input->post('_searchTahun');
			$pembetulan_ke     = $this->input->post('_searchPembetulan');
			$download_category = $this->input->post('_download_category');
			$category          = $this->input->post('_category');

			if($this->input->post('_searchCabang') == "all"){
				$kode_cabang     = "";
			}
			else{
				$kode_cabang         = $this->input->post('_searchCabang');
			}

			if($download_category == "pusat"){

				$hasil = $this->ppn_masa->get_download("", $kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category, $start, $length, $keywords);

			}else{

				$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
				$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;

				$hasil = $this->ppn_masa->get_download($pajak_header_id, $kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category, $start, $length, $keywords);
			}

			$rowCount            = $hasil['jmlRow'];
			$query               = $hasil['query'];

			if($rowCount > 0){

				$pushData = true;
				$j = 0;
				$nomorNya = 1;
				$lastNomor = 0;

				foreach($query->result_array() as $row)	{

					$val_fg_pengGanti   = ($row['FG_PENGGANTI'] != "") ? $row['FG_PENGGANTI'] : $row['FG_PENGGANTI_NEW'];
					$kd_jenis_transaksi = ($row['KD_JENIS_TRANSAKSI'] == "") ? "" : sprintf("%02d", $row['KD_JENIS_TRANSAKSI']);

					$ppnNya     = $row['JUMLAH_POTONG_PPN'];
					$dppNya     = $row['JUMLAH_DPP'];
					$invoiceNya = $row['INVOICE_NUM'];
					/*if($nama_pajak == "PPN KELUARAN" && $category == "dokumen_lain"){
						$pushData = false;
						if(in_array(strtolower($invoiceNya), $dupInvoice['INVOICE_NUM'])){
							if($invoiceNya != $lastInv){
								$ppnNya      = $dupInvoice['JUMLAH_POTONG_PPN'][$j];
								$dppNya      = $dupInvoice['JUMLAH_DPP'][$j];
								$pushData = true;
								$j++;
							}
							$lastInv = $row['INVOICE_NUM'];
						}
						else{
							$pushData = true;
						}
			        }*/
			      /*  if($row['RNUM'] > $nomorNya){
			        	$nomorNya = $lastNomor++;
			        }*/

			        $nomorNya = $row['RNUM'];

			        if($pushData){

			        /*	if($row['RNUM'] > $lastNomor && $lastNomor != 0){
			        		$nomorNya = $lastNomor++;
			        	}*/
			        	/*if($nomorNya-1 != $lastNomor && $lastNomor !=0){
							$lastNomor++;
			        		$nomorNya = $lastNomor;
			        	}*/

						$result['data'][] = array(
										'pajak_header_id'        => $row['PAJAK_HEADER_ID'],
										'pajak_line_id'          => $row['PAJAK_LINE_ID'],
										'vendor_id'              => $row['VENDOR_ID'],
										'akun_pajak'             => $row['AKUN_PAJAK'],
										'masa_pajak'             => $row['MASA_PAJAK'],
										'tahun_pajak'            => $row['TAHUN_PAJAK'],
										'kode_cabang'            => get_nama_cabang($row['KODE_CABANG']),
										'no'                     => $nomorNya,
										'jenis_transaksi'        => ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 2,
										'jenis_dokumen'          => $row['JENIS_DOKUMEN'],
										'kd_jenis_transaksi'     => $kd_jenis_transaksi,
										'fg_pengganti'           => $val_fg_pengGanti,
										'no_dokumen_lain_ganti'  => ($row['NO_DOKUMEN_LAIN_GANTI'] != "") ? $row['NO_DOKUMEN_LAIN_GANTI'] : $row['FAKTUR_ASAL'],
										'no_dokumen_lain'        => $row['NO_DOKUMEN_LAIN'],
										'tanggal_dokumen_lain'   => $row['TANGGAL_DOKUMEN_LAIN'],
										'no_faktur_pajak'        => $row['NO_FAKTUR_PAJAK'],
										'tanggal_faktur_pajak'   => $row['TANGGAL_FAKTUR_PAJAK'],
										'npwp'                   => $row['NPWP1'],
										'nama_wp'                => $row['VENDOR_NAME'],
										'alamat_wp'              => $row['ADDRESS_LINE1'],
										'invoice_number'         => $row['INVOICE_NUM'],
										'mata_uang'              => $row['INVOICE_CURRENCY_CODE'],
										'dpp'                    => number_format($dppNya,2,'.',','),
										'jumlah_potong'          => number_format($ppnNya,2,'.',','),
										'jumlah_ppnbm'           => number_format($row['JUMLAH_PPNBM'],2,'.',','),
										'keterangan'             => $row['KETERANGAN'],
										'fapr'                   => $row['FAPR'],
										'tanggal_approval'       => $row['TANGGAL_APPROVAL'],
										'is_creditable'          => ($row['IS_CREDITABLE'] != "") ? $row['IS_CREDITABLE'] : 1,
										'id_keterangan_tambahan' => $row['ID_KETERANGAN_TAMBAHAN'],
										'fg_uang_muka'           => ($row['FG_UANG_MUKA'] != "") ? $row['FG_UANG_MUKA'] : 0,
										'uang_muka_dpp'          => ($row['UANG_MUKA_DPP'] != "") ? $row['UANG_MUKA_DPP'] : 0,
										'uang_muka_ppn'          => ($row['UANG_MUKA_PPN'] != "") ? $row['UANG_MUKA_PPN'] : 0,
										'uang_muka_ppnbm'        => ($row['UANG_MUKA_PPNBM'] != "") ? $row['UANG_MUKA_PPNBM'] : 0,
										'referensi'              => $row['REFERENSI'],
										'faktur_asal'            => $row['FAKTUR_ASAL'],
										'tanggal_faktur_asal'    => $row['TANGGAL_FAKTUR_ASAL'],
										'dpp_asal'               => $row['DPP_ASAL'],
										'ppn_asal'               => $row['PPN_ASAL']
									);
						$lastNomor = $nomorNya;
			        }

				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}

    	}

    	echo json_encode($result);

    }

    function export_csv($category_download, $kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category, $creditable="", $withAkun = 0) {

		ini_set('memory_limit', '-1');

		$this->load->helper('csv_helper');

		$date           = date("d", time());
		$dokumen_lain   = array();
		$faktur_standar = array();

		$nama_pajak      = str_replace("%20", " ", $nama_pajak);
		$groupByInvoiceNUm = false;

		if($nama_pajak == "PPN KELUARAN" && $category == "dokumen_lain"){
			$groupByInvoiceNUm = true;
		}

		if($category_download == "kompilasi" && $kode_cabang == "all"){
			$data       = $this->ppn_masa->get_csv("", "", $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category_download, $category, $creditable, $groupByInvoiceNUm);
			$get_cabang = $this->ppn_masa->get_cabang_in_header("", $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);

			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id("", $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			foreach ($get_pajak_header_id as $key => $value) {
				$pajak_header_id[] = $value['PAJAK_HEADER_ID'];
			}
		}
		else{
			$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
			$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
			$data                = $this->ppn_masa->get_csv($pajak_header_id, $kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $category_download, $category, $creditable, $groupByInvoiceNUm);
			
			$get_cabang          = $this->ppn_masa->get_cabang_in_header($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		}

		$nama_cabangArr = array();
		foreach ($get_cabang as $value) {
			$nama_cabangArr[$value->KODE_CABANG] = str_replace(" ","",get_nama_cabang($value->KODE_CABANG));
		}
		ksort($nama_cabangArr);

        if($nama_pajak == "PPN MASUKAN"){
        	if($category == "dokumen_lain"){
        		$tileDMFM = "DK_DM";
        	}
        	else{
        		$tileDMFM = "FM";
        	}
        }
        else{
        	if($category == "dokumen_lain"){
        		$tileDMFM = "DK_DM";
        	}
        	else{
        		$tileDMFM = "FK";
        	}
        }

		$title_dokumen_lain = array(
	                            	$tileDMFM,
	                            	'JENIS_TRANSAKSI',
	                            	'JENIS_DOKUMEN',
	                            	'KD_JNS_TRANSAKSI',
	                            	'FG_PENGGANTI',
	                            	'NOMOR_DOK_LAIN_GANTI',
	                            	'NOMOR_DOK_LAIN',
	                            	'TANGGAL_DOK_LAIN',
	                            	'MASA_PAJAK',
	                            	'TAHUN_PAJAK',
	                            	'NPWP',
	                            	'NAMA',
	                            	'ALAMAT_LENGKAP',
	                            	'JUMLAH_DPP',
	                            	'JUMLAH_PPN',
	                            	'JUMLAH_PPNBM',
	                            	'KETERANGAN',
									'FAPR',
									'TGL_APPROVAL'
								);

		if($withAkun == 1){
			array_shift($title_dokumen_lain); 
			array_unshift($title_dokumen_lain, $tileDMFM, "AKUN");
        }
		
		if($nama_pajak == "PPN MASUKAN"){

			$title_faktur_standar = array(
										$tileDMFM,
		                            	'KD_JENIS_TRANSAKSI',
		                            	'FG_PENGGANTI',
										'NOMOR_FAKTUR',
										'MASA_PAJAK',
										'TAHUN_PAJAK',
										'TANGGAL_FAKTUR',
										'NPWP',
										'NAMA',
										'ALAMAT_LENGKAP',
										'JUMLAH_DPP',
										'JUMLAH_PPN',
										'JUMLAH_PPNBM',
										'IS_CREDITABLE'
									);
			if($withAkun == 1){
				array_shift($title_faktur_standar); 
				array_unshift($title_faktur_standar, $tileDMFM, "AKUN");
	        }

		}
		else{

			$title_faktur_standar = array(
		                    			$tileDMFM,
		                            	'KD_JENIS_TRANSAKSI',
		                            	'FG_PENGGANTI',
										'NOMOR_FAKTUR',
										'MASA_PAJAK',
										'TAHUN_PAJAK',
										'TANGGAL_FAKTUR',
										'NPWP',
										'NAMA',
										'ALAMAT_LENGKAP',
										'JUMLAH_DPP',
										'JUMLAH_PPN',
										'JUMLAH_PPNBM',
										'ID_KETERANGAN_TAMBAHAN',
										'FG_UANG_MUKA',
										'UANG_MUKA_DPP',
										'UANG_MUKA_PPN',
										'UANG_MUKA_PPNBM',
										'REFERENSI'
									);
		}

    	array_push($dokumen_lain, $title_dokumen_lain);
		array_push($faktur_standar, $title_faktur_standar);
		
		$adaEfaktur = true;
							
        if (!empty($data)) {
        	if($nama_pajak == "PPN KELUARAN" && $category == "dokumen_lain"){

				$resCheckData = $this->ppn_masa->get_by_duplicate_invoice($pajak_header_id);

				$lastInv    = "";
				$dupInvoice = array();
				foreach ($resCheckData as $key => $value) {
					$dupInvoice['INVOICE_NUM'][]       = strtolower($value['INVOICE_NUM']);
					$dupInvoice['JUMLAH_POTONG_PPN'][] = $value['JUMLAH_POTONG_PPN'];
					$dupInvoice['JUMLAH_DPP'][]        = $value['JUMLAH_DPP'];
				}
			}
			
			$pushDokLain = true;
			$j = 0;
        	foreach($data->result_array() as $row)	{

				$tanggal_dokumen_lain = ($row['TANGGAL_DOKUMEN_LAIN']) ? date("d/m/Y", strtotime($row['TANGGAL_DOKUMEN_LAIN'])) : '';
				$tanggal_approval     = ($row['TANGGAL_DOKUMEN_LAIN']) ? date("Ymdhis", strtotime($row['TANGGAL_DOKUMEN_LAIN']))."000000" : '';
				$tanggal_faktur       = ($row['TANGGAL_FAKTUR_PAJAK']) ? date("d/m/Y", strtotime($row['TANGGAL_FAKTUR_PAJAK'])) : '';
				
				$val_fg_pengGanti     = ($row['FG_PENGGANTI'] != "") ? $row['FG_PENGGANTI'] : $row['FG_PENGGANTI_NEW'];
				$kd_jenis_transaksi   = ($row['KD_JENIS_TRANSAKSI'] == "") ? "" : sprintf("%02d", $row['KD_JENIS_TRANSAKSI']);
				$fg_pengganti         = ($row['FG_PENGGANTI'] != "") ? $row['FG_PENGGANTI'] : $row['FG_PENGGANTI_NEW'];

		        if($nama_pajak == $this->daftar_pajak[0]){
		        	if($category == "dokumen_lain"){
						$val_jenis_transaksi    = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 2;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 5;
		        	}
		        	else{
						$val_jenis_transaksi = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
		        	}
		        }
		        else{
		        	if($category == "dokumen_lain"){
						$val_jenis_transaksi    = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 5;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
		        	}
		        	else{
						$val_jenis_transaksi    = ($row['JENIS_TRANSAKSI'] != "") ? $row['JENIS_TRANSAKSI'] : 3;
						$val_jenis_dokumen      = ($row['JENIS_DOKUMEN'] != "") ? $row['JENIS_DOKUMEN'] : 3;
		        	}
		        }

				$ppnNya     = $row['JUMLAH_POTONG_PPN'];
				$dppNya     = $row['DPP'];
				$invoiceNya = $row['INVOICE_NUM'];
				if($nama_pajak == "PPN KELUARAN" && $category == "dokumen_lain"){
					$pushDokLain = false;
					if(in_array(strtolower($invoiceNya), $dupInvoice['INVOICE_NUM'])){
						if($invoiceNya != $lastInv){
							$ppnNya      = $dupInvoice['JUMLAH_POTONG_PPN'][$j];
							$dppNya      = $dupInvoice['JUMLAH_DPP'][$j];
							$pushDokLain = true;
							$j++;
						}
						$lastInv = $row['INVOICE_NUM'];
					}
					else{
						$pushDokLain = true;
					}
		        }

        		$npwp = format_npwp($row['NPWP1'], false);
        		$no_dokumen_lain_ganti = ($row['NO_DOKUMEN_LAIN_GANTI'] != "") ? $row['NO_DOKUMEN_LAIN_GANTI'] : $row['FAKTUR_ASAL'];

	        	$address = utf8_encode($row['ADDRESS_LINE1']);
				$address = str_replace(' ',' ',$address);
				$address = str_replace('\'','',$address);
				$address = str_replace('"','',$address);
				$address = str_replace('`','',$address);
				$address = trim($address);
				$address = trim(preg_replace('/\s\s+/', ' ', $address));

				$alamatNya = preg_replace("/[\r\n]+/", " ", $address);

		        if($pushDokLain){

		        	$arrDokumenLain = array(
											($nama_pajak == "PPN MASUKAN") ? "DM" : "DK",
											$val_jenis_transaksi,
											$val_jenis_dokumen,
											$kd_jenis_transaksi,
											$fg_pengganti,
											$no_dokumen_lain_ganti,
											$row['NO_DOKUMEN_LAIN'],
											$tanggal_dokumen_lain,
											sprintf("%02d", $row['BULAN_PAJAK']),
											$row['TAHUN_PAJAK'],
											$npwp,
											$row['VENDOR_NAME'],
											$alamatNya,
											$dppNya,
											$ppnNya,
											($row['JUMLAH_PPNBM'] != "") ? $row['JUMLAH_PPNBM'] : 0,
											$row['KETERANGAN'],
											$row['FAPR'],
											$tanggal_approval
										);

		        	if($withAkun == 1){
						array_shift($arrDokumenLain); 
						array_unshift($arrDokumenLain,($nama_pajak == "PPN MASUKAN") ? "DM" : "DK", $row['AKUN_PAJAK']);
			        }
		        	array_push($dokumen_lain, $arrDokumenLain);
		        }

				if($nama_pajak == "PPN MASUKAN"){

					$arrFakturMasukan = array(
											"FM",
											$kd_jenis_transaksi,
											$fg_pengganti,
											str_replace(".","", str_replace("-","",substr($row['NO_FAKTUR_PAJAK'], 3))),
											sprintf("%02d", $row['BULAN_PAJAK']),
											$row['TAHUN_PAJAK'],
											$tanggal_faktur,
											$npwp,
											$row['VENDOR_NAME'],
											$alamatNya,
											$row['DPP'],
											$row['JUMLAH_POTONG_PPN'],
											($row['JUMLAH_PPNBM'] != "") ? $row['JUMLAH_PPNBM'] : 0,
											$row['IS_CREDITABLE']
										);

					if($withAkun == 1){
						array_shift($arrFakturMasukan); 
						array_unshift($arrFakturMasukan, "FM", $row['AKUN_PAJAK']);
			        }
					array_push($faktur_standar, $arrFakturMasukan);
				}
				else{

					if($row['E_FAKTUR'] != 'keluaran' ){
						array_push($faktur_standar,
							array(
									"FK",
									$kd_jenis_transaksi,
									$fg_pengganti,
									str_replace(".","", str_replace("-","",substr($row['NO_FAKTUR_PAJAK'], 3))),
									sprintf("%02d", $row['BULAN_PAJAK']),
									$row['TAHUN_PAJAK'],
									$tanggal_faktur,
									$npwp,
									$row['VENDOR_NAME'],
									$alamatNya,
									$row['DPP'],
									$row['JUMLAH_POTONG_PPN'],
									($row['JUMLAH_PPNBM'] != "") ? $row['JUMLAH_PPNBM'] : 0,
									$row['ID_KETERANGAN_TAMBAHAN'],
									($row['FG_UANG_MUKA'] != "") ? $row['FG_UANG_MUKA'] : 0,
									($row['UANG_MUKA_DPP'] != "") ? $row['UANG_MUKA_DPP'] : 0,
									($row['UANG_MUKA_PPN'] != "") ? $row['UANG_MUKA_PPN'] : 0,
									($row['UANG_MUKA_PPNBM'] != "") ? $row['UANG_MUKA_PPNBM'] : 0,
									$row['REFERENSI']
								)
							);
					}
					else{
						$adaEfaktur = true;
					}

				}

			}
        }

        $masa_pajak = strtoupper(get_masa_pajak($bulan_pajak));

        $nama_cabang = array();
		foreach ($nama_cabangArr as $key => $value) {
			$nama_cabang[] = $value;
		}

		if($category_download == "kompilasi" && $kode_cabang == "all"){
			$fileName = "Kompilasi_".$tahun_pajak."_".$masa_pajak."_".$pembetulan_ke;
		}
		else{
			$fileName = $tahun_pajak."_".$masa_pajak."_".$pembetulan_ke."_".$nama_cabang[0];
		}

        if($nama_pajak == "PPN MASUKAN"){
        	if($category == "dokumen_lain"){
        		convert_to_csv($dokumen_lain, "Dokumen_Masukan_".$fileName.".csv", ";");
        	}
        	else{
        		convert_to_csv($faktur_standar, "Faktur_Masukan_".$fileName."_".$creditable.".csv", ";");
        	}
        }
        else{
        	if($category == "dokumen_lain"){
        		convert_to_csv($dokumen_lain, "Dokumen_Keluaran_".$fileName.".csv", ";");
        	}
        	else{

	        	if($adaEfaktur == true){

					$path       = "./uploads/importCsv/ppn_masa/efaktur_keluaran/".$tahun_pajak."/".$bulan_pajak."_".$masa_pajak."/";

					if($kode_cabang == "all" && count($nama_cabang) > 1){

						$files_process = glob($path."{,.}*", GLOB_BRACE);
						$i = 0;
						foreach ($files_process as $file) {
							$fileCab = explode("_", $file);
							$endArr  = str_replace(".csv","",end($fileCab));
							if(in_array($endArr, $nama_cabang)){
								$filenya = $path."Ekspor_Efaktur_Keluaran_".$tahun_pajak."_".$masa_pajak."_".$pembetulan_ke."_".$nama_cabang[$i].".csv";
								if(file_exists($filenya)){
									if($i == 0){
										$dataFilePertama = file_get_contents($filenya);
									}
									if($i > 0){
										$handle        = fopen($filenya, "r");
										$row           = 0;
										$dataX         = array();
										$dataFileKeDua = array();
										while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
											if($row >= 3){
												$dataX[] = "\"".implode("\",\"", $data)."\"";
											}
											$row++;
										}
										$dataFileKeDua[] = implode("\n", $dataX);
									}
								}
								$i++;
							}
							unset($fileCab);
						}

						$dataJoin = implode("\n", $dataFileKeDua);
						$joinFile = $dataFilePertama.$dataJoin;
						$filename = "Efaktur_Keluaran_Kompilasi_".$tahun_pajak."_".strtoupper(get_masa_pajak($bulan_pajak))."_".$pembetulan_ke;

						$temp_memory = fopen('php://memory', 'w');
			            fwrite($temp_memory, $joinFile);
				        fseek($temp_memory, 0);
				        header('Content-Type: application/csv');
				        header('Content-Disposition: attachement; filename="'.$filename.'.csv";');
				        fpassthru($temp_memory);

					}
					else{

						$file_url  = $path."Ekspor_Efaktur_Keluaran_".$tahun_pajak."_".$masa_pajak."_".$pembetulan_ke."_".$nama_cabang[0].".csv";
						$filename = "Efaktur_Keluaran_".$tahun_pajak."_".strtoupper(get_masa_pajak($bulan_pajak))."_".$pembetulan_ke."_".$nama_cabang[0];
					
						if(file_exists($file_url)){
							header('Content-Type: application/octet-stream');
							header("Content-Transfer-Encoding: Binary"); 
							header('Content-Disposition: attachement; filename="'.$filename.'.csv";');
							readfile($file_url);
						}
					}
				}
        	}
        }

    }


	/* Closing */

	public function closing(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/closing", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{
			
			$this->data['template_page'] = "ppn_masa/closing";
			$this->data['title']         = 'PPN Masa';
			$this->data['subtitle']      = "Closing";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['list_cabang']   = $this->cabang->get_all();
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}

    function load_closing()
	{
		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/closing", $this->session->userdata['menu_url'])){
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

			$start       = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length      = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw        = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords    = (isset($_POST['search'])) ? $_POST['search']['value'] : '';
			
			$tahun_pajak   = $this->input->post('_searchTahun');
			$pembetulan_ke = $this->input->post('_searchPembetulanKe');
			$kode_cabang   = $this->input->post('_searchCabang');
			
			$hasil       = $this->ppn_masa->get_closing($kode_cabang, $tahun_pajak, $pembetulan_ke, $start, $length, $keywords);
			$rowCount    = $hasil['jmlRow'];
			$query       = $hasil['query'];

			if($rowCount > 0){

				foreach($query->result_array() as $row)	{

					if ($row['STATUS']=="Open") {
						$st ="<span class='label label-success'>".$row['STATUS']."</span>";
					} else {
						$st ="<span class='label label-danger'>".$row['STATUS']."</span>";
					}
					$result['data'][] = array(
								'no'            => $row['RNUM'],
								'nama_pajak'    => $row['NAMA_PAJAK'],
								'masa_pajak'    => $row['MASA_PAJAK'],
								'bulan_pajak'   => $row['BULAN_PAJAK'],
								'tahun_pajak'   => $row['TAHUN_PAJAK'],
								'pembetulan_ke' => $row['PEMBETULAN_KE'],
								'kode_cabang'   => $row['KODE_CABANG'],
								'nama_cabang'   => get_nama_cabang($row['KODE_CABANG']),
								'params'        => $row['STATUS'],
								'status'        => $st
								);
				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

		echo json_encode($result);
    }


	function save_closing()
	{
	
		$username    = $this->session->userdata('identity');
		
		$kode_cabang   = $this->input->post('cabang');
		$status        = $this->input->post('status');
		$nama_pajak    = $this->input->post('nama');
		$bulan_pajak   = $this->input->post('bulan');
		$tahun_pajak   = $this->input->post('tahun');
		$pembetulan_ke = $this->input->post('pembetulan');
		
		$get_pajak_header_id = $this->ppn_masa->get_pajak_header_id($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke);
		
		$pajak_header_id     = ($get_pajak_header_id) ? $get_pajak_header_id->PAJAK_HEADER_ID : 0;
		$period_id           = ($get_pajak_header_id) ? $get_pajak_header_id->PERIOD_ID : 0;

		$data	= $this->ppn_masa->action_save_closing($pajak_header_id, $period_id, $nama_pajak, $status, $username);

		if($data){
			echo '1';
		} else {
			echo '0';
		}
	}


	/* View Status */

	public function view_status(){

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/view_status", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		if($permission === false)
		{
			redirect('dashboard');
		}
		else{
			
			$this->data['template_page'] = "ppn_masa/view_status";
			$this->data['title']         = 'PPN Masa';
			$this->data['subtitle']      = "View Status";
			$this->data['activepage']    = "ppn_masa";
			
			$this->data['daftar_pajak']  = get_daftar_pajak('PPNMASA', true);
			$this->data['list_cabang']   = $this->cabang->get_all();
			
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
		
	}

    function load_status()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/view_status", $this->session->userdata['menu_url'])){
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

			$start         = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length        = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw          = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			
			$kode_cabang   = $this->input->post('_searchCabang');
			$nama_pajak    = $this->input->post('_searchPpn');
			$bulan_pajak   = $this->input->post('_searchBulan');
			$tahun_pajak   = $this->input->post('_searchTahun');
			$pembetulan_ke = $this->input->post('_searchPembetulan');
			
			$hasil         = $this->ppn_masa->get_status($kode_cabang, $nama_pajak, $bulan_pajak, $tahun_pajak, $pembetulan_ke, $start, $length);
			$rowCount      = $hasil['jmlRow'];
			$query         = $hasil['query'];
			$dibayarkan    = 0;
			$jumlahpotong  = 0;
			$ppn_impor     = 0;

			if($rowCount > 0){

				foreach($query->result_array() as $row)	{
					
					$hasil         = $this->ppn_masa->jumlah_ppn($row['PAJAK_HEADER_ID'],$row['NAMA_PAJAK']);
					$jumlahpotong  = $hasil['query']->row()->JUMLAH_POTONG;

					if($row['NAMA_PAJAK'] == "PPN MASUKAN"){
						$get_ppn_impor = $this->ppn_masa->jumlah_ppn($row['PAJAK_HEADER_ID'], $row['NAMA_PAJAK'], "ppn_impor");
						$ppn_impor     = $get_ppn_impor['query']->row()->JUMLAH_POTONG;
					}
					
					$dibayarkan    = $jumlahpotong;

					$result['data'][] = array(
								'no'				=> $row['RNUM'],
								'pajak_header_id'	=> $row['PAJAK_HEADER_ID'],
								'kode_cabang'		=> $row['KODE_CABANG'],
								'nama_cabang'		=> get_nama_cabang($row['KODE_CABANG']),
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
								'ttl_jml_potong' 	=> number_format($dibayarkan,2,'.',',')
								);
				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

		echo json_encode($result);
    }


	function input_url_doc(){

		$this->template->set('title', 'Archive Link');
		$data['activepage'] = "ppn_masa";
		$data['subtitle']   = "Archive Link";
		
		$data['stand_alone'] = true;
		$group_pajak         = get_daftar_pajak('PPNMASA'); // PPH, PPH21, PPNMASA, PPNWAPU
		
		$list_pajak          = array();
	
		foreach ($group_pajak as $key => $value) {
			$list_pajak[] = $value->JENIS_PAJAK;
		}

		$data['nama_pajak']  = $list_pajak;

		$this->template->load('template', 'administrator/archive_link',$data);

	}

	
	function load_history()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("ppn_masa/view_status", $this->session->userdata['menu_url'])){
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

			$start           = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length          = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw            = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			
			$pajak_header_id = $this->input->post('_searchPajakHeader');

			$hasil    = $this->ppn_masa->get_history($pajak_header_id, $start, $length);
			$rowCount = $hasil['jmlRow'];
			$query    = $hasil['query'];

			if($rowCount > 0){

				foreach($query->result_array() as $row)	{

					$result['data'][] = array(
											'no'			=> $row['RNUM'],
											'action_code'	=> $row['ACTION_CODE'],
											'action_date'	=> $row['ACTION_DATE'],
											'user_name'		=> $row['USER_NAME'],
											'catatan'		=> $row['CATATAN']
											);
				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

		echo json_encode($result);
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

}

/* End of file Ppn_masa.php */
/* Location: ./application/controllers/Ppn_masa.php */