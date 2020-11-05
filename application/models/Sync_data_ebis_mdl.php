<?php  defined('BASEPATH') OR exit('No direct script access allowed');


class Sync_data_ebis_mdl extends CI_Model {
	
    public function __construct()
    {
        parent::__construct();
    }

	function get_download_rcpt()
	{
			//insert new data
			$sql2	="insert into QRCODE_GETDATA_HISTORY
					  (GETDATA_ID,FILE_NAME,created_by,creation_date)
					  values
					  (QRCODE_GETDATA_HISTORY_S.nextval,'7167STOCKRECEIPT20201010000000.csv',1,sysdate)
					  ";
			
			$query2	= $this->db->query($sql2);			

			return true;
		}
	}

	function do_process_rcpt($file_path,$ConcReqId) {
		
		$row = 0;
		$handle = fopen($file_path, "r");
		$user_name = $this->session->userdata('identity');
		
		$dataCsv  = array();
		while (($data = fgetcsv($handle, 1000, ";","'","\\")) !== FALSE) {

			if($row >= 0){
						 
				$sql	="insert into STOCK_RECEIPT_OUTBOUND 
						(STOCK_RECEIPT_ID,
						ITEM_NUMBER,
						STOCK_ITEM_DESCRIPTION,
						STOCK_ITEM_SPEC,
						OEM_MODEL_NO,
						OEM_PART_NO,
						SUBINVENTORY,
						LOCATOR,
						LOT_NUMBER,
						RECEIPT_NUMBER,
						RECEIPT_DATE,
						PO_NUMBER,
						QUANTITY,
						UOM,
						LOCATOR_SEGMENT1,
						LOCATOR_SEGMENT2,
						LOCATOR_SEGMENT3,
						LOCATOR_SEGMENT4,
						LOCATOR_SEGMENT5,
						LOCATOR_SEGMENT6,
						LOCATOR_SEGMENT7,
						LOCATOR_SEGMENT8,
						CREATED_BY,
						CREATION_DATE,
						LAST_UPDATED_BY,
						LAST_UPDATE_DATE
						) 
						 VALUES
						 (STOCK_RECEIPT_OUTBOUND_SEQ.nextval,
						 '".$data[0]."',
						 '".$data[1]."',
						 '".$data[2]."',
						 '".$data[3]."',
						 '".$data[4]."',
						 ".$data[5].",
						 '".$data[6]."',
						 ".$data[7].",
						 '".$data[8]."',
						 '".$data[9]."',
						 '".$data[10]."',
						 '".$data[11]."',
						 '".$data[12]."',
						 '".$data[13]."',
						 '".$data[14]."',
						 '".$data[15]."',
						 '".$data[16]."',
						 '".$data[17]."',
						 '".$data[18]."',
						 '".$data[19]."',
						 '".$data[20]."',	
						 '".$data[21]."',
						 '".$data[22]."',
						 '".$data[23]."',
						 '".$data[24]."',
						 '".$data[25]."',
						 '".$id."',
						 SYSDATE
						 )";
					 
				$query 		= $this->db->query($sql);	
				if (!$query) {
					return false;
				}

			}

			$row++;
		}
		
		return true;
		
    }	

