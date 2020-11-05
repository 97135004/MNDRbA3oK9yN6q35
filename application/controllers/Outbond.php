<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Outbond extends CI_Controller
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

    function receipt_outbond()
    {
        $this->template->set('title', 'Receipt Outbond');
        $data['subtitle']    = "List Data Receipt Outbond";
        $data['activepage'] = "Outbound";
        $this->template->load('template', 'outbond/receipt_outbond', $data);
    }

    function load_rec_out()
    {
        $result    = $this->Outbond_mdl->get_receiptoutbond();
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
