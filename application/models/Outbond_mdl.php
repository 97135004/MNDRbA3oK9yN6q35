<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Outbond_mdl extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /*===========================================================================Receipt Outbond===================================================================*/

    function get_receiptoutbond()
    {
        $q        = (isset($_POST['search']['value'])) ? $_POST['search']['value'] : '';
        $where    = "";

        if ($q) { //check lgsg where atau and
            $where = " where (upper(item_number) like '%" . strtoupper($q) . "%' or upper(stock_item_description) like '%" . strtoupper($q) . "%' or upper(stock_item_spec) like '%" . strtoupper($q) . "%') ";
        }

        $queryExec = "SELECT 
                        STOCK_RECEIPT_ID,
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
                        LOCATOR_SEGMENT8 
                      FROM STOCK_RECEIPT_OUTBOUND " . $where;

        $queryCount = "SELECT count(1) JML      
						 FROM STOCK_RECEIPT_OUTBOUND " . $where;

        $sql        = "SELECT * FROM (
						SELECT rownum rnum, a.* 
						FROM(
							" . $queryExec . "
						) a 
						WHERE rownum <=" . $_POST['start'] . "+" . $_POST['length'] . "
					)
                    WHERE rnum >" . $_POST['start'] . "";

        $sql2        = $queryExec;
        $query2     = $this->db->query($sql2);

        //start row count				
        $selectCount    = $this->db->query($queryCount);
        $row            = $selectCount->row();
        $rowCount      = $row->JML;
        //end get row count

        //$rowCount	= $query2->num_rows() ;
        //print_r($sql); exit();
        $query = $this->db->query($sql);
        if ($rowCount > 0) {
            $ii    =    0;
            foreach ($query->result_array() as $row) {
                $ii++;
                $result['data'][] = array(
                    'no'                     => $row['RNUM'],
                    'stock_receipt_id'       => $row['STOCK_RECEIPT_ID'],
                    'item_number'            => $row['ITEM_NUMBER'],
                    'stock_item_description' => $row['STOCK_ITEM_DESCRIPTION'],
                    'stock_item_spec'        => $row['STOCK_ITEM_SPEC'],
                    'oem_model_no'           => $row['OEM_MODEL_NO'],
                    'oem_part_no'            => $row['OEM_PART_NO'],
                    'subinventory'           => $row['SUBINVENTORY'],
                    'locator'                => $row['LOCATOR'],
                    'lot_number'             => $row['LOT_NUMBER'],
                    'receipt_number'         => $row['RECEIPT_NUMBER'],
                    'receipt_date'           => $row['RECEIPT_DATE'],
                    'po_number'              => $row['PO_NUMBER'],
                    'quantity'               => $row['QUANTITY'],
                    'uom'                    => $row['UOM'],
                    'locator_segment1'       => $row['LOCATOR_SEGMENT1'],
                    'locator_segment2'       => $row['LOCATOR_SEGMENT2'],
                    'locator_segment3'       => $row['LOCATOR_SEGMENT3'],
                    'locator_segment4'       => $row['LOCATOR_SEGMENT4'],
                    'locator_segment5'       => $row['LOCATOR_SEGMENT5'],
                    'locator_segment6'       => $row['LOCATOR_SEGMENT6'],
                    'locator_segment7'       => $row['LOCATOR_SEGMENT7'],
                    'locator_segment8'       => $row['LOCATOR_SEGMENT8']
                );
            }

            $query->free_result();

            $result['draw']            = $_POST['draw'] = ($_POST['draw']) ? $_POST['draw'] : 0;
            $result['recordsTotal']    = $rowCount;
            $result['recordsFiltered'] = $rowCount;
        } else {
            $result['data']            = "";
            $result['draw']            = "";
            $result['recordsTotal']    = 0;
            $result['recordsFiltered'] = 0;
        }
        return $result;
    }

    function action_save_rec_out()
    {
        $customer_id     = $this->input->post('customer_id');
        $customer_name     = $this->input->post('customer_name');
        $customer_number = $this->input->post('customer_number');
        $npwp             = $this->input->post('npwp');

        $sql    = "Update SIMTAX_MASTER_PELANGGAN 
		              set CUSTOMER_NAME='" . $customer_name . "',
						  NPWP='" . $npwp . "'
  				    where CUSTOMER_ID ='" . $customer_id . "' ";
        $query    = $this->db->query($sql);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    function action_delete_rec_out()
    {
        $customer_id        = $this->input->post('customer_id');
        $customer_site_id    = $this->input->post('customer_site_id');

        $sql    = "delete from SIMTAX_MASTER_PELANGGAN 
		                where CUSTOMER_ID ='" . $customer_id . "'
						  and CUSTOMER_SITE_ID = '" . $customer_site_id . "'";
        $query    = $this->db->query($sql);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }
    /*TAMBAH*/
    function action_tambah_rec_out()

    {
        $customer_id                = $this->input->post('customer_id');
        $customer_name                = $this->input->post('customer_name');
        $alias_customer                = $this->input->post('alias_customer');
        $customer_number            = $this->input->post('customer_number');
        $npwp                        = $this->input->post('npwp');
        $operating_unit                = $this->input->post('operating_unit');
        $customer_site_id            = $this->input->post('customer_site_id');
        $customer_site_number        = $this->input->post('customer_site_number');
        $customer_site_name            = $this->input->post('customer_site_name');
        $address_line1                = $this->input->post('address_line1');
        $address_line2                = $this->input->post('address_line2');
        $address_line3                = $this->input->post('address_line3');
        $city                        = $this->input->post('city');
        $province                    = $this->input->post('province');
        $country                    = $this->input->post('country');
        $zip                        = $this->input->post('zip');

        //flag
        $isNewRecord        = $this->input->post('isNewRecord'); // 1 tambah, 0 edit

        if ($isNewRecord == 1) {
            $sql    = "insert into SIMTAX_MASTER_PELANGGAN  ( 
						customer_id,
						customer_name,
						alias_customer,
						customer_number,
						npwp,
						operating_unit,
						customer_site_id,
						customer_site_number,
						customer_site_name,
						address_line1,
						address_line2,
						address_line3,
						city,
						province,
						country,
						zip)
				values (SIMTAX_MASTER_SUPPLIER_S.NEXTVAL,
						'" . $customer_name . "',
						'" . $alias_customer . "',
						'" . $customer_number . "',
						'" . $npwp . "',
						'" . $operating_unit . "',
						'0',
						'0',
						'" . $customer_site_name . "',
						'" . $address_line1 . "',
						'" . $address_line2 . "',
						'" . $address_line3 . "',
						'" . $city . "',
						'" . $province . "',
						'" . $country . "',
						'" . $zip . "')";
        } else {
            $sql    = "Update SIMTAX_MASTER_PELANGGAN
						  set CUSTOMER_NAME		='" . $customer_name . "',
							  NPWP				='" . $npwp . "',
							  alias_customer	='" . $alias_customer . "',
							  customer_number	='" . $customer_number . "',
							  operating_unit	='" . $operating_unit . "',
							  customer_site_name='" . $customer_site_name . "',
							  address_line1		='" . $address_line1 . "',
							  address_line2		='" . $address_line2 . "',
							  address_line3		='" . $address_line3 . "',
							  city				='" . $city . "',
							  province			='" . $province . "',
							  country 			= '" . $country . "',
							  zip 				= '" . $zip . "'						  
						where CUSTOMER_ID ='" . $customer_id . "'
						  and customer_site_id = '" . $customer_site_id . "'";
        }

        $query    = $this->db->query($sql);

        if ($query) {

            if ($isNewRecord == 1) {
                simtax_update_history("SIMTAX_MASTER_PELANGGAN", "CREATE", "CUSTOMER_ID");
            } else {
                $params = array(
                    "CUSTOMER_ID"      => $customer_id,
                    "CUSTOMER_SITE_ID" => $customer_site_id
                );
                simtax_update_history("SIMTAX_MASTER_PELANGGAN", "UPDATE", $params);
            }

            return true;
        } else {
            return false;
        }
    }
}
