<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Outbound extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('dashboard', 'refresh');
        }

        $this->load->model('Outbond_mdl');
    }

    /*==========================================================RECEIPT OUTBOUND===================================================================*/

    function receipt_outbound()
    {
        $this->template->set('title', 'Receipt Outbond');
        $data['subtitle']    = "List Data Receipt Outbond";
        $data['activepage'] = "Outbound";
        $this->template->load('template', 'outbond/receipt_outbond', $data);

        

        // $result = $this->db->get('STOCK_RECEIPT_OUTBOUND')->result_array();

        // foreach($result as $rowdata)
        // {
        //     $PARAMETER_1 = $header_id_max;
            // $OUT_MESSAGE = "";
            
            // $stid = oci_parse($this->db->conn_id, 'BEGIN :OUT_MESSAGE := SIMTAX_PAJAK_UTILITY_PKG.createPembetulan(:PARAMETER_1); end;');

            // oci_bind_by_name($stid, ':PARAMETER_1',  $PARAMETER_1,200);
            // oci_bind_by_name($stid, ':OUT_MESSAGE',  $OUT_MESSAGE ,100, SQLT_CHR);

            // if(oci_execute($stid)){
            //   $results = $OUT_MESSAGE;
            // }
            
            // oci_free_statement($stid);
            
            // if ($results == -1) {
            // 	return false;
            // } else {
            // 	return true;
            // }
        // }


        
    }

    function load_rec_out()
    {
        $result    = $this->Outbond_mdl->get_receiptoutbond();

        $rowCount	= $result['jmlRow'] ;
		$query 		= $result['query'];	
		if ($rowCount>0){
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;
					$checked		= ($row['IS_CHEKLIST']==1)?"checked":"";
					$checkbox		= "<div class='checkbox checkbox-danger' style='height:10px'>
										<input id='checkbox".$row['RNUM']."' class='checklist' type='checkbox' ".$checked." disabled >
										<label for='checkbox".$row['RNUM']."'>&nbsp;</label>
									  </div>";
					$result['data'][] = array(
								'checkbox'			        => $checkbox,
								'no'				        => $row['RNUM'],
								'stock_receipt_id'	        => $row['STOCK_RECEIPT_ID'],
								'item_number'			    => $row['ITEM_NUMBER'],
								'stock_item_description'	=> $row['STOCK_ITEM_DESCRIPTION'],
								'stock_item_spec'		    => $row['STOCK_ITEM_SPEC'],
								'oem_model_no' 			    => $row['OEM_MODEL_NO'],
								'oem_part_no' 		        => $row['OEM_PART_NO'],
								'subinventory' 	            => $row['SUBINVENTORY'],
								'locator' 	                => $row['LOCATOR'],
								'lot_number'				=> $row['LOT_NUMBER'],
								'receipt_number'			=> $row['RECEIPT_NUMBER'],
								'receipt_date'				=> $row['RECEIPT_DATE'],
								'po_number'				    => $row['PO_NUMBER'],
								'quantity'			        => $row['QUANTITY'],
								'uom'			            => $row['UOM'],
								'locator_segment1'		    => $row['LOCATOR_SEGMENT1'],
								'locator_segment2'			=> $row['LOCATOR_SEGMENT2'],
								'locator_segment3'			=> $row['LOCATOR_SEGMENT3'],
								'locator_segment4'			=> $row['LOCATOR_SEGMENT4'],
								'locator_segment5'			=> $row['LOCATOR_SEGMENT5'],
								'locator_segment6'			=> $row['LOCATOR_SEGMENT6'],
								'locator_segment7'			=> $row['LOCATOR_SEGMENT7'],
								'locator_segment8'			=> $row['LOCATOR_SEGMENT8']
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

    function save_rec_out()
    {
        // $data    = $this->Outbond_mdl->action_save_rec_out();
        // if ($data) {
        //     echo '1';
        // } else {
        //     echo '0';
        // }
    }

    function delete_rec_out()
    {
        // $data    = $this->Outbond_mdl->action_delete_rec_out();
        // if ($data) {
        //     echo '1';
        // } else {
        //     echo '0';
        // }
    }

    function tambah_rec_out()
    {

        // if (isset($_POST) && !empty($_POST)) {
        //     // $this->form_validation->set_rules('customer_name', 'NAMA PELANGGAN', 'required');
        //     // $this->form_validation->set_rules('npwp', 'NPWP', 'required');
        //     // $this->form_validation->set_rules('address_line1', 'ALAMAT LINE1', 'required');

        //     if ($this->form_validation->run() === TRUE) {
        //         $data    = $this->Outbond_mdl->action_tambah_rec_out();
        //         if ($data) {
        //             echo '1';
        //         } else {
        //             echo '0';
        //         }
        //     } else {
        //         echo validation_errors();
        //     }
        // }
    }
}
