<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_data_ebis extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->logged_in())
                {
                        redirect('dashboard', 'refresh');
                }

		$this->load->model('Sync_data_ebis_mdl');
	}
	
	function show_page()
	{
		$this->template->set('title', 'Ambil data dari eBIS');
		$data['subtitle']   = "Download Data";
		$data['activepage'] = "administrator";
		
		$this->template->load('template', 'administrator/Sync_Ebis_Template',$data);
	}
	
	function download_ref()
	{
		
		$data	= $this->Sync_data_ebis_mdl->get_download_ref();
		
		if($data){
			echo '1';
		} else {
			echo '0';
		}
		
	}

	function download_rcpt()
	{
		//validasi utk cek tidak boleh download kl ada data yg masih sudah pernah disubmit

		$root            = $_SERVER['DOCUMENT_ROOT'];
		$nmfile = glob($root.'/qrcode/download/ebs/unprocess/*7167STOCKRECEIPT*.csv', GLOB_BRACE);

		$sql	="  select count(1) ADA_DATA 
					  from STOCK_ISSUE_INBOUND
					 where file_name = $nmfile
					   and STATUS not in ('DRAFT')
					  ";		

		$qReqID     = $this->db->query($sql);
		$row        = $qReqID->row();       	
		$isAdaData  = $row->ADA_DATA; 

		// end validasi
		set_time_limit(0);
		
		if ($isAdaData == 1)
		{
			echo '2';	
		} else 
		{
			$data	= $this->Sync_Ebis_mdl->get_download_rcpt();
			
			if($data){
				echo '1';
			} else {
				echo '0';
			}
		}
	}
	
	function process_rcpt()
	{
	
		$root            = $_SERVER['DOCUMENT_ROOT'];
		$nmfile = glob($root.'/qrcode/download/ebs/unprocess/*7167STOCKRECEIPT*.csv', GLOB_BRACE);

		$sql	=" select GET_DATA_ID 
				     from qrcode_getdata_history
				    where file_name = $nmfile
					  ";
		
		$qReqID     = $this->db->query($sql);
		$row        = $qReqID->row();       	
		$GetDataId  = $row->GET_DATA_ID; 					  
		
		$file_path  =  "./download/ebs/unprocess/".$nmfile;
		$moved_path =  "./download/ebs/process/".$nmfile;
		
		set_time_limit(0);
		
		
		if (file_exists($file_path)) {
			
				$data	= $this->Sync_Ebis_mdl->do_process_rcpt($file_path,$jenis_trx);
	
				if($data){

					$sql	="update qrcode_getdata_history
								 set IMPORT_TO_SIMTAX_FLAG = 'Y'
							   where GET_DATA_ID = '".$GetDataId."'
							  ";
					$this->db->query($sql);		
						
					rename($file_path, $moved_path);
					echo '1'; 
				} else {
					echo '0'; //file to staging failed
				}	
				

		} else {
			echo '2'; // file not found
		}
		
		
	}	
	
	function process_ref()
	{

		$bulan			= $this->input->post('srcBulan');
		$tahun			= $this->input->post('srcTahun');	
		$jenis_trx		= $this->input->post('scrJenis');
		
		$sql	=" select CONCURRENT_REQUEST_ID 
				     from simtax_getdata_history
				    where IMPORT_TO_SIMTAX_FLAG = 'N'
					  and parameter1 = '".$bulan."'
				      and parameter2 = '".$tahun."'
				      and parameter5 = '".$jenis_trx."'
					  ";
		
		$qReqID     = $this->db->query($sql);
		$row        = $qReqID->row();       	
		$ConcReqId  = $row->CONCURRENT_REQUEST_ID; 					  
		
		$file_path  =  "./download/ebs/unprocess/o".$ConcReqId.".csv";
		$moved_path =  "./download/ebs/process/o".$ConcReqId.".csv";

		if (file_exists($file_path)) {
			
			$data	= $this->Sync_Ebis_mdl->do_process_ref($file_path,$jenis_trx);
			//$this->db->_error_message();
			//$this->db->_error_number();

			if($data){

				$sql	="update simtax_getdata_history
							 set IMPORT_TO_SIMTAX_FLAG = 'Y'
						   where CONCURRENT_REQUEST_ID = '".$ConcReqId."'
						  ";
				$this->db->query($sql);		
					
				rename($file_path, $moved_path);
				echo '1'; 
				//$result['isSuccess'] 	 = 1;
				//$result['message']        = $this->db->_error_message(); 
			} else {
				//$result['isSuccess'] 	 = 0;
				//$result['message']        = $this->db->_error_message(); 
				echo '0'; //file to staging failed
			}			
		} else {
			//$result['isSuccess'] 	 = 2;
			echo '2'; // file not found
		}

		//echo json_encode($result);		
		
	}	
	
	function load_history()
	{
		
      	$hasil		= $this->Sync_Ebis_mdl->get_history();
		$rowCount	= $hasil['jmlRow'] ;
		$query 		= $hasil['query'];		
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;

					$result['data'][] = array(
								'req_id'        => $row['CONCURRENT_REQUEST_ID'],
								'req_date'      => $row['REQUESTED_DATE'],
								'bulan'         => $row['BULAN'],
								'tahun'         => $row['TAHUN'],
								'kode_cabang'   => $row['KODE_CABANG'],
								'tipe_doc'      => $row['TIPE_DOKUMEN'],
								'import_flag'   => $row['IMPORT_TO_SIMTAX_FLAG'],
								'import_date'   => $row['IMPORT_TO_SIMTAX_DATE'],
								'rep_req_id'    => $row['REPLACE_BY_CONC_REQ_ID'],
								'nama_cabang'   => $row['NAMA_CABANG'],
								'status_import' => $row['STATUS_IMPORT']
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
	
}
