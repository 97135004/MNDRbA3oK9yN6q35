<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
                if (!$this->ion_auth->logged_in())
                {
                        redirect('dashboard', 'refresh');
                }


		$this->load->model('cabang_mdl');
		$this->load->model('Pph_badan_mdl');
		$this->load->model('Pph_mdl');
	}


	function show_equal_pph23()
	{
		$this->template->set('title', 'Rincian Beban Lain');
		$data['subtitle']   = "Cetak Laporan Ekualisasi PPh Pasal 23 dan 26";
		$data['activepage'] = "laporan_ekualisasi";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_equalisasi_pph23',$data);		
	}	
	
	function show_equal_ppn_tahun()
	{
		$this->template->set('title', 'Laporan Ekualisasi PPN');
		$data['subtitle']   = "Cetak Laporan Ekualisasi PPN";
		$data['activepage'] = "laporan_ekualisasi";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_equalisasi_ppn_masa',$data);		
	}		
	
	function show_report_spt_wapu_thn()
	{
		$this->template->set('title', 'Rincian Beban Lain');
		$data['subtitle']   = "Cetak Rekap SPT Setahun";
		$data['activepage'] = "ppn_wapu";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_rekap_spt_wapu_tahunan',$data);		
	}	
	
	function show_report_ppn_wapu_thn()
	{
		$this->template->set('title', 'Rekap PPN Setahun');
		$data['subtitle']   = "Cetak Rekap PPN Setahun";
		$data['activepage'] = "ppn_wapu";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_rekap_ppn_wapu_tahunan',$data);		
	}	
	
	function cetak_equal_pph23()
	{
		
		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		ob_start();		
		
		define('FPDF_FONTPATH',$this->config->item('fonts_path')); 
		$this->load->library('fpdf');
		//require('fpdf.php');		
		$pdf = new FPDF();
		$pdf->SetFont('Times','B',14);
		$pdf->AddPage();

		$header = array('Account', 'Acc Name', 'Grand Total','Hutang PPh 23', 'DPP PPh 23', 'PPh 23');	

		//$pdf->Cell(lebar kesamping,lebar ke bawah,isi/content,border,0,allignment,fill:true/false);
			
		//Title			
		$pdf->Cell(50,30,'',1,0,'L');
		$pdf->Image('./uploads/logo_ipc.png',12,12,50,25);
		$pdf->Cell(100,30,'IPC - PPh 23 Summary Report',1,1,'C');
		$pdf->Ln(10);				
			
		//parameter
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(40,5,'Tahun Pajak',0,0,'L');
		$pdf->Cell(0,5,' : '.$tahun,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Periode/Masa Pajak',0,0,'L');
		$pdf->Cell(0,5,' : '.$masa,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Cabang',0,0,'L');
		$pdf->Cell(0,5,' : '.$namacabang,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Report Date',0,0,'L');
		$pdf->Cell(0,5,' : '.$date,0,0,'L');
		$pdf->Ln(10);
		
		//header 1	
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(120,5,'Trial Balance','TL',0,'C');
		$pdf->Cell(60,5,'SPT SIMTAX','TLR',0,'C');
		$pdf->Ln();
		
		// Header text
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(30,5,$header[0],1,0,'C');
		$pdf->Cell(30,5,$header[1],1,0,'C');
		$pdf->Cell(30,5,$header[2],1,0,'C');
		$pdf->Cell(30,5,$header[3],1,0,'C');
		$pdf->Cell(30,5,$header[4],1,0,'C');
		$pdf->Cell(30,5,$header[5],1,0,'C');
		$pdf->Ln();
		
		//get detail
			$queryExec	= " select 
								   sum(nvl(spl.NEW_DPP,spl.DPP)) DPP
								 , sum(nvl(spl.NEW_JUMLAH_POTONG,spl.JUMLAH_POTONG)) JUMLAH_POTONG
								 , substr(spl.akun_pajak,17,8) KODE_AKUN
								 , (select ffvt.DESCRIPTION
									  from fnd_flex_values ffv
										 , fnd_flex_values_tl ffvt
										 , fnd_flex_value_sets ffvs
									where ffv.flex_value_id = ffvt.flex_value_id     
									  and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
									  and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
									  and ffv.FLEX_VALUE = substr(spl.akun_pajak,17,8)) NAMA_AKUN
							  from simtax_pajak_headers sph
								 , simtax_pajak_lines spl
								 , gl_code_combinations gcc
							 where sph.pajak_header_id = spl.pajak_header_id
							   and sph.BULAN_PAJAK = '".$bulan."'
							   and sph.tahun_pajak = '".$tahun."'
							   and sph.kode_cabang = '".$cabang."'
							   and sph.nama_pajak = 'PPH PSL 23 DAN 26'
							   and spl.TAX_CCID = gcc.CODE_COMBINATION_ID
							group by substr(spl.akun_pajak,17,8)
							order by 3
							";		
			
			$query 		= $this->db->query($queryExec);
			
			$ii	=	0;
			$batasText = 17;
			
			foreach($query->result_array() as $row)	{
					$ii++;
					
					//17 char
					$pdf->SetFont('Times','',10);		
					$panjangText = $pdf->GetStringWidth($row['NAMA_AKUN']);
					
					if ($panjangText <= 17) { 						
						$pdf->Cell(30,5,$row['KODE_AKUN'],1,0,'C');
						$pdf->Cell(30,5,$row['NAMA_AKUN'],1,0,'L');
						$pdf->Cell(30,5,'',1,0,'C');
						$pdf->Cell(30,5,'',1,0,'C');
						$pdf->Cell(30,5,number_format($row['DPP']),1,0,'R');
						$pdf->Cell(30,5,number_format($row['JUMLAH_POTONG']),1,0,'R');
						$pdf->Ln();
					} else {
						
						$sisaText   = $panjangText % $batasText;
						$banyakLoop = ceil($panjangText / $batasText);
						//$i-1*17
						for ($i = 1; $i <= $banyakLoop; $i++) {
							
							$awal = ($i-1);
							$akhir = $awal * $batasText;
							
							if ($i==1) {
								
								$pdf->Cell(30,5,$row['KODE_AKUN'],'LR',0,'C');
								$pdf->Cell(30,5,substr($row['NAMA_AKUN'],$akhir,$batasText-1),'LR',0,'L');
								$pdf->Cell(30,5,'','LR',0,'C');
								$pdf->Cell(30,5,'','LR',0,'C');
								$pdf->Cell(30,5,number_format($row['DPP']),'LR',0,'R');
								$pdf->Cell(30,5,number_format($row['JUMLAH_POTONG']),'LR',0,'R');
								$pdf->Ln();	
								
							} else if ($i = $banyakLoop){
								$pdf->Cell(30,5,'','LBR',0,'C');
								$pdf->Cell(30,5,substr($row['NAMA_AKUN'],$akhir,$batasText-1),'LBR',0,'L');
								$pdf->Cell(30,5,'','LBR',0,'C');
								$pdf->Cell(30,5,'','LBR',0,'C');
								$pdf->Cell(30,5,'','LBR',0,'R');
								$pdf->Cell(30,5,'','LBR',0,'R');
								$pdf->Ln();									
							} else {
								$pdf->Cell(30,5,'','LR',0,'C');
								$pdf->Cell(30,5,substr($row['NAMA_AKUN'],$akhir,$batasText-1),'LR',0,'L');
								$pdf->Cell(30,5,'','LR',0,'C');
								$pdf->Cell(30,5,'','LR',0,'C');
								$pdf->Cell(30,5,'','LR',0,'R');
								$pdf->Cell(30,5,'','LR',0,'R');
								$pdf->Ln();									
							}
						}						
					
					}	
					
			}		

		//end get detail			
		
		$pdf->Output();		
		ob_end_flush(); 
		//echo $this->fpdf->Output('hello_world.pdf','D');// Name of PDF file		
	}	
	
	function show_equal_pph22()
	{
		$this->template->set('title', 'Rincian Beban Lain');
		$data['subtitle']	= "Cetak Laporan Ekualisasi PPh Pasal 22";
		$data['error'] = "";
		$this->template->load('template', 'laporan/lap_equalisasi_pph22',$data);		
	}	
	
	function cetak_equal_pph22()
	{
		
		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		ob_start();		
		
		define('FPDF_FONTPATH',$this->config->item('fonts_path')); 
		//$this->load->library('fpdf');
		require('fpdf.php');		
		$pdf = new FPDF();
		$pdf->SetFont('Times','B',14);
		$pdf->AddPage();

		$header = array('Account', 'Acc Name', 'Grand Total','Hutang PPh 22', 'DPP PPh 22', 'PPh 22');	

		//$pdf->Cell(lebar kesamping,lebar ke bawah,isi/content,border,0,allignment,fill:true/false);
			
		//Title			
		$pdf->Cell(50,30,'',1,0,'L');
		$pdf->Image('./uploads/logo_ipc.png',12,12,50,25);
		$pdf->Cell(100,30,'IPC - PPh 22 Summary Report',1,1,'C');
		$pdf->Ln(10);				
			
		//parameter
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(40,5,'Tahun Pajak',0,0,'L');
		$pdf->Cell(0,5,' : '.$tahun,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Periode/Masa Pajak',0,0,'L');
		$pdf->Cell(0,5,' : '.$masa,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Cabang',0,0,'L');
		$pdf->Cell(0,5,' : '.$namacabang,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Report Date',0,0,'L');
		$pdf->Cell(0,5,' : '.$date,0,0,'L');
		$pdf->Ln(10);
		
		//header 1	
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(120,5,'Trial Balance','TL',0,'C');
		$pdf->Cell(60,5,'SPT SIMTAX','TLR',0,'C');
		$pdf->Ln();
		
		// Header text
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(30,5,$header[0],1,0,'C');
		$pdf->Cell(30,5,$header[1],1,0,'C');
		$pdf->Cell(30,5,$header[2],1,0,'C');
		$pdf->Cell(30,5,$header[3],1,0,'C');
		$pdf->Cell(30,5,$header[4],1,0,'C');
		$pdf->Cell(30,5,$header[5],1,0,'C');
		$pdf->Ln();
		
		//get detail
			$queryExec	= " select sum(nvl(spl.NEW_DPP,spl.DPP)) DPP
								 , sum(nvl(spl.NEW_JUMLAH_POTONG,spl.JUMLAH_POTONG)) JUMLAH_POTONG
								 , gcc.segment5 KODE_AKUN
								 , (select ffvt.DESCRIPTION
									  from fnd_flex_values ffv
										 , fnd_flex_values_tl ffvt
										 , fnd_flex_value_sets ffvs
									where ffv.flex_value_id = ffvt.flex_value_id     
									  and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
									  and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
									  and ffv.FLEX_VALUE = gcc.segment5) NAMA_AKUN
							  from simtax_pajak_headers sph
								 , simtax_pajak_lines spl
								 , gl_code_combinations gcc
							 where sph.pajak_header_id = spl.pajak_header_id
							   and sph.BULAN_PAJAK = '".$bulan."'
							   and sph.tahun_pajak = '".$tahun."'
							   and sph.kode_cabang = '".$cabang."'
							   and sph.nama_pajak = 'PPH PSL 22'
							   and spl.TAX_CCID = gcc.CODE_COMBINATION_ID
							group by gcc.segment5 
							order by 3
							";		
			
			$query 		= $this->db->query($queryExec);
			
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;
					
					$pdf->SetFont('Times','',10);
					$pdf->Cell(30,5,$row['KODE_AKUN'],1,0,'C');
					$pdf->Cell(30,5,$row['NAMA_AKUN'],1,0,'L');
					$pdf->Cell(30,5,'',1,0,'C');
					$pdf->Cell(30,5,'',1,0,'C');
					$pdf->Cell(30,5,number_format($row['DPP']),1,0,'R');
					$pdf->Cell(30,5,number_format($row['JUMLAH_POTONG']),1,0,'R');
					$pdf->Ln();
					
			}		

		//end get detail			
		
		$pdf->Output();		
		ob_end_flush(); 
		//echo $this->fpdf->Output('hello_world.pdf','D');// Name of PDF file		
	}	
	
	function show_equal_pph42()
	{
		$this->template->set('title', 'Rincian Beban Lain');
		$data['subtitle']	= "Cetak Laporan Ekualisasi PPh Pasal 4 Ayat 2";
		$data['error'] = "";
		$this->template->load('template', 'laporan/lap_equalisasi_pph42',$data);		
	}	
	
	function cetak_equal_pph42()
	{
		
		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		ob_start();		
		
		define('FPDF_FONTPATH',$this->config->item('fonts_path')); 
		//$this->load->library('fpdf');
		require('fpdf.php');		
		$pdf = new FPDF();
		$pdf->SetFont('Times','B',14);
		$pdf->AddPage();

		$header = array('Account', 'Acc Name', 'Grand Total','Hutang PPh 4(2)', 'DPP PPh 4(2)', 'PPh 4(2)');	

		//$pdf->Cell(lebar kesamping,lebar ke bawah,isi/content,border,0,allignment,fill:true/false);
			
		//Title			
		$pdf->Cell(50,30,'',1,0,'L');
		$pdf->Image('./uploads/logo_ipc.png',12,12,50,25);
		$pdf->Cell(100,30,'IPC - PPh 4 Ayat 2 Summary Report',1,1,'C');
		$pdf->Ln(10);				
			
		//parameter
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(40,5,'Tahun Pajak',0,0,'L');
		$pdf->Cell(0,5,' : '.$tahun,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Periode/Masa Pajak',0,0,'L');
		$pdf->Cell(0,5,' : '.$masa,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Cabang',0,0,'L');
		$pdf->Cell(0,5,' : '.$namacabang,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(40,5,'Report Date',0,0,'L');
		$pdf->Cell(0,5,' : '.$date,0,0,'L');
		$pdf->Ln(10);
		
		//header 1	
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(120,5,'Trial Balance','TL',0,'C');
		$pdf->Cell(60,5,'SPT SIMTAX','TLR',0,'C');
		$pdf->Ln();
		
		// Header text
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(30,5,$header[0],1,0,'C');
		$pdf->Cell(30,5,$header[1],1,0,'C');
		$pdf->Cell(30,5,$header[2],1,0,'C');
		$pdf->Cell(30,5,$header[3],1,0,'C');
		$pdf->Cell(30,5,$header[4],1,0,'C');
		$pdf->Cell(30,5,$header[5],1,0,'C');
		$pdf->Ln();
		
		//get detail
			$queryExec	= " select sum(nvl(spl.NEW_DPP,spl.DPP)) DPP
								 , sum(nvl(spl.NEW_JUMLAH_POTONG,spl.JUMLAH_POTONG)) JUMLAH_POTONG
								 , gcc.segment5 KODE_AKUN
								 , (select ffvt.DESCRIPTION
									  from fnd_flex_values ffv
										 , fnd_flex_values_tl ffvt
										 , fnd_flex_value_sets ffvs
									where ffv.flex_value_id = ffvt.flex_value_id     
									  and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
									  and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
									  and ffv.FLEX_VALUE = gcc.segment5) NAMA_AKUN
							  from simtax_pajak_headers sph
								 , simtax_pajak_lines spl
								 , gl_code_combinations gcc
							 where sph.pajak_header_id = spl.pajak_header_id
							   and sph.BULAN_PAJAK = '".$bulan."'
							   and sph.tahun_pajak = '".$tahun."'
							   and sph.kode_cabang = '".$cabang."'
							   and sph.nama_pajak = 'PPH PSL 4 AYAT 2'
							   and spl.TAX_CCID = gcc.CODE_COMBINATION_ID
							group by gcc.segment5 
							order by 3
							";		
			
			$query 		= $this->db->query($queryExec);
			
			$ii	=	0;
			foreach($query->result_array() as $row)	{
					$ii++;
					
					$pdf->SetFont('Times','',10);
					$pdf->Cell(30,5,$row['KODE_AKUN'],1,0,'C');
					$pdf->Cell(30,5,$row['NAMA_AKUN'],1,0,'L');
					$pdf->Cell(30,5,'',1,0,'C');
					$pdf->Cell(30,5,'',1,0,'C');
					$pdf->Cell(30,5,number_format($row['DPP']),1,0,'R');
					$pdf->Cell(30,5,number_format($row['JUMLAH_POTONG']),1,0,'R');
					$pdf->Ln();
					
			}		

		//end get detail			
		
		$pdf->Output();		
		ob_end_flush(); 
		//echo $this->fpdf->Output('hello_world.pdf','D');// Name of PDF file		
	}	
	
	
	function cetak_equal_pph23_xls()
	{

		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi PPH 23/26")
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi PPH 23/26")
								->setKeywords("PPH 23/26");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
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
		$excel->setActiveSheetIndex(0)->setCellValue('A1', ""); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('A1:B6'); // Set Merge Cell pada kolom A1 sampai E1
		
		//Judul Laporan
		$excel->setActiveSheetIndex(0)->setCellValue('C1', "IPC - PPh Pasal 23 Summary Report"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('C1:F6'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // Set text center untuk kolom A1

		//set parameter
		$excel->setActiveSheetIndex(0)->setCellValue('A8', "Tahun Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B8', ": ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A9', "Periode/Masa Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B9', ": ".$masa); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A10', "Cabang"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B10', ": ".$namacabang); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A11', "Report Date"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B11', ": ".$date); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		// Buat header tabel
		$excel->setActiveSheetIndex(0)->setCellValue('A14', "Trial Balance"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A14:D14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14:D14')->applyFromArray($style_row);
		
		$excel->setActiveSheetIndex(0)->setCellValue('E14', "SPT SIMTAX"); // Set kolom B3 dengan tulisan "NIS"
		$excel->getActiveSheet()->mergeCells('E14:F14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14:F14')->applyFromArray($style_row);
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('A15', "Account"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B15', "Acc Name"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C15', "Grand Total"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D15', "Hutang PPh 23"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E15', "DPP PPh 23 "); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F15', "PPh 23"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->getStyle('A15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E15')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F15')->applyFromArray($style_row);				

		/*
		$gdImage = imagecreatefromjpeg('./uploads/logo_ipc.jpg');
		$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setCoordinates('A1');
		$objDrawing->setImageResource($gdImage);
		//$objDrawing->setPath($gdImage);
		//$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		//$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setOffsetX(25);                       //setOffsetX works properly
		$objDrawing->setOffsetY(10);   		
		$objDrawing->setWidth(150);                 //set width, height
		$objDrawing->setHeight(100);
		$objDrawing->setWorksheet($excel->getActiveSheet());
		*/
		
		//get detail
			$queryExec	= " select 
								   sum(nvl(spl.NEW_DPP,spl.DPP)) DPP
								 , sum(nvl(spl.NEW_JUMLAH_POTONG,spl.JUMLAH_POTONG)) JUMLAH_POTONG
                                 , nvl(substr(spl.akun_pajak,17,8),'N/A') KODE_AKUN
                                 , nvl((select ffvt.DESCRIPTION
                                      from fnd_flex_values ffv
                                         , fnd_flex_values_tl ffvt
                                         , fnd_flex_value_sets ffvs
                                    where ffv.flex_value_id = ffvt.flex_value_id     
                                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                                      and ffv.FLEX_VALUE = substr(spl.akun_pajak,17,8)),'N/A') NAMA_AKUN
							  from simtax_pajak_headers sph
								 , simtax_pajak_lines spl
								 , gl_code_combinations gcc
							 where sph.pajak_header_id = spl.pajak_header_id
							   and sph.BULAN_PAJAK = '".$bulan."'
							   and sph.tahun_pajak = '".$tahun."'
							   and sph.kode_cabang = '".$cabang."'
							   and sph.nama_pajak = 'PPH PSL 23 DAN 26'
							   and spl.TAX_CCID = gcc.CODE_COMBINATION_ID
							   and nvl(spl.IS_CHEKLIST,0) = 1
							group by substr(spl.akun_pajak,17,8)
							order by 3
							";		
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 16; // Set baris pertama untuk isi tabel adalah baris ke 4
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $row['KODE_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['DPP']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['JUMLAH_POTONG']);

				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				

				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Ekualisasi PPh 23 dan 26");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Lap Ekualisasi PPh 23 dan 26.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}
	
	function cetak_equal_pph42_xls()
	{

		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi PPH 4 Ayat 2")
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi PPH 4 Ayat 2")
								->setKeywords("PPH 4 Ayat 2");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
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
		$excel->setActiveSheetIndex(0)->setCellValue('A1', ""); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('A1:B6'); // Set Merge Cell pada kolom A1 sampai E1
		
		//Judul Laporan
		$excel->setActiveSheetIndex(0)->setCellValue('C1', "IPC - PPh Pasal 4 Ayat 2 Summary Report"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('C1:F6'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // Set text center untuk kolom A1

		//set parameter
		$excel->setActiveSheetIndex(0)->setCellValue('A8', "Tahun Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B8', ": ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A9', "Periode/Masa Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B9', ": ".$masa); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A10', "Cabang"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B10', ": ".$namacabang); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A11', "Report Date"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B11', ": ".$date); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		// Buat header tabel
		$excel->setActiveSheetIndex(0)->setCellValue('A14', "Trial Balance"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A14:D14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14:D14')->applyFromArray($style_row);
		
		$excel->setActiveSheetIndex(0)->setCellValue('E14', "SPT SIMTAX"); // Set kolom B3 dengan tulisan "NIS"
		$excel->getActiveSheet()->mergeCells('E14:F14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14:F14')->applyFromArray($style_row);
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('A15', "Account"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B15', "Acc Name"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C15', "Grand Total"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D15', "Hutang PPh 4 Ayat 2"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E15', "DPP PPh 4 Ayat 2"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F15', "PPh 4 Ayat 2"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->getStyle('A15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E15')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F15')->applyFromArray($style_row);				

		/*
		$gdImage = imagecreatefromjpeg('./uploads/logo_ipc.jpg');
		$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setCoordinates('A1');
		$objDrawing->setImageResource($gdImage);
		//$objDrawing->setPath($gdImage);
		//$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		//$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setOffsetX(25);                       //setOffsetX works properly
		$objDrawing->setOffsetY(10);   		
		$objDrawing->setWidth(150);                 //set width, height
		$objDrawing->setHeight(100);
		$objDrawing->setWorksheet($excel->getActiveSheet());
		*/
		
		//get detail
			$queryExec	= " select sum(nvl(spl.NEW_DPP,spl.DPP)) DPP
								 , sum(nvl(spl.NEW_JUMLAH_POTONG,spl.JUMLAH_POTONG)) JUMLAH_POTONG
								 , gcc.segment5 KODE_AKUN
								 , (select ffvt.DESCRIPTION
									  from fnd_flex_values ffv
										 , fnd_flex_values_tl ffvt
										 , fnd_flex_value_sets ffvs
									where ffv.flex_value_id = ffvt.flex_value_id     
									  and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
									  and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
									  and ffv.FLEX_VALUE = gcc.segment5) NAMA_AKUN
							  from simtax_pajak_headers sph
								 , simtax_pajak_lines spl
								 , gl_code_combinations gcc
							 where sph.pajak_header_id = spl.pajak_header_id
							   and sph.BULAN_PAJAK = '".$bulan."'
							   and sph.tahun_pajak = '".$tahun."'
							   and sph.kode_cabang = '".$cabang."'
							   and sph.nama_pajak = 'PPH PSL 4 AYAT 2'
							   and spl.TAX_CCID = gcc.CODE_COMBINATION_ID
							group by gcc.segment5 
							order by 3
							";			
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 16; // Set baris pertama untuk isi tabel adalah baris ke 4
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $row['KODE_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['DPP']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['JUMLAH_POTONG']);

				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				

				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Ekualisasi PPh 4 Ayat 2");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Lap Ekualisasi PPh 4 Ayat 2.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}	
	
	function cetak_equal_pph22_xls()
	{

		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi PPH 22")
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi PPH 22")
								->setKeywords("PPH 22");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
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
		$excel->setActiveSheetIndex(0)->setCellValue('A1', ""); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('A1:B6'); // Set Merge Cell pada kolom A1 sampai E1
		
		//Judul Laporan
		$excel->setActiveSheetIndex(0)->setCellValue('C1', "IPC - PPh Pasal 22 Summary Report"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('C1:F6'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // Set text center untuk kolom A1

		//set parameter
		$excel->setActiveSheetIndex(0)->setCellValue('A8', "Tahun Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B8', ": ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A9', "Periode/Masa Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B9', ": ".$masa); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A10', "Cabang"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B10', ": ".$namacabang); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A11', "Report Date"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B11', ": ".$date); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		// Buat header tabel
		$excel->setActiveSheetIndex(0)->setCellValue('A14', "Trial Balance"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A14:D14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14:D14')->applyFromArray($style_row);
		
		$excel->setActiveSheetIndex(0)->setCellValue('E14', "SPT SIMTAX"); // Set kolom B3 dengan tulisan "NIS"
		$excel->getActiveSheet()->mergeCells('E14:F14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14:F14')->applyFromArray($style_row);
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('A15', "Account"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B15', "Acc Name"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C15', "Grand Total"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D15', "Hutang PPh 22"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E15', "DPP PPh 22"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F15', "PPh 22"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->getStyle('A15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E15')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F15')->applyFromArray($style_row);				

		/*
		$gdImage = imagecreatefromjpeg('./uploads/logo_ipc.jpg');
		$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setCoordinates('A1');
		$objDrawing->setImageResource($gdImage);
		//$objDrawing->setPath($gdImage);
		//$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		//$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setOffsetX(25);                       //setOffsetX works properly
		$objDrawing->setOffsetY(10);   		
		$objDrawing->setWidth(150);                 //set width, height
		$objDrawing->setHeight(100);
		$objDrawing->setWorksheet($excel->getActiveSheet());
		*/
		
		//get detail
			$queryExec	= " select sum(nvl(spl.NEW_DPP,spl.DPP)) DPP
								 , sum(nvl(spl.NEW_JUMLAH_POTONG,spl.JUMLAH_POTONG)) JUMLAH_POTONG
								 , gcc.segment5 KODE_AKUN
								 , (select ffvt.DESCRIPTION
									  from fnd_flex_values ffv
										 , fnd_flex_values_tl ffvt
										 , fnd_flex_value_sets ffvs
									where ffv.flex_value_id = ffvt.flex_value_id     
									  and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
									  and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
									  and ffv.FLEX_VALUE = gcc.segment5) NAMA_AKUN
							  from simtax_pajak_headers sph
								 , simtax_pajak_lines spl
								 , gl_code_combinations gcc
							 where sph.pajak_header_id = spl.pajak_header_id
							   and sph.BULAN_PAJAK = '".$bulan."'
							   and sph.tahun_pajak = '".$tahun."'
							   and sph.kode_cabang = '".$cabang."'
							   and sph.nama_pajak = 'PPH PSL 22'
							   and spl.TAX_CCID = gcc.CODE_COMBINATION_ID
							group by gcc.segment5 
							order by 3
							";			
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 16; // Set baris pertama untuk isi tabel adalah baris ke 4
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $row['KODE_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['DPP']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['JUMLAH_POTONG']);

				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				

				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Ekualisasi PPh 22");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Lap Ekualisasi PPh 22.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}	
	
	function show_equal_pph21()
	{
		$this->template->set('title', 'Rincian Beban Lain');
		$data['subtitle']	= "Cetak Laporan Ekualisasi PPh Pasal 21";
		$data['error'] = "";
		$this->template->load('template', 'laporan/lap_equalisasi_pph21',$data);		
	}
	
	function cetak_equal_pph21_xls()
	{

		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		
		$shortMonthArr 		= array("", "JAN", "FEB", "MAR", "APR", "MEI", "JUN", "JUL", "AGU", "SEP", "OKT", "NOV", "DES");
		$bulanTeks			= $shortMonthArr[$bulan];		
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi PPH 21")
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi PPH 21")
								->setKeywords("PPH 23/26");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
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
		$excel->setActiveSheetIndex(0)->setCellValue('A1', ""); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('A1:B6'); // Set Merge Cell pada kolom A1 sampai E1
		
		//Judul Laporan
		$excel->setActiveSheetIndex(0)->setCellValue('C1', "IPC - PPh Pasal 21 Summary Report"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('C1:F6'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('C1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // Set text center untuk kolom A1

		//set parameter
		$excel->setActiveSheetIndex(0)->setCellValue('A8', "Tahun Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B8', ": ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A9', "Periode/Masa Pajak"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B9', ": ".$masa); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A10', "Cabang"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B10', ": ".$namacabang); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A11', "Report Date"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B11', ": ".$date); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		// Buat header tabel
		$excel->setActiveSheetIndex(0)->setCellValue('A14', "Trial Balance"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A14:D14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('A14:D14')->applyFromArray($style_row);
		
		$excel->setActiveSheetIndex(0)->setCellValue('E14', "SPT SIMTAX"); // Set kolom B3 dengan tulisan "NIS"
		$excel->getActiveSheet()->mergeCells('E14:F14'); // Set Merge Cell pada kolom A1 sampai E1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setBold(TRUE); // Set bold kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getFont()->setSize(15); // Set font size 15 untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // Set text center untuk kolom A1
		$excel->getActiveSheet()->getStyle('E14:F14')->applyFromArray($style_row);
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('A15', "Account"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B15', "Acc Name"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C15', "Grand Total"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D15', "Hutang PPh 21"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E15', "DPP PPh 21"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F15', "PPh 21"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->getStyle('A15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E15')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F15')->applyFromArray($style_row);				

		/*
		$gdImage = imagecreatefromjpeg('./uploads/logo_ipc.jpg');
		$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setCoordinates('A1');
		$objDrawing->setImageResource($gdImage);
		$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setOffsetX(25);                       //setOffsetX works properly
		$objDrawing->setOffsetY(10);   		
		$objDrawing->setWidth(150);                 //set width, height
		$objDrawing->setHeight(100);
		$objDrawing->setWorksheet($excel->getActiveSheet());
		*/
		
		//get detail				
			$queryExec	= " select sum(COSTED_VALUE) DPP
								 , NULL JUMLAH_POTONG
								 , SPD.segment5 KODE_AKUN
								 , (select ffvt.DESCRIPTION
									  from fnd_flex_values ffv
										 , fnd_flex_values_tl ffvt
										 , fnd_flex_value_sets ffvs
									where ffv.flex_value_id = ffvt.flex_value_id     
									  and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
									  and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
									  and ffv.FLEX_VALUE = SPD.segment5) NAMA_AKUN
							from SIMTAX_PPH21_DTL SPD
							 where segment2 = '".$cabang."'
							   and segment1 = '01'  
							   and to_char(EFFECTIVE_DATE,'MON-YYYY') = '".$bulanTeks."-".$tahun."'
							group by segment5
							order by 3
							";								
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 16; // Set baris pertama untuk isi tabel adalah baris ke 4
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $row['KODE_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_AKUN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['DPP']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['JUMLAH_POTONG']);

				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				

				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Ekualisasi PPh 21");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Lap Ekualisasi PPh 21.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}	
	
	function cetak_report_spt_wapu_thn_xls()
	{

		$tahun 			= $_REQUEST['tahun'];
		$pembetulanKe 	= $_REQUEST['pembetulanKe'];
		$cabang 		= $_REQUEST['kd_cabang'];

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
		} else{
			$kd_cabang = '';
		}
		
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak SPT Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak SPT Setahun")
								->setKeywords("WAPU");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_cur = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, // Set text jadi di tengah secara vertical (middle)
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)

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
		$excel->setActiveSheetIndex(0)->setCellValue('A2', "REKAP SPT KOMPILASI"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A5', "SPT NORMAL"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A6', "TAHUN ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Januari"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D7', "Februari"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E7', "Maret"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F7', "April"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('G7', "Mei"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('H7', "Juni"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('I7', "Juli"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J7', "Agustus"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('K7', "September"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('L7', "Oktober"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('M7', "November"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('N7', "Desember"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->getActiveSheet()->getStyle('A7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('G7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('H7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('I7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('J7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('K7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('L7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('M7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('N7')->applyFromArray($style_row);				
		
		
		//get detail
			/*
			$queryExec	= " select rownum, wapu.* from (select kode_cabang, nama_cabang, spt, bulan_pajak from simtax_rpt_spt_wapu_tahunan_v
							where tahun_pajak = '".$tahun."'
							and pembetulan_ke = 0)
							pivot (
							max(spt)
							for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
							) wapu
							order by kode_cabang
							";								
			*/
			if ($kd_cabang ==""){
				$whereCabang = "'000','010','020','030','040','050', '060','070','080','090','100','110','120'";
			} else{
				$whereCabang = "'".$kd_cabang."'";
			}

			$queryExec	= "select skc.KODE_CABANG
								--, skc.NAMA_CABANG
								, case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								  else 'Cabang ' || skc.nama_cabang
								  end nama_cabang								
								, januari, februari, maret, april, mei, juni, juli, agustus, september, oktober, november, desember from simtax_kode_cabang skc
								,(select rownum, wapu.* from (select kode_cabang, nama_cabang, disp_ppn_ptg, bulan_pajak from simtax_rpt_spt_wapu_tahunan_v
															where tahun_pajak = '".$tahun."'
															and pembetulan_ke = '".$pembetulanKe."')
															pivot (
															max(disp_ppn_ptg)
															for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
															) wapu
															) rpt
								where skc.kode_cabang = rpt.kode_cabang (+)                            
								  and skc.kode_cabang in (".$whereCabang.")
								order by skc.kode_cabang";
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 8; // Set baris pertama untuk isi tabel adalah baris ke 4
			$ttl_jan = 0;								
			$ttl_feb = 0;								
			$ttl_mar = 0;								
			$ttl_apr = 0;								
			$ttl_mei = 0;								
			$ttl_jun = 0;								
			$ttl_jul = 0;								
			$ttl_aug = 0;								
			$ttl_sep = 0;								
			$ttl_okt = 0;								
			$ttl_nov = 0;								
			$ttl_des = 0;	
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, ($row['JANUARI']) ? $row['JANUARI'] : "-");	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, ($row['FEBRUARI']) ? $row['FEBRUARI'] : "-");	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, ($row['MARET']) ? $row['MARET'] : "-");	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, ($row['APRIL']) ? $row['APRIL']: "-");
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, ($row['MEI']) ? $row['MEI'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($row['JUNI']) ? $row['JUNI'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, ($row['JULI']) ? $row['JULI'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, ($row['AGUSTUS']) ? $row['AGUSTUS'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, ($row['SEPTEMBER']) ? $row['SEPTEMBER'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, ($row['OKTOBER']) ? $row['OKTOBER'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($row['NOVEMBER']) ? $row['NOVEMBER'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, ($row['DESEMBER']) ? $row['DESEMBER'] : "-");

				$excel->getActiveSheet()->getStyle('C'.$numrow.':N'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
								
				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_cur);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_cur);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_cur);				
												
				$ttl_jan = $ttl_jan + $row['JANUARI'];								
				$ttl_feb = $ttl_feb + $row['FEBRUARI'];								
				$ttl_mar = $ttl_mar + $row['MARET'];								
				$ttl_apr = $ttl_apr + $row['APRIL'];								
				$ttl_mei = $ttl_mei + $row['MEI'];								
				$ttl_jun = $ttl_jun + $row['JUNI'];								
				$ttl_jul = $ttl_jul + $row['JULI'];								
				$ttl_aug = $ttl_aug + $row['AGUSTUS'];								
				$ttl_sep = $ttl_sep + $row['SEPTEMBER'];								
				$ttl_okt = $ttl_okt + $row['OKTOBER'];								
				$ttl_nov = $ttl_nov + $row['NOVEMBER'];								
				$ttl_des = $ttl_des + $row['DESEMBER'];	
				
				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		//total
		$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, "JUMLAH DISETOR");
		$excel->getActiveSheet()->mergeCells('A'.$numrow.':B'.$numrow);		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, ($ttl_jan) ? $ttl_jan : "-");	
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, ($ttl_feb) ? $ttl_feb : "-");	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, ($ttl_mar) ? $ttl_mar : "-");	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, ($ttl_apr) ? $ttl_apr : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, ($ttl_mei) ? $ttl_mei : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($ttl_jun) ? $ttl_jun : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, ($ttl_jul) ? $ttl_jul : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, ($ttl_aug) ? $ttl_aug : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, ($ttl_sep) ? $ttl_sep : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, ($ttl_okt) ? $ttl_okt : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($ttl_nov) ? $ttl_nov : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, ($ttl_des) ? $ttl_des : "-");

		$excel->getActiveSheet()->getStyle('C'.$numrow.':N'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_cur);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_cur);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_cur);
				
		//setahun
		$numrow = $numrow += 1; //$numrow++;
		$ttl_all = $ttl_jan + $ttl_feb + $ttl_mar + $ttl_apr + $ttl_mei + $ttl_jun + $ttl_jul + $ttl_aug + $ttl_sep + $ttl_okt + $ttl_nov + $ttl_des;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH SETAHUN");		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($ttl_all ),2,'.',',');	

		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_cur);
		
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekapt SPT Setahun");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap SPT Tahunan WAPU.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}		
	
	function cetak_report_ppn_wapu_thn_xls()
	{

		$tahun 			= $_REQUEST['tahun'];
		$pembetulanKe 	= $_REQUEST['pembetulanKe'];
		
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak PPN Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak PPN Setahun")
								->setKeywords("WAPU");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_cur = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, // Set text jadi di tengah secara vertical (middle)
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi di tengah secara vertical (middle)
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
		$excel->setActiveSheetIndex(0)->setCellValue('A2', "KOMPILASI"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A5', "REKAP PEMBAYARAN PPN/WAPU"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A6', "TAHUN ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Januari"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D7', "Februari"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E7', "Maret"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F7', "April"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('G7', "Mei"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('H7', "Juni"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('I7', "Juli"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J7', "Agustus"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('K7', "September"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('L7', "Oktober"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('M7', "November"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('N7', "Desember"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->getActiveSheet()->getStyle('A7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('G7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('H7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('I7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('J7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('K7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('L7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('M7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('N7')->applyFromArray($style_row);				
		
		
		//get detail
			/*
			$queryExec	= " select rownum, wapu.* from (select kode_cabang, nama_cabang, ppn, bulan_pajak from simtax_rpt_spt_wapu_tahunan_v
							where tahun_pajak = '".$tahun."'
							and pembetulan_ke = 0)
							pivot (
							max(ppn)
							for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
							) wapu
							order by kode_cabang
							";	
			*/	

			$queryExec	= "select skc.KODE_CABANG
								--, skc.NAMA_CABANG
								 , case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								   else 'Cabang ' || skc.nama_cabang
								   end nama_cabang								
								, januari
								, februari, maret, april, mei, juni, juli, agustus, september, oktober, november, desember from simtax_kode_cabang skc
								,(select rownum, wapu.* from (select kode_cabang, nama_cabang, ppn, bulan_pajak from simtax_rpt_spt_wapu_tahunan_v
															where tahun_pajak = '".$tahun."'
															and pembetulan_ke = '".$pembetulanKe."')
															pivot (
															max(ppn)
															for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
															) wapu
															) rpt
								where skc.kode_cabang = rpt.kode_cabang (+)                            
								  and skc.kode_cabang in ('000','010','020','030','040','050',
															'060','070','080','090','100','110','120')
								order by skc.kode_cabang";	
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 8; // Set baris pertama untuk isi tabel adalah baris ke 4
			$ttl_jan = 0;								
			$ttl_feb = 0;								
			$ttl_mar = 0;								
			$ttl_apr = 0;								
			$ttl_mei = 0;								
			$ttl_jun = 0;								
			$ttl_jul = 0;								
			$ttl_aug = 0;								
			$ttl_sep = 0;								
			$ttl_okt = 0;								
			$ttl_nov = 0;								
			$ttl_des = 0;	
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, ($row['JANUARI']) ? $row['JANUARI'] : "-");	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, ($row['FEBRUARI']) ? $row['FEBRUARI'] : "-");	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, ($row['MARET']) ? $row['MARET'] : "-");	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, ($row['APRIL']) ? $row['APRIL'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, ($row['MEI']) ? $row['MEI'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($row['JUNI']) ? $row['JUNI'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, ($row['JULI']) ? $row['JULI'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, ($row['AGUSTUS']) ? $row['AGUSTUS'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, ($row['SEPTEMBER']) ? $row['SEPTEMBER'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, ($row['OKTOBER']) ? $row['OKTOBER'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($row['NOVEMBER']) ? $row['NOVEMBER'] : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, ($row['DESEMBER']) ? $row['DESEMBER'] : "-");

				$excel->getActiveSheet()->getStyle('C'.$numrow.':N'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
								
				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_cur);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_cur);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_cur);				
				$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_cur);				
												
				$ttl_jan = $ttl_jan + $row['JANUARI'];								
				$ttl_feb = $ttl_feb + $row['FEBRUARI'];								
				$ttl_mar = $ttl_mar + $row['MARET'];								
				$ttl_apr = $ttl_apr + $row['APRIL'];								
				$ttl_mei = $ttl_mei + $row['MEI'];								
				$ttl_jun = $ttl_jun + $row['JUNI'];								
				$ttl_jul = $ttl_jul + $row['JULI'];								
				$ttl_aug = $ttl_aug + $row['AGUSTUS'];								
				$ttl_sep = $ttl_sep + $row['SEPTEMBER'];								
				$ttl_okt = $ttl_okt + $row['OKTOBER'];								
				$ttl_nov = $ttl_nov + $row['NOVEMBER'];								
				$ttl_des = $ttl_des + $row['DESEMBER'];	
					
				$no++;	
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		//total
		$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, "JUMLAH DISETOR");
		$excel->getActiveSheet()->mergeCells('A'.$numrow.':B'.$numrow);		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, ($ttl_jan) ? $ttl_jan : "-");	
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, ($ttl_feb) ? $ttl_feb : "-");	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, ($ttl_mar) ? $ttl_mar : "-");	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, ($ttl_apr) ? $ttl_apr : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, ($ttl_mei) ? $ttl_mei : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($ttl_jun) ? $ttl_jun : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, ($ttl_jul) ? $ttl_jul : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, ($ttl_aug) ? $ttl_aug : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, ($ttl_sep) ? $ttl_sep : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, ($ttl_okt) ? $ttl_okt : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($ttl_nov) ? $ttl_nov : "-");
		$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, ($ttl_des) ? $ttl_des : "-");

		$excel->getActiveSheet()->getStyle('C'.$numrow.':N'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_cur);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_cur);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_cur);				
		$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_cur);
		
		//setahun
		//setahun
		$numrow = $numrow += 1; //$numrow++;
		$ttl_all = $ttl_jan + $ttl_feb + $ttl_mar + $ttl_apr + $ttl_mei + $ttl_jun + $ttl_jul + $ttl_aug + $ttl_sep + $ttl_okt + $ttl_nov + $ttl_des;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH SETAHUN");		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($ttl_all ),2,'.',',');

		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_cur);
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekap PPN Setahun");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap PPN Tahunan WAPU.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}
	
	function show_report_ppn_masa_bln()
	{
		$this->template->set('title', 'PPN MASA BULANAN');
		$data['subtitle']   = "Rekap Pembayaran";
		$data['activepage'] = "ppn_masa";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_rekap_ppn_masa_bulanan',$data);		
	}	
	
	function cetak_report_ppn_masa_bln()
	{
		
		$tahun        = $_REQUEST['tahun'];
		$bulan        = $_REQUEST['bulan'];
		$namabulan    = $_REQUEST['namabulan'];
		$pembetulanKe = $_REQUEST['pembetulanKe'];
		
		$date        = date("Y-m-d H:i:s");
		
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_1 = array(
			'font' 	   => array('bold' => true),
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_kolom = array(
			'font' 	   => array('bold' => true),
		   'alignment' => array(
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
		$excel->getActiveSheet()->getStyle('A1')->applyFromArray($style_row_1);

		$excel->setActiveSheetIndex(0)->setCellValue('A3', "REKAP PEMBAYARAN PPN MASA ".$namabulan." ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('A3')->applyFromArray($style_row_1);
		
		// Buat header tabel nya pada baris ke 3
		
		$excel->setActiveSheetIndex(0)->setCellValue('A4', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C4', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D4', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E4', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('C5', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D5', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E5', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F5', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"
		
		$excel->getActiveSheet()->mergeCells('A4:A5');		
		$excel->getActiveSheet()->mergeCells('B4:B5');		
		
		$excel->getActiveSheet()->getStyle('A4:A5')->applyFromArray($style_row_kolom);
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($style_row_kolom);
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($style_row_kolom);
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($style_row_kolom);
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($style_row_kolom);				
		$excel->getActiveSheet()->getStyle('F4:F5')->applyFromArray($style_row_kolom);	
		
		$excel->getActiveSheet()->getStyle('A4:A5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('F4:F5')->applyFromArray($style_col2);		
		/*
		$excel->getActiveSheet()->getStyle('C8')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D8')->applyFromArray($style_col2);		
		$excel->getActiveSheet()->getStyle('E8')->applyFromArray($style_col2);		
		$excel->getActiveSheet()->getStyle('F8')->applyFromArray($style_col2);		
		*/
		
		//get detail				
			/*$joinCondition  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splm.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splm.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splm.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splm.ORGANIZATION_ID";*/
			$queryExec	= " select skc.kode_cabang
								 --, skc.nama_cabang
								 , case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								   else 'Cabang ' || skc.nama_cabang
								   end nama_cabang								 
								 , ppn_header.bulan_pajak
								 , ppn_header.tahun_pajak
								 , nvl(ppn_keluaran.jumlah_potong,0) PPN_KELUARAN
								 , nvl(ppn_masukan.jumlah_potong,0) PPN_MASUKAN
								 , nvl(pmk.jumlah_potong,0) pmk78
								 , abs(NVL (pmk_78.pmk_78, 0)) pmk78 
								 , nvl(ppn_keluaran.jumlah_potong,0) - (nvl(ppn_masukan.jumlah_potong,0) - ABS (NVL (pmk_78.pmk_78, 0))) KURANG_LEBIH
							  from simtax_kode_cabang skc
							, (select 
								   skc.NAMA_CABANG
								 , sphh.KODE_CABANG
								 , sphh.TAHUN_PAJAK
								 , sphh.BULAN_PAJAK
								 , sphh.MASA_PAJAK
							  from simtax_pajak_headers sphh
								 , simtax_pajak_lines splh
								 , simtax_kode_cabang skc
							 where sphh.nama_pajak in ('PPN KELUARAN','PPN MASUKAN')
							   and sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
							   and nvl(splh.IS_CHEKLIST,0) = 1
							   and skc.KODE_CABANG = sphh.KODE_CABANG
							   and sphh.tahun_pajak = '".$tahun."'
							   and sphh.bulan_pajak = '".$bulan."'
							   and sphh.pembetulan_ke = '".$pembetulanKe."'
							group by skc.NAMA_CABANG, sphh.KODE_CABANG, sphh.TAHUN_PAJAK, sphh.BULAN_PAJAK, sphh.MASA_PAJAK) ppn_header
							,(select skc.NAMA_CABANG
								 , sphm.KODE_CABANG
								 , sphm.TAHUN_PAJAK
								 , sphm.BULAN_PAJAK
								 , sphm.MASA_PAJAK
								 --, sum(nvl(splm.JUMLAH_POTONG,0))*-1 JUMLAH_POTONG
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
							  from simtax_pajak_headers sphm
								 , simtax_pajak_lines splm
								 , simtax_kode_cabang skc
							 where sphm.nama_pajak = 'PPN KELUARAN'
							   and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
							   and nvl(splm.IS_CHEKLIST,0) = 1
							   and skc.KODE_CABANG = sphm.KODE_CABANG
							   and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."'
							   and sphm.pembetulan_ke = '".$pembetulanKe."'
							   and splm.kd_jenis_transaksi IN (1,4,6,9)  
							group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_keluaran
							,(select skc.NAMA_CABANG
								 , sphm.KODE_CABANG
								 , sphm.TAHUN_PAJAK
								 , sphm.BULAN_PAJAK
								 , sphm.MASA_PAJAK
								 --, sum(nvl(splm.JUMLAH_POTONG,0))*-1 JUMLAH_POTONG
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
								 , min(abs(nvl(sphm.PMK78,0))) PMK78
							  from simtax_pajak_headers sphm
								 , simtax_pajak_lines splm
								 , simtax_kode_cabang skc
							 where sphm.nama_pajak = 'PPN MASUKAN'
							   and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
							   and nvl(splm.IS_CHEKLIST,0) = 1
							   and skc.KODE_CABANG = sphm.KODE_CABANG
							   and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."'
							   and sphm.pembetulan_ke = '".$pembetulanKe."'
							   and ((splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') or (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1'))
							group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_masukan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and splm.dl_fs = 'dokumen_lain' 
                               and splm.kd_jenis_transaksi IN ('11','12') 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_impor,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and splm.is_pmk = '1'
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) pmk,
                            (  SELECT skc.NAMA_CABANG,
                               sphm.KODE_CABANG,
                               sphm.TAHUN_PAJAK,
                               sphm.BULAN_PAJAK,
                               sphm.MASA_PAJAK,
                               ceil(ABS(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100)
                               - SUM (NVL (splm.JUMLAH_POTONG * -1, 0))))
                                  PMK_78
                              FROM simtax_pajak_headers sphm,
                               simtax_pajak_lines splm,
                               simtax_kode_cabang skc
                             WHERE     sphm.nama_pajak = 'PPN MASUKAN'
                               AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               AND NVL (splm.IS_CHEKLIST, 0) = 1
                               AND splm.is_pmk = 1
                               AND skc.KODE_CABANG = sphm.KODE_CABANG
                               AND NVL (splm.IS_CHEKLIST, 0) = 1
                               and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               AND splm.is_pmk = '1'
                              GROUP BY skc.NAMA_CABANG,
                               sphm.KODE_CABANG,
                               sphm.TAHUN_PAJAK,
                               sphm.BULAN_PAJAK,
                               sphm.MASA_PAJAK) PMK_78
							where 1=1
							and skc.KODE_CABANG = ppn_header.kode_cabang (+)
							and ppn_header.nama_cabang = ppn_keluaran.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_keluaran.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_keluaran.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_keluaran.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_keluaran.masa_pajak (+)
							and ppn_header.nama_cabang = ppn_masukan.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_masukan.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_masukan.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_masukan.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_masukan.masa_pajak (+)
							and ppn_header.nama_cabang = ppn_impor.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_impor.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_impor.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_impor.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_impor.masa_pajak (+)
							and ppn_header.nama_cabang = pmk.nama_cabang (+)
							and ppn_header.kode_cabang = pmk.kode_cabang (+)
							and ppn_header.tahun_pajak = pmk.tahun_pajak (+)
							and ppn_header.bulan_pajak = pmk.bulan_pajak (+)
							and ppn_header.masa_pajak  = pmk.masa_pajak (+)
							AND ppn_header.nama_cabang = pmk_78.nama_cabang(+)
					       	AND ppn_header.kode_cabang = pmk_78.kode_cabang(+)
					       	AND ppn_header.tahun_pajak = pmk_78.tahun_pajak(+)
					       	AND ppn_header.bulan_pajak = pmk_78.bulan_pajak(+)
					       	AND ppn_header.masa_pajak = pmk_78.masa_pajak(+)
							and skc.KODE_CABANG in ('000','010','020','030','040','050',
							'060','070','080','090','100','110','120')
							union all
							select '991', 'Kompensasi' ,null,null,null,null,( select CASE WHEN KOMPENSASI_BLN_LALU <0 THEN KOMPENSASI_BLN_LALU
ELSE 0 END KOMPENSASI from SIMTAX_PMK_PPNMASA
							WHERE BULAN_PAJAK = '".$bulan."' AND TAHUN_PAJAK = '".$tahun."'and pembetulan_ke = '".$pembetulanKe."') KOMPENSASI ,null,null from dual
                                                     union all
                             select '992', 'Pemindahbukuan' ,null,null,null,null,( select PBK from SIMTAX_PMK_PPNMASA
                             WHERE BULAN_PAJAK = '".$bulan."' AND TAHUN_PAJAK = '".$tahun."'and pembetulan_ke = '".$pembetulanKe."') PBK ,null,null from dual
                             union all
                             select '993', 'PMK Tahunan' ,null,null,null,null,( select PMK from SIMTAX_PMK_PPNMASA
                             WHERE BULAN_PAJAK = '".$bulan."' AND TAHUN_PAJAK = '".$tahun."'and pembetulan_ke = '".$pembetulanKe."') PMK ,null,null from dual

                            order by 1
							";			
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 6; // Set baris pertama untuk isi tabel adalah baris ke 4
			$ttl_keluar = 0;								
			$ttl_masuk = 0;								
			$ttl_pmk = 0;								
			$ttl_selisih = 0;									
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['PPN_KELUARAN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['PPN_MASUKAN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['PMK78']);
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['KURANG_LEBIH']);

				$excel->setActiveSheetIndex(0)->setCellValue('F19','=C19-(D19-E19)');
				$excel->setActiveSheetIndex(0)->setCellValue('F20','=C20-(D20-E20)');
				$excel->setActiveSheetIndex(0)->setCellValue('F21','=C21-(D21-E21)');

				$excel->setActiveSheetIndex(0)->setCellValue('C22','=C22+C20+C21');
				$excel->setActiveSheetIndex(0)->setCellValue('D22','=D22+D20+D21');
				$excel->setActiveSheetIndex(0)->setCellValue('E22','=E22+E20+E21');
				// $excel->setActiveSheetIndex(0)->setCellValue('F25','=F25+F23+F24');

				$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
										
				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				
												
				$ttl_keluar  = ($ttl_keluar + $row['PPN_KELUARAN']);								
				$ttl_masuk   = ($ttl_masuk + $row['PPN_MASUKAN']);								
				$ttl_pmk     = $ttl_pmk + $row['PMK78'];								
				$ttl_selisih = $ttl_selisih + $row['KURANG_LEBIH'];
				
				$no++;
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		//total
		$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, "Jmlh. Yg msh hrs dibayar");
		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row_kolom);
		$excel->getActiveSheet()->mergeCells('A'.$numrow.':B'.$numrow);		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $ttl_keluar);	
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ttl_masuk);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ttl_pmk);	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, '=SUM(F6:F18)');

		$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
		
		//setahun
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(30); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekap PPN Bulanan");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap PPN MASA Bulanan.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function cetak_report_ppn_masa_bln_cbg()
	{
		
		$tahun        = $_REQUEST['tahun'];
		$bulan        = $_REQUEST['bulan'];
		$cabang       = $_REQUEST['cabang'];
		$pembetulanKe = $_REQUEST['pembetulanKe'];

		$whereCabang = "";

		if($cabang != "all")
		{
			$whereCabang = "and skc.KODE_CABANG = '".$cabang."'";
		}
		
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_bold = array(
			   'font' => array('bold' => true)
		);

		$style_col_bold = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_bold_italic = array(
			   'font' => array('bold' => true, 'italic' => true)
		);
		
		//buat header cetakan
		//logo IPC
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"

		$excel->getActiveSheet()->getStyle('B1')->applyFromArray($style_row_bold_italic);

		// $excel->setActiveSheetIndex(0)->setCellValue('A2', "KOMPILASI"); // Set kolom A1 dengan tulisan "DATA SISWA"

		$monthArr = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "REKAP PEMBAYARAN PPN MASA ".$monthArr[$bulan]." ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"

		$excel->getActiveSheet()->getStyle('B3')->applyFromArray($style_row_bold);
		
		
		// Buat header tabel nya pada baris ke 3
		
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C4', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D4', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('E4', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('G4', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('D5', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('E5', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('F5', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('G5', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"
		
		$excel->getActiveSheet()->mergeCells('B4:B5');		
		$excel->getActiveSheet()->mergeCells('C4:C5');		
		
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($style_col_bold);
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($style_col_bold);
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($style_col_bold);
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($style_col_bold);
		$excel->getActiveSheet()->getStyle('F4:F5')->applyFromArray($style_col_bold);				
		$excel->getActiveSheet()->getStyle('G4:G5')->applyFromArray($style_col_bold);	
		
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('F4:F5')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('G4:G5')->applyFromArray($style_col2);		
		/*
		$excel->getActiveSheet()->getStyle('C8')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D8')->applyFromArray($style_col2);		
		$excel->getActiveSheet()->getStyle('E8')->applyFromArray($style_col2);		
		$excel->getActiveSheet()->getStyle('F8')->applyFromArray($style_col2);		
		*/
		
		//get detail	
				/*$joinCondition  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splm.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splm.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splm.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splm.ORGANIZATION_ID";*/			
			$queryExec	= " select skc.kode_cabang
								 , case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								   else 'Cabang ' || skc.nama_cabang
								   end nama_cabang								 
								 , ppn_header.bulan_pajak
								 , ppn_header.tahun_pajak
								 , nvl(ppn_keluaran.jumlah_potong,0) PPN_KELUARAN
								 , nvl(ppn_masukan.jumlah_potong,0) PPN_MASUKAN
								 , nvl(pmk.jumlah_potong,0) pmk_78
								 , NVL(pmk_78.pmk_78, 0) pmk78 
								 , nvl(ppn_keluaran.jumlah_potong,0) - (nvl(ppn_masukan.jumlah_potong,0) - nvl(pmk_78.pmk_78,0)) KURANG_LEBIH
							  from simtax_kode_cabang skc
							, (select 
								   skc.NAMA_CABANG
								 , sphh.KODE_CABANG
								 , sphh.TAHUN_PAJAK
								 , sphh.BULAN_PAJAK
								 , sphh.MASA_PAJAK
							  from simtax_pajak_headers sphh
								 , simtax_pajak_lines splh
								 , simtax_kode_cabang skc
							 where sphh.nama_pajak in ('PPN KELUARAN','PPN MASUKAN')
							   and sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
							   and SKC.KODE_CABANG = sphh.KODE_CABANG
							   and nvl(splh.IS_CHEKLIST,0) = 1
							   ".$whereCabang."
							   and sphh.tahun_pajak = '".$tahun."'
							   and sphh.bulan_pajak = '".$bulan."'
							group by skc.NAMA_CABANG, sphh.KODE_CABANG, sphh.TAHUN_PAJAK, sphh.BULAN_PAJAK, sphh.MASA_PAJAK) ppn_header
							,(select skc.NAMA_CABANG
								 , sphm.KODE_CABANG
								 , sphm.TAHUN_PAJAK
								 , sphm.BULAN_PAJAK
								 , sphm.MASA_PAJAK
								 --, sum(nvl(splm.JUMLAH_POTONG,0))*-1 JUMLAH_POTONG
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
							  from simtax_pajak_headers sphm
								 , simtax_pajak_lines splm
								 , simtax_kode_cabang skc
							 where sphm.nama_pajak = 'PPN KELUARAN'
							   and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
							   and SKC.KODE_CABANG = sphm.KODE_CABANG
							   and nvl(splm.IS_CHEKLIST,0) = 1
							   ".$whereCabang."
							   and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."' 
							   and splm.kd_jenis_transaksi IN (1,4,6,9)
							group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_keluaran
							,(select skc.NAMA_CABANG
								 , sphm.KODE_CABANG
								 , sphm.TAHUN_PAJAK
								 , sphm.BULAN_PAJAK
								 , sphm.MASA_PAJAK
								 --, sum(nvl(splm.JUMLAH_POTONG,0))*-1 JUMLAH_POTONG
								 , sum(abs(splm.JUMLAH_POTONG)) JUMLAH_POTONG
								 , min(abs(nvl(sphm.PMK78,0))) PMK78
							  from simtax_pajak_headers sphm
								 , simtax_pajak_lines splm
								 , simtax_kode_cabang skc
							 where sphm.nama_pajak = 'PPN MASUKAN'
							   and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
							   and SKC.KODE_CABANG = sphm.KODE_CABANG
							   and nvl(splm.IS_CHEKLIST,0) = 1
							   ".$whereCabang."
							   and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$bulan."'							   
							   and ((splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') or (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1'))
							group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_masukan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
							   and sphm.bulan_pajak = '".$bulan."'							   
                               and splm.dl_fs = 'dokumen_lain' 
                               and splm.kd_jenis_transaksi IN ('11','12') 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_impor,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
							   and sphm.bulan_pajak = '".$bulan."'							   
                               and splm.is_pmk = '1'
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) pmk,
                             (  SELECT skc.NAMA_CABANG,
			                   sphm.KODE_CABANG,
			                   sphm.TAHUN_PAJAK,
			                   sphm.BULAN_PAJAK,
			                   sphm.MASA_PAJAK,
			                   ceil(abs(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100)
			                   - SUM (NVL (splm.JUMLAH_POTONG * -1, 0))))
			                      PMK_78
			              	FROM simtax_pajak_headers sphm,
			                   simtax_pajak_lines splm,
			                   simtax_kode_cabang skc
			             	WHERE     sphm.nama_pajak = 'PPN MASUKAN'
			                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
			                   AND NVL (splm.IS_CHEKLIST, 0) = 1
			                   AND splm.is_pmk = 1
			                   AND skc.KODE_CABANG = sphm.KODE_CABANG
			                   and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
							   and sphm.bulan_pajak = '".$bulan."'	
			          		GROUP BY skc.NAMA_CABANG,
			                   sphm.KODE_CABANG,
			                   sphm.TAHUN_PAJAK,
			                   sphm.BULAN_PAJAK,
			                   sphm.MASA_PAJAK) PMK_78
							where 1=1
							and skc.KODE_CABANG = ppn_header.kode_cabang (+)
							and ppn_header.nama_cabang = ppn_keluaran.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_keluaran.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_keluaran.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_keluaran.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_keluaran.masa_pajak (+)
							and ppn_header.nama_cabang = ppn_masukan.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_masukan.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_masukan.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_masukan.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_masukan.masa_pajak (+)
							and ppn_header.nama_cabang = ppn_impor.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_impor.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_impor.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_impor.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_impor.masa_pajak (+)
							and ppn_header.nama_cabang = pmk.nama_cabang (+)
							and ppn_header.kode_cabang = pmk.kode_cabang (+)
							and ppn_header.tahun_pajak = pmk.tahun_pajak (+)
							and ppn_header.bulan_pajak = pmk.bulan_pajak (+)
							and ppn_header.masa_pajak  = pmk.masa_pajak (+)
							AND ppn_header.nama_cabang = pmk_78.nama_cabang(+)
					        AND ppn_header.kode_cabang = pmk_78.kode_cabang(+)
					        AND ppn_header.tahun_pajak = pmk_78.tahun_pajak(+)
					        AND ppn_header.bulan_pajak = pmk_78.bulan_pajak(+)
					        AND ppn_header.masa_pajak = pmk_78.masa_pajak(+)
							and skc.KODE_CABANG in ('000','010','020','030','040','050',
							'060','070','080','090','100','110','120')
							--order by skc.kode_cabang
							union all
							select '991', 'Kompensasi', null, null, null, null, null, null, null from dual
							union all
							select '992', 'Pemindahbukuan', null, null, null, null, null, null, null from dual
							union all
							select '993', 'PMK Tahunan', null, null, null, null, null, null, null from dual
							ORDER BY 1 ";		
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 7; // Set baris pertama untuk isi tabel adalah baris ke 4
			$ttl_keluar = 0;								
			$ttl_masuk = 0;								
			$ttl_pmk = 0;								
			$ttl_selisih = 0;									
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['PPN_KELUARAN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['PPN_MASUKAN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['PMK78']);
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['KURANG_LEBIH']);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->getActiveSheet()->getStyle('B6')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C6')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D6')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E6')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F6')->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('G6')->applyFromArray($style_row);
								
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);				
												
				$ttl_keluar = $ttl_keluar + $row['PPN_KELUARAN'];								
				$ttl_masuk = $ttl_masuk + $row['PPN_MASUKAN'];								
				$ttl_pmk = $ttl_pmk + $row['PMK78'];								
				$ttl_selisih = $ttl_selisih + $row['KURANG_LEBIH'];
				
				$no++;
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		//total
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Jmlh. Yg msh hrs dibayar");
		$excel->getActiveSheet()->mergeCells('B'.$numrow.':C'.$numrow);		
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ttl_keluar);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ttl_masuk);	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $ttl_pmk);	
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ttl_selisih);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_col_bold);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col_bold);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
		
		//setahun
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(5); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(30); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekap PPN Bulanan");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap PPN MASA Bulanan.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}		
	
	function cetak_equal_ppn_masa_tahunan()
	{

		$tahun 		= $_REQUEST['tahun'];
		$cabang		= $_REQUEST['cabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak EKUALISASI PPN MASA")
								->setSubject("Cetakan")
								->setDescription("Cetak EKUALISASI PPN MASA")
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

		$style_col1 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col3 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col5 = array(
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

		$style_colhead = array(
			'font' => array('bold' => true), // Set font nya jadi bold
		);

		$style_coltotal = array(
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_colselisih = array(
			'font' => array('bold' => true), // Set font nya jadi bold
			'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		);
		
		$noBorder_Bold_Tengah = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);		
		
		$border_noBold_kiri = array(
		        'font' => array('bold' => false), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi di tengah secara vertical (middle)
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_hsl = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		);

		$border_Bold = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  ),
		);
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "EKUALISASI PAJAK PERTAMBAHAN NILAI TAHUN ".$tahun."");
		$excel->getActiveSheet()->getStyle('B2')->applyFromArray($border_Bold);

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Ekualisasi dengan pendapatan"); 
		$excel->getActiveSheet()->mergeCells('B3:E6');		
		$excel->getActiveSheet()->getStyle('B3:E6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F3:F6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G3:G6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H3:L6')->applyFromArray($border_noBold_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('H3', "Nama WP");
		$excel->setActiveSheetIndex(0)->setCellValue('H4', "NPWP"); 
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Jenis Pajak"); 
		$excel->setActiveSheetIndex(0)->setCellValue('H6', "Tahun Pajak"); 
		
		$excel->setActiveSheetIndex(0)->setCellValue('I3', ":  PT. (PERSERO) PELABUHAN INDONESIA II"); 
		$excel->setActiveSheetIndex(0)->setCellValue('I4', ":  01.061.005.3-093.000"); 
		$excel->setActiveSheetIndex(0)->setCellValue('I5', ":  PPN"); 
		$excel->setActiveSheetIndex(0)->setCellValue('I6', ":  ".$tahun."");

		$excel->setActiveSheetIndex(0)->setCellValue('B7', "No"); 
		$excel->getActiveSheet()->mergeCells('B7:B8');		
		$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B9:B47')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E9:E47')->applyFromArray($style_col5);
		$excel->getActiveSheet()->getStyle('F9:F47')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('G9:G47')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('H9:H47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('I9:I47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('J9:J47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('K9:K47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('L9:L47')->applyFromArray($style_col3);

		$excel->getActiveSheet()->getStyle('B48:B48')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B48:B48')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B48:D48')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('E48:E48')->applyFromArray($style_col5);
		$excel->getActiveSheet()->getStyle('F48:F48')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('G48:G48')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('H48:H48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('I48:I48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('J48:J48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('K48:K48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('L48:L48')->applyFromArray($style_col3); 

		$excel->setActiveSheetIndex(0)->setCellValue('C8', "U R A I A N"); 
		$excel->getActiveSheet()->mergeCells('C7:D8');		
		$excel->getActiveSheet()->getStyle('C7:D8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('E7', ""); 
		$excel->getActiveSheet()->mergeCells('E7:E8');
		$excel->setActiveSheetIndex(0)->setCellValue('F7', "AKUN"); 
		$excel->getActiveSheet()->mergeCells('F7:F8');
		$excel->getActiveSheet()->getStyle('F7:F8')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_col);		
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('H7', "Jumlah menurut"); 
		$excel->setActiveSheetIndex(0)->setCellValue('H8', "Sub Buku Besar");
		$excel->getActiveSheet()->getStyle('H7:H8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('I7', "PPN Dipungut");
		$excel->setActiveSheetIndex(0)->setCellValue('I8', "Sendiri");		
		$excel->getActiveSheet()->getStyle('I7:I8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('J7', "PPN Dipungut");
		$excel->setActiveSheetIndex(0)->setCellValue('J8', "Oleh Pemungut");		
		$excel->getActiveSheet()->getStyle('J7:J8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('K7', "PPN");
		$excel->setActiveSheetIndex(0)->setCellValue('K8', "Dibebaskan/DTP");		
		$excel->getActiveSheet()->getStyle('K7:K8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('L7', "Tidak terutang PPN");
		$excel->setActiveSheetIndex(0)->setCellValue('L8', "& Bukan Objek PPN");
		$excel->getActiveSheet()->getStyle('L7:L8')->applyFromArray($noBorder_Bold_Tengah);
		
		$no = 1;
		$numrow = 11;
		/* $queryExec	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) IN
                                   ('701', '702', '703', '704', '705', '706', '707', '708')
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.jumlah_potong
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                        LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                        AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                        LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                        AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) in  ('701', '702', '703', '704', '705', '706', '707', '708')
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1"; */
					
		$queryExec ="
			select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) IN
                                   ('701', '702', '703', '704', '705', '706', '707', '708')
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                      --  LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                      --  AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                      --  LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                      --  AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) in  ('701', '702', '703', '704', '705', '706', '707', '708')
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    order by 1
		";
		$query 		= $this->db->query($queryExec);

		foreach($query->result_array() as $row)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B10',"A");
			$excel->setActiveSheetIndex(0)->setCellValue('C10',"Pendapatan Usaha");
			$excel->getActiveSheet()->getStyle('C10:C10')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D11',"PELAYANAN JASA KAPAL");
			$excel->setActiveSheetIndex(0)->setCellValue('D12',"PELAYANAN JASA BARANG");
			$excel->setActiveSheetIndex(0)->setCellValue('D13',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('D14',"PELAYANAN TERMINAL");
			$excel->setActiveSheetIndex(0)->setCellValue('D15',"PELAYANAN TERMINAL PETIKEMAS");
			$excel->setActiveSheetIndex(0)->setCellValue('D16',"PENGUSAH TANAH, BANGUNAN, AIR & LISTRIK");
			$excel->setActiveSheetIndex(0)->setCellValue('D17',"FASILITAS RUPA-RUPA USAHA");
			$excel->setActiveSheetIndex(0)->setCellValue('D18',"KERJASAMA DENGAN MITRA USAHA");

			$excel->setActiveSheetIndex(0)->setCellValue('D20',"Total Pendapatan Usaha");
			$excel->getActiveSheet()->getStyle('D20:D20')->applyFromArray($style_colhead);

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('H20','=SUM(H11:H18)');
			$excel->getActiveSheet()->getStyle('H20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('I20','=SUM(I11:I18)');
			$excel->getActiveSheet()->getStyle('I20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('I'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $row['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('J20','=SUM(J11:J18)');
			$excel->getActiveSheet()->getStyle('J20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $row['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('K20','=SUM(K11:K18)');
			$excel->getActiveSheet()->getStyle('K20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('K'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['BUKAN_PPN']);
			$excel->setActiveSheetIndex(0)->setCellValue('L20','=SUM(L11:L18)');
			$excel->getActiveSheet()->getStyle('L20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('L'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H20'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I20'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$no++;
		$numrow++;
		}

		$no1 = 1;
		$numrow1 = 23;
		/* $queryExec1	= "select
                                  q_master.kode_akun
                                , substr(q_master.kode_akun,0,3)  akun
                                , q_master.description_akun
                                , q_master.balance
                                , q_pendapatan.sendiri
                                , q_pendapatan.oleh_pemungut
                                , q_pendapatan.dibebaskan
                                , q_pendapatan.bukan_ppn
                                from 
                            (  SELECT kode_akun kode_akun,
                                            akun_description,
                                         (select ffvt.DESCRIPTION
                              from fnd_flex_values ffv
                                 , fnd_flex_values_tl ffvt
                                 , fnd_flex_value_sets ffvs
                            where ffv.flex_value_id = ffvt.flex_value_id     
                              and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                              and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                              and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3)) description_akun,
                                          SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                                FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                               WHERE kode_akun IN
                                           ('79101111','79101121','79101131','79101141'
                                            ,'79101161','79101172','79101181','79101182','79199999')
                               and tahun_pajak = '".$tahun."'
                            GROUP BY kode_akun,  akun_description) q_master
                            ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                                   , spl.dpp
                                  , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                              from simtax_pajak_headers sph
                              inner join simtax_pajak_lines spl
                                  on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                  INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                                  LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                                  AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                                  LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                                    AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                            where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                               and sph.tahun_pajak = '".$tahun."'
                               AND SPL.IS_CHEKLIST = '1'
                               --and sph.kode_cabang = '".$cabang."'
                               and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                               --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                               )
                               PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                            where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                            order by 1"; */
		
		$queryExec1	="
			select
                                  q_master.kode_akun
                                , substr(q_master.kode_akun,0,3)  akun
                                , q_master.description_akun
                                , q_master.balance
                                , q_pendapatan.sendiri
                                , q_pendapatan.oleh_pemungut
                                , q_pendapatan.dibebaskan
                                , q_pendapatan.bukan_ppn
                                from 
                            (  SELECT kode_akun kode_akun,
                                            akun_description,
                                         (select ffvt.DESCRIPTION
                              from fnd_flex_values ffv
                                 , fnd_flex_values_tl ffvt
                                 , fnd_flex_value_sets ffvs
                            where ffv.flex_value_id = ffvt.flex_value_id     
                              and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                              and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                              and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3)) description_akun,
                                          SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                                FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                               WHERE kode_akun IN
                                           ('79101111','79101121','79101131','79101141'
                                            ,'79101161','79101172','79101181','79101182','79199999')
                               and tahun_pajak = '".$tahun."'
                            GROUP BY kode_akun,  akun_description) q_master
                            ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select spl.akun_pajak akun_pajak
                                   , spl.dpp
                                  , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                              from simtax_pajak_headers sph
                              inner join simtax_pajak_lines spl
                                  on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                 INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                                 -- LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                                 -- AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                                 -- LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                                --    AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                            where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                               and sph.tahun_pajak = '".$tahun."'
                               AND SPL.IS_CHEKLIST = '1'
                               --and sph.kode_cabang = '".$cabang."'
                               and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                               --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                               )
                               PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))
															
														) q_pendapatan
                            where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                            order by 1
		";
		$query1 	= $this->db->query($queryExec1);

		foreach($query1->result_array() as $row1)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B22',"B");
			$excel->setActiveSheetIndex(0)->setCellValue('C22',"Pendapatan Diluar Usaha");
			$excel->getActiveSheet()->getStyle('C22:C22')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D23',"Laba Selisih Kurs");
			$excel->setActiveSheetIndex(0)->setCellValue('D24',"Bunga Deposito");
			$excel->setActiveSheetIndex(0)->setCellValue('D25',"Jasa Giro");
			$excel->setActiveSheetIndex(0)->setCellValue('D26',"Denda");
			$excel->setActiveSheetIndex(0)->setCellValue('D27',"Dokumen Tender / Administrasi");
			$excel->setActiveSheetIndex(0)->setCellValue('D28',"Pendapatan Premium");
			$excel->setActiveSheetIndex(0)->setCellValue('D29',"Bagian Laba PT. JICT");
			$excel->setActiveSheetIndex(0)->setCellValue('D30',"Bagian Laba KSO Koja");
			$excel->setActiveSheetIndex(0)->setCellValue('D31',"Pend. Diluar Usaha Lainnya");

			$excel->setActiveSheetIndex(0)->setCellValue('D32',"Total PDLU");
			$excel->getActiveSheet()->getStyle('D32:D32')->applyFromArray($style_colhead);

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow1, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow1, $row1['KODE_AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow1, $row1['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow1, $row1['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('H32','=SUM(H23:H31)');
			$excel->getActiveSheet()->getStyle('H32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('H'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow1, $row1['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('I32','=SUM(I23:I31)');
			$excel->getActiveSheet()->getStyle('I32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('I'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow1, $row1['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('J32','=SUM(J23:J31)');
			$excel->getActiveSheet()->getStyle('J32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('J'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow1, $row1['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('K32','=SUM(K23:K31)');
			$excel->getActiveSheet()->getStyle('K32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('K'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow1, $row1['BUKAN_PPN']);
			$excel->setActiveSheetIndex(0)->setCellValue('L32','=SUM(L23:L31)');
			$excel->getActiveSheet()->getStyle('L32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('L'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow1)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H32'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I32'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$no1++;
		$numrow1++;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('B34',"C");
		$excel->setActiveSheetIndex(0)->setCellValue('C34',"Total Pendapatan sesuai Lap Keuangan (A+B)");
		$excel->getActiveSheet()->getStyle('C34:C34')->applyFromArray($style_colhead);

		$excel->setActiveSheetIndex(0)->setCellValue('H34','=SUM(H20+H32)');
		$excel->getActiveSheet()->getStyle('H34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('H44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('I34','=SUM(I20+I32)');
		$excel->getActiveSheet()->getStyle('I34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('I44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('J34','=SUM(J20+J32)');
		$excel->getActiveSheet()->getStyle('J34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('J44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('K34','=SUM(K20+K32)');
		$excel->getActiveSheet()->getStyle('K34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('K44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('L34','=SUM(L20+L32)');
		$excel->getActiveSheet()->getStyle('L34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('L44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$no3 = 1;
		$numrow3 = 37;
		$queryExec3	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '311'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                           INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '311'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query3 	= $this->db->query($queryExec3);

		foreach($query3->result_array() as $row3)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B36',"D");
			$excel->setActiveSheetIndex(0)->setCellValue('C36',"Pendapatan yg  Diterima Di Muka");
			$excel->getActiveSheet()->getStyle('C36:C36')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D37',"Jangka Pendek");

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow3, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow3, $row3['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow3, $row3['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow3, $row3['BALANCE']);
			$excel->getActiveSheet()->getStyle('H'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H32')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow3, $row3['SENDIRI']);
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow3, $row3['OLEH_PEMUNGUT']);
			$excel->getActiveSheet()->getStyle('J'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow3, $row3['DIBEBASKAN']);
			$excel->getActiveSheet()->getStyle('K'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow3, $row3['BUKAN_PPN']);
			$excel->getActiveSheet()->getStyle('L'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow3)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$no3++;
		$numrow3++;
		}

		$no4 = 1;
		$numrow4 = 40;
		$queryExec4	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '405'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '405'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query4 	= $this->db->query($queryExec4);

		foreach($query4->result_array() as $row4)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B39',"E");
			$excel->setActiveSheetIndex(0)->setCellValue('C39',"Pendapatan yg  Diterima Di Muka");
			$excel->getActiveSheet()->getStyle('C39:C39')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D40',"Jangka Panjang");

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow4, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow4, $row4['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow4, $row4['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow4, $row4['BALANCE']);
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow4, $row4['SENDIRI']);
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow4, $row4['OLEH_PEMUNGUT']);
			$excel->getActiveSheet()->getStyle('J'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow4, $row4['DIBEBASKAN']);
			$excel->getActiveSheet()->getStyle('K'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow4, $row4['BUKAN_PPN']);
			$excel->getActiveSheet()->getStyle('L'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$no4++;
		$numrow4++;
		}

		$no5 = 1;
		$numrow5 = 42;
		$queryExec5	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '111'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '111'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query5 	= $this->db->query($queryExec5);

		foreach($query5->result_array() as $row5)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B42',"G");
			$excel->setActiveSheetIndex(0)->setCellValue('C42',"Pendapatan Y.M.A Diterima");
			$excel->getActiveSheet()->getStyle('C42:C42')->applyFromArray($style_colhead);

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow5, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow5, $row5['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow5, $row5['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow5, $row5['BALANCE']);
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow5, $row5['SENDIRI']);
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow5, $row5['OLEH_PEMUNGUT']);
			$excel->getActiveSheet()->getStyle('J'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow5, $row5['DIBEBASKAN']);
			$excel->getActiveSheet()->getStyle('K'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow5, $row5['BUKAN_PPN']);
			$excel->getActiveSheet()->getStyle('L'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$no5++;
		$numrow5++;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('D44',"TOTAL OMZET PENJUALAN (C+D+E)");
		$excel->getActiveSheet()->getStyle('D44:D44')->applyFromArray($style_colhead);

		$excel->setActiveSheetIndex(0)->setCellValue('H44','=SUM(H34+H37+H40)');
		$excel->getActiveSheet()->getStyle('H44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('H44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('I44','=SUM(I34+I37+I40)');
		$excel->getActiveSheet()->getStyle('I44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('I44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('J44','=SUM(J34+J37+J40)');
		$excel->getActiveSheet()->getStyle('J44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('J44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('K44','=SUM(K34+K37+K40)');
		$excel->getActiveSheet()->getStyle('K44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('K44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('L44','=SUM(L34+L37+L40)');
		$excel->getActiveSheet()->getStyle('L44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('L44')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$queryExecekl	= "SELECT SUM (q_master.balance) balance,
                     SUM (q_pendapatan.sendiri) sendiri,
                     SUM (q_pendapatan.oleh_pemungut) oleh_pemungut,
                     SUM (q_pendapatan.dibebaskan) dibebaskan,
                     SUM (q_pendapatan.bukan_ppn) bukan_ppn
                FROM (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                               SUM (NVL (debit, 0) - 1) - SUM (NVL (credit, 0) - 1) balance,
                               masa_pajak
                          FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                         WHERE SUBSTR (kode_akun, 0, 3) IN
                                     ('701', '702', '703', '704', '705', '706', '707', '708')
                               AND tahun_pajak = '".$tahun."'
                      GROUP BY SUBSTR (kode_akun, 0, 3), masa_pajak) q_master,
                     (SELECT akun_pajak,
                             sendiri,
                             oleh_pemungut,
                             dibebaskan,
                             bukan_ppn,
                             masa_pajak,
                             pembetulan_ke
                        FROM (SELECT SUBSTR (spl.akun_pajak, 0, 3) akun_pajak,
                                     spl.dpp jumlah_potong,
                                     sph.masa_pajak,
                                     sph.pembetulan_ke,
                                     case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                                FROM    simtax_pajak_headers sph
                                     INNER JOIN
                                        simtax_pajak_lines spl
                                     ON SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                     INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                               WHERE sph.nama_pajak IN ('PPN MASUKAN', 'PPN KELUARAN')
                                     AND sph.tahun_pajak ='".$tahun."'
                                     AND SPL.IS_CHEKLIST = '1'
                                     AND (SPL.NO_FAKTUR_PAJAK IS NOT NULL
                                          OR SPL.NO_DOKUMEN_LAIN IS NOT NULL)
                                     AND SUBSTR (spl.akun_pajak, 0, 3) IN
                                              ('701', '702', '703','704', '705', '706', '707', '708')
                                     ORDER BY SPH.PEMBETULAN_KE ASC
                             ) PIVOT (SUM (jumlah_potong*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                            WHERE q_master.kode_akun = q_pendapatan.akun_pajak
                             AND q_master.masa_pajak = Q_PENDAPATAN.masa_pajak
                            GROUP BY q_pendapatan.pembetulan_ke, q_master.masa_pajak
                            ORDER BY q_master.masa_pajak DESC";

		$queryekl 		= $this->db->query($queryExecekl);
			$balanceekl 		= 0;
			$sendiriekl			= 0;
			$oleh_pemungutekl	= 0;
			$dibebaskanekl		= 0;
			$bukan_ppnekl		= 0;
			$tot_ekl			= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('D46',"TOTAL OMZET (DPP) MENURUT SPT");
			$excel->getActiveSheet()->getStyle('D46:D46')->applyFromArray($style_colhead);

			foreach($queryekl->result_array() as $rowekl)	{
			$balanceekl 		+= $rowekl['BALANCE'];
			$sendiriekl			+= $rowekl['SENDIRI'];
			$oleh_pemungutekl	+= $rowekl['OLEH_PEMUNGUT'];
			$dibebaskanekl		+= $rowekl['DIBEBASKAN'];
			$bukan_ppnekl		+= $rowekl['BUKAN_PPN'];

			$tot_ekl			+= $rowekl['SENDIRI'] + $rowekl['OLEH_PEMUNGUT'] + $rowekl['DIBEBASKAN'];

			$excel->setActiveSheetIndex(0)->setCellValue('H46', $balanceekl);
			$excel->setActiveSheetIndex(0)->setCellValue('I46', $sendiriekl);
			$excel->setActiveSheetIndex(0)->setCellValue('J46', $oleh_pemungutekl);
			$excel->setActiveSheetIndex(0)->setCellValue('K46', $dibebaskanekl);

			$excel->getActiveSheet()->getStyle('H46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('I46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('J46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('K46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('L46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
			}

		//
		$excel->setActiveSheetIndex(0)->setCellValue('D48',"SELISIH");
		$excel->getActiveSheet()->getStyle('D48:D48')->applyFromArray($style_colselisih);

		$excel->setActiveSheetIndex(0)->setCellValue('H48','=(H44-H46)');
		$excel->getActiveSheet()->getStyle('H46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('I48','=(I44-I46)');
		$excel->getActiveSheet()->getStyle('I46')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('J48','=(J44-J46)');
		$excel->getActiveSheet()->getStyle('J48')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('K48','=(K44-K46)');
		$excel->getActiveSheet()->getStyle('K48')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('L48','=(L44-L46)');
		$excel->getActiveSheet()->getStyle('L48')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		//
		$excel->setActiveSheetIndex(0)->setCellValue('D52',"MENGETAHUI :");

		$cabang		= $this->session->userdata('kd_cabang');
		$queryExec1	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = 'DVP Pajak'
                            and nama_pajak = 'SPT PPN Masa'
                            and document_type = 'Ekualisasi' 
                            and kode_cabang = '".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
			$query1 	= $this->db->query($queryExec1);
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
			$objDrawing->setCoordinates('D55');
			$objDrawing->setHeight(80); // logo height
			$objDrawing->setWorksheet($excel->getActiveSheet());
		}

		$excel->setActiveSheetIndex(0)->setCellValue('D53', $petugas_ttd);
		$excel->setActiveSheetIndex(0)->setCellValue('D59', $jabatan_petugas_ttd);
		}
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(2); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(3); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(3); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(45); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(0)	; // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(65); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(30); // Set width kolom A

		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Ekualisasi PPN Masa");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Ekualisasi PPN MASA.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_equal_ppn_masa_bln()
	{
		$this->template->set('title', 'Laporan Ekualisasi PPN MASA');
		$data['subtitle']	= "Cetak Laporan Ekualisasi PPN MASA";
		$data['activepage'] = "laporan_ekualisasi";
		$data['error'] = "";
		$this->template->load('template', 'laporan/lap_equalisasi_ppn_masa_bln',$data);		
	}

	function cetak_equal_ppn_masa_bulanan()
	{

		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$cabang		= "";
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak EKUALISASI PPN MASA BULANAN")
								->setSubject("Cetakan")
								->setDescription("Cetak EKUALISASI PPN MASA BULANAN")
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

		$style_col1 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col3 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col5 = array(
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

		$style_colhead = array(
			'font' => array('bold' => true), // Set font nya jadi bold
		);

		$style_coltotal = array(
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_colselisih = array(
			'font' => array('bold' => true), // Set font nya jadi bold
			'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		);
		
		$noBorder_Bold_Tengah = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);		
		
		$border_noBold_kiri = array(
		        'font' => array('bold' => false), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi di tengah secara vertical (middle)
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_hsl = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		);

		$border_Bold = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  ),
		);
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "EKUALISASI PAJAK PERTAMBAHAN NILAI TAHUN ".$tahun." BULAN ".strtoupper(get_masa_pajak($bulan,"id",true))." ");
		$excel->getActiveSheet()->getStyle('B2')->applyFromArray($border_Bold);

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Ekualisasi dengan pendapatan"); 
		$excel->getActiveSheet()->mergeCells('B3:E6');		
		$excel->getActiveSheet()->getStyle('B3:E6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F3:F6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G3:G6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H3:L6')->applyFromArray($border_noBold_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('H3', "Nama WP");
		$excel->setActiveSheetIndex(0)->setCellValue('H4', "NPWP"); 
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Jenis Pajak"); 
		$excel->setActiveSheetIndex(0)->setCellValue('H6', "Tahun Pajak"); 
		
		$excel->setActiveSheetIndex(0)->setCellValue('I3', ":  PT. (PERSERO) PELABUHAN INDONESIA II"); 
		$excel->setActiveSheetIndex(0)->setCellValue('I4', ":  01.061.005.3-093.000"); 
		$excel->setActiveSheetIndex(0)->setCellValue('I5', ":  PPN"); 
		$excel->setActiveSheetIndex(0)->setCellValue('I6', ":  ".$tahun."");

		$excel->setActiveSheetIndex(0)->setCellValue('B7', "No"); 
		$excel->getActiveSheet()->mergeCells('B7:B8');		
		$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B9:B47')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E9:E47')->applyFromArray($style_col5);
		$excel->getActiveSheet()->getStyle('F9:F47')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('G9:G47')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('H9:H47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('I9:I47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('J9:J47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('K9:K47')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('L9:L47')->applyFromArray($style_col3);

		$excel->getActiveSheet()->getStyle('B48:B48')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B48:B48')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B48:D48')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('E48:E48')->applyFromArray($style_col5);
		$excel->getActiveSheet()->getStyle('F48:F48')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('G48:G48')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('H48:H48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('I48:I48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('J48:J48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('K48:K48')->applyFromArray($style_col3);
		$excel->getActiveSheet()->getStyle('L48:L48')->applyFromArray($style_col3); 

		$excel->setActiveSheetIndex(0)->setCellValue('C8', "U R A I A N"); 
		$excel->getActiveSheet()->mergeCells('C7:D8');		
		$excel->getActiveSheet()->getStyle('C7:D8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('E7', ""); 
		$excel->getActiveSheet()->mergeCells('E7:E8');
		$excel->setActiveSheetIndex(0)->setCellValue('F7', "AKUN"); 
		$excel->getActiveSheet()->mergeCells('F7:F8');
		$excel->getActiveSheet()->getStyle('F7:F8')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_col);		
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('H7', "Jumlah menurut"); 
		$excel->setActiveSheetIndex(0)->setCellValue('H8', "Sub Buku Besar");
		$excel->getActiveSheet()->getStyle('H7:H8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('I7', "PPN Dipungut");
		$excel->setActiveSheetIndex(0)->setCellValue('I8', "Sendiri");		
		$excel->getActiveSheet()->getStyle('I7:I8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('J7', "PPN Dipungut");
		$excel->setActiveSheetIndex(0)->setCellValue('J8', "Oleh Pemungut");		
		$excel->getActiveSheet()->getStyle('J7:J8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('K7', "PPN");
		$excel->setActiveSheetIndex(0)->setCellValue('K8', "Dibebaskan/DTP");		
		$excel->getActiveSheet()->getStyle('K7:K8')->applyFromArray($noBorder_Bold_Tengah);

		$excel->setActiveSheetIndex(0)->setCellValue('L7', "Tidak terutang PPN");
		$excel->setActiveSheetIndex(0)->setCellValue('L8', "& Bukan Objek PPN");
		$excel->getActiveSheet()->getStyle('L7:L8')->applyFromArray($noBorder_Bold_Tengah);
		
		$no = 1;
		$numrow = 11;
					
		$queryExec ="
			select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) IN
                                   ('701', '702', '703', '704', '705', '706', '707', '708')
                       and bulan_pajak = '".$bulan."'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                      --  LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                      --  AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                      --  LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                      --  AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.bulan_pajak = '".$bulan."'
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) in  ('701', '702', '703', '704', '705', '706', '707', '708')
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1
		";
		$query 		= $this->db->query($queryExec);

		foreach($query->result_array() as $row)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B10',"A");
			$excel->setActiveSheetIndex(0)->setCellValue('C10',"Pendapatan Usaha");
			$excel->getActiveSheet()->getStyle('C10:C10')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D11',"PELAYANAN JASA KAPAL");
			$excel->setActiveSheetIndex(0)->setCellValue('D12',"PELAYANAN JASA BARANG");
			$excel->setActiveSheetIndex(0)->setCellValue('D13',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('D14',"PELAYANAN TERMINAL");
			$excel->setActiveSheetIndex(0)->setCellValue('D15',"PELAYANAN TERMINAL PETIKEMAS");
			$excel->setActiveSheetIndex(0)->setCellValue('D16',"PENGUSAH TANAH, BANGUNAN, AIR & LISTRIK");
			$excel->setActiveSheetIndex(0)->setCellValue('D17',"FASILITAS RUPA-RUPA USAHA");
			$excel->setActiveSheetIndex(0)->setCellValue('D18',"KERJASAMA DENGAN MITRA USAHA");

			$excel->setActiveSheetIndex(0)->setCellValue('D20',"Total Pendapatan Usaha");
			$excel->getActiveSheet()->getStyle('D20:D20')->applyFromArray($style_colhead);

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('H20','=SUM(H11:H18)');
			$excel->getActiveSheet()->getStyle('H20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('I20','=SUM(I11:I18)');
			$excel->getActiveSheet()->getStyle('I20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('I'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $row['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('J20','=SUM(J11:J18)');
			$excel->getActiveSheet()->getStyle('J20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $row['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('K20','=SUM(K11:K18)');
			$excel->getActiveSheet()->getStyle('K20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('K'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['BUKAN_PPN']);
			$excel->setActiveSheetIndex(0)->setCellValue('L20','=SUM(L11:L18)');
			$excel->getActiveSheet()->getStyle('L20')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('L'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H20'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I20'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$no++;
		$numrow++;
		}

		$no1 = 1;
		$numrow1 = 23;
		
		$queryExec1	="
			select
                                  q_master.kode_akun
                                , substr(q_master.kode_akun,0,3)  akun
                                , q_master.description_akun
                                , q_master.balance
                                , q_pendapatan.sendiri
                                , q_pendapatan.oleh_pemungut
                                , q_pendapatan.dibebaskan
                                , q_pendapatan.bukan_ppn
                                from 
                            (  SELECT kode_akun kode_akun,
                                            akun_description,
                                         (select ffvt.DESCRIPTION
                              from fnd_flex_values ffv
                                 , fnd_flex_values_tl ffvt
                                 , fnd_flex_value_sets ffvs
                            where ffv.flex_value_id = ffvt.flex_value_id     
                              and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                              and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                              and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3)) description_akun,
                                          SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                                FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                               WHERE kode_akun IN
                                           ('79101111','79101121','79101131','79101141'
                                            ,'79101161','79101172','79101181','79101182','79199999')
                               and bulan_pajak = '".$bulan."'
                               and tahun_pajak = '".$tahun."'
                            GROUP BY kode_akun,  akun_description) q_master
                            ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select spl.akun_pajak akun_pajak
                                   , spl.dpp
                                  , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                              from simtax_pajak_headers sph
                              inner join simtax_pajak_lines spl
                                  on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                 INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                                 -- LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                                 -- AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                                 -- LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                                --    AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                            where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                               and sph.bulan_pajak = '".$bulan."'
                               and sph.tahun_pajak = '".$tahun."'
                               AND SPL.IS_CHEKLIST = '1'
                               --and sph.kode_cabang = '".$cabang."'
                               and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                               --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                               )
                               PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))
															
														) q_pendapatan
                            where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                            order by 1
		";
		$query1 	= $this->db->query($queryExec1);

		foreach($query1->result_array() as $row1)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B22',"B");
			$excel->setActiveSheetIndex(0)->setCellValue('C22',"Pendapatan Diluar Usaha");
			$excel->getActiveSheet()->getStyle('C22:C22')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D23',"Laba Selisih Kurs");
			$excel->setActiveSheetIndex(0)->setCellValue('D24',"Bunga Deposito");
			$excel->setActiveSheetIndex(0)->setCellValue('D25',"Jasa Giro");
			$excel->setActiveSheetIndex(0)->setCellValue('D26',"Denda");
			$excel->setActiveSheetIndex(0)->setCellValue('D27',"Dokumen Tender / Administrasi");
			$excel->setActiveSheetIndex(0)->setCellValue('D28',"Pendapatan Premium");
			$excel->setActiveSheetIndex(0)->setCellValue('D29',"Bagian Laba PT. JICT");
			$excel->setActiveSheetIndex(0)->setCellValue('D30',"Bagian Laba KSO Koja");
			$excel->setActiveSheetIndex(0)->setCellValue('D31',"Pend. Diluar Usaha Lainnya");

			$excel->setActiveSheetIndex(0)->setCellValue('D32',"Total PDLU");
			$excel->getActiveSheet()->getStyle('D32:D32')->applyFromArray($style_colhead);

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow1, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow1, $row1['KODE_AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow1, $row1['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow1, $row1['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('H32','=SUM(H23:H31)');
			$excel->getActiveSheet()->getStyle('H32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('H'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow1, $row1['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('I32','=SUM(I23:I31)');
			$excel->getActiveSheet()->getStyle('I32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('I'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow1, $row1['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('J32','=SUM(J23:J31)');
			$excel->getActiveSheet()->getStyle('J32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('J'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow1, $row1['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('K32','=SUM(K23:K31)');
			$excel->getActiveSheet()->getStyle('K32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('K'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow1, $row1['BUKAN_PPN']);
			$excel->setActiveSheetIndex(0)->setCellValue('L32','=SUM(L23:L31)');
			$excel->getActiveSheet()->getStyle('L32')->applyFromArray($style_coltotal);
			$excel->getActiveSheet()->getStyle('L'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow1)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H32'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I32'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow1)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow1)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$no1++;
		$numrow1++;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('B34',"C");
		$excel->setActiveSheetIndex(0)->setCellValue('C34',"Total Pendapatan sesuai Lap Keuangan (A+B)");
		$excel->getActiveSheet()->getStyle('C34:C34')->applyFromArray($style_colhead);

		$excel->setActiveSheetIndex(0)->setCellValue('H34','=SUM(H20+H32)');
		$excel->getActiveSheet()->getStyle('H34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('H44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('I34','=SUM(I20+I32)');
		$excel->getActiveSheet()->getStyle('I34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('I44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('J34','=SUM(J20+J32)');
		$excel->getActiveSheet()->getStyle('J34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('J44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('K34','=SUM(K20+K32)');
		$excel->getActiveSheet()->getStyle('K34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('K44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('L34','=SUM(L20+L32)');
		$excel->getActiveSheet()->getStyle('L34')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('L44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$no3 = 1;
		$numrow3 = 37;
		$queryExec3	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '311'
                       and bulan_pajak = '".$bulan."'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                           INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.bulan_pajak = '".$bulan."'
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '311'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query3 	= $this->db->query($queryExec3);

		foreach($query3->result_array() as $row3)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B36',"D");
			$excel->setActiveSheetIndex(0)->setCellValue('C36',"Pendapatan yg  Diterima Di Muka");
			$excel->getActiveSheet()->getStyle('C36:C36')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D37',"Jangka Pendek");

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow3, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow3, $row3['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow3, $row3['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow3, $row3['BALANCE']);
			$excel->getActiveSheet()->getStyle('H'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H32')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow3, $row3['SENDIRI']);
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow3, $row3['OLEH_PEMUNGUT']);
			$excel->getActiveSheet()->getStyle('J'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow3, $row3['DIBEBASKAN']);
			$excel->getActiveSheet()->getStyle('K'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow3, $row3['BUKAN_PPN']);
			$excel->getActiveSheet()->getStyle('L'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow3)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow3)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow3)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$no3++;
		$numrow3++;
		}

		$no4 = 1;
		$numrow4 = 40;
		$queryExec4	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '405'
                       and bulan_pajak = '".$bulan."'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.bulan_pajak = '".$bulan."'
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '405'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query4 	= $this->db->query($queryExec4);

		foreach($query4->result_array() as $row4)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B39',"E");
			$excel->setActiveSheetIndex(0)->setCellValue('C39',"Pendapatan yg  Diterima Di Muka");
			$excel->getActiveSheet()->getStyle('C39:C39')->applyFromArray($style_colhead);
			$excel->setActiveSheetIndex(0)->setCellValue('D40',"Jangka Panjang");

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow4, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow4, $row4['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow4, $row4['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow4, $row4['BALANCE']);
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow4, $row4['SENDIRI']);
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow4, $row4['OLEH_PEMUNGUT']);
			$excel->getActiveSheet()->getStyle('J'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow4, $row4['DIBEBASKAN']);
			$excel->getActiveSheet()->getStyle('K'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow4, $row4['BUKAN_PPN']);
			$excel->getActiveSheet()->getStyle('L'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow4)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow4)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$no4++;
		$numrow4++;
		}

		$no5 = 1;
		$numrow5 = 42;
		$queryExec5	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '111'
                       and bulan_pajak = '".$bulan."'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.dpp
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.bulan_pajak = '".$bulan."'
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '111'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (dpp*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query5 	= $this->db->query($queryExec5);

		foreach($query5->result_array() as $row5)	{	
			$excel->setActiveSheetIndex(0)->setCellValue('B42',"G");
			$excel->setActiveSheetIndex(0)->setCellValue('C42',"Pendapatan Y.M.A Diterima");
			$excel->getActiveSheet()->getStyle('C42:C42')->applyFromArray($style_colhead);

			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow5, "");
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow5, $row5['AKUN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow5, $row5['DESCRIPTION_AKUN']);

			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow5, $row5['BALANCE']);
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow5, $row5['SENDIRI']);
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow5, $row5['OLEH_PEMUNGUT']);
			$excel->getActiveSheet()->getStyle('J'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('J'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow5, $row5['DIBEBASKAN']);
			$excel->getActiveSheet()->getStyle('K'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('K'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow5, $row5['BUKAN_PPN']);
			$excel->getActiveSheet()->getStyle('L'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->applyFromArray($style_row_hsl);

			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('I'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('I'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('J'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('J'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('K'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('K'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('L'.$numrow5)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('L'.$numrow5)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$no5++;
		$numrow5++;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('D44',"TOTAL OMZET PENJUALAN (C+D+E)");
		$excel->getActiveSheet()->getStyle('D44:D44')->applyFromArray($style_colhead);

		$excel->setActiveSheetIndex(0)->setCellValue('H44','=SUM(H34+H37+H40)');
		$excel->getActiveSheet()->getStyle('H44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('H44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('I44','=SUM(I34+I37+I40)');
		$excel->getActiveSheet()->getStyle('I44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('I44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('J44','=SUM(J34+J37+J40)');
		$excel->getActiveSheet()->getStyle('J44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('J44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('K44','=SUM(K34+K37+K40)');
		$excel->getActiveSheet()->getStyle('K44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('K44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('L44','=SUM(L34+L37+L40)');
		$excel->getActiveSheet()->getStyle('L44')->applyFromArray($style_coltotal);
		$excel->getActiveSheet()->getStyle('L44')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$queryExecekl	= "SELECT SUM (q_master.balance) balance,
                     SUM (q_pendapatan.sendiri) sendiri,
                     SUM (q_pendapatan.oleh_pemungut) oleh_pemungut,
                     SUM (q_pendapatan.dibebaskan) dibebaskan,
                     SUM (q_pendapatan.bukan_ppn) bukan_ppn
                FROM (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                               SUM (NVL (debit, 0) - 1) - SUM (NVL (credit, 0) - 1) balance,
                               masa_pajak
                          FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                         WHERE SUBSTR (kode_akun, 0, 3) IN
                                     ('701', '702', '703', '704', '705', '706', '707', '708')
                               AND bulan_pajak = '".$bulan."'
                               and tahun_pajak = '".$tahun."'
                      GROUP BY SUBSTR (kode_akun, 0, 3), masa_pajak) q_master,
                     (SELECT akun_pajak,
                             sendiri,
                             oleh_pemungut,
                             dibebaskan,
                             bukan_ppn,
                             masa_pajak,
                             pembetulan_ke
                        FROM (SELECT SUBSTR (spl.akun_pajak, 0, 3) akun_pajak,
                                     spl.dpp jumlah_potong,
                                     sph.masa_pajak,
                                     sph.pembetulan_ke,
                                     case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                                FROM    simtax_pajak_headers sph
                                     INNER JOIN
                                        simtax_pajak_lines spl
                                     ON SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                     INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                               WHERE sph.nama_pajak IN ('PPN MASUKAN', 'PPN KELUARAN')
                                     AND sph.bulan_pajak ='".$bulan."'
                                     and sph.tahun_pajak = '".$tahun."'
                                     AND SPL.IS_CHEKLIST = '1'
                                     AND (SPL.NO_FAKTUR_PAJAK IS NOT NULL
                                          OR SPL.NO_DOKUMEN_LAIN IS NOT NULL)
                                     AND SUBSTR (spl.akun_pajak, 0, 3) IN
                                              ('701', '702', '703','704', '705', '706', '707', '708')
                                     ORDER BY SPH.PEMBETULAN_KE ASC
                             ) PIVOT (SUM (jumlah_potong*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                            WHERE q_master.kode_akun = q_pendapatan.akun_pajak
                             AND q_master.masa_pajak = Q_PENDAPATAN.masa_pajak
                            GROUP BY q_pendapatan.pembetulan_ke, q_master.masa_pajak
                            ORDER BY q_master.masa_pajak DESC";

		$queryekl 		= $this->db->query($queryExecekl);
			$balanceekl 		= 0;
			$sendiriekl			= 0;
			$oleh_pemungutekl	= 0;
			$dibebaskanekl		= 0;
			$bukan_ppnekl		= 0;
			$tot_ekl			= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('D46',"TOTAL OMZET (DPP) MENURUT SPT");
			$excel->getActiveSheet()->getStyle('D46:D46')->applyFromArray($style_colhead);

			foreach($queryekl->result_array() as $rowekl)	{
			$balanceekl 		+= $rowekl['BALANCE'];
			$sendiriekl			+= $rowekl['SENDIRI'];
			$oleh_pemungutekl	+= $rowekl['OLEH_PEMUNGUT'];
			$dibebaskanekl		+= $rowekl['DIBEBASKAN'];
			$bukan_ppnekl		+= $rowekl['BUKAN_PPN'];

			$tot_ekl			+= $rowekl['SENDIRI'] + $rowekl['OLEH_PEMUNGUT'] + $rowekl['DIBEBASKAN'];

			$excel->setActiveSheetIndex(0)->setCellValue('H46', $balanceekl);
			$excel->setActiveSheetIndex(0)->setCellValue('I46', $sendiriekl);
			$excel->setActiveSheetIndex(0)->setCellValue('J46', $oleh_pemungutekl);
			$excel->setActiveSheetIndex(0)->setCellValue('K46', $dibebaskanekl);

			$excel->getActiveSheet()->getStyle('H46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('I46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('J46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('K46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('L46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			}

		//
		$excel->setActiveSheetIndex(0)->setCellValue('D48',"SELISIH");
		$excel->getActiveSheet()->getStyle('D48:D48')->applyFromArray($style_colselisih);

		$excel->setActiveSheetIndex(0)->setCellValue('H48','=(H44-H46)');
		$excel->getActiveSheet()->getStyle('H46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('I48','=(I44-I46)');
		$excel->getActiveSheet()->getStyle('I46')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('J48','=(J44-J46)');
		$excel->getActiveSheet()->getStyle('J48')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('K48','=(K44-K46)');
		$excel->getActiveSheet()->getStyle('K48')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('L48','=(L44-L46)');
		$excel->getActiveSheet()->getStyle('L48')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		//
		$excel->setActiveSheetIndex(0)->setCellValue('D52',"MENGETAHUI :");

		$cabang		= $this->session->userdata('kd_cabang');
		$nama_pajak = 'SPT PPN Masa';
		$jpp 		= 'DVP Pajak';
		$dt 		= 'Ekualisasi';

		$queryExec1	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = '".$jpp."'
                            and nama_pajak = '".$nama_pajak."'
                            and document_type = '".$dt."' 
                            and kode_cabang = '".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
		$query1 	= $this->db->query($queryExec1);
		$rowCount	= $query1->num_rows();

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
				$objDrawing->setCoordinates('D55');
				$objDrawing->setHeight(80); // logo height
				$objDrawing->setWorksheet($excel->getActiveSheet());
			}
			$excel->setActiveSheetIndex(0)->setCellValue('D53', $petugas_ttd);
			$excel->setActiveSheetIndex(0)->setCellValue('D59', $jabatan_petugas_ttd);
		}
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(2); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(3); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(3); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(45); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(0)	; // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(65); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(30); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(30); // Set width kolom A

		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Ekualisasi PPN Masa");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Ekualisasi PPN MASA.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}
	
	
	//Awal Add Udin==============================================================
	function show_equal_pph_all()
	{
		$this->template->set('title', 'Laporan Ekualisasi PPh');
		$data['subtitle']	= "Cetak Laporan Ekualisasi PPh";
		$data['activepage'] = "laporan_ekualisasi";
		$this->template->load('template', 'laporan/lap_equalisasi_pph',$data);		
	}	

	
	function cetak_equal_pph_xls()
	{

		$pajak 		= $_REQUEST['pajak'];
		$nmpajak 	= $_REQUEST['nmpajak'];
		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['kd_cabang'];
		
		if ($pajak=="PPH PSL 15"){
			$this->cetak_equal_pph15_xls($pajak,$nmpajak,$tahun,$bulan,$masa,$cabang);
		} else if ($pajak == "PPH PSL 22" || $pajak=="PPH PSL 23 DAN 26"){
			$this->cetak_equal_pph23_26_xls($pajak,$nmpajak,$tahun,$bulan,$masa,$cabang);
		} else if($pajak=="PPH PSL 4 AYAT 2"){
			$this->cetak_equal_pph4_ayat2_xls($pajak,$nmpajak,$tahun,$bulan,$masa,$cabang);
		} 		
	}	
	
	function cetak_equal_pph15_xls($pajak,$nmpajak,$tahun,$bulan,$masa,$cabang)
	{
		$header_id	= $this->Pph_mdl->get_header_id_max($pajak,$bulan,$tahun,$cabang);
		$where		= "";	
		$where_23	= "";
		$nama_cabang 	= strtoupper(get_nama_cabang($cabang));
		$header 		= "and sph.pajak_header_id= '".$header_id."' ";
		$header1 		= "--and sph.pajak_header_id= '".$header_id."' ";

		$nama_bulan 	= get_masa_pajak($bulan,'id',true);

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
			$header_id = $header;
		} else{
			$kd_cabang = "";
			$header = $header1;
		}
					
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi ".$nmpajak)
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi ".$nmpajak)
								->setKeywords($nmpajak);
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$center_bold_border = array(
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
		
		$center_no_bold_border = array(
		        'font' => array('bold' => false),
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
		
		$center_bold_noborder = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$center_nobold_noborder = array(
		        'font' => array('bold' => false), 
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);	
		
		$center_bold_border_bottom_left = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN)
		  )
		);
		
		$center_bold_border_top = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(				
			 'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
			 'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis			
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$center_bold_border_bottom = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(			 
			 'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$left_bold_border = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kika_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$borderfull_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);			
		
		$border_kika_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$noborder_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);			
		
		$noborder_nobold_rata_kanan = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
			
		
		$border_top_buttom_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			  'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$parent_col = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11,
								'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kiri = array(
		    'borders' => array(				  	 
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kanan = array(
		    'borders' => array(				
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis					  
		  )
		);
		
		$border_bawah = array(
		    'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$noborder_rata_kiri = array(
		        'font' => array('name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);

		$center_border = array(
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

		$kantor = "";	

		if($cabang == 'all'){
			$kantor = "PT PELABUHAN INDONESIA II (PERSERO)";
		}else{
			$kantor = "PT PELABUHAN INDONESIA II CABANG ".$nama_cabang;
		}			
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('B1', $kantor);
		$excel->getActiveSheet()->mergeCells('B1:D1');	
		$excel->getActiveSheet()->getStyle('B1:D1')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Ekualisasi Objek ".$nmpajak." :");
		$excel->getActiveSheet()->mergeCells('B2:D2');	
		$excel->getActiveSheet()->getStyle('B2:D2')->applyFromArray($noborder_rata_kiri);
		
		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan ".$nama_bulan);
		$excel->getActiveSheet()->mergeCells('B3:E3');
		$excel->getActiveSheet()->getStyle('B3:E3')->applyFromArray($noborder_rata_kiri);		
		
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('F4:H4');	
		$excel->getActiveSheet()->getStyle('F4:H4')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('I4', "Keterangan");
		$excel->getActiveSheet()->mergeCells('I4:I5');	
		$excel->getActiveSheet()->getStyle('I4:I5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "Account");
		$excel->getActiveSheet()->mergeCells('B4:B5');
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('C4', "Uraian");
		$excel->getActiveSheet()->mergeCells('C4:C5');	
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('D4', "Klas");
		$excel->getActiveSheet()->mergeCells('D4:D5');	
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('E4', "Jumlah Biaya");
		$excel->getActiveSheet()->mergeCells('E4:E5');
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('G5', "Objek PPh Pasal 15");	
		$excel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Bukan Objek");
		$excel->getActiveSheet()->getStyle('H5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "PPh Pasal 15");			
		$excel->getActiveSheet()->getStyle('H5')->applyFromArray($center_border);
		
		// end header	
			$no = 1; 
			$numrow = 6;					
			$numrowBorderStart = 6;

			if ($kd_cabang == ""){
				$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
			} else{
				$whereCabang = "'".$kd_cabang."'";
			}					
				
					$where		.= " and kode_cabang in (".$whereCabang.") ";
					$where_23	.= " and sph.kode_cabang in (".$whereCabang.") ";
				if($bulan) {
					$where 		.= " and bulan_pajak= '".$bulan."' ";
					$where_23 	.= " and sph.bulan_pajak= '".$bulan."' ";
				}				
				$queryExecSub8 = "Select tb.kode_akun	
									, o23.kode_akun kd23
									, tb.akun_description
									, tb.jumlah_tb1 jumlah_tb
									, nvl(O23.nil_objek_23,0) nil23
									, case	
										when SUBSTR(TB.kode_akun,1,1)=8 then 1
										when SUBSTR(TB.kode_akun,1,2)=31 then 2
										when SUBSTR(TB.kode_akun,1,3)=207 then 3
										when SUBSTR(TB.kode_akun,1,3) between 103 and 106 then 4
										when SUBSTR(TB.kode_akun,1,3) in (203,110,107) then 5
										when SUBSTR(TB.kode_akun,1,3) in (208,209) then 6
										else 7										
									  end
										urut
									from
										(
										Select kode_akun, akun_description
												, (sum(nvl(DEBIT,0)) - sum(nvl(CREDIT,0))) jumlah_tb1 	
											from SIMTAX_RINCIAN_BL_PPH_BADAN 
											where tahun_pajak= '".$tahun."' 
												".$where." 
												and SUBSTR(kode_akun,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)												 
											group by kode_akun, akun_description 		
										) tb,
										(
											select kode_akun,sum(begin_balance) begin_balance from (
												select kode_akun, kode_cabang, begin_balance												
												from simtax_rincian_bl_pph_badan
												where tahun_pajak = '".$tahun."' 
												".$where." 
												and SUBSTR(kode_akun,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)
												group by kode_akun, kode_cabang, begin_balance
											)
											group by kode_akun
										) bb,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_23 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."' 
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1
												".$header."
												and Substr(SPL.GL_ACCOUNT,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)													
											group by SPL.GL_ACCOUNT
										) o23
								 where tb.KODE_AKUN=O23.KODE_AKUN (+)
									   and tb.KODE_AKUN=bb.KODE_AKUN (+)
								 order by urut, TB.KODE_AKUN";
				
				$querySub8			= $this->db->query($queryExecSub8);
				$sum_tb            = 0;
				$sum_bukan_objek23 = 0;			
				$sum_objek23       = 0;
				foreach($querySub8->result_array() as $row)	{							
					// List Akun		
					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['KODE_AKUN']);								
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['AKUN_DESCRIPTION']);								
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");							
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['JUMLAH_TB']);	
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['JUMLAH_TB']-$row['NIL23']);	
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['NIL23']);				
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, "");	
					
					$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);					
					$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);					
					$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);
					$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);			
					
					$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
					$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
										
					$sum_tb            += $row['JUMLAH_TB'];
					$sum_bukan_objek23 += ($row['JUMLAH_TB']-$row['NIL23']);		
					$sum_objek23       += $row['NIL23'];	
					$numrow++;
			}
						
		
		$numrow+=1;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Total Objek PPh 15");
		$excel->getActiveSheet()->mergeCells('B'.$numrow.':D'.$numrow);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $sum_tb);	
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $sum_objek23);	
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $sum_bukan_objek23);	
				
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($noborder_rata_kiri);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);		
		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');	
		$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');	
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(2); 
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(10); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(60); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(8); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(2); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); 
		
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Equal ".$nmpajak);
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Laporan Ekualisasi '.$nmpajak.' '.$masa.' '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}	
		
	function cetak_equal_pph23_26_xls($pajak,$nmpajak,$tahun,$bulan,$masa,$cabang)
	{		
		$header_id		= $this->Pph_mdl->get_header_id_max($pajak,$bulan,$tahun,$cabang);
		$where			= "";	
		$where_23		= "";
		$nama_cabang 	= strtoupper(get_nama_cabang($cabang));
		$header 		= "and sph.pajak_header_id= '".$header_id."' ";
		$header1 		= "--and sph.pajak_header_id= '".$header_id."' ";

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
			$header_id = $header;
		} else{
			$kd_cabang = "";
			$header = $header1;
		}	
				
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi ".$nmpajak)
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi ".$nmpajak)
								->setKeywords($nmpajak);
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$center_bold_border = array(
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
		
		$center_no_bold_border = array(
		        'font' => array('bold' => false),
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
		
		$center_bold_noborder = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$center_nobold_noborder = array(
		       'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11),
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);	
		
		$border_kika_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$borderfull_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);			
		
		$border_kika_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$noborder_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);			
		
		$noborder_nobold_rata_kanan = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$parent_col = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11,
								'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kiri = array(
		    'borders' => array(				  	 
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kanan = array(
		    'borders' => array(				
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis					  
		  )
		);
		
		$border_bawah = array(
		    'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$kantor = "";	

		if($cabang == 'all'){
			$kantor = "PT PELABUHAN INDONESIA II (PERSERO)";
		}else{
			$kantor = "PT PELABUHAN INDONESIA II CABANG ".$nama_cabang;
		}	
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('B1', $kantor);
		$excel->getActiveSheet()->mergeCells('B1:D1');
		$excel->getActiveSheet()->getStyle('B1:D1')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Ekualisasi Objek ".$nmpajak." :");
		$excel->getActiveSheet()->mergeCells('B2:D2');

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan");
		$excel->getActiveSheet()->mergeCells('B3:D3');
		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan : ".$masa." ".$tahun);
		
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "Account");
		$excel->getActiveSheet()->mergeCells('B4:B5');	
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('C4', "Uraian");
		$excel->getActiveSheet()->mergeCells('C4:C5');	
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('D4', "Klas");
		$excel->getActiveSheet()->mergeCells('D4:D5');	
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('E4', "Jumlah Biaya");
		$excel->getActiveSheet()->mergeCells('E4:E5');	
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($center_no_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('F4:H4');	
		$excel->getActiveSheet()->getStyle('F4:H4')->applyFromArray($center_no_bold_border);
		
		if($pajak == "PPH PSL 23 DAN 26"){
			$title_header = "23";
		} else {
			$title_header = "22";
		}

		$excel->setActiveSheetIndex(0)->setCellValue('G5', "Objek ".$title_header);
		$excel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($center_no_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Bukan Objek ".$title_header);
		$excel->getActiveSheet()->getStyle('H5')->applyFromArray($center_no_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('I4', "Keterangan");
		$excel->getActiveSheet()->mergeCells('I4:I5');	
		$excel->getActiveSheet()->getStyle('I4:I5')->applyFromArray($center_no_bold_border);
		
		
		// end header	
			//$no = 1; 
			$numrow = 6;			
			$numrowBorderStart = 6;

			if ($kd_cabang == ""){
				$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
			} else{
				$whereCabang = "'".$kd_cabang."'";
			}
			
			$tot_tb        = 0;
			$tot_obj22     = 0;
			$tot_bkn_obj22 = 0;



					$where		.= " and kode_cabang in (".$whereCabang.") ";
					$where_23	.= " and sph.kode_cabang in (".$whereCabang.") ";			
				if($bulan) {
					$where 		.= " and bulan_pajak= '".$bulan."' ";
					$where_23 	.= " and sph.bulan_pajak= '".$bulan."' ";
				}							
				$queryExecSub8 = "Select tb.kode_akun	
									, o23.kode_akun kd23
									, tb.akun_description
									, tb.jumlah_tb1 jumlah_tb
									, nvl(O23.nil_objek_23,0) nil23
									, case
										when SUBSTR(TB.kode_akun,1,1)=8 then 1
										when SUBSTR(TB.kode_akun,1,2)=31 then 2
										when SUBSTR(TB.kode_akun,1,3)=207 then 3
										when SUBSTR(TB.kode_akun,1,3) between 103 and 106 then 4
										when SUBSTR(TB.kode_akun,1,3) in (203,110,107) then 5
										when SUBSTR(TB.kode_akun,1,3) in (208,209) then 6
										else 7
									  end
										urut
									from
										(
										Select kode_akun, akun_description
												, (sum(nvl(DEBIT,0)) - sum(nvl(CREDIT,0))) jumlah_tb1 	
											from SIMTAX_RINCIAN_BL_PPH_BADAN 
											where tahun_pajak= '".$tahun."' 
												".$where." 
												and (
														SUBSTR(kode_akun,1,1)=8 
														or SUBSTR(kode_akun,1,2)=31 
														or SUBSTR(kode_akun,1,3)=207 
														or SUBSTR(kode_akun,1,3) between 103 and 106 
														or SUBSTR(kode_akun,1,3) in (203,110,107)
														or SUBSTR(kode_akun,1,3) in (208,209)
													) 
											group by kode_akun, akun_description 		
										) tb,
										(
											select kode_akun,sum(begin_balance) begin_balance from (
												select kode_akun, kode_cabang, begin_balance												
												from simtax_rincian_bl_pph_badan
												where tahun_pajak = '".$tahun."' 
												".$where." 
												and (
														SUBSTR(kode_akun,1,1)=8 
														or SUBSTR(kode_akun,1,2)=31 
														or SUBSTR(kode_akun,1,3)=207 
														or SUBSTR(kode_akun,1,3) between 103 and 106 
														or SUBSTR(kode_akun,1,3) in (203,110,107)
														or SUBSTR(kode_akun,1,3) in (208,209)
													) 
												group by kode_akun, kode_cabang, begin_balance
											)
											group by kode_akun
										) bb,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_23 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."' 
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1												".$header."
												and (
														Substr(SPL.GL_ACCOUNT,1,1)=8 
														or Substr(SPL.GL_ACCOUNT,1,2)=31 
														or Substr(SPL.GL_ACCOUNT,1,3)=207 
														or Substr(SPL.GL_ACCOUNT,1,3) between 103 and 106
														or Substr(SPL.GL_ACCOUNT,1,3) in (203,110,107)
														or Substr(SPL.GL_ACCOUNT,1,3) in (208,209)
													)
											group by SPL.GL_ACCOUNT
										) o23
								 where tb.KODE_AKUN=O23.KODE_AKUN (+)
									and tb.KODE_AKUN=bb.KODE_AKUN (+)
								 order by TB.KODE_AKUN";

								 //print_r($queryExecSub8); die();
				
				$querySub8		           = $this->db->query($queryExecSub8);
				foreach($querySub8->result_array() as $row)	{

					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['KODE_AKUN']);
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['AKUN_DESCRIPTION']);
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['JUMLAH_TB']);	
					$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, "");
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['NIL23']);
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['JUMLAH_TB']-$row['NIL23']);
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, "");

					$tot_tb 			+= $row['JUMLAH_TB'];
					$tot_obj22 			+= $row['JUMLAH_TB']-$row['NIL23'];
					$tot_bkn_obj22 		+= $row['NIL23'];							
					
					$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
					$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

					$numrow++;
			}

			$numrow +=1;
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "Total Objek PPh ".$title_header);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $tot_tb);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $tot_bkn_obj22);
			$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $tot_obj22);

			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$numrow++;

		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(1); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(50); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(10); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(2); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); 	
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Equal ".$nmpajak);
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Laporan Ekualisasi '.$nmpajak.' '.$masa.' '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}	
	
	function cetak_equal_pph4_ayat2_xls($pajak,$nmpajak,$tahun,$bulan,$masa,$cabang)
	{
		$header_id	= $this->Pph_mdl->get_header_id_max($pajak,$bulan,$tahun,$cabang);
		$where		= "";	
		$where_23	= "";
		$nama_cabang 	= strtoupper(get_nama_cabang($cabang));
		$header 		= "and sph.pajak_header_id= '".$header_id."' ";
		$header1 		= "--and sph.pajak_header_id= '".$header_id."' ";

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
			$header_id = $header;
		} else{
			$kd_cabang = "";
			$header = $header1;
		}	
				
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi ".$nmpajak)
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi ".$nmpajak)
								->setKeywords($nmpajak);
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$center_bold_border = array(
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
		
		$center_no_bold_border = array(
		        'font' => array('bold' => false),
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
		
		$center_bold_noborder = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$center_nobold_noborder = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11),
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);	
		
		$center_bold_border_bottom_left = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN)
		  )
		);
		
		$border_kika_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$borderfull_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);			
		
		$border_kika_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$noborder_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);			
		
		$noborder_nobold_rata_kanan = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$parent_col = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11,
								'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kiri = array(
		    'borders' => array(				  	 
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kanan = array(
		    'borders' => array(				
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis					  
		  )
		);
		
		$border_bawah = array(
		    'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		//buat header cetakan
		$kantor = "";	

		if($cabang == 'all'){
			$kantor = "PT PELABUHAN INDONESIA II (PERSERO)";
		}else{
			$kantor = "PT PELABUHAN INDONESIA II CABANG ".$nama_cabang;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('B1', $kantor);
		$excel->getActiveSheet()->getStyle('B1')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Ekualisasi Objek ".$nmpajak);

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan");
		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan ".$masa." ".$tahun);	
		
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('F4:H4');	
		$excel->getActiveSheet()->getStyle('F4:H4')->applyFromArray($center_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('I4', "Keterangan");
		$excel->getActiveSheet()->mergeCells('I4:I5');	
		$excel->getActiveSheet()->getStyle('I4:I5')->applyFromArray($center_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "Account");
		$excel->getActiveSheet()->mergeCells('B4:B5');	
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($center_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('C4', "Uraian");
		$excel->getActiveSheet()->mergeCells('C4:C5');	
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($center_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('D4', "Klas");
		$excel->getActiveSheet()->mergeCells('D4:D5');	
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('E4', "Jumlah Biaya");
		$excel->getActiveSheet()->mergeCells('E4:E5');	
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('G5', "Objek 42");	
		$excel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Bukan Objek 42");	
		$excel->getActiveSheet()->getStyle('H5')->applyFromArray($center_bold_border);
		
		// end header	
			$no = 1; 
			$numrow = 6;	
			$numrowBorderStart = 6;

			if ($kd_cabang == ""){
				$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
			} else{
				$whereCabang = "'".$kd_cabang."'";
			}

					$where		.= " and kode_cabang in (".$whereCabang.") ";
					$where_23	.= " and sph.kode_cabang in (".$whereCabang.") ";
				if($bulan) {
					$where 		.= " and bulan_pajak= '".$bulan."' ";
					$where_23 	.= " and sph.bulan_pajak= '".$bulan."' ";
				}
				$queryExecSub8 = "Select tb.kode_akun	
									, o23.kode_akun kd23
									, tb.akun_description
									, tb.jumlah_tb1 jumlah_tb
									, nvl(O23.nil_objek_23,0) nil23
									, case	
										when SUBSTR(TB.kode_akun,1,1)=8 then 1
										when SUBSTR(TB.kode_akun,1,2)=31 then 2
										when SUBSTR(TB.kode_akun,1,3)=207 then 3
										when SUBSTR(TB.kode_akun,1,3) between 103 and 106 then 4
										when SUBSTR(TB.kode_akun,1,3) in (203,110,107) then 5
										when SUBSTR(TB.kode_akun,1,3) in (208,209) then 6
										else 7
										
									  end
										urut
									from
										(
										  Select kode_akun, akun_description
												, (sum(nvl(DEBIT,0)) - sum(nvl(CREDIT,0))) jumlah_tb1 	
											from SIMTAX_RINCIAN_BL_PPH_BADAN 
											where tahun_pajak= '".$tahun."' 
												".$where." 
												and SUBSTR(kode_akun,1,3) in (106,109,203,206,207,209,301,302,304,306,310,311,801,891) 								 
											group by kode_akun, akun_description 		
										) tb,
										(
											select kode_akun,sum(begin_balance) begin_balance from (
												select kode_akun, kode_cabang, begin_balance												
												from simtax_rincian_bl_pph_badan
												where tahun_pajak = '".$tahun."' 
												".$where." 
												and SUBSTR(kode_akun,1,3) in 
												(106,109,203,206,207,209,301,302,304,306,310,311,801,891) 
												group by kode_akun, kode_cabang, begin_balance
											)
											group by kode_akun
										) bb,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_23 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."' 
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1
												".$header."
												and Substr(SPL.GL_ACCOUNT,1,3) in (106,109,203,206,207,209,301,302,304,306,310,311,801,891) 										
											group by SPL.GL_ACCOUNT
										) o23
								 where tb.KODE_AKUN=O23.KODE_AKUN (+)
									and tb.KODE_AKUN=bb.KODE_AKUN (+)
								 order by urut, TB.KODE_AKUN";
				
				$querySub8		= $this->db->query($queryExecSub8);				
				$sum_tb            = 0;
				$sum_bukan_objek23 = 0;			
				$sum_objek23       = 0;
				foreach($querySub8->result_array() as $row)	{									
					// List Akun	
					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['KODE_AKUN']);						
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['AKUN_DESCRIPTION']);
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['JUMLAH_TB']);	
					$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, "");
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['NIL23']);
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['JUMLAH_TB']-$row['NIL23']);
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, "");	

					$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($center_nobold_noborder);					
					$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);					
					$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);					
					
					$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
					$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					
					$sum_tb            += $row['JUMLAH_TB'];
					$sum_bukan_objek23 += ($row['JUMLAH_TB']-$row['NIL23']);		
					$sum_objek23       += $row['NIL23'];	
					$numrow++;
					$no++;
			}
						
		
		$numrow+=1;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "Total Objek PPh");	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $sum_tb);	
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $sum_objek23);	
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $sum_bukan_objek23);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);

		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
		$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(1); 
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(50); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(10); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(2); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); 	
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Equal ".$nmpajak);
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Laporan Ekualisasi '.$nmpajak.' '.$masa.' '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}
	
	//Akhir Add Udin=============================================================

	//Awal Add Heri==============================================================
	function show_equal_pph_pasal_21()
	{
		$this->template->set('title', 'Laporan Ekualisasi PPh Psl 21	');
		$data['subtitle']	= "Cetak Laporan Ekualisasi PPh Psl 21";
		$data['activepage'] = "laporan_ekualisasi";
		$this->template->load('template', 'laporan/lap_equalisasi_pph_psl_21',$data);		
	}

	function cetak_equal_pph_psl_21_xls()
	{

		$pajak 		= $_REQUEST['pajak'];
		$nmpajak 	= "PPh Psl 21";
		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		//$cabang		= $_REQUEST['cabang'];
		$namacabang	= $_REQUEST['namacabang'];
		//$cabang		=  $this->session->userdata('kd_cabang');
		$cabang		= $_REQUEST['kd_cabang'];
		$date	    = date("Y-m-d H:i:s");
		$where		= "";	
		$where_23	= "";

		if ($cabang !='all'){
			$kd_cabang = $cabang;
		} else{
			$kd_cabang = '';
		}

		ini_set('memory_limit', '-1');
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi ".$nmpajak)
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi ".$nmpajak)
								->setKeywords($nmpajak);
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$center_bold_border = array(
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
		
		$center_no_bold_border = array(
		        'font' => array('bold' => false),
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
		
		$center_bold_noborder = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$center_nobold_noborder = array(
		        'font' => array('bold' => false), 
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);	
		
		$border_kika_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$borderfull_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);			
		
		$border_kika_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$noborder_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);			
		
		$noborder_nobold_rata_kanan = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 9), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$parent_col = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 9,
								'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
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
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Ekualisasi Objek ".$nmpajak." :");
		$excel->getActiveSheet()->mergeCells('B2:D2');	
		$excel->getActiveSheet()->getStyle('B2:D2')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan ".$masa); 
		$excel->getActiveSheet()->mergeCells('B3:D3');	
		$excel->getActiveSheet()->getStyle('B3:D3')->applyFromArray($noborder_bold_rata_kiri);
		
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "Menurut Fiskus");
		$excel->getActiveSheet()->mergeCells('B4:E4');	
		$excel->getActiveSheet()->getStyle('B4:E4')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('B5', "Account");
		$excel->getActiveSheet()->mergeCells('B5:B5');	
		$excel->getActiveSheet()->getStyle('B5:B5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('C5', "Uraian");
		$excel->getActiveSheet()->mergeCells('C5:C5');	
		$excel->getActiveSheet()->getStyle('C5:C5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('D5', "Klas");
		$excel->getActiveSheet()->mergeCells('D5:D5');	
		$excel->getActiveSheet()->getStyle('D5:D5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('E5', "Jumlah Biaya");
		$excel->getActiveSheet()->mergeCells('E5:E5');	
		$excel->getActiveSheet()->getStyle('E5:E5')->applyFromArray($center_no_bold_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('F4:H4');	
		$excel->getActiveSheet()->getStyle('F4:H4')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('F5', "Object 21");
		$excel->getActiveSheet()->mergeCells('F5:G5');	
		$excel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Bukan Object 21");
		$excel->getActiveSheet()->mergeCells('H5:H5');	
		$excel->getActiveSheet()->getStyle('H5:H5')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('I4', "Keterangan");
		$excel->getActiveSheet()->mergeCells('I4:I5');	
		$excel->getActiveSheet()->getStyle('I4:I5')->applyFromArray($center_no_bold_border);
		
		// end header
				if($bulan){
					if ($bulan<10){
						$bulan ="0".$bulan ;
					}
					//$namabulan  = $this->Pph_mdl->getMonth($bulan);
					$where 		.= " and to_char(effective_date,'mm')= '".$bulan."' ";
					$where_23 	.= " and srb.bulan_pajak= '".$bulan."' ";
				}
				if($tahun){
					$where 		.= " and to_char(effective_date,'yyyy')= '".$tahun."' ";
					$where_23 	.= " and srb.tahun_pajak= '".$tahun."' ";
				}

				if ($kd_cabang == ""){
					$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
				} else{
					$whereCabang = "'".$kd_cabang."'";
				}

                                 $queryExecSub8 = "Select
                                      tb.segment5    
                                    , o23.kode_akun kd23
                                    , o23.akun_description
                                    , nvl(o23.debit,0) debit
                                    , nvl(o23.credit,0) credit
                                    , nvl(tb.costed_value,0) costed_value
                                    , case
                                        when SUBSTR(TB.segment5,1,1)=8 then 1
                                      end
                                        urut
                                    from
									(
                                            select
                                               substr(srb.KODE_AKUN,1,8) kode_akun
                                             , sum(srb.debit) debit
                                             , sum(srb.credit) credit
                                             , srb.akun_description
                                            from SIMTAX_RINCIAN_BL_PPH_BADAN srb
                                            where 1=1
                                            ".$where_23."
                                            and kode_cabang in (".$whereCabang.")
                                            and  (Substr(srb.KODE_AKUN,1,1) in ('1','2','3','8'))
                                            group by substr(srb.KODE_AKUN,1,8), srb.akun_description
                                        ) o23,
                                        (
                                        Select
                                                 (SUBSTR(effective_date,4,3)) as effective_date 
                                               , (SUBSTR(effective_date,8,9)) as effective_date_thn
                                               , segment5
                                               , sum(costed_value) costed_value
                                            from SIMTAX_PPH21_DTL
                                            where 1=1
                                            ".$where."
                                            and segment2 in (".$whereCabang.")
                                            and (SUBSTR(segment5,1,1) in ('1','2','3','8'))
                                            group by segment5 , (SUBSTR(effective_date,8,9)), (SUBSTR(effective_date,4,3))
                                        ) tb                                        
                                 --where tb.segment5=O23.KODE_AKUN (+)
								 where O23.KODE_AKUN = tb.segment5 (+)
                                 order by O23.KODE_AKUN";
			
				$querySub8		= $this->db->query($queryExecSub8);
				$sum_tb            = 0;
				$sum_bukan_objek23 = 0;			
				$sum_objek23       = 0;

				$numrow = 5;

				foreach($querySub8->result_array() as $row)	{	
					$numrow++;		
					//$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['SEGMENT5']);								
					//edit by Derry
					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['KD23']);								
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['AKUN_DESCRIPTION']);	
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");	
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['DEBIT'] - $row['CREDIT']);	
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['COSTED_VALUE']);
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($row['DEBIT'] - $row['CREDIT']) - $row['COSTED_VALUE']);
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, "");

					$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
					$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	

					$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);					
					$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);
					$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);

					$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
					$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

					$sum_tb            += ($row['DEBIT'] - $row['CREDIT']);
					$sum_bukan_objek23 += ($row['DEBIT'] - $row['CREDIT']) - $row['COSTED_VALUE'];		
					$sum_objek23       += $row['COSTED_VALUE'];
				}			
		
		$numrow+=2;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, 'Total Object '.$nmpajak.' ');
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $sum_tb);	
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $sum_objek23);
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $sum_bukan_objek23);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);			
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);

		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');	
		$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(1); 
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(10); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(40); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(10); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(5); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);  
	
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle($nmpajak);
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Laporan Ekualisasi '.$nmpajak.' '.$masa.' '.$tahun.' '.$namacabang.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_report_ppn_masa_thn()
	{
		$this->template->set('title', 'Rekap SPT PPN');
		$data['subtitle']   = "Cetak Rekap SPT PPN";
		$data['activepage'] = "ppn_masa";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_rekap_ppn_masa_tahunan',$data);		
	}		
	
	function cetak_rekap_ppn_masa_tahunan_awal()
	{

		$tahun        = $_REQUEST['tahun'];
		$pembetulanKe = $_REQUEST['pembetulanKe'];
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak SPT Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak SPT Setahun")
								->setKeywords("WAPU");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_hsl = array(
		   'font' => array('bold' => true),
		   'alignment' => array(
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

		$style_row_jud = array(
		   'font' => array('bold' => true),
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_head = array(
		   'font' => array('bold' => true),
		   'alignment' => array(
		  ),
		);
		//buat header cetakan
		//logo IPC
		$excel->setActiveSheetIndex(0)->setCellValue('A5', "REKAPITULASI SETORAN PAJAK PERTAMBAHAN NILAI TAHUN ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('A5')->applyFromArray($style_row_head);

		$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('C9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"
		
		$excel->getActiveSheet()->mergeCells('A7:A9');		
		$excel->getActiveSheet()->mergeCells('B7:B9');		
		
		$excel->getActiveSheet()->getStyle('A7:A9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('B7:B9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('C7:C9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D7:D9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E7:E9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('F7:F9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('A7:A9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('B7:B9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('C7:C9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D7:D9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('E7:E9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('F7:F9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('G8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('H8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('I8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('G9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('H9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('I9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('G7:G9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('H7:H9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('I7:I9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('J7:J9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('G7:G9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('H7:H9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('I7:I9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('J7:J9')->applyFromArray($style_col2);
		
		//
		$excel->setActiveSheetIndex(0)->setCellValue('K8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('L8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('M8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('N8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('K9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('L9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('M9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('N9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('K7:K9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('L7:L9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('M7:M9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('N7:N9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('K7:K9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('L7:L9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('M7:M9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('N7:N9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('O8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('P8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Q8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('R8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('O9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('P9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Q9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('R9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('O7:O9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('P7:P9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('Q7:Q9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('R7:R9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('O7:O9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('P7:P9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('Q7:Q9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('R7:R9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('S8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('T8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('U8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('V8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('S9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('T9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('U9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('V9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('S7:S9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('T7:T9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('U7:U9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('V7:V9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('S7:S9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('T7:T9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('U7:U9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('V7:V9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('W8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('X8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Y8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('Z8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('W9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('X9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Y9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('Z9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('W7:W9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('X7:X9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('Y7:Y9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('Z7:Z9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('W7:W9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('X7:X9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('Y7:Y9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('Z7:Z9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AA8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AB8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AC8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AD8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AA9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AB9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AC9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AD9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AA7:AA9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AB7:AB9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AC7:AC9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AD7:AD9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AA7:AA9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AB7:AB9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AC7:AC9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AD7:AD9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AE8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AF8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AG8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AH8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AE9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AF9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AG9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AH9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AE7:AE9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AF7:AF9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AG7:AG9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AH7:AH9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AE7:AE9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AF7:AF9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AG7:AG9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AH7:AH9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AI8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AJ8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AK8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AL8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AI9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AJ9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AK9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AL9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AI7:AI9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AJ7:AJ9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AK7:AK9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AL7:AL9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AI7:AI9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AJ7:AJ9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AK7:AK9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AL7:AL9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AM8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AN8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AO8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AP8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AM9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AN9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AO9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AP9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AM7:AM9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AN7:AN9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AO7:AO9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AP7:AP9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AM7:AM9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AN7:AN9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AO7:AO9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AP7:AP9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AQ8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AR8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AS8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AT8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AQ9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AR9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AS9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AT9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AQ7:AQ9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AR7:AR9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AS7:AS9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AT7:AT9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AQ7:AQ9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AR7:AR9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AS7:AS9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AT7:AT9')->applyFromArray($style_col2);
		// Buat header tabel nya pada baris ke 3
		//$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Januari"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->getActiveSheet()->mergeCells('C7:F7');	
		//$excel->getActiveSheet()->getStyle('C7:E7')->applyFromArray($noborder_bold_rata_kiri);
		$excel->setActiveSheetIndex(0)->setCellValue('G7', "Februari"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->getActiveSheet()->mergeCells('G7:J7');
		$excel->setActiveSheetIndex(0)->setCellValue('K7', "Maret"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('K7:N7');
		$excel->setActiveSheetIndex(0)->setCellValue('O7', "April"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('O7:R7');
		$excel->setActiveSheetIndex(0)->setCellValue('P7', "Mei"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('P7:R7');
		$excel->setActiveSheetIndex(0)->setCellValue('S7', "Juni"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('S7:V7');
		$excel->setActiveSheetIndex(0)->setCellValue('W7', "Juli"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('W7:Z7');
		$excel->setActiveSheetIndex(0)->setCellValue('AA7', "Agustus"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AA7:AD7');
		$excel->setActiveSheetIndex(0)->setCellValue('AE7', "September"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AE7:AH7');
		$excel->setActiveSheetIndex(0)->setCellValue('AI7', "Oktober"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AI7:AL7');
		$excel->setActiveSheetIndex(0)->setCellValue('AM7', "November"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AM7:AP7');
		$excel->setActiveSheetIndex(0)->setCellValue('AQ7', "Desember"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AQ7:AT7');

		$excel->getActiveSheet()->getStyle('A7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('G7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('H7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('I7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('J7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('K7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('L7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('M7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('N7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('O7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('P7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('V7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('Q7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('R7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('S7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('T7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('U7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('V7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('W7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('X7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('Y7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('Z7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AA7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AB7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AC7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AD7')->applyFromArray($style_row);	
		$excel->getActiveSheet()->getStyle('AE7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AF7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AG7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AH7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AI7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AJ7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AK7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AL7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AM7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AN7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AO7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AP7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AQ7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AR7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AS7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AT7')->applyFromArray($style_row);			
		
		
		//get detail
			/*
			$queryExec	= " select rownum, wapu.* from (select kode_cabang, nama_cabang, spt, bulan_pajak from simtax_rpt_spt_wapu_tahunan_v
							where tahun_pajak = '".$tahun."'
							and pembetulan_ke = 0)
							pivot (
							max(spt)
							for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
							) wapu
							order by kode_cabang
							";								
			*/
			$queryExec	= "select skc.KODE_CABANG
                                --, skc.NAMA_CABANG
                                , case skc.nama_cabang
                                    when 'Kantor Pusat' then skc.nama_cabang
                                  else 'Cabang ' || skc.nama_cabang
                                  end nama_cabang                                
                                , januari, februari, maret, april, mei, juni, juli, agustus, september, oktober, november, desember from simtax_kode_cabang skc
                                ,(select rownum, ppn.* from (select kode_cabang, nama_cabang, spt, bulan_pajak from simtax_report_spt_tahunan_v
                                                            where tahun_pajak = '".$tahun."'
                                                            and pembetulan_ke = '".$pembetulanKe."')
                                                            pivot (
                                                            max(spt)
                                                            for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
                                                            ) ppn
                                                            ) rpt
                                where skc.kode_cabang = rpt.kode_cabang (+)                            
                                  and skc.kode_cabang in ('000','010','020','030','040','050',
                                                            '060','070','080','090','100','110','120')
                                --order by skc.kode_cabang
                                                     union all
                                                     select '991', 'Satker Priok' ,null,null,null,null,null,null,null,null,null,null,null,null from dual
                                                     union all
                                                     select '992', 'Satker Sorong' ,null,null,null,null,null,null,null,null,null,null,null,null from dual
                                                     union all
                                                     select '993', 'Satker PPL' ,null,null,null,null,null,null,null,null,null,null,null,null from dual
                                                     union all
                                                     select '994', 'Kompensasi' ,null,null,null,null,null,null,null,null,null,null,null,null from dual
                                                    order by 1";
			
			$query 		= $this->db->query($queryExec);

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 10; // Set baris pertama untuk isi tabel adalah baris ke 4
			$ttl_jan = 0;								
			$ttl_feb = 0;								
			$ttl_mar = 0;								
			$ttl_apr = 0;								
			$ttl_mei = 0;								
			$ttl_jun = 0;								
			$ttl_jul = 0;								
			$ttl_aug = 0;								
			$ttl_sep = 0;								
			$ttl_okt = 0;								
			$ttl_nov = 0;								
			$ttl_des = 0;	
						
			foreach($query->result_array() as $row)	{
					
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['JANUARI']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['FEBRUARI']);	
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['MARET']);	
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['APRIL']);
				$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $row['MEI']);
				$excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, $row['JUNI']);
				$excel->setActiveSheetIndex(0)->setCellValue('U'.$numrow, $row['JULI']);
				$excel->setActiveSheetIndex(0)->setCellValue('X'.$numrow, $row['AGUSTUS']);
				$excel->setActiveSheetIndex(0)->setCellValue('AA'.$numrow, $row['SEPTEMBER']);
				$excel->setActiveSheetIndex(0)->setCellValue('AC'.$numrow, $row['OKTOBER']);
				$excel->setActiveSheetIndex(0)->setCellValue('AF'.$numrow, $row['NOVEMBER']);
				$excel->setActiveSheetIndex(0)->setCellValue('AI'.$numrow, $row['DESEMBER']);
								
				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('V'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Q'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('R'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('S'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('T'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('U'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('V'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('W'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('X'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Y'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Z'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('AA'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AB'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AC'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AD'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AE'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('AF'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AG'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AH'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('AI'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AJ'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AK'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AL'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AM'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AN'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AO'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AP'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AQ'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AR'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AS'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AT'.$numrow)->applyFromArray($style_row);				
												
				/*$ttl_jan = $ttl_jan + $row['JANUARI'];								
				$ttl_feb = $ttl_feb + $row['FEBRUARI'];								
				$ttl_mar = $ttl_mar + $row['MARET'];								
				$ttl_apr = $ttl_apr + $row['APRIL'];								
				$ttl_mei = $ttl_mei + $row['MEI'];								
				$ttl_jun = $ttl_jun + $row['JUNI'];								
				$ttl_jul = $ttl_jul + $row['JULI'];								
				$ttl_aug = $ttl_aug + $row['AGUSTUS'];								
				$ttl_sep = $ttl_sep + $row['SEPTEMBER'];								
				$ttl_okt = $ttl_okt + $row['OKTOBER'];								
				$ttl_nov = $ttl_nov + $row['NOVEMBER'];								
				$ttl_des = $ttl_des + $row['DESEMBER'];*/	
				
				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}		

		//end get detail
		//total
		$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, "JUMLAH DISETOR");
		$excel->getActiveSheet()->mergeCells('A'.$numrow.':B'.$numrow);		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $ttl_jan);	
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ttl_feb);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ttl_mar);	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $ttl_apr);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ttl_mei);
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $ttl_jun);
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $ttl_jul);
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $ttl_aug);
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ttl_sep);
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $ttl_okt);
		$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $ttl_nov);
		$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('Q'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('S'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('T'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('U'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('V'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('W'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('X'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('Y'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('Z'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AA'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AB'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AC'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AD'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AE'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AF'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AG'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AH'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AI'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AJ'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AK'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AL'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AM'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AN'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AO'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AP'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AQ'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AR'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AS'.$numrow, $ttl_des);
		$excel->setActiveSheetIndex(0)->setCellValue('AT'.$numrow, $ttl_des);

		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('Q'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('R'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('S'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('T'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('U'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('V'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('W'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('X'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('Y'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('Z'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AA'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AB'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AC'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AD'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AE'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AF'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AG'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AH'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AI'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AJ'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AK'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AL'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AM'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AN'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AO'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AP'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AQ'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AR'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AS'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AT'.$numrow)->applyFromArray($style_row_hsl);
				
		//setahun
		/*$numrow = $numrow += 1; //$numrow++;
		$ttl_all = $ttl_jan + $ttl_feb + $ttl_mar + $ttl_apr + $ttl_mei + $ttl_jun + $ttl_jul + $ttl_aug + $ttl_sep + $ttl_okt + $ttl_nov + $ttl_des;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH SETAHUN");		
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $ttl_all );*/	
		
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(23); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('P')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('R')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('S')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('T')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('U')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('V')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('W')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('X')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('Y')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('Z')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AA')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AB')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AC')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AD')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AE')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AF')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AG')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AH')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AI')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AJ')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AK')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AL')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AM')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AN')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AO')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AP')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AQ')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AR')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AS')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AT')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekapt SPT Setahun");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap SPT Tahunan PPN.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}
	
	
	function show_report_ppn_masa_tahun_v1()
	{
		$this->template->set('title', 'Rekap SPT PPN');
		$data['subtitle']   = "Cetak Rekap SPT PPN";
		$data['activepage'] = "ppn_masa";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_rekap_ppn_masa_tahunan_cabang',$data);		
	}

	function cetak_rekap_ppn_masa_tahunan_v1()
	{

		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		$tahun 				= $_REQUEST['tahun'];
		$pembetulanKe 		= $_REQUEST['pembetulanKe'];
		$cabang 		    = $_REQUEST['kd_cabang'];

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
		} else{
			$kd_cabang = '';
		}		
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak SPT Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak SPT Setahun")
								->setKeywords("WAPU");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row1 = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_hsl = array(
		   		'font' => array('bold' => true),
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_ats = array(
				'font' => array('bold' => true),
		   'alignment' => array(
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

		$style_row_jud = array(
		   'font' => array('bold' => true),
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$border_Bold = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  ),
		);
		
		//buat header cetakan
		//logo IPC
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "REKAPITULASI SETORAN PAJAK PERTAMBAHAN NILAI TAHUN ".$tahun."");
		$excel->getActiveSheet()->getStyle('B2')->applyFromArray($border_Bold);
		
		$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('C9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('D9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('E9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('F9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"

		/*$excel->setActiveSheetIndex(0)->setCellValue('F26','=C26-D26-E26');
		$excel->setActiveSheetIndex(0)->setCellValue('F27','=C27-D27-E27');
		$excel->setActiveSheetIndex(0)->setCellValue('F28','=C28-D28-E28');*/

		
		$excel->getActiveSheet()->mergeCells('A7:A9');		
		$excel->getActiveSheet()->mergeCells('B7:B9');		
		
		$excel->getActiveSheet()->getStyle('A7:A9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('B7:B9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('C7:C9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D7:D9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E7:E9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('F7:F9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('A7:A9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('B7:B9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('C7:C9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('D7:D9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('E7:E9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('F7:F9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('G8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('H8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('I8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('G9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('H9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('I9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('G7:G9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('H7:H9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('I7:I9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('J7:J9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('G7:G9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('H7:H9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('I7:I9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('J7:J9')->applyFromArray($style_col2);
		
		//
		$excel->setActiveSheetIndex(0)->setCellValue('K8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('L8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('M8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('N8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('K9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('L9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('M9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('N9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('K7:K9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('L7:L9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('M7:M9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('N7:N9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('K7:K9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('L7:L9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('M7:M9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('N7:N9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('O8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('P8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Q8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('R8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('O9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('P9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Q9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('R9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('O7:O9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('P7:P9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('Q7:Q9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('R7:R9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('O7:O9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('P7:P9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('Q7:Q9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('R7:R9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('S8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('T8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('U8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('V8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('S9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('T9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('U9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('V9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('S7:S9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('T7:T9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('U7:U9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('V7:V9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('S7:S9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('T7:T9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('U7:U9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('V7:V9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('W8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('X8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Y8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('Z8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('W9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('X9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('Y9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('Z9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('W7:W9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('X7:X9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('Y7:Y9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('Z7:Z9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('W7:W9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('X7:X9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('Y7:Y9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('Z7:Z9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AA8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AB8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AC8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AD8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AA9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AB9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AC9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AD9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AA7:AA9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AB7:AB9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AC7:AC9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AD7:AD9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AA7:AA9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AB7:AB9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AC7:AC9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AD7:AD9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AE8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AF8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AG8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AH8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AE9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AF9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AG9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AH9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AE7:AE9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AF7:AF9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AG7:AG9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AH7:AH9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AE7:AE9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AF7:AF9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AG7:AG9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AH7:AH9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AI8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AJ8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AK8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AL8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AI9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AJ9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AK9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AL9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AI7:AI9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AJ7:AJ9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AK7:AK9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AL7:AL9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AI7:AI9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AJ7:AJ9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AK7:AK9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AL7:AL9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AM8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AN8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AO8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AP8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AM9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AN9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AO9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AP9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AM7:AM9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AN7:AN9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AO7:AO9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AP7:AP9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AM7:AM9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AN7:AN9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AO7:AO9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AP7:AP9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AQ8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AR8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AS8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AT8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"

		$excel->setActiveSheetIndex(0)->setCellValue('AQ9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AR9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AS9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AT9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AQ7:AQ9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AR7:AR9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AS7:AS9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AT7:AT9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AQ7:AQ9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AR7:AR9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AS7:AS9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AT7:AT9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AU8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AV8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AW8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AX8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAAU

		$excel->setActiveSheetIndex(0)->setCellValue('AU9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AV9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('AW9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('AX9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AU7:AU9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AV7:AV9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AW7:AW9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('AX7:AX9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AU7:AU9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AV7:AV9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AW7:AW9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('AX7:AX9')->applyFromArray($style_col2);

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AY8', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AZ8', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('BA8', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('BB8', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAAU

		$excel->setActiveSheetIndex(0)->setCellValue('AY9', "Rp."); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('AZ9', "Rp."); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('BA9', "Rp."); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('BB9', "Bayar"); // Set kolom E3 dengan tulisan "ALAMAT"		
		
		$excel->getActiveSheet()->getStyle('AY7:AY9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('AZ7:AZ9')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('BA7:BA9')->applyFromArray($style_row_jud);				
		$excel->getActiveSheet()->getStyle('BB7:BB9')->applyFromArray($style_row_jud);	
		
		$excel->getActiveSheet()->getStyle('AY7:AY9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('AZ7:AZ9')->applyFromArray($style_col2);
		$excel->getActiveSheet()->getStyle('BA7:BA9')->applyFromArray($style_col2);				
		$excel->getActiveSheet()->getStyle('BB7:BB9')->applyFromArray($style_col2);
		// Buat header tabel nya pada baris ke 3
		//$excel->setActiveSheetIndex(0)->setCellValue('A7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Cabang/Unit"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Januari"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->getActiveSheet()->mergeCells('C7:F7');	
		//$excel->getActiveSheet()->getStyle('C7:E7')->applyFromArray($noborder_bold_rata_kiri);
		$excel->setActiveSheetIndex(0)->setCellValue('G7', "Februari"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->getActiveSheet()->mergeCells('G7:J7');
		$excel->setActiveSheetIndex(0)->setCellValue('K7', "Maret"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('K7:N7');
		$excel->setActiveSheetIndex(0)->setCellValue('O7', "April"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('O7:R7');
		$excel->setActiveSheetIndex(0)->setCellValue('S7', "Mei"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('S7:V7');
		$excel->setActiveSheetIndex(0)->setCellValue('W7', "Juni"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('W7:Z7');
		$excel->setActiveSheetIndex(0)->setCellValue('AA7', "Juli"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AA7:AD7');
		$excel->setActiveSheetIndex(0)->setCellValue('AE7', "Agustus"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AE7:AH7');
		$excel->setActiveSheetIndex(0)->setCellValue('AI7', "September"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AI7:AL7');
		$excel->setActiveSheetIndex(0)->setCellValue('AM7', "Oktober"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AM7:AP7');
		$excel->setActiveSheetIndex(0)->setCellValue('AQ7', "November"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AQ7:AT7');
		$excel->setActiveSheetIndex(0)->setCellValue('AU7', "Desember"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AU7:AX7');
		$excel->setActiveSheetIndex(0)->setCellValue('AY7', "TOTAL"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->getActiveSheet()->mergeCells('AY7:BB7');

		$excel->getActiveSheet()->getStyle('A7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('F7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('G7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('H7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('I7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('J7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('K7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('L7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('M7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('N7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('O7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('P7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('V7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('Q7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('R7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('S7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('T7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('U7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('V7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('W7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('X7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('Y7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('Z7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AA7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AB7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AC7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AD7')->applyFromArray($style_row);	
		$excel->getActiveSheet()->getStyle('AE7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AF7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AG7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AH7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AI7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AJ7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AK7')->applyFromArray($style_row);				
		$excel->getActiveSheet()->getStyle('AL7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AM7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AN7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AO7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AP7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AQ7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AR7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AS7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AT7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AU7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AV7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AW7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AX7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AY7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('AZ7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('BA7')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('BB7')->applyFromArray($style_row);	

		//
		$excel->setActiveSheetIndex(0)->setCellValue('BD7', "Bulan");
		$excel->setActiveSheetIndex(0)->setCellValue('BE7', "Tanggal Setor");	
		$excel->setActiveSheetIndex(0)->setCellValue('BF7', "Kode Map");
		$excel->setActiveSheetIndex(0)->setCellValue('BG7', "Kode Jenis Setor");
		$excel->setActiveSheetIndex(0)->setCellValue('BH7', "NTPN");
		$excel->setActiveSheetIndex(0)->setCellValue('BI7', "Bank Persepsi");
		$excel->setActiveSheetIndex(0)->setCellValue('BJ7', "Nilai Setoran");
		$excel->setActiveSheetIndex(0)->setCellValue('BK7', "Tanggal Lapor Ke KPP");

		$excel->getActiveSheet()->getStyle('BD7')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BE7')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BF7')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BG7')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BH7')->applyFromArray($style_row_ats);				
		$excel->getActiveSheet()->getStyle('BI7')->applyFromArray($style_row_ats);				
		$excel->getActiveSheet()->getStyle('BJ7')->applyFromArray($style_row_ats);				
		$excel->getActiveSheet()->getStyle('BK7')->applyFromArray($style_row_ats);

		$excel->setActiveSheetIndex(0)->setCellValue('BD8', "1");
		/*$j = 9;
		$masa_pajak_full = "";
		for ($i=1; $i <= 12 ; $i++) {
			$masa_pajak_full = get_masa_pajak($i, "id", true);	
			$excel->setActiveSheetIndex(0)->setCellValue('BD'.$j, $masa_pajak_full);
			$j++;
		}*/

		$excel->setActiveSheetIndex(0)->setCellValue('BD21', "");
		/*$excel->setActiveSheetIndex(0)->setCellValue('BD22', "Total");*/

		$excel->getActiveSheet()->getStyle('BD8')->applyFromArray($style_row1);
		/*$excel->getActiveSheet()->getStyle('BD9')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD10')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD11')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD12')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD13')->applyFromArray($style_row_ats);				
		$excel->getActiveSheet()->getStyle('BD14')->applyFromArray($style_row_ats);				
		$excel->getActiveSheet()->getStyle('BD15')->applyFromArray($style_row_ats);				
		$excel->getActiveSheet()->getStyle('BD16')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD17')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD18')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD19')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD20')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD21')->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BD22')->applyFromArray($style_row_ats);*/

		$excel->setActiveSheetIndex(0)->setCellValue('BE8', "2");
		
		$sqlNtpn    = "select * from simtax_ntpn where tahun = '".$tahun."' and pembetulan = '".$pembetulanKe."' and jenis_pajak is null";
		$queryNtpn  = $this->db->query($sqlNtpn);
		$resultNtpn = $queryNtpn->result_array();
		$bulanVal   = "";
		$ntpnVal    = array();
		$ntpnTotal  = 0;
		// echo "<pre>";
			// print_r($resultNtpn); die();

		$x=0;
		foreach ($resultNtpn as $key => $value) {
			$bulanVal = $value['BULAN'];
			$ntpnVal[$bulanVal][]         = $value;
			/*$ntpnVal['NTPN'][$bulanVal]          = $value['NTPN'][$x];
			$ntpnVal['BANK'][$bulanVal]          = $value['BANK'];
			$ntpnVal['TANGGAL_LAPOR'][$bulanVal] = $value['TANGGAL_LAPOR'];
			$ntpnVal['TANGGAL_SETOR'][$bulanVal] = $value['TANGGAL_SETOR'];*/

			$ntpnTotal += $value['NTPN'];

			$x++;

		}
			// print_r($ntpnVal);die;

		$j = 9;
		$masa_pajak_full = "";


		for ($i=1; $i <= 12 ; $i++) {
			$masa_pajak_full = get_masa_pajak($i, "id", true);	

			// echo $ntpnVal[$i][0][0];

			$arkeys = array_keys($ntpnVal);

				/*echo $count;
				print_r($ntpnVal);
				die;*/
			if(in_array($i, $arkeys)){
				$count = count($ntpnVal[$i]);
				$pushCount[$i] = $count;
				if($count > 1){
					$x=0;
					$firstJ = $j;
					foreach ($ntpnVal[$i] as $key => $value) {
						// echo $value['NTPN']." ".$value['TANGGAL_SETOR']." <br>";
						if($x==0){
							$excel->setActiveSheetIndex(0)->setCellValue('BD'.$j, $masa_pajak_full);
							$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row_ats);
						}

						$excel->setActiveSheetIndex(0)->setCellValue('BE'.$j, $value['TANGGAL_SETOR']);
						$excel->setActiveSheetIndex(0)->setCellValue('BH'.$j, $value['NTPN']);
						$excel->setActiveSheetIndex(0)->setCellValue('BI'.$j, $value['BANK']);
						$excel->getActiveSheet()->getStyle('BI'.$j)->applyFromArray($style_row);
						$j++;
						$x++;
					}
					$jmin1 = $j-1;
					$excel->getActiveSheet()->mergeCells('BD'.$firstJ.':BD'.$jmin1);
					$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row_ats);

				}
				else{
					$pushCount[$i] = 1;
					// echo $ntpnVal[$i][0]['NTPN']." ".$ntpnVal[$i][0]['TANGGAL_SETOR']." <br>";
					$excel->setActiveSheetIndex(0)->setCellValue('BD'.$j, $masa_pajak_full);
					$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row_ats);
					$excel->setActiveSheetIndex(0)->setCellValue('BE'.$j, $ntpnVal[$i][0]['TANGGAL_SETOR']);
					$excel->setActiveSheetIndex(0)->setCellValue('BH'.$j, $ntpnVal[$i][0]['NTPN']);
					$excel->setActiveSheetIndex(0)->setCellValue('BI'.$j, $ntpnVal[$i][0]['BANK']);
					$excel->getActiveSheet()->getStyle('BI'.$j)->applyFromArray($style_row);
					$j++;
				}
				// echo $count;
				// die;
				// echo $ntpnVal[$i]['TANGGAL_SETOR']."<br>";
				/*$tgl_setor     = ($ntpnVal['TANGGAL_SETOR'][$i]) ? date("d/m/Y", strtotime($ntpnVal['TANGGAL_SETOR'][$i])) : '';
				$valueTglSetor = $tgl_setor;*/
			}
			else{
				$pushCount[$i] = 1;
				$excel->setActiveSheetIndex(0)->setCellValue('BD'.$j, $masa_pajak_full);
				$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row_ats);
				$excel->setActiveSheetIndex(0)->setCellValue('BE'.$j, "");
				$excel->getActiveSheet()->getStyle('BE'.$j)->applyFromArray($style_row);
				$excel->setActiveSheetIndex(0)->setCellValue('BH'.$j, "");
				$excel->getActiveSheet()->getStyle('BH'.$j)->applyFromArray($style_row);
				$excel->setActiveSheetIndex(0)->setCellValue('BI'.$j, "");
				$excel->getActiveSheet()->getStyle('BI'.$j)->applyFromArray($style_row);
				$j++;
			}
			// $excel->setActiveSheetIndex(0)->setCellValue('BE'.$j, $valueTglSetor);
		}
		/*echo "<pre>";

		print_r($pushCount);*/

		// die;

		$j+=0;
		$excel->setActiveSheetIndex(0)->setCellValue('BD'.$j, "");
		$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BE'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BF'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BH'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BI'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BK'.$j)->applyFromArray($style_row_ats);	

		$excel->setActiveSheetIndex(0)->setCellValue('BE8', "2");
		$excel->getActiveSheet()->getStyle('BE8')->applyFromArray($style_row1);

		$j = 9;

		for ($i=1; $i <= 12 ; $i++) {

			$count = $pushCount[$i];

			if($count > 1){
				$firstJ = $j;
				for ($k=0; $k < $count; $k++) { 
					if($k==0){
						// echo $j. " 41121 <br>";
						$excel->setActiveSheetIndex(0)->setCellValue('BF'.$j, "411211");
					}
					$j++;
				}
				$jmin1 = $j-1;
				// echo "Merge : BF".$firstJ.":BF".$jmin1." <br>";
				$excel->getActiveSheet()->mergeCells('BF'.$firstJ.':BF'.$jmin1);

			}
			else{
				// echo $j. " 411211 <br>";
				$excel->setActiveSheetIndex(0)->setCellValue('BF'.$j, "411211");
				$j++;
			}
		}
// die;

		$excel->setActiveSheetIndex(0)->setCellValue('BF8', "3");
		$excel->getActiveSheet()->getStyle('BF8')->applyFromArray($style_row1);

		$j = 9;

		for ($i=1; $i <= 12 ; $i++) {

			$count = $pushCount[$i];

			if($count > 1){
				$firstJ = $j;
				for ($k=0; $k < $count; $k++) { 
					if($k==0){
						// echo $j. " 41121 <br>";
						$excel->setActiveSheetIndex(0)->setCellValue('BG'.$j, "100");
						$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row);
					}
						$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BE'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BF'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BH'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row);
						$excel->getActiveSheet()->getStyle('BK'.$j)->applyFromArray($style_row);

					$j++;
				}
				$jmin1 = $j-1;
				// echo "Merge : BG".$firstJ.":BG".$jmin1." <br>";
				$excel->getActiveSheet()->mergeCells('BG'.$firstJ.':BG'.$jmin1);
				$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BE'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BF'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BH'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BK'.$j)->applyFromArray($style_row);

			}
			else{
				// echo $j. " 100 <br>";
				$excel->setActiveSheetIndex(0)->setCellValue('BG'.$j, "100");
				$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BE'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BF'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BH'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BK'.$j)->applyFromArray($style_row);
				$j++;
			}
		}
// die;

		$excel->setActiveSheetIndex(0)->setCellValue('BG8', "4");
		$excel->getActiveSheet()->getStyle('BG8')->applyFromArray($style_row1);

		$excel->setActiveSheetIndex(0)->setCellValue('BH8', "5");
		/*$j = 9;
		for ($i=1; $i <= 12 ; $i++) {
			$valueNtpn = 0;
			if(in_array($i, $ntpnVal['BULAN'])){
				$valueNtpn = $ntpnVal['NTPN'][$i];
			}
			$excel->setActiveSheetIndex(0)->setCellValue('BH'.$j, $valueNtpn);
			$j++;
		}*/
		// $excel->setActiveSheetIndex(0)->setCellValue('BH22', $ntpnTotal);

		$excel->getActiveSheet()->getStyle('BH8')->applyFromArray($style_row1);

		/*$excel->getActiveSheet()->getStyle('BH9:BH22')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');*/

		$excel->setActiveSheetIndex(0)->setCellValue('BI8', "6");
		/*$j = 9;
		for ($i=1; $i <= 12 ; $i++) {
			$valueBank = '-';
			if(in_array($i, $ntpnVal['BULAN'])){
				$valueBank = $ntpnVal['BANK'][$i];
			}
			$excel->setActiveSheetIndex(0)->setCellValue('BI'.$j, $valueBank);
			$j++;
		}*/

		$excel->getActiveSheet()->getStyle('BI8')->applyFromArray($style_row1);

		$excel->setActiveSheetIndex(0)->setCellValue('BK8', "8");
		/*$j = 9;
		for ($i=1; $i <= 12 ; $i++) {
			$valueTglLapor = '-';
			if(in_array($i, $ntpnVal['BULAN'])){
				$tgl_lapor     = ($ntpnVal['TANGGAL_LAPOR'][$i]) ? date("d/m/Y", strtotime($ntpnVal['TANGGAL_LAPOR'][$i])) : '';
				$valueTglLapor = $tgl_lapor;
			}
			$excel->setActiveSheetIndex(0)->setCellValue('BK'.$j, $valueTglLapor);
			$j++;
		}*/
		
		$excel->getActiveSheet()->getStyle('BK8')->applyFromArray($style_row1);

		//get detail			
			/* $queryExec	= "select skc.KODE_CABANG
								--, skc.NAMA_CABANG
								, case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								  else 'Cabang ' || skc.nama_cabang
								  end nama_cabang								
								, januari, februari, maret, april, mei, juni, juli, agustus, september, oktober, november, desember from simtax_kode_cabang skc
								,(select rownum, ppn.* from (select kode_cabang, nama_cabang, spt, bulan_pajak from simtax_report_spt_tahunan_v
															where tahun_pajak = '".$tahun."'
															and pembetulan_ke = 0)
															pivot (
															max(spt)
															for bulan_pajak in (1 as Januari,2 as Februari,3 as Maret,4 April,5 Mei,6 Juni,7 Juli,8 Agustus,9 September,10 Oktober,11 November,12 Desember)
															) ppn
															) rpt
								where skc.kode_cabang = rpt.kode_cabang (+)                            
								  and skc.kode_cabang in ('000','010','020','030','040','050',
															'060','070','080','090','100','110','120')
								order by skc.kode_cabang"; */

			if ($cabang == 'all'){
				$whereCabang = "'000','010','020','030','040','050',
															'060','070','080','090','100','110','120')
								 union all
                                                     select '991' kode_cabang, 'Satker Priok' nama_cabang from dual
                                                     union all
                                                     select '992' kode_cabang, 'Satker Sorong' nama_cabang from dual
                                                     union all
                                                     select '993' kode_cabang, 'Satker PPL' nama_cabang from dual
                                                     union all
                                                     select '994' kode_cabang, 'Kompensasi' nama_cabang from dual
                                                     union all
                                                     select '995' kode_cabang, 'Pemindahbukuan' nama_cabang from dual
                                                     union all
                                                     select '996' kode_cabang, 'PMK Tahunan' nama_cabang from dual
                                                    order by 1";
				$whereCabangDetail = "";
			} else{
				$whereCabang = "'".$kd_cabang."')";
				$whereCabangDetail = "'".$kd_cabang."'";
			}
			
			$queryExec	= "select skc.KODE_CABANG								
								, case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								  else 'Cabang ' || skc.nama_cabang
								  end nama_cabang	
								from simtax_kode_cabang skc
								where skc.kode_cabang in (".$whereCabang."
								 ";
								
			$query 		= $this->db->query($queryExec);
			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 10; // Set baris pertama untuk isi tabel adalah baris ke 4	
			$startFormatNumber = 10;
			$startFormatNumber1 = 9;
			$sum_nil_setor =0;		
			
			for($i=1;$i<=12;$i++){
				$ttl_ppn_keluaran[$i]=0; $ttl_ppn_masukan[$i]=0; $ttl_pmk78[$i]=0; $ttl_kurang_lebih[$i]=0;

			}


			/*$joinCondition  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splm.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splm.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splm.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splm.ORGANIZATION_ID";*/

			foreach($query->result_array() as $row)	{
								
				$ppn_keluaran[]=0; $ppn_masukan[]=0; $pmk78[]=0; $kurang_lebih[]=0;	

				$ttl_ppn_keluaran_cbg=0;
				$ttl_ppn_masukan_cbg=0;
				$ttl_pmk78_cbg=0;
				$ttl_kurang_lebih_cbg=0;

				/*if($whereCabangDetail == ""){
					$whereCabang = $rowCabang;
				}		*/	
								
				for($i=1;$i<=12;$i++){
					
				$kompensasi  = 0;
				$pbk_tahunan = 0;
				$pmk_tahunan = 0;
					//awal ambil nilai percabang================================================================================
					
					$queryExec	= " select skc.kode_cabang								 
								 , case skc.nama_cabang
									when 'Kantor Pusat' then skc.nama_cabang
								   else 'Cabang ' || skc.nama_cabang
								   end nama_cabang								 
								 , ppn_header.bulan_pajak
								 , ppn_header.tahun_pajak
								 , nvl(ppn_keluaran.jumlah_potong,0) PPN_KELUARAN
								 , nvl(ppn_masukan.jumlah_potong,0) PPN_MASUKAN
								 , nvl(ppn_keluaran.jumlah_potong,0) - (nvl(ppn_masukan.jumlah_potong,0) - ABS (NVL (pmk_78.pmk_78, 0))) KURANG_LEBIH
								 , pmk_78.pmk_78 pmk_78
							  from simtax_kode_cabang skc
							, (select 
								   skc.NAMA_CABANG
								 , sphh.KODE_CABANG
								 , sphh.TAHUN_PAJAK
								 , sphh.BULAN_PAJAK
								 , sphh.MASA_PAJAK
							  from simtax_pajak_headers sphh
								 , simtax_pajak_lines splh
								 , simtax_kode_cabang skc
							 where sphh.nama_pajak in ('PPN KELUARAN','PPN MASUKAN')
							   and sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
							   and skc.KODE_CABANG = sphh.KODE_CABANG
							   and nvl(splh.IS_CHEKLIST,0) = 1
							   and skc.KODE_CABANG = sphh.KODE_CABANG
							   and sphh.tahun_pajak = '".$tahun."'
							   and sphh.bulan_pajak = '".$i."'
							  and sphh.kode_cabang	 = '".$row['KODE_CABANG']."'
							  and sphh.pembetulan_ke = '".$pembetulanKe."'
							group by skc.NAMA_CABANG, sphh.KODE_CABANG, sphh.TAHUN_PAJAK, sphh.BULAN_PAJAK, sphh.MASA_PAJAK) ppn_header
							,(select skc.NAMA_CABANG
								 , sphm.KODE_CABANG
								 , sphm.TAHUN_PAJAK
								 , sphm.BULAN_PAJAK
								 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
							  from simtax_pajak_headers sphm
								 , simtax_pajak_lines splm
								 , simtax_kode_cabang skc
							 where sphm.nama_pajak = 'PPN KELUARAN'
								and sphm.PAJAK_HEADER_ID    = splm.PAJAK_HEADER_ID
								and skc.KODE_CABANG         = sphm.KODE_CABANG
								and nvl(splm.IS_CHEKLIST,0) = 1
								and skc.KODE_CABANG         = sphm.KODE_CABANG
								and sphm.tahun_pajak        = '".$tahun."'
								and sphm.bulan_pajak        = '".$i."'  
								and sphm.kode_cabang        = '".$row['KODE_CABANG']."'
								and sphm.pembetulan_ke      = '".$pembetulanKe."'
                                and splm.kd_jenis_transaksi IN (1,4,6,9)
							group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_keluaran
							,(select skc.NAMA_CABANG
								 , sphm.KODE_CABANG
								 , sphm.TAHUN_PAJAK
								 , sphm.BULAN_PAJAK
								 , sphm.MASA_PAJAK
								, sum(nvl(splm.JUMLAH_POTONG*-1,0)) JUMLAH_POTONG
								 , min(abs(nvl(sphm.PMK78,0))) PMK78
							  from simtax_pajak_headers sphm
								 , simtax_pajak_lines splm
								 , simtax_kode_cabang skc
							 where sphm.nama_pajak = 'PPN MASUKAN'
							   and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
							   and skc.KODE_CABANG = sphm.KODE_CABANG
							   and nvl(splm.IS_CHEKLIST,0) = 1
							   and skc.KODE_CABANG = sphm.KODE_CABANG
							   and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$i."'         
							  and sphm.kode_cabang  = '".$row['KODE_CABANG']."'
							  and sphm.pembetulan_ke = '".$pembetulanKe."'
							   	AND ((splm.kd_jenis_transaksi IN (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') OR (splm.kd_jenis_transaksi IN (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') AND SPLM.IS_CREDITABLE = '1'))
							group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_masukan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
							   and sphm.bulan_pajak = '".$i."'         
							   and sphm.kode_cabang  = '".$row['KODE_CABANG']."'
							   and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and splm.dl_fs = 'dokumen_lain' 
                               and splm.kd_jenis_transaksi IN ('11','12') 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_impor,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                                 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$i."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang  = '".$row['KODE_CABANG']."'
                               and is_pmk = '1'
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) pmk,
                            (  SELECT skc.NAMA_CABANG,
			                   sphm.KODE_CABANG,
			                   sphm.TAHUN_PAJAK,
			                   sphm.BULAN_PAJAK,
			                   sphm.MASA_PAJAK,
			                   ceil(abs(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100)
			                   - SUM (NVL (splm.JUMLAH_POTONG * -1, 0))))
			                      PMK_78
			              	FROM simtax_pajak_headers sphm,
			                   simtax_pajak_lines splm,
			                   simtax_kode_cabang skc
			             	WHERE     sphm.nama_pajak = 'PPN MASUKAN'
			                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
			                   AND skc.KODE_CABANG = sphm.KODE_CABANG
			                   AND NVL (splm.IS_CHEKLIST, 0) = 1
			                   AND skc.KODE_CABANG = sphm.KODE_CABANG
			                   and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$i."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang  = '".$row['KODE_CABANG']."'
			                   and splm.is_pmk = 1
			          		GROUP BY skc.NAMA_CABANG,
			                   sphm.KODE_CABANG,
			                   sphm.TAHUN_PAJAK,
			                   sphm.BULAN_PAJAK,
			                   sphm.MASA_PAJAK) pmk_78
							where 1=1
							and skc.KODE_CABANG = ppn_header.kode_cabang (+)
							and ppn_header.nama_cabang = ppn_keluaran.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_keluaran.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_keluaran.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_keluaran.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_keluaran.masa_pajak (+)
							and ppn_header.nama_cabang = ppn_masukan.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_masukan.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_masukan.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_masukan.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_masukan.masa_pajak (+)
							AND ppn_header.nama_cabang = pmk_78.nama_cabang(+)
				         	AND ppn_header.kode_cabang = pmk_78.kode_cabang(+)
				         	AND ppn_header.tahun_pajak = pmk_78.tahun_pajak(+)
				         	AND ppn_header.bulan_pajak = pmk_78.bulan_pajak(+)
				         	AND ppn_header.masa_pajak = pmk_78.masa_pajak(+)
							and ppn_header.nama_cabang = ppn_impor.nama_cabang (+)
							and ppn_header.kode_cabang = ppn_impor.kode_cabang (+)
							and ppn_header.tahun_pajak = ppn_impor.tahun_pajak (+)
							and ppn_header.bulan_pajak = ppn_impor.bulan_pajak (+)
							and ppn_header.masa_pajak  = ppn_impor.masa_pajak (+)
							and ppn_header.nama_cabang = pmk.nama_cabang (+)
				         	and ppn_header.kode_cabang = pmk.kode_cabang (+)
				        	and ppn_header.tahun_pajak = pmk.tahun_pajak (+)
				         	and ppn_header.bulan_pajak = pmk.bulan_pajak (+)
				         	and ppn_header.masa_pajak  = pmk.masa_pajak (+)
							and skc.kode_cabang  = '".$row['KODE_CABANG']."'
							order by skc.kode_cabang
							";

							//print_r($queryExec)	; die();			
								
						$queryb		= $this->db->query($queryExec);
						$rowb		= $queryb->row();

						$query_kompen = "SELECT CASE WHEN KOMPENSASI_BLN_LALU <0 THEN KOMPENSASI_BLN_LALU
											ELSE 0 END KOMPENSASI, PMK, PBK from SIMTAX_PMK_PPNMASA
											WHERE BULAN_PAJAK = '".$i."' AND TAHUN_PAJAK = '".$tahun."'
											AND PEMBETULAN_KE = '".$pembetulanKe."'";
						
						$queryc = $this->db->query($query_kompen);
						$totc   = $queryc->num_rows();
						$rowc   = $queryc->row();

						if($totc > 0){
							$kompensasi  = $rowc->KOMPENSASI;
							$pbk_tahunan = $rowc->PBK;
							$pmk_tahunan = $rowc->PMK;
						}


						if($row['NAMA_CABANG']== 'Satker Priok' || $row['NAMA_CABANG'] == 'Satker Sorong' || $row['NAMA_CABANG'] == 'Satker PPL')
						{
							$ppn_keluaran[$i]	= 0; 
							$ppn_masukan[$i]	= 0; 						
							$pmk78[$i]			= 0; 
							$kurang_lebih[$i]	= 0;
							
							/*//total
							$ttl_ppn_keluaran[$i]	+= 0; 
							$ttl_ppn_masukan[$i]	+= 0; 
							$ttl_pmk78[$i]			+= 0; 
							$ttl_kurang_lebih[$i]	+= 0;
							$sum_nil_setor			+= 0;*/

						}
						elseif($row['NAMA_CABANG'] == 'Kompensasi'){
							$ppn_keluaran[$i]	= 0;
							$ppn_masukan[$i]    = $kompensasi;
							$pmk78[$i]			= 0;
							$kurang_lebih[$i]	= -$kompensasi;
						}
						elseif($row['NAMA_CABANG'] == 'Pemindahbukuan'){
							$ppn_keluaran[$i]	= 0; 
							$ppn_masukan[$i]    = $pbk_tahunan;
							$pmk78[$i]			= 0;
							$kurang_lebih[$i]	= -$pbk_tahunan;
						}
						elseif($row['NAMA_CABANG'] == 'PMK Tahunan'){
							$ppn_keluaran[$i]	= 0; 
							$ppn_masukan[$i]    = $pmk_tahunan;
							$pmk78[$i]			= 0;
							$kurang_lebih[$i]	= -$pmk_tahunan;	
						}
						else
						{

							$ppn_keluaran[$i]	= $rowb->PPN_KELUARAN; 
							$ppn_masukan[$i]	= $rowb->PPN_MASUKAN; 						
							$pmk78[$i]			= $rowb->PMK_78; 
							$kurang_lebih[$i]	= $rowb->KURANG_LEBIH;
							
							
						}
							//total
								$ttl_ppn_keluaran[$i]	+= $ppn_keluaran[$i]; 
								$ttl_ppn_masukan[$i]	+= $ppn_masukan[$i]; 
								$ttl_pmk78[$i]			+= $pmk78[$i]; 
								$ttl_kurang_lebih[$i]	+= $kurang_lebih[$i];
								$sum_nil_setor			+= $kurang_lebih[$i];

								$ttl_ppn_keluaran_cbg		+= $ppn_keluaran[$i]; 
								$ttl_ppn_masukan_cbg		+= $ppn_masukan[$i]; 
								$ttl_pmk78_cbg				+= $pmk78[$i]; 
								$ttl_kurang_lebih_cbg		+= $kurang_lebih[$i];				
					//akhir ambil nilai percabang===============================================================================
					
				}
				
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['NAMA_CABANG']);	
				
				//JANUARI
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $ppn_keluaran[1]);	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ppn_masukan[1]);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $pmk78[1]);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $kurang_lebih[1]);	
				
				//FRBRUARI
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ppn_keluaran[2]);	
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $ppn_masukan[2]);	
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $pmk78[2]);	
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $kurang_lebih[2]);
				
				//MARET
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ppn_keluaran[3]);	
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $ppn_masukan[3]);	
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $pmk78[3]);	
				$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $kurang_lebih[3]);
				
				//APRIL
				$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $ppn_keluaran[4]);	
				$excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $ppn_masukan[4]);	
				$excel->setActiveSheetIndex(0)->setCellValue('Q'.$numrow, $pmk78[4]);	
				$excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, $kurang_lebih[4]);	
				
				 //MEI			
				$excel->setActiveSheetIndex(0)->setCellValue('S'.$numrow, $ppn_keluaran[5]);	
				$excel->setActiveSheetIndex(0)->setCellValue('T'.$numrow, $ppn_masukan[5]);	
				$excel->setActiveSheetIndex(0)->setCellValue('U'.$numrow, $pmk78[5]);	
				$excel->setActiveSheetIndex(0)->setCellValue('V'.$numrow, $kurang_lebih[5]);	
				
				//JUNI
				$excel->setActiveSheetIndex(0)->setCellValue('W'.$numrow, $ppn_keluaran[6]);	
				$excel->setActiveSheetIndex(0)->setCellValue('X'.$numrow, $ppn_masukan[6]);	
				$excel->setActiveSheetIndex(0)->setCellValue('Y'.$numrow, $pmk78[6]);	
				$excel->setActiveSheetIndex(0)->setCellValue('Z'.$numrow, $kurang_lebih[6]);	
				
				//JULI
				$excel->setActiveSheetIndex(0)->setCellValue('AA'.$numrow, $ppn_keluaran[7]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AB'.$numrow, $ppn_masukan[7]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AC'.$numrow, $pmk78[7]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AD'.$numrow, $kurang_lebih[7]);	
				
				//AGUSTUS
				$excel->setActiveSheetIndex(0)->setCellValue('AE'.$numrow, $ppn_keluaran[8]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AF'.$numrow, $ppn_masukan[8]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AG'.$numrow, $pmk78[8]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AH'.$numrow, $kurang_lebih[8]);	
				
				//SEPTEMBER
				$excel->setActiveSheetIndex(0)->setCellValue('AI'.$numrow, $ppn_keluaran[9]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AJ'.$numrow, $ppn_masukan[9]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AK'.$numrow, $pmk78[9]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AL'.$numrow, $kurang_lebih[9]);

				//OKTOBER
				$excel->setActiveSheetIndex(0)->setCellValue('AM'.$numrow, $ppn_keluaran[10]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AN'.$numrow, $ppn_masukan[10]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AO'.$numrow, $pmk78[10]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AV'.$numrow, $kurang_lebih[10]);	

				//NOVEMBER
				$excel->setActiveSheetIndex(0)->setCellValue('AQ'.$numrow, $ppn_keluaran[11]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AR'.$numrow, $ppn_masukan[11]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AS'.$numrow, $pmk78[11]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AT'.$numrow, $kurang_lebih[11]); 

				//DESEMBER
				$excel->setActiveSheetIndex(0)->setCellValue('AU'.$numrow, $ppn_keluaran[12]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AV'.$numrow, $ppn_masukan[12]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AW'.$numrow, $pmk78[12]);	
				$excel->setActiveSheetIndex(0)->setCellValue('AX'.$numrow, $kurang_lebih[12]);

				//TOTAL
				$excel->setActiveSheetIndex(0)->setCellValue('AY'.$numrow, $ttl_ppn_keluaran_cbg);	
				$excel->setActiveSheetIndex(0)->setCellValue('AZ'.$numrow, $ttl_ppn_masukan_cbg);	
				$excel->setActiveSheetIndex(0)->setCellValue('BA'.$numrow, $ttl_pmk78_cbg);	
				$excel->setActiveSheetIndex(0)->setCellValue('BB'.$numrow, $ttl_kurang_lebih_cbg);

				
				/* $excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, 'MARET');	
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, 'APRIL');
				$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, 'MEI');
				$excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, 'JUNI');
				$excel->setActiveSheetIndex(0)->setCellValue('U'.$numrow, 'JULI');
				$excel->setActiveSheetIndex(0)->setCellValue('X'.$numrow, 'AGUSTUS');
				$excel->setActiveSheetIndex(0)->setCellValue('AA'.$numrow, 'SEPTEMBER');
				$excel->setActiveSheetIndex(0)->setCellValue('AC'.$numrow, 'OKTOBER');
				$excel->setActiveSheetIndex(0)->setCellValue('AF'.$numrow, 'NOVEMBER');
				$excel->setActiveSheetIndex(0)->setCellValue('AI'.$numrow, 'DESEMBER'); */
								
				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Q'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('R'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('S'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('T'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('U'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('V'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('W'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('X'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Y'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Z'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('AA'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AB'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AC'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AD'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AE'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('AF'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AG'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AH'.$numrow)->applyFromArray($style_row);				
				$excel->getActiveSheet()->getStyle('AI'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AJ'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AK'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AL'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AM'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AN'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AO'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AP'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AQ'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AR'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AS'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AT'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AU'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AV'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AW'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AX'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AY'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('AZ'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BA'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('BB'.$numrow)->applyFromArray($style_row);			
				
				$no++;
				$numrow++;				
			}		
		//end get detail
		
		
		//total		
		$ttl_des = 0;
		$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, "KURANG / (LEBIH) BAYAR");
		$excel->getActiveSheet()->mergeCells('A'.$numrow.':B'.$numrow);		
		//JANUARI
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $ttl_ppn_keluaran[1]);	
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ttl_ppn_masukan[1]);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ttl_pmk78[1]);	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $ttl_kurang_lebih[1]);
		//FEBRUARI
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ttl_ppn_keluaran[2]);		
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $ttl_ppn_masukan[2]);
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $ttl_pmk78[2]);
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $ttl_kurang_lebih[2]);
		//MARET
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ttl_ppn_keluaran[3]);
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $ttl_ppn_masukan[3]);
		$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $ttl_pmk78[3]);
		$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $ttl_kurang_lebih[3]);
		//APRIL
		$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $ttl_ppn_keluaran[4]);
		$excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $ttl_ppn_masukan[4]);
		$excel->setActiveSheetIndex(0)->setCellValue('Q'.$numrow, $ttl_pmk78[4]);
		$excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, $ttl_kurang_lebih[4]);
		
		$excel->setActiveSheetIndex(0)->setCellValue('S'.$numrow, $ttl_ppn_keluaran[5]);
		$excel->setActiveSheetIndex(0)->setCellValue('T'.$numrow, $ttl_ppn_masukan[5]);
		$excel->setActiveSheetIndex(0)->setCellValue('U'.$numrow, $ttl_pmk78[5]);
		$excel->setActiveSheetIndex(0)->setCellValue('V'.$numrow, $ttl_kurang_lebih[5]);

		$excel->setActiveSheetIndex(0)->setCellValue('W'.$numrow, $ttl_ppn_keluaran[6]);
		$excel->setActiveSheetIndex(0)->setCellValue('X'.$numrow, $ttl_ppn_masukan[6]);
		$excel->setActiveSheetIndex(0)->setCellValue('Y'.$numrow, $ttl_pmk78[6]);
		$excel->setActiveSheetIndex(0)->setCellValue('Z'.$numrow, $ttl_kurang_lebih[6]);

		$excel->setActiveSheetIndex(0)->setCellValue('AA'.$numrow, $ttl_ppn_keluaran[7]);
		$excel->setActiveSheetIndex(0)->setCellValue('AB'.$numrow, $ttl_ppn_masukan[7]);
		$excel->setActiveSheetIndex(0)->setCellValue('AC'.$numrow, $ttl_pmk78[7]);
		$excel->setActiveSheetIndex(0)->setCellValue('AD'.$numrow, $ttl_kurang_lebih[7]);

		$excel->setActiveSheetIndex(0)->setCellValue('AE'.$numrow, $ttl_ppn_keluaran[8]);
		$excel->setActiveSheetIndex(0)->setCellValue('AF'.$numrow, $ttl_ppn_masukan[8]);
		$excel->setActiveSheetIndex(0)->setCellValue('AG'.$numrow, $ttl_pmk78[8]);
		$excel->setActiveSheetIndex(0)->setCellValue('AH'.$numrow, $ttl_kurang_lebih[8]);

		$excel->setActiveSheetIndex(0)->setCellValue('AI'.$numrow, $ttl_ppn_keluaran[9]);
		$excel->setActiveSheetIndex(0)->setCellValue('AJ'.$numrow, $ttl_ppn_masukan[9]);
		$excel->setActiveSheetIndex(0)->setCellValue('AK'.$numrow, $ttl_pmk78[9]);
		$excel->setActiveSheetIndex(0)->setCellValue('AL'.$numrow, $ttl_kurang_lebih[9]);

		$excel->setActiveSheetIndex(0)->setCellValue('AM'.$numrow, $ttl_ppn_keluaran[10]);
		$excel->setActiveSheetIndex(0)->setCellValue('AN'.$numrow, $ttl_ppn_masukan[10]);
		$excel->setActiveSheetIndex(0)->setCellValue('AO'.$numrow, $ttl_pmk78[10]);
		$excel->setActiveSheetIndex(0)->setCellValue('AP'.$numrow, $ttl_kurang_lebih[10]);

		$excel->setActiveSheetIndex(0)->setCellValue('AQ'.$numrow, $ttl_ppn_keluaran[11]);
		$excel->setActiveSheetIndex(0)->setCellValue('AR'.$numrow, $ttl_ppn_masukan[11]);
		$excel->setActiveSheetIndex(0)->setCellValue('AS'.$numrow, $ttl_pmk78[11]);
		$excel->setActiveSheetIndex(0)->setCellValue('AT'.$numrow, $ttl_kurang_lebih[11]);

		$excel->setActiveSheetIndex(0)->setCellValue('AU'.$numrow, $ttl_ppn_keluaran[12]);
		$excel->setActiveSheetIndex(0)->setCellValue('AV'.$numrow, $ttl_ppn_masukan[12]);
		$excel->setActiveSheetIndex(0)->setCellValue('AW'.$numrow, $ttl_pmk78[12]);
		$excel->setActiveSheetIndex(0)->setCellValue('AX'.$numrow, $ttl_kurang_lebih[12]);

		$excel->setActiveSheetIndex(0)->setCellValue('AY27', '=SUM(AY10:AY26)');
		$excel->setActiveSheetIndex(0)->setCellValue('AZ27', '=SUM(AZ10:AZ26)');
		$excel->setActiveSheetIndex(0)->setCellValue('BA27', '=SUM(BA10:BA26)');
		$excel->setActiveSheetIndex(0)->setCellValue('BB27', '=SUM(BB10:BB26)');

		/*$excel->setActiveSheetIndex(0)->setCellValue('F26','=C26-D26-E26');
		$excel->setActiveSheetIndex(0)->setCellValue('F27','=C27-D27-E27');
		$excel->setActiveSheetIndex(0)->setCellValue('F28','=C28-D28-E28');

		$excel->setActiveSheetIndex(0)->setCellValue('J26','=G26-H26-I26');
		$excel->setActiveSheetIndex(0)->setCellValue('J27','=G27-H27-I27');
		$excel->setActiveSheetIndex(0)->setCellValue('J28','=G28-H28-I28');

		$excel->setActiveSheetIndex(0)->setCellValue('N26','=K26-L26-M26');
		$excel->setActiveSheetIndex(0)->setCellValue('N27','=K27-L27-M27');
		$excel->setActiveSheetIndex(0)->setCellValue('N28','=K28-L28-M28');

		$excel->setActiveSheetIndex(0)->setCellValue('R26','=O26-P26-Q26');
		$excel->setActiveSheetIndex(0)->setCellValue('R27','=O27-P27-Q27');
		$excel->setActiveSheetIndex(0)->setCellValue('R28','=O28-P28-Q28');

		$excel->setActiveSheetIndex(0)->setCellValue('V26','=S26-T26-U26');
		$excel->setActiveSheetIndex(0)->setCellValue('V27','=S27-T27-U27');
		$excel->setActiveSheetIndex(0)->setCellValue('V28','=S28-T28-U28');

		$excel->setActiveSheetIndex(0)->setCellValue('Z26','=W26-X26-Y26');
		$excel->setActiveSheetIndex(0)->setCellValue('Z27','=W27-X27-Y27');
		$excel->setActiveSheetIndex(0)->setCellValue('Z28','=W28-X28-Y28');

		$excel->setActiveSheetIndex(0)->setCellValue('AD26','=AA26-AB26-AC26');
		$excel->setActiveSheetIndex(0)->setCellValue('AD27','=AA27-AB27-AC27');
		$excel->setActiveSheetIndex(0)->setCellValue('AD28','=AA28-AB28-AC28');

		$excel->setActiveSheetIndex(0)->setCellValue('AH26','=AE26-AF26-AG26');
		$excel->setActiveSheetIndex(0)->setCellValue('AH27','=AE27-AF27-AG27');
		$excel->setActiveSheetIndex(0)->setCellValue('AH28','=AE28-AF28-AG28');

		$excel->setActiveSheetIndex(0)->setCellValue('AL26','=AI26-AJ26-AK26');
		$excel->setActiveSheetIndex(0)->setCellValue('AL27','=AI27-AJ27-AK27');
		$excel->setActiveSheetIndex(0)->setCellValue('AL28','=AI28-AJ28-AK28');

		$excel->setActiveSheetIndex(0)->setCellValue('AP26','=AM26-AN26-AO26');
		$excel->setActiveSheetIndex(0)->setCellValue('AP27','=AM27-AN27-AO27');
		$excel->setActiveSheetIndex(0)->setCellValue('AP28','=AM28-AN28-AO28');

		$excel->setActiveSheetIndex(0)->setCellValue('AT26','=AQ26-AR26-AS26');
		$excel->setActiveSheetIndex(0)->setCellValue('AT27','=AQ27-AR27-AS27');
		$excel->setActiveSheetIndex(0)->setCellValue('AT28','=AQ28-AR28-AS28');

		$excel->setActiveSheetIndex(0)->setCellValue('AX26','=AU26-AV26-AW26');
		$excel->setActiveSheetIndex(0)->setCellValue('AX27','=AU27-AV27-AW27');
		$excel->setActiveSheetIndex(0)->setCellValue('AX28','=AU28-AV28-AW28');
				*/
		$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_hsl);				
		$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('Q'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('R'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('S'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('T'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('U'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('V'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('W'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('X'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('Y'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('Z'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AA'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AB'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AC'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AD'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AE'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AF'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AG'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AH'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AI'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AJ'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AK'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AL'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AM'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AN'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AO'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AP'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AQ'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AR'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AS'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AT'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AU'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AV'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AW'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AX'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AY'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('AZ'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('BA'.$numrow)->applyFromArray($style_row_hsl);
		$excel->getActiveSheet()->getStyle('BB'.$numrow)->applyFromArray($style_row_hsl);
				
		$endFormatNumber = $numrow;
		$excel->getActiveSheet()->getStyle('C'.$startFormatNumber.':BB'.$endFormatNumber)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('BJ8', '7');
		$excel->getActiveSheet()->getStyle('BJ8')->applyFromArray($style_row1);

		$j=9;

		for ($i=1; $i <= 12 ; $i++) {

			$count = $pushCount[$i];

			if($count > 1){
				$firstJ = $j;
				for ($k=0; $k < $count; $k++) { 
					// echo $j. " ".$ttl_kurang_lebih[$i]." <br>";
					if($k==0){
						$excel->setActiveSheetIndex(0)->setCellValue('BJ'.$j, $ttl_kurang_lebih[$i]);
					}
					$j++;
				}
				$jmin1 = $j-1;
				$excel->getActiveSheet()->mergeCells('BJ'.$firstJ.':BJ'.$jmin1);

			}
			else{
				// echo $j. " ".$ttl_kurang_lebih[$i]." <br>";
				$excel->setActiveSheetIndex(0)->setCellValue('BJ'.$j, $ttl_kurang_lebih[$i]);
				$j++;
			}
		}
		// die;

		$j+=1;
		$excel->setActiveSheetIndex(0)->setCellValue('BD'.$j, "Total");
		$excel->setActiveSheetIndex(0)->setCellValue('BJ'.$j, $sum_nil_setor);
		$excel->getActiveSheet()->getStyle('BD'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BE'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BF'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BG'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BH'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BI'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BJ'.$j)->applyFromArray($style_row_ats);
		$excel->getActiveSheet()->getStyle('BK'.$j)->applyFromArray($style_row_ats);

		$excel->getActiveSheet()->getStyle('BJ'.$startFormatNumber1.':BJ'.$endFormatNumber)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		//
		/*$excel->setActiveSheetIndex(0)->setCellValue('AY10', '=SUM(C10+G10+K10+O10+S10+W10+AA10+AE10+AI10+AM10+AQ10+AU10)');
		$excel->getActiveSheet()->getStyle('AY10'.$startFormatNumber1.':AY10'.$endFormatNumber)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		//
		$excel->setActiveSheetIndex(0)->setCellValue('AZ10', '=SUM(D10+G10+L10+P10+T10+X10+AB10+AF10+AJ10+AN10+AR10+AV10)');
		$excel->getActiveSheet()->getStyle('AZ10'.$startFormatNumber1.':AZ10'.$endFormatNumber)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		//
		$excel->setActiveSheetIndex(0)->setCellValue('BA10', '=SUM(E10+H10+M10+Q10+U10+Y10+AC10+AG10+AK10+AO10+AS10+AW10)');
		$excel->getActiveSheet()->getStyle('BA10'.$startFormatNumber1.':BA10'.$endFormatNumber)->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');*/
		
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(23); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('P')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('R')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('S')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('T')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('U')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('V')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('W')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('X')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('Y')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('Z')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AA')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AB')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AC')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AD')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AE')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AF')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AG')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AH')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AI')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AJ')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AK')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AL')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AM')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AN')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AO')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AP')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AQ')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AR')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AS')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AT')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AU')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AV')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AW')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AX')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AY')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('AZ')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BA')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BB')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BC')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BD')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BE')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BF')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BG')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BH')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BI')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BJ')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('BK')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekapt SPT Setahun");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap SPT Tahunan PPN Masa '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function cetak_rekap_ppn_masa_tahunan()
	{
		if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0)
		{
		    @set_time_limit(300);
		}
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak SPT Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak SPT Setahun")
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_1 = array(
			'font' 	   => array('bold' => true),
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_kolom = array(
			'font' 	   => array('bold' => true),
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_2 = array(
			'font' 	   => array('italic' => true)
		);

		$style_row_atas = array(
			'font' 	   => array('size' => 6),
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_no = array(
			'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,// Set text jadi di tengah secara vertical (middle)
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
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B1')->applyFromArray($style_row_2);

		$excel->setActiveSheetIndex(0)->setCellValue('B3', "REKAPITULASI SETORAN PAJAK PERTAMBAHAN NILAI TAHUN 2017"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B3')->applyFromArray($style_row_1);
		
		// Buat header tabel nya pada baris ke 3
		
		$excel->setActiveSheetIndex(0)->setCellValue('B5', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C5', "Bulan"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D5', "PPN Keluaran"); // Set kolom C3 dengan tulisan "NAMA"
		$excel->setActiveSheetIndex(0)->setCellValue('E5', "PPN Masukan"); // Set kolom D3 dengan tulisan "JENIS KELAMIN"
		$excel->setActiveSheetIndex(0)->setCellValue('F5', "PMK 78"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('G5', "Kurang / Lebih"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Tanggal Setor"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('I5', "Kode Map"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('J5', "Kode jenis Setor"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('K5', "NTPN"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('L5', "Nilai Setoran"); // Set kolom E3 dengan tulisan "ALAMAT"
		$excel->setActiveSheetIndex(0)->setCellValue('M5', "Tanggal Lapor SPT"); // Set kolom E3 dengan tulisan "ALAMAT"

		$loop = horizontal_loop_excel("B", 12);

		foreach ($loop as $key => $value) {

			$excel->getActiveSheet()->mergeCells($value.'5:'.$value.'6');
			$excel->getActiveSheet()->getStyle($value.'5:'.$value.'6')->applyFromArray($style_row_kolom);
			$excel->getActiveSheet()->getStyle($value.'5:'.$value.'6')->applyFromArray($style_col2);
		}

		$tahun 				= $_REQUEST['tahun'];
		$pembetulanKe 		= $_REQUEST['pembetulanKe'];
		$cabang 			= $_REQUEST['kd_cabang'];

		if ($cabang !='all'){
			$whereCabang = "'".$cabang."'";
		} else{
			$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
		}

		$no = 1; // Untuk penomoran tabel, di awal set dengan 1
		$numrow = 8; // Set baris pertama untuk isi tabel adalah baris ke 4	
		$startFormatNumber = 10;
		$startFormatNumber1 = 9;
		$sum_nil_setor =0;
		$j = 8;

		$kode_map         = "411211";
		$kode_jenis_setor = "100";

		$queryExec = "SELECT ppn_header.bulan_pajak,
					         ppn_header.tahun_pajak,
					         nvl(ppn_keluaran.jumlah_potong, 0) PPN_KELUARAN,
					         nvl(ppn_masukan.jumlah_potong, 0) PPN_MASUKAN,
					         nvl(pmk78.jumlah_potong, 0) PMK78,
					         nvl(ppn_keluaran.jumlah_potong,0) - (nvl(ppn_masukan.jumlah_potong,0) - nvl(pmk78.jumlah_potong,0)) KURANG_LEBIH
					    FROM simtax_kode_cabang skc,
					         (  SELECT 
					                   sphh.TAHUN_PAJAK,
					                   sphh.BULAN_PAJAK,
					                   sphh.MASA_PAJAK
					              FROM simtax_pajak_headers sphh,
					                   simtax_pajak_lines splh
					             WHERE     sphh.nama_pajak IN ('PPN KELUARAN', 'PPN MASUKAN')
					                   AND sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
					                   AND splh.IS_CHEKLIST = 1
					                   AND sphh.tahun_pajak = '".$tahun."'
					                   AND sphh.pembetulan_ke = '".$pembetulanKe."'
					          GROUP BY 
					                   sphh.TAHUN_PAJAK,
					                   sphh.BULAN_PAJAK,
					                   sphh.MASA_PAJAK) ppn_header,
					         (  SELECT
					                   sphm.TAHUN_PAJAK,
					                   sphm.BULAN_PAJAK,
					                   sphm.MASA_PAJAK,
					                   SUM (splm.JUMLAH_POTONG) JUMLAH_POTONG
					              FROM simtax_pajak_headers sphm,
					                   simtax_pajak_lines splm,
					                   simtax_kode_cabang skc
					             WHERE     sphm.nama_pajak = 'PPN KELUARAN'
					                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
					                   AND skc.KODE_CABANG = sphm.KODE_CABANG
					                   AND splm.IS_CHEKLIST = 1
					                   AND sphm.tahun_pajak = '".$tahun."'
					                   AND sphm.pembetulan_ke = '".$pembetulanKe."'
					                   AND sphm.kode_cabang in (".$whereCabang.")
					                   AND splm.kd_jenis_transaksi IN (1, 4, 6, 9)
					          GROUP BY
					                   sphm.TAHUN_PAJAK,
					                   sphm.BULAN_PAJAK,
					                   sphm.MASA_PAJAK) ppn_keluaran,
					         (  SELECT
					                   sphm.TAHUN_PAJAK,
					                   sphm.BULAN_PAJAK,
					                   sphm.MASA_PAJAK,
					                   SUM (splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
					              FROM simtax_pajak_headers sphm,
					                   simtax_pajak_lines splm,
					                   simtax_kode_cabang skc
					             WHERE     sphm.nama_pajak = 'PPN MASUKAN'
					                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
					                   AND skc.KODE_CABANG = sphm.KODE_CABANG
					                   AND splm.IS_CHEKLIST = 1
					                   AND sphm.tahun_pajak = '".$tahun."'
					                   AND sphm.pembetulan_ke = '".$pembetulanKe."'
					                   AND sphm.kode_cabang in (".$whereCabang.")
					                   AND (
					                        (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain')
					                        or
					                        (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1')
					                        )
					          GROUP BY
					                   sphm.TAHUN_PAJAK,
					                   sphm.BULAN_PAJAK,
					                   sphm.MASA_PAJAK) ppn_masukan,
					         (  SELECT
					                   sphm.TAHUN_PAJAK,
					                   sphm.BULAN_PAJAK,
					                   sphm.MASA_PAJAK,
					                   abs(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100) - SUM (NVL (splm.JUMLAH_POTONG * -1, 0)))
					                            jumlah_potong
					              FROM simtax_pajak_headers sphm,
					                   simtax_pajak_lines splm,
					                   simtax_kode_cabang skc
					             WHERE     sphm.nama_pajak = 'PPN MASUKAN'
					                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
					                   AND skc.KODE_CABANG = sphm.KODE_CABANG
					                   AND splm.IS_CHEKLIST = 1
					                   AND sphm.tahun_pajak = '".$tahun."'
					                   AND sphm.pembetulan_ke = '".$pembetulanKe."'
					                   AND sphm.kode_cabang in (".$whereCabang.")
					                   AND splm.is_pmk = 1
					          GROUP BY
					                   sphm.TAHUN_PAJAK,
					                   sphm.BULAN_PAJAK,
					                   sphm.MASA_PAJAK) pmk78
					   WHERE     1 = 1
					         AND skc.aktif = 'Y'
					         AND ppn_header.tahun_pajak = ppn_keluaran.tahun_pajak(+)
					         AND ppn_header.bulan_pajak = ppn_keluaran.bulan_pajak(+)
					         AND ppn_header.masa_pajak  = ppn_keluaran.masa_pajak(+)
					         AND ppn_header.tahun_pajak = ppn_masukan.tahun_pajak(+)
					         AND ppn_header.bulan_pajak = ppn_masukan.bulan_pajak(+)
					         AND ppn_header.masa_pajak  = ppn_masukan.masa_pajak(+)
					         AND ppn_header.tahun_pajak = pmk78.tahun_pajak(+)
					         AND ppn_header.bulan_pajak = pmk78.bulan_pajak(+)
					         AND ppn_header.masa_pajak  = pmk78.masa_pajak(+)
					         group by ppn_header.bulan_pajak, ppn_header.tahun_pajak, ppn_keluaran.jumlah_potong, ppn_masukan.jumlah_potong, pmk78.jumlah_potong
					ORDER BY ppn_header.bulan_pajak";

		$query = $this->db->query($queryExec);
		$dataPPN = $query->result_array();

		$sqlNTPN = "SELECT TANGGAL_SETOR, BANK, NTPN, TANGGAL_LAPOR, BULAN from simtax_ntpn where tahun = '".$tahun."' and pembetulan = '".$pembetulanKe."' and jenis_pajak is null order by bulan";
		$queryNTPN = $this->db->query($sqlNTPN);
		$dataNTPN  = $queryNTPN->result_array();
		// echo "<pre>";
		// print_r($dataNTPN);
		// die;

		$data2 = array();

		foreach ($dataNTPN as $key => $value) {
			$bulan_pajak = $value['BULAN'];
			$data2[$bulan_pajak][] = $value;
		}

		foreach ($dataPPN as $key => $value) {
			$bulan_pajak = $value['BULAN_PAJAK'];
			$data[$bulan_pajak] = $value;
		}
		for ($i=1; $i <= 12 ; $i++) {

			$ppn_keluaran  = 0;
			$ppn_masukan   = 0;
			$pmk78         = 0;
			$kurang_lebih  = 0;
			$nilai_setor   = "";
			$kode_map         = "";
			$kode_jenis_setor = "";
			
			$tanggal_setor = "";
			$tanggal_lapor = "";
			$ntpn          = "";

			$arrKeys = array_keys($data);

			if(in_array($i, $arrKeys)){

				$arrKeys2 = array_keys($data2);

				$ppn_keluaran = $data[$i]['PPN_KELUARAN'];
				$ppn_masukan  = $data[$i]['PPN_MASUKAN'];
				$pmk78        = $data[$i]['PMK78'];
				$kurang_lebih = $data[$i]['KURANG_LEBIH'];
				$nilai_setor  = $kurang_lebih;
				$kode_map         = "411211";
				$kode_jenis_setor = "100";
				

				if(in_array($i, $arrKeys2)){
					$ntpn = $data2;
					$countntpn = count($data2[$i]);

					if($countntpn > 1){
						$firstNumrow = $numrow;
						for ($j=0; $j < $countntpn; $j++) {
							$ntpn          = $data2[$i][$j]['NTPN'];
							$tanggal_setor = $data2[$i][$j]['TANGGAL_SETOR'];
							$tanggal_lapor = $data2[$i][$j]['TANGGAL_LAPOR'];

							if($j==0){

								$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);
								$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, get_masa_pajak($i, "id", true));
								$excel->getActiveSheet()->getStyle('C'.$j)->applyFromArray($style_row_kolom);
								$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ppn_keluaran);
								$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ppn_masukan);
								$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $pmk78);
								$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $kurang_lebih);
								$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($tanggal_setor) ? date("d/m/Y", strtotime($tanggal_setor)) : "-");
								$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $kode_map);
								$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $kode_jenis_setor);
								$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ntpn);
								$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $nilai_setor);
								$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($tanggal_lapor) ? date("d/m/Y", strtotime($tanggal_lapor)) : "-");

							}
							else{
								$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($tanggal_setor) ? date("d/m/Y", strtotime($tanggal_setor)) : "-");
								$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ntpn);
								$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($tanggal_lapor) ? date("d/m/Y", strtotime($tanggal_lapor)) : "-");

							}

							$numrow++;
						}
						$numrow-=1;

						$excel->getActiveSheet()->mergeCells('B'.$firstNumrow.':B'.$numrow);
						$excel->getActiveSheet()->mergeCells('C'.$firstNumrow.':C'.$numrow);
						$excel->getActiveSheet()->mergeCells('D'.$firstNumrow.':D'.$numrow);
						$excel->getActiveSheet()->mergeCells('E'.$firstNumrow.':E'.$numrow);
						$excel->getActiveSheet()->mergeCells('F'.$firstNumrow.':F'.$numrow);
						$excel->getActiveSheet()->mergeCells('G'.$firstNumrow.':G'.$numrow);
						$excel->getActiveSheet()->mergeCells('i'.$firstNumrow.':i'.$numrow);
						$excel->getActiveSheet()->mergeCells('J'.$firstNumrow.':J'.$numrow);
						$excel->getActiveSheet()->mergeCells('L'.$firstNumrow.':L'.$numrow);

					}
					else{

						$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);
						$ntpn          = $data2[$i][0]['NTPN'];
						$tanggal_setor = $data2[$i][0]['TANGGAL_SETOR'];
						$tanggal_lapor = $data2[$i][0]['TANGGAL_LAPOR'];

						$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, get_masa_pajak($i, "id", true));
						$excel->getActiveSheet()->getStyle('C'.$j)->applyFromArray($style_row_kolom);
						$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ppn_keluaran);
						$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ppn_masukan);
						$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $pmk78);
						$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $kurang_lebih);
						$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($tanggal_setor) ? date("d/m/Y", strtotime($tanggal_setor)) : "-");
						$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $kode_map);
						$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $kode_jenis_setor);
						$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ntpn);
						$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $nilai_setor);
						$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($tanggal_lapor) ? date("d/m/Y", strtotime($tanggal_lapor)) : "-");
					}

				}else{

					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, get_masa_pajak($i, "id", true));
					$excel->getActiveSheet()->getStyle('C'.$j)->applyFromArray($style_row_kolom);
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ppn_keluaran);
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ppn_masukan);
					$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $pmk78);
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $kurang_lebih);
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($tanggal_setor) ? date("d/m/Y", strtotime($tanggal_setor)) : "-");
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $kode_map);
					$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $kode_jenis_setor);
					$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ntpn);
					$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $nilai_setor);
					$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($tanggal_lapor) ? date("d/m/Y", strtotime($tanggal_lapor)) : "-");
				}

			}
			else{
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, get_masa_pajak($i, "id", true));
				$excel->getActiveSheet()->getStyle('C'.$j)->applyFromArray($style_row_kolom);
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ppn_keluaran);
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ppn_masukan);
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $pmk78);
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $kurang_lebih);
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ($tanggal_setor) ? date("d/m/Y", strtotime($tanggal_setor)) : "-");
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $kode_map);
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $kode_jenis_setor);
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ntpn);
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $nilai_setor);
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, ($tanggal_lapor) ? date("d/m/Y", strtotime($tanggal_lapor)) : "-");
			}

			$numrow++;
			$no++;
		}
		
		$total = $numrow;
		$loop  = horizontal_loop_excel("D", 10);
		$end   = end($loop);

		$excel->getActiveSheet()->getStyle("B7:M7")->applyFromArray($style_row_atas);

		for ($i=8; $i < $total; $i++) {

			$excel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$i)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$i)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('L'.$i)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($style_row_no);
			$excel->getActiveSheet()->getStyle('C'.$i)->applyFromArray($style_row_kolom);
			$excel->getActiveSheet()->getStyle('D'.$i)->applyFromArray($style_row);
			$excel->getActiveSheet()->getStyle('E'.$i)->applyFromArray($style_row);
			$excel->getActiveSheet()->getStyle('F'.$i)->applyFromArray($style_row);
			$excel->getActiveSheet()->getStyle('G'.$i)->applyFromArray($style_row);
			$excel->getActiveSheet()->getStyle('H'.$i)->applyFromArray($style_row_no);
			$excel->getActiveSheet()->getStyle('I'.$i)->applyFromArray($style_row_no);
			$excel->getActiveSheet()->getStyle('J'.$i)->applyFromArray($style_row_no);
			$excel->getActiveSheet()->getStyle('K'.$i)->applyFromArray($style_row_no);
			$excel->getActiveSheet()->getStyle('L'.$i)->applyFromArray($style_row);
			$excel->getActiveSheet()->getStyle('M'.$i)->applyFromArray($style_row_no);

		}

		$loop = horizontal_loop_excel("B", 12);
		$x = 1;
		foreach ($loop as $key => $value) {
			$excel->getActiveSheet()->getStyle($value.'7')->applyFromArray($style_row_no);
			$excel->setActiveSheetIndex(0)->setCellValue($value.'7', $x);
			$x++;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "KURANG / (LEBIH) BAYAR");
		$excel->getActiveSheet()->mergeCells('B'.$numrow.':C'.$numrow);
		$excel->getActiveSheet()->getStyle('B'.$numrow.':G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('L'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$numrowX = $numrow-1;

		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, '=SUM(D8:D'.$numrowX.')');
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, '=SUM(E8:E'.$numrowX.')');
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, '=SUM(F8:F'.$numrowX.')');
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, '=SUM(G8:G'.$numrowX.')');
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, '=SUM(L8:L'.$numrowX.')');

		$excel->getActiveSheet()->getStyle('B'.$numrow.':'.'c'.$numrow)->applyFromArray($style_row_kolom);

		$loop = horizontal_loop_excel("D", 10);
		foreach ($loop as $key => $value) {
			$excel->getActiveSheet()->getStyle($value.$numrow)->applyFromArray($style_row);
			$excel->getActiveSheet()->getStyle($value.$numrow)->applyFromArray($style_row_kolom);
		}
		
		$loop = horizontal_loop_excel("A", 13);

		$x=0;
		foreach ($loop as $key => $value) {
			if($x < 2){
				$excel->getActiveSheet()->getColumnDimension($value)->setWidth(5);
			}
			else{
				if($x==2){
					$excel->getActiveSheet()->getColumnDimension($value)->setWidth(30);
				}
				else{
					$excel->getActiveSheet()->getColumnDimension($value)->setWidth(20);
				}
			}
			$x++;
		}
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekap PPN Tahunan");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap PPN MASA Tahunan.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_cetak_1111ab_kompilasi()
	{
		$this->template->set('title', '1111AB');
		$data['subtitle']	= "Cetak Laporan 1111AB";
		$data['activepage'] = "ppn_masa";
		$this->template->load('template', 'laporan/lap_rekap_spt_kompilasi_ppn_masa',$data);		
	}

	function cetak_rekap_ppn_masa_kompilasi()
	{

		$tahun 				= $_REQUEST['tahun'];
		//$bulan 				= $_REQUEST['bulan'];
		$pembetulanKe 		= $_REQUEST['pembetulanKe'];
		$cabang 			= $_REQUEST['kd_cabang'];

		if($cabang != 'all'){
			$kd_cabang = $cabang;
		}else{
			$kd_cabang = '';
		}

		//$namabulan = get_masa_pajak($bulan,"id",true);
		
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

		$style_col_judul = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col_msk = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		  )
		);

		$style_col_atas = array(
		        'font' => array('bold' => true,
								'size' => 14), // Set font nya jadi bold
		);
		
		$style_col2 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);		
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_header = array(
				'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_jdl = array(
				'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
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
		$excel->setActiveSheetIndex(0)->setCellValue('A2', "REKAP SPT KOMPILASI"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A4', "SPT MASA PPN"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A5',$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('A6', "PPN KELUARAN"); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		
		// Buat header tabel nya pada baris ke 3
		
		$excel->setActiveSheetIndex(0)->setCellValue('A7', ""); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Terutang PPN"); // Set kolom A3 dengan tulisan "NO"

		$excel->setActiveSheetIndex(0)->setCellValue('A8', "Penyerahan Barang dan Jasa"); // Set kolom B3 dengan tulisan "NIS"
		$excel->getActiveSheet()->mergeCells('A8:D8');		
		$excel->getActiveSheet()->getStyle('A8:D8')->applyFromArray($style_col_judul);

		$excel->setActiveSheetIndex(0)->setCellValue('C7', "DPP"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D7', "PPN"); // Set kolom C3 dengan tulisan "NAMA"		
		
		$excel->getActiveSheet()->getStyle('A1')->applyFromArray($style_col_atas);
		$excel->getActiveSheet()->getStyle('A2')->applyFromArray($style_col_atas);
		$excel->getActiveSheet()->getStyle('A4')->applyFromArray($style_col_msk);
		$excel->getActiveSheet()->getStyle('A5')->applyFromArray($style_col_msk);
		$excel->getActiveSheet()->getStyle('A6')->applyFromArray($style_col_msk);
		$excel->getActiveSheet()->getStyle('A7')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row_jdl);
		//$excel->getActiveSheet()->getStyle('A8')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row_jdl);

		$excel->setActiveSheetIndex(0)->setCellValue('B9', "Eksport"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B10', "PPN-nya harus dipungut sendiri"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B11', "PPN-nya dipungut oleh pemungut PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B12', "PPN-nya tidak dipungut"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B13', "PPN-nya dibebaskan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B14', "Total"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A15', "Tidak terutang PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A16', "Jumlah seluruh penyerahan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A17', "Perhitungan PPN kurang bayar/lebih bayar"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A17:B17');		
		$excel->getActiveSheet()->getStyle('A17:B17')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('B18', "Pajak keluaran yang harus dipungut sendiri"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B19', "PPN disetor dimuka"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B20', "Pajak masukan yang dapat diperhitungkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B21', "PPN kurang/lebih bayar"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B22', "PPN kurang/lebih bayar pada SPT yang dibetulkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B23', "PPN kurang/lebih bayar karena pembetulan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B24', "PPN kurang bayar dilunasi tanggal"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B25', "NTPN"); // Set kolom A3 dengan tulisan "NO"

		$excel->getActiveSheet()->getStyle('B8')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B9')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B10')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B11')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B12')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B13')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B16')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B17')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B28')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B29')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B30')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B33')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B34')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B35')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B36')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B37')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B38')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B42')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B44')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B46')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B51')->applyFromArray($style_row);

		// $excel->getActiveSheet()->getStyle('A9')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A10')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A11')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A12')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A13')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('A15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A16')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A17')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B18')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B19')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B20')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B21')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B22')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B23')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B24')->applyFromArray($style_row);

		$excel->getActiveSheet()->getStyle('C8')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C9')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C10')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C11')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C12')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C13')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C16')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C17')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C18')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C19')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C20')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C21')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C22')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C23')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C24')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C25')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C28')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C30')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C31')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C32')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C33')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C34')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C35')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C36')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C37')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C38')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C39')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C40')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C41')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C42')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C44')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C46')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C47')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C48')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C49')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C50')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C51')->applyFromArray($style_row);

		$excel->getActiveSheet()->getStyle('D8')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D9')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D10')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D11')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D12')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D13')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D16')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D17')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D18')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D19')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D20')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D21')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D22')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D23')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D24')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D25')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D28')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D29')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D30')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D31')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D32')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D33')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D34')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D35')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D36')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D37')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D38')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D39')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D40')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D41')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D42')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('D43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D44')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D46')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D47')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D48')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D49')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D50')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D51')->applyFromArray($style_row_header);

		//PPN MASUKAN
		$excel->setActiveSheetIndex(0)->setCellValue('A28', ""); // Set kolom A3 dengan tulisan "NO"
		/*$excel->setActiveSheetIndex(0)->setCellValue('A27', "PPN MASUKAN"); // Set kolom A3 dengan tulisan "NO"*/
		$excel->getActiveSheet()->mergeCells('A27:D27');		
		$excel->getActiveSheet()->getStyle('A27:D27')->applyFromArray($style_col_judul);

		$excel->setActiveSheetIndex(0)->setCellValue('B28', "Terutang PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C28', "DPP"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('D28', "PPN"); // Set kolom A3 dengan tulisan "NO"

		$excel->setActiveSheetIndex(0)->setCellValue('A29', "Ekspor BKP berwujud/BKP tidak berwujud"); // Set kolom A3 dengan tulisan "NO" 
		$excel->getActiveSheet()->mergeCells('A29:B29');		
		$excel->getActiveSheet()->getStyle('A29:B29')->applyFromArray($style_col_judul);

		$excel->getActiveSheet()->getStyle('A28')->applyFromArray($style_row_header);
		/*$excel->getActiveSheet()->getStyle('A27')->applyFromArray($style_row_header);*/
		$excel->getActiveSheet()->getStyle('B28')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('C28')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('D28')->applyFromArray($style_row_jdl);
		//$excel->getActiveSheet()->getStyle('A29')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('A30', "Penyerahan dalam negeri"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A30:D30');		
		$excel->getActiveSheet()->getStyle('A30:D30')->applyFromArray($style_col_judul);

		$excel->setActiveSheetIndex(0)->setCellValue('B31', "Penyerahan dalam negeri dengan faktur pajak yang tidak digunggung"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B32', "Penyerahan dalam negeri dengan faktur pajak yang  digunggung"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A33', "Rincian penyerahan dalam negeri"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A33:D33');		
		$excel->getActiveSheet()->getStyle('A33:D33')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('B34', "PPN-nya harus dipungut sendiri"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B35', "PPN-nya dipungut oleh pemungut PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B36', "PPN-nya tidak dipungut"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B37', "PPN-nya dibebaskan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A38', "Rekapitulasi perolehan"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A38:D38');		
		$excel->getActiveSheet()->getStyle('A38:D38')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('B39', "PPN Impor"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B40', "PPN yang dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B41', "PPN yang tidak dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B42', "Total"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A44', "Penghitungan PM yang dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A45', "Pajak masukan yang dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A46', "Pajak masukan lainnya"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B47', "Kompensasi kelebihan PPN masa pajak sebelumnya"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B48', "Kompensasi kelebihan PPN karena pembetulan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B49', "Hasil perhitungan kembali pajak masukan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B50', "Total (B33 s/d B35)"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('A51', "Jumlah pajak masukan yang dapat diperhitungkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('A51:B51');		
		$excel->getActiveSheet()->getStyle('A51:B51')->applyFromArray($style_row_header);

		$excel->getActiveSheet()->getStyle('B25')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A28')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A29')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A30')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B31')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B32')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A33')->applyFromArray($style_row_header);
		// $excel->getActiveSheet()->getStyle('A34')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A35')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A36')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A37')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A38')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B39')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B40')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B41')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A42')->applyFromArray($style_row_header);
		//$excel->getActiveSheet()->getStyle('A43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A44')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('A45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('A46')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B47')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B48')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B49')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B50')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A51')->applyFromArray($style_row_header);

		/*$joinCondition  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splm.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splm.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splm.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splm.ORGANIZATION_ID";
		$joinCondition2  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splh.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splh.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splh.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splh.ORGANIZATION_ID";*/

		if ($kd_cabang == ""){
			$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
		}else{
			$whereCabang = "'".$kd_cabang."'";
		}

		$queryExec 		= "SELECT
SUM(ppn_dipungut_sendiri.jumlah_potong) PPN_SENDIRI, SUM(ppn_dipungut_sendiri.dpp) DPP_SENDIRI,
SUM(ppn_oleh_pemungut.jumlah_potong) PPN_OLEH_PEMUNGUT, SUM(ppn_oleh_pemungut.dpp) DPP_OLEH_PEMUNGUT,
SUM(ppn_tidak_dipungut.jumlah_potong) PPN_TIDAK_DIPUNGUT, SUM(ppn_tidak_dipungut.dpp) DPP_TIDAK_DIPUNGUT,
SUM(ppn_dibebaskan.jumlah_potong) PPN_DIBEBASKAN, SUM(ppn_dibebaskan.dpp) DPP_DIBEBASKAN,
SUM(ppn_dipungut_sendiri2.jumlah_potong) PPN_SENDIRI2, SUM(ppn_dipungut_sendiri2.dpp) DPP_SENDIRI2,
SUM(ppn_oleh_pemungut2.jumlah_potong) PPN_OLEH_PEMUNGUT2, SUM(ppn_oleh_pemungut2.dpp) DPP_OLEH_PEMUNGUT2,
SUM(ppn_impor.jumlah_potong) PPN_IMPOR, SUM(ppn_impor.dpp) DPP_IMPOR,
SUM(ppn_di_kreditkan.jumlah_potong) PPN_DIKREDITKAN, SUM(ppn_di_kreditkan.dpp) DPP_DIKREDITKAN,
SUM(ppn_tidak_di_kreditkan.jumlah_potong) PPN_TIDAK_DIKREDITKAN, SUM(ppn_tidak_di_kreditkan.dpp) DPP_TIDAK_DIKREDITKAN,
SUM(ppn_masukan.pmk78 ) PMK78, sum(pmk_78.pmk_78 * -1) PMK
                              from simtax_kode_cabang skc
                            , (select 
                                   skc.NAMA_CABANG
                                 , sphh.KODE_CABANG
                                 , sphh.TAHUN_PAJAK
                                 , sphh.BULAN_PAJAK
                                 , sphh.MASA_PAJAK
                              from simtax_pajak_headers sphh
                                 , simtax_pajak_lines splh
                                 , simtax_kode_cabang skc
                             where sphh.nama_pajak in ('PPN KELUARAN','PPN MASUKAN')
                               and sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
                               and nvl(splh.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphh.KODE_CABANG
                               and sphh.tahun_pajak = '".$tahun."'
                               and sphh.pembetulan_ke = '".$pembetulanKe."'
                               and sphh.kode_cabang in (".$whereCabang.")
                               and sphh.status not in ('DRAFT','REJECT SUPERVISOR')
                            group by skc.NAMA_CABANG, sphh.KODE_CABANG, sphh.TAHUN_PAJAK, sphh.BULAN_PAJAK, sphh.MASA_PAJAK) ppn_header,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")                              
                               and splm.kd_jenis_transaksi IN (1,4,6,9)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dipungut_sendiri,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                                 --, sum(nvl(splm.JUMLAH_POTONG,0))*-1 JUMLAH_POTONG
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.kd_jenis_transaksi IN (2,3)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_oleh_pemungut,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.kd_jenis_transaksi = '7'
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_tidak_dipungut,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.kd_jenis_transaksi = '8'
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dibebaskan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.kd_jenis_transaksi IN (1,4,6,9)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dipungut_sendiri2,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.kd_jenis_transaksi IN (2,3)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_oleh_pemungut2,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               AND ((splm.kd_jenis_transaksi IN (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') OR (splm.kd_jenis_transaksi IN (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') AND SPLM.IS_CREDITABLE = '1'))
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_di_kreditkan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.is_creditable = 0
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_tidak_di_kreditkan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
                               and splm.dl_fs = 'dokumen_lain' 
                               and splm.kd_jenis_transaksi IN ('11','12') 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_impor,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , min(nvl(sphm.PMK78,0)) PMK78
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
							   AND ((splm.kd_jenis_transaksi IN (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') OR (splm.kd_jenis_transaksi IN (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') AND SPLM.IS_CREDITABLE = '1'))
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_masukan,
                            (  SELECT skc.NAMA_CABANG,
			                   sphm.KODE_CABANG,
			                   sphm.TAHUN_PAJAK,
			                   sphm.BULAN_PAJAK,
			                   sphm.MASA_PAJAK,
			                   ceil(abs(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100)
			                   - SUM (NVL (splm.JUMLAH_POTONG * -1, 0))))
			                      PMK_78
			              	FROM simtax_pajak_headers sphm,
			                   simtax_pajak_lines splm,
			                   simtax_kode_cabang skc
			             	WHERE     sphm.nama_pajak = 'PPN MASUKAN'
			                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
			                   AND NVL (splm.IS_CHEKLIST, 0) = 1
			                   AND splm.is_pmk = 1
			                   AND skc.KODE_CABANG = sphm.KODE_CABANG
			                   and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               and sphm.kode_cabang in (".$whereCabang.")
			          		GROUP BY skc.NAMA_CABANG,
			                   sphm.KODE_CABANG,
			                   sphm.TAHUN_PAJAK,
			                   sphm.BULAN_PAJAK,
			                   sphm.MASA_PAJAK) PMK_78
                            where 1=1
                            and skc.KODE_CABANG = ppn_header.kode_cabang (+)
                            and ppn_header.nama_cabang = ppn_dipungut_sendiri.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dipungut_sendiri.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dipungut_sendiri.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dipungut_sendiri.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dipungut_sendiri.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_oleh_pemungut.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_oleh_pemungut.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_oleh_pemungut.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_oleh_pemungut.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_oleh_pemungut.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_tidak_dipungut.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_tidak_dipungut.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_tidak_dipungut.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_tidak_dipungut.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_tidak_dipungut.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_dibebaskan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dibebaskan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dibebaskan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dibebaskan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dibebaskan.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_dipungut_sendiri2.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dipungut_sendiri2.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dipungut_sendiri2.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dipungut_sendiri2.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dipungut_sendiri2.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_oleh_pemungut2.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_oleh_pemungut2.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_oleh_pemungut2.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_oleh_pemungut2.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_oleh_pemungut2.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_di_kreditkan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_di_kreditkan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_di_kreditkan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_di_kreditkan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_di_kreditkan.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_tidak_di_kreditkan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_tidak_di_kreditkan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_tidak_di_kreditkan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_tidak_di_kreditkan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_tidak_di_kreditkan.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_impor.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_impor.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_impor.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_impor.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_impor.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_masukan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_masukan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_masukan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_masukan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_masukan.masa_pajak (+)
                            AND ppn_header.nama_cabang = pmk_78.nama_cabang(+)
				         	AND ppn_header.kode_cabang = pmk_78.kode_cabang(+)
				         	AND ppn_header.tahun_pajak = pmk_78.tahun_pajak(+)
				         	AND ppn_header.bulan_pajak = pmk_78.bulan_pajak(+)
				         	AND ppn_header.masa_pajak = pmk_78.masa_pajak(+)
                            and skc.KODE_CABANG in (".$whereCabang.")
                            order by skc.kode_cabang";

		
		$query 			= $this->db->query($queryExec);
		$row = $query->row();

		//KELUARAN
		$excel->setActiveSheetIndex(0)->setCellValue('C10', $row->DPP_SENDIRI);
		$excel->getActiveSheet()->getStyle('C10')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D10', $row->PPN_SENDIRI);
		$excel->getActiveSheet()->getStyle('D10')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C11', $row->DPP_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('C11')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D11', $row->PPN_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('D11')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C12', $row->DPP_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('C12')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D12', $row->PPN_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('D12')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C13', $row->DPP_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('C13')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D13', $row->PPN_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('D13')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		//MASUKAN
		$excel->setActiveSheetIndex(0)->setCellValue('C34', $row->DPP_SENDIRI);
		$excel->getActiveSheet()->getStyle('C34')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D34', $row->PPN_SENDIRI);
		$excel->getActiveSheet()->getStyle('D34')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C35', $row->DPP_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('C35')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D35', $row->PPN_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('D35')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C36', $row->DPP_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('C36')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D36', $row->PPN_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('D36')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C37', $row->DPP_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('C37')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D37', $row->PPN_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('D37')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C14', '=sum(C10:C13)');
		$excel->getActiveSheet()->getStyle('C14')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D14', '=sum(D10:D13)');
		$excel->getActiveSheet()->getStyle('D14')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D18', '=D10');
		$excel->getActiveSheet()->getStyle('D18')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C16', '=C14+C15');
		$excel->getActiveSheet()->getStyle('C16')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D20', '=D51');
		$excel->getActiveSheet()->getStyle('D20')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D21', '=D18-D19-D20');
		$excel->getActiveSheet()->getStyle('D21')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D23', '=D21-D22');
		$excel->getActiveSheet()->getStyle('D23')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C31', '=sum(C34:C37)');
		$excel->getActiveSheet()->getStyle('C31')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D31', '=sum(D34:D37)');
		$excel->getActiveSheet()->getStyle('D31')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		//$excel->setActiveSheetIndex(0)->setCellValue('D45', '=D39+D40');
		$excel->setActiveSheetIndex(0)->setCellValue('D45', $row->PPN_DIKREDITKAN);
		$excel->getActiveSheet()->getStyle('D45')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D49', $row->PMK);
		$excel->getActiveSheet()->getStyle('D49')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C50', '=sum(C47:C49)');
		$excel->getActiveSheet()->getStyle('C50')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D50', '=sum(D47:D49)');
		$excel->getActiveSheet()->getStyle('D50')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D51', '=D45+D50');
		$excel->getActiveSheet()->getStyle('D51')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C39', $row->DPP_IMPOR);
		$excel->getActiveSheet()->getStyle('C39')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D39', $row->PPN_IMPOR);
		$excel->getActiveSheet()->getStyle('D39')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C40', $row->DPP_DIKREDITKAN-$row->DPP_IMPOR);
		$excel->getActiveSheet()->getStyle('C40')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D40', $row->PPN_DIKREDITKAN-$row->PPN_IMPOR);
		$excel->getActiveSheet()->getStyle('D40')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C41', $row->DPP_TIDAK_DIKREDITKAN);
		$excel->getActiveSheet()->getStyle('C41')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D41', $row->PPN_TIDAK_DIKREDITKAN);
		$excel->getActiveSheet()->getStyle('D41')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('C42', '=sum(C39:C41)');
		$excel->getActiveSheet()->getStyle('C42')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D42', '=sum(D39:D41)');
		$excel->getActiveSheet()->getStyle('D42')->getNumberFormat()->setFormatCode('_(#,##0.00_);_(\(#,##0.00\);_("-"??_);_(@_)');

		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(35); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(65); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("1111AB Kompilasi ".$tahun);
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="1111AB Kompilasi '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function cetak_rekap_ppn_masa_kompilasi_bln()
	{

		$tahun 				= $_REQUEST['tahun'];
		$bulan 				= $_REQUEST['bulan'];
		$pembetulanKe 		= $_REQUEST['pembetulanKe'];
		$cabang 			= $_REQUEST['cabang'];

		if ($cabang == "all"){
			$where_cabang = "";
			$where_cabang2 = "";
		}
		else{
			$where_cabang = "and sphm.kode_cabang = '".$cabang."'";
			$where_cabang2 = "and sphh.kode_cabang = '".$cabang."'";

		}

		$namabulan = get_masa_pajak($bulan,"id",true);
		
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

		$style_col_judul = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col_msk = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		);

		$style_col_atas = array(
		        'font' => array('bold' => true,
								'size' => 14), // Set font nya jadi bold
		);
		
		$style_col2 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);		
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_header = array(
				'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_jdl = array(
				'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
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
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "REKAP SPT KOMPILASI"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "SPT MASA PPN"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B5', $namabulan." ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B6', "PPN KELUARAN"); // Set kolom A1 dengan tulisan "DATA SISWA"
		
		
		// Buat header tabel nya pada baris ke 3
		
		$excel->setActiveSheetIndex(0)->setCellValue('B7', ""); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Terutang PPN"); // Set kolom A3 dengan tulisan "NO"

		$excel->setActiveSheetIndex(0)->setCellValue('B8', "Penyerahan Barang dan Jasa"); // Set kolom B3 dengan tulisan "NIS"
		$excel->getActiveSheet()->mergeCells('B8:E8');		
		$excel->getActiveSheet()->getStyle('B8:E8')->applyFromArray($style_col_judul);

		$excel->setActiveSheetIndex(0)->setCellValue('D7', "DPP"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('E7', "PPN"); // Set kolom C3 dengan tulisan "NAMA"		
		
		$excel->getActiveSheet()->getStyle('B1')->applyFromArray($style_col_atas);
		$excel->getActiveSheet()->getStyle('B2')->applyFromArray($style_col_atas);
		$excel->getActiveSheet()->getStyle('B4')->applyFromArray($style_col_msk);
		$excel->getActiveSheet()->getStyle('B5')->applyFromArray($style_col_msk);
		$excel->getActiveSheet()->getStyle('B6')->applyFromArray($style_col_msk);
		$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row_jdl);
		//$excel->getActiveSheet()->getStyle('A8')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('E7')->applyFromArray($style_row_jdl);

		$excel->setActiveSheetIndex(0)->setCellValue('C9', "Eksport"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C10', "PPN-nya harus dipungut sendiri"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C11', "PPN-nya dipungut oleh pemungut PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C12', "PPN-nya tidak dipungut"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C13', "PPN-nya dibebaskan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C14', "Total"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B15', "Tidak terutang PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B16', "Jumlah seluruh penyerahan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B17', "Perhitungan PPN kurang bayar/lebih bayar"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('B17:C17');		
		$excel->getActiveSheet()->getStyle('B17:C17')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('C18', "Pajak keluaran yang harus dipungut sendiri"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C19', "PPN disetor dimuka"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C20', "Pajak masukan yang dapat diperhitungkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C21', "PPN kurang/lebih bayar"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C22', "PPN kurang/lebih bayar pada SPT yang dibetulkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C23', "PPN kurang/lebih bayar karena pembetulan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C24', "PPN kurang bayar dilunasi tanggal"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C25', "NTPN"); // Set kolom A3 dengan tulisan "NO"

		$excel->getActiveSheet()->getStyle('C8')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C9')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C10')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C11')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C12')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C13')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C16')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B17')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C28')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C29')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B30')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B33')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C34')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C35')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C36')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C37')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B38')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C42')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C44')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C46')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('B51')->applyFromArray($style_row);

		// $excel->getActiveSheet()->getStyle('A9')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A10')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A11')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A12')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A13')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B16')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A17')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C18')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C19')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C20')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C21')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C22')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C23')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C24')->applyFromArray($style_row);

		$excel->getActiveSheet()->getStyle('D8')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D9')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D10')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D11')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D12')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D13')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D16')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D17')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D18')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D19')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D20')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D21')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D22')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D23')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D24')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D25')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D28')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D30')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D31')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D32')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D33')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D34')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D35')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D36')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D37')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D38')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D39')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D40')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D41')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D42')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('D43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D44')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D46')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D47')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D48')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D49')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D50')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D51')->applyFromArray($style_row);

		$excel->getActiveSheet()->getStyle('E8')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E9')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E10')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E11')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E12')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E13')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E14')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('E15')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E16')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E17')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E18')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E19')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E20')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E21')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E22')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E23')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E24')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E25')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E28')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E29')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E30')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E31')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E32')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E33')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E34')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E35')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E36')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E37')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E38')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E39')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E40')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E41')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E42')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('E43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E44')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E46')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E47')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E48')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E49')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E50')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E51')->applyFromArray($style_row_header);

		//PPN MASUKAN
		$excel->setActiveSheetIndex(0)->setCellValue('B28', ""); // Set kolom A3 dengan tulisan "NO"
		/*$excel->setActiveSheetIndex(0)->setCellValue('A27', "PPN MASUKAN"); // Set kolom A3 dengan tulisan "NO"*/
		$excel->getActiveSheet()->mergeCells('B27:E27');		
		$excel->getActiveSheet()->getStyle('B27:E27')->applyFromArray($style_col_judul);

		$excel->setActiveSheetIndex(0)->setCellValue('C28', "Terutang PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('D28', "DPP"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('E28', "PPN"); // Set kolom A3 dengan tulisan "NO"

		$excel->setActiveSheetIndex(0)->setCellValue('B29', "Ekspor BKP berwujud/BKP tidak berwujud"); // Set kolom A3 dengan tulisan "NO" 
		$excel->getActiveSheet()->mergeCells('B29:C29');		
		$excel->getActiveSheet()->getStyle('B29:C29')->applyFromArray($style_col_judul);

		$excel->getActiveSheet()->getStyle('B28')->applyFromArray($style_row_header);
		/*$excel->getActiveSheet()->getStyle('A27')->applyFromArray($style_row_header);*/
		$excel->getActiveSheet()->getStyle('C28')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('D28')->applyFromArray($style_row_jdl);
		$excel->getActiveSheet()->getStyle('E28')->applyFromArray($style_row_jdl);
		//$excel->getActiveSheet()->getStyle('A29')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('B30', "Penyerahan dalam negeri"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('B30:E30');		
		$excel->getActiveSheet()->getStyle('B30:E30')->applyFromArray($style_col_judul);

		$excel->setActiveSheetIndex(0)->setCellValue('C31', "Penyerahan dalam negeri dengan faktur pajak yang tidak digunggung"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C32', "Penyerahan dalam negeri dengan faktur pajak yang  digunggung"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B33', "Rincian penyerahan dalam negeri"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('B33:E33');		
		$excel->getActiveSheet()->getStyle('B33:E33')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('C34', "PPN-nya harus dipungut sendiri"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C35', "PPN-nya dipungut oleh pemungut PPN"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C36', "PPN-nya tidak dipungut"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C37', "PPN-nya dibebaskan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B38', "Rekapitulasi perolehan"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('B38:E38');		
		$excel->getActiveSheet()->getStyle('B38:E38')->applyFromArray($style_row_header);

		$excel->setActiveSheetIndex(0)->setCellValue('C39', "PPN Impor"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C40', "PPN yang dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C41', "PPN yang tidak dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C42', "Total"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B44', "Penghitungan PM yang dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B45', "Pajak masukan yang dapat dikreditkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B46', "Pajak masukan lainnya"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C47', "Kompensasi kelebihan PPN masa pajak sebelumnya"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C48', "Kompensasi kelebihan PPN karena pembetulan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C49', "Hasil perhitungan kembali pajak masukan"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C50', "Total (B33 s/d B35)"); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('B51', "Jumlah pajak masukan yang dapat diperhitungkan"); // Set kolom A3 dengan tulisan "NO"
		$excel->getActiveSheet()->mergeCells('B51:C51');		
		$excel->getActiveSheet()->getStyle('B51:C51')->applyFromArray($style_row_header);

		$excel->getActiveSheet()->getStyle('C25')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A26')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B27')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B28')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A29')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B30')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C31')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C32')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B33')->applyFromArray($style_row_header);
		// $excel->getActiveSheet()->getStyle('A34')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A35')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A36')->applyFromArray($style_row);
		// $excel->getActiveSheet()->getStyle('A37')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A38')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('C39')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C40')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C41')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A42')->applyFromArray($style_row_header);
		//$excel->getActiveSheet()->getStyle('A43')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B44')->applyFromArray($style_row_header);
		$excel->getActiveSheet()->getStyle('B45')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('B46')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C47')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C48')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C49')->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C50')->applyFromArray($style_row);
		//$excel->getActiveSheet()->getStyle('A51')->applyFromArray($style_row_header);

		/*$joinCondition  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splm.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splm.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splm.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splm.ORGANIZATION_ID";
		$joinCondition2  = " LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS
								          ON SMS.VENDOR_ID = splh.VENDOR_ID
								         AND SMS.VENDOR_SITE_ID = splh.VENDOR_SITE_ID
								   LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL
								          ON SMPEL.CUSTOMER_ID = splh.CUSTOMER_ID
								         AND SMPEL.ORGANIZATION_ID = splh.ORGANIZATION_ID";*/

		$queryExec 		= "SELECT
SUM(ppn_dipungut_sendiri.jumlah_potong) PPN_SENDIRI, SUM(ppn_dipungut_sendiri.dpp) DPP_SENDIRI,
SUM(ppn_oleh_pemungut.jumlah_potong) PPN_OLEH_PEMUNGUT, SUM(ppn_oleh_pemungut.dpp) DPP_OLEH_PEMUNGUT,
SUM(ppn_tidak_dipungut.jumlah_potong) PPN_TIDAK_DIPUNGUT, SUM(ppn_tidak_dipungut.dpp) DPP_TIDAK_DIPUNGUT,
SUM(ppn_dibebaskan.jumlah_potong) PPN_DIBEBASKAN, SUM(ppn_dibebaskan.dpp) DPP_DIBEBASKAN,
SUM(ppn_dipungut_sendiri2.jumlah_potong) PPN_SENDIRI2, SUM(ppn_dipungut_sendiri2.dpp) DPP_SENDIRI2,
SUM(ppn_oleh_pemungut2.jumlah_potong) PPN_OLEH_PEMUNGUT2, SUM(ppn_oleh_pemungut2.dpp) DPP_OLEH_PEMUNGUT2,
SUM(ppn_impor.jumlah_potong) PPN_IMPOR, SUM(ppn_impor.dpp) DPP_IMPOR,
SUM(ppn_di_kreditkan.jumlah_potong) PPN_DIKREDITKAN, SUM(ppn_di_kreditkan.dpp) DPP_DIKREDITKAN,
SUM(ppn_tidak_di_kreditkan.jumlah_potong) PPN_TIDAK_DIKREDITKAN, SUM(ppn_tidak_di_kreditkan.dpp) DPP_TIDAK_DIKREDITKAN,
SUM(pmk.jumlah_potong ) PMK_OLD, SUM (pmk_78.pmk_78 * -1) PMK
                              from simtax_kode_cabang skc
                            , (select 
                                   skc.NAMA_CABANG
                                 , sphh.KODE_CABANG
                                 , sphh.TAHUN_PAJAK
                                 , sphh.BULAN_PAJAK
                                 , sphh.MASA_PAJAK
                              from simtax_pajak_headers sphh
                                 , simtax_pajak_lines splh
                                 , simtax_kode_cabang skc
                             where sphh.nama_pajak in ('PPN KELUARAN','PPN MASUKAN')
                               and sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
                               and nvl(splh.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphh.KODE_CABANG
                               and sphh.tahun_pajak = '".$tahun."'
                               and sphh.bulan_pajak = '".$bulan."'
                               and sphh.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang2."
                            group by skc.NAMA_CABANG, sphh.KODE_CABANG, sphh.TAHUN_PAJAK, sphh.BULAN_PAJAK, sphh.MASA_PAJAK) ppn_header,
                             (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'                         
                               ".$where_cabang."
                               and splm.kd_jenis_transaksi IN (1,4,6,9)    
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dipungut_sendiri,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                                 --, sum(nvl(splm.JUMLAH_POTONG,0))*-1 JUMLAH_POTONG
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."                               
                                and splm.kd_jenis_transaksi IN (2,3)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_oleh_pemungut,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."                               
                                and splm.kd_jenis_transaksi = 7
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_tidak_dipungut,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                               and splm.kd_jenis_transaksi = 8
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dibebaskan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."                               
                               and ((splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') or (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1')) 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dipungut_sendiri2,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."                               
                                and splm.kd_jenis_transaksi IN (2,3)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_oleh_pemungut2,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                               and ((splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') or (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1'))
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_di_kreditkan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                               and splm.is_creditable = 0
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_tidak_di_kreditkan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                               and splm.dl_fs = 'dokumen_lain' 
                               and splm.kd_jenis_transaksi IN ('11','12') 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_impor,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , min(nvl(sphm.PMK78,0)) PMK78
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                               and ((splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') or (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1'))      
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_masukan,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
								 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                               and splm.is_pmk = '1'   
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) pmk,
                            (  SELECT skc.NAMA_CABANG,
                               sphm.KODE_CABANG,
                               sphm.TAHUN_PAJAK,
                               sphm.BULAN_PAJAK,
                               sphm.MASA_PAJAK,
                               ceil(abs(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100)
                               - SUM (NVL (splm.JUMLAH_POTONG * -1, 0))))
                                  PMK_78
                              FROM simtax_pajak_headers sphm,
                               simtax_pajak_lines splm,
                               simtax_kode_cabang skc
                             WHERE     sphm.nama_pajak = 'PPN MASUKAN'
                               AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               AND NVL (splm.IS_CHEKLIST, 0) = 1
                               AND splm.is_pmk = 1
                               AND skc.KODE_CABANG = sphm.KODE_CABANG
                               AND NVL (splm.IS_CHEKLIST, 0) = 1
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = '".$pembetulanKe."'
                               ".$where_cabang."
                              GROUP BY skc.NAMA_CABANG,
                               sphm.KODE_CABANG,
                               sphm.TAHUN_PAJAK,
                               sphm.BULAN_PAJAK,
                               sphm.MASA_PAJAK) PMK_78
                            where 1=1
                            and skc.KODE_CABANG = ppn_header.kode_cabang (+)
                            and ppn_header.nama_cabang = ppn_dipungut_sendiri.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dipungut_sendiri.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dipungut_sendiri.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dipungut_sendiri.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dipungut_sendiri.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_oleh_pemungut.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_oleh_pemungut.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_oleh_pemungut.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_oleh_pemungut.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_oleh_pemungut.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_tidak_dipungut.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_tidak_dipungut.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_tidak_dipungut.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_tidak_dipungut.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_tidak_dipungut.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_dibebaskan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dibebaskan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dibebaskan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dibebaskan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dibebaskan.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_dipungut_sendiri2.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dipungut_sendiri2.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dipungut_sendiri2.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dipungut_sendiri2.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dipungut_sendiri2.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_oleh_pemungut2.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_oleh_pemungut2.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_oleh_pemungut2.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_oleh_pemungut2.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_oleh_pemungut2.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_di_kreditkan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_di_kreditkan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_di_kreditkan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_di_kreditkan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_di_kreditkan.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_tidak_di_kreditkan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_tidak_di_kreditkan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_tidak_di_kreditkan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_tidak_di_kreditkan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_tidak_di_kreditkan.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_impor.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_impor.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_impor.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_impor.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_impor.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_masukan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_masukan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_masukan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_masukan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_masukan.masa_pajak (+)
                            and ppn_header.nama_cabang = pmk.nama_cabang (+)
                            and ppn_header.kode_cabang = pmk.kode_cabang (+)
                            and ppn_header.tahun_pajak = pmk.tahun_pajak (+)
                            and ppn_header.bulan_pajak = pmk.bulan_pajak (+)
                            and ppn_header.masa_pajak  = pmk.masa_pajak (+)
                            AND ppn_header.nama_cabang = pmk_78.nama_cabang(+)
				         	AND ppn_header.kode_cabang = pmk_78.kode_cabang(+)
				         	AND ppn_header.tahun_pajak = pmk_78.tahun_pajak(+)
				         	AND ppn_header.bulan_pajak = pmk_78.bulan_pajak(+)
				         	AND ppn_header.masa_pajak = pmk_78.masa_pajak(+)
                            and skc.KODE_CABANG in ('000','010','020','030','040','050',
                            '060','070','080','090','100','110','120')
                            order by skc.kode_cabang";

		
		$query 			= $this->db->query($queryExec);
		$row = $query->row();

		//KELUARAN
		$excel->setActiveSheetIndex(0)->setCellValue('D10', $row->DPP_SENDIRI);
		$excel->getActiveSheet()->getStyle('D10')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E10', $row->PPN_SENDIRI);
		$excel->getActiveSheet()->getStyle('E10')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D11', $row->DPP_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('D11')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E11', $row->PPN_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('E11')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D12', $row->DPP_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('D12')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E12', $row->PPN_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('E12')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D13', $row->DPP_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('D13')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E13', $row->PPN_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('E13')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		//MASUKAN
		$excel->setActiveSheetIndex(0)->setCellValue('D34', $row->DPP_SENDIRI);
		$excel->getActiveSheet()->getStyle('D34')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E34', $row->PPN_SENDIRI);
		$excel->getActiveSheet()->getStyle('E34')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D35', $row->DPP_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('D35')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E35', $row->PPN_OLEH_PEMUNGUT);
		$excel->getActiveSheet()->getStyle('E35')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D36', $row->DPP_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('D36')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E36', $row->PPN_TIDAK_DIPUNGUT);
		$excel->getActiveSheet()->getStyle('E36')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D37', $row->DPP_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('D37')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E37', $row->PPN_DIBEBASKAN);
		$excel->getActiveSheet()->getStyle('E37')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D14', '=sum(D10:D13)');
		$excel->getActiveSheet()->getStyle('D14')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E14', '=sum(E10:E13)');
		$excel->getActiveSheet()->getStyle('E14')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E18', '=E10');
		$excel->getActiveSheet()->getStyle('E18')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D16', '=D14+D15');
		$excel->getActiveSheet()->getStyle('D16')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E20', '=E51');
		$excel->getActiveSheet()->getStyle('E20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E21', '=E18-E19-E20');
		$excel->getActiveSheet()->getStyle('E21')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E23', '=E21-E22');
		$excel->getActiveSheet()->getStyle('E23')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D31', '=sum(D34:D37)');
		$excel->getActiveSheet()->getStyle('D31')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E31', '=sum(E34:E37)');
		$excel->getActiveSheet()->getStyle('E31')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		//$excel->setActiveSheetIndex(0)->setCellValue('E45', '=E39+E40');
		$excel->setActiveSheetIndex(0)->setCellValue('E45', $row->PPN_DIKREDITKAN);
		$excel->getActiveSheet()->getStyle('E45')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E49', $row->PMK);
		$excel->getActiveSheet()->getStyle('E49')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D50', '=sum(D47:D49)');
		$excel->getActiveSheet()->getStyle('D50')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E50', '=sum(E47:E49)');
		$excel->getActiveSheet()->getStyle('E50')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E51', '=E45+E50');
		$excel->getActiveSheet()->getStyle('E51')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D39', $row->DPP_IMPOR);
		$excel->getActiveSheet()->getStyle('D39')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E39', $row->PPN_IMPOR);
		$excel->getActiveSheet()->getStyle('E39')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D40', $row->DPP_DIKREDITKAN-$row->DPP_IMPOR);
		$excel->getActiveSheet()->getStyle('D40')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E40', $row->PPN_DIKREDITKAN-$row->PPN_IMPOR);
		$excel->getActiveSheet()->getStyle('E40')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D41', $row->DPP_TIDAK_DIKREDITKAN);
		$excel->getActiveSheet()->getStyle('D41')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E41', $row->PPN_TIDAK_DIKREDITKAN);
		$excel->getActiveSheet()->getStyle('E41')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('D42', '=sum(D39:D41)');
		$excel->getActiveSheet()->getStyle('D42')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->setActiveSheetIndex(0)->setCellValue('E42', '=sum(E39:E41)');
		$excel->getActiveSheet()->getStyle('E42')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(35); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(65); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Rekap PPN ".$namabulan." ".$tahun);
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Rekap PPN MASA '.$namabulan.' '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_report_spt_ppn_masa_rekapitulasi()
	{
		$this->template->set('title', 'Lampiran I (Rekap SPT)');
		$data['subtitle']   = "Cetak Lampiran I (Rekap SPT)";
		$data['activepage'] = "laporan_ekualisasi";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_rekap_spt_ppn_masa_rekapitulasi',$data);		
	}

	function cetak_report_spt_ppn_masa_rekapitulasi()
	{

		$tahun 		= $_REQUEST['tahun'];
		
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak SPT Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak SPT Setahun")
								->setKeywords("WAPU");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_ttd = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_jabatan = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_right = array(
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

		$style_row_centre = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		  ),
		);

		$style_row_jud = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
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
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "KOMPILASI"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('B4:J4');
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "SPT MASA PPN JANUARI S/D DESEMBER ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B4:J4')->applyFromArray($style_row_centre);
		$excel->getActiveSheet()->mergeCells('B5:J5');
		$excel->setActiveSheetIndex(0)->setCellValue('B5', "REKAPITULASI PENYERAHAN BARANG DAN JASA (PPN)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B5:J5')->applyFromArray($style_row_centre);

		$excel->getActiveSheet()->mergeCells('D6:J6');
		$excel->setActiveSheetIndex(0)->setCellValue('D6', "PENYERAHAN BARANG DAN JASA (DPP)"); // Set kolom A1 dengan tulisan "DATA SISWA"	
		/*$excel->setActiveSheetIndex(0)->setCellValue('I1', "Setelah Pembetulan ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"*/
		
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Bulan"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D7', "PPN DIPUNGUT"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D8', "SENDIRI"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('E7', "PPN DIPUNGUT"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('E8', "PEMUNGUT"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('F7', "PPN TIDAK"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('F8', "DIPUNGUT"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('G7', "PPN DIBEBASKAN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('G8', ""); // Set kolom B3 dengan tulisan "NIS"
		/*$excel->setActiveSheetIndex(0)->setCellValue('H7', "PPN TIDAK DIPUNGUT"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('H8', ""); // Set kolom B3 dengan tulisan "NIS"*/
		$excel->setActiveSheetIndex(0)->setCellValue('I7', "TIDAK TERUTANG"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('I8', "PPN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('J7', "JUMLAH PENYERAHAN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('J8', ""); // Set kolom B3 dengan tulisan "NIS"

		$excel->getActiveSheet()->mergeCells('B7:B8');
		$excel->getActiveSheet()->mergeCells('C7:C8');
		$excel->getActiveSheet()->getStyle('D6:J6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('B6:B8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('C6:C8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D7:D8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('F7:F8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('H7:H8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('I7:I8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('J7:J8')->applyFromArray($style_row_jud);

			$queryExec	= "
			  SELECT case
        when masa_pajak = 'JAN' then 'Januari'
        when masa_pajak = 'FEB' then 'Februari'
        when masa_pajak = 'MAR' then 'Maret'
        when masa_pajak = 'APR' then 'April'
        when masa_pajak = 'MAY' then 'Mei'
        when masa_pajak = 'JUN' then 'Juni'
        when masa_pajak = 'JUL' then 'Juli'
        when masa_pajak = 'AUG' then 'Agustus'
        when masa_pajak = 'SEP' then 'September'
        when masa_pajak = 'OKT' then 'Oktober'
        when masa_pajak = 'NOV' then 'November'
        else 'Desember'
        end as masa_pajak,
             sum(sendiri)*1 sendiri,
             sum(oleh_pemungut)*1 oleh_pemungut,
             sum(dibebaskan)*1 dibebaskan,
             sum(bukan_ppn)*1 bukan_ppn,
             pembetulan_ke
        FROM (SELECT 
                     spl.jumlah_potong jumlah_potong,
                     sph.masa_pajak,
                     sph.bulan_pajak,
                     sph.pembetulan_ke,
                     --SUBSTR (NVL (SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN), 0, 2) kode_faktur
                     case
                       when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                       when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                       when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                       when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                       else NULL
                       end kode_faktur
                FROM simtax_pajak_lines spl
                     INNER JOIN
                        simtax_pajak_headers sph
                     ON SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                     INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
               WHERE sph.nama_pajak IN ('PPN KELUARAN')
                     AND SPL.IS_CHEKLIST = '1'
                     AND sph.tahun_pajak = '".$tahun."'
                     --ORDER BY SPH.PEMBETULAN_KE ASC
             ) PIVOT (SUM (jumlah_potong*1)
               FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))
               group by masa_pajak, pembetulan_ke, bulan_pajak
               order by bulan_pajak asc";
			
			$query 		= $this->db->query($queryExec);
			//$rowb		= $query->row();

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 9; // Set baris pertama untuk isi tabel adalah baris ke 4
			$ttl_sendiri 			= 0;								
			$ttl_pemungut 			= 0;
			$ttl_bukan_ppn			= 0;								
			$ttl_tdk_dipungut 		= 0;								
			$ttl_dibebaskan 		= 0;								
			$ttl_ppn_tdk_dipungut 	= 0;								
			$ttl_tdk_terutang 		= 0;							
			
						
			foreach($query->result_array() as $row)	{
				if($row['SENDIRI'] =="" && $row['OLEH_PEMUNGUT'] =="" && $row['BUKAN_PPN'] =="" && $row['DIBEBASKAN'] ==""){
				}else{
					
				$ttl_sendiri 			+= $row['SENDIRI'];
				$ttl_pemungut			+= $row['OLEH_PEMUNGUT'];
				$ttl_bukan_ppn			+= $row['BUKAN_PPN'];
				$ttl_dibebaskan 		+= $row['DIBEBASKAN'];

				$ttl 					 = 0;
				$excel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);	
				/*$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, ($row['MASA_PAJAK']) ? $row['MASA_PAJAK']:$row['MASA_PAJAK']." ". "Pembetulan"." ".$row['PEMBETULAN_KE']);*/	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['MASA_PAJAK']." ". "Pembetulan"." ".$row['PEMBETULAN_KE']);
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['SENDIRI']);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['OLEH_PEMUNGUT']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['BUKAN_PPN']);
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['DIBEBASKAN']);	
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, "");
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, "-");
				$ttl = $row['SENDIRI'] + $row['OLEH_PEMUNGUT'] + $row['BUKAN_PPN'] + $row['DIBEBASKAN'];
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $ttl);
				
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_right);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_right);	
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);	
				
				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}
			}		

		//end get detail
		//total

		$ttl_jumlah = 0;
		$ttl_jumlah = $ttl_sendiri + $ttl_pemungut + $ttl_bukan_ppn + $ttl_dibebaskan;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH");
		$excel->getActiveSheet()->mergeCells('B'.$numrow.':C'.$numrow);	
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ttl_sendiri);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ttl_pemungut);	
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $ttl_bukan_ppn);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ttl_dibebaskan);
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $ttl_ppn_tdk_dipungut);
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $ttl_tdk_terutang);
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $ttl_jumlah);

		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);

		$numrow += 1;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Catatan : Diisi berdasarkan angka-angka Dasar Penyerahan Barang dan Jasa yang ada pada SPT Masa PPN");
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_ttd);

		$numrow += 2;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "MENGETAHUI :");		
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_ttd);

		$cabang		= $this->session->userdata('kd_cabang');
		$queryExec1	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = 'DVP Pajak'
                            and nama_pajak = 'SPT PPN Masa'
                            and document_type = 'Ekualisasi' 
                            and kode_cabang ='".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
			$query1 	= $this->db->query($queryExec1);
			$rowCount 	= $query1->num_rows();

		if($rowCount > 0){
			$rowb1		= $query1->row();

			$ttd 					= $rowb1->URL_TANDA_TANGAN;
			$petugas_ttd			= $rowb1->NAMA_PETUGAS_PENANDATANGAN;
			$jabatan_petugas_ttd	= $rowb1->JABATAN_PETUGAS_PENANDATANGAN;

		$numrow += 1;
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$logo = $ttd; // Provide path to your logo file
		if(file_exists($logo)){
			$objDrawing->setPath($logo);  //setOffsetY has no effect
			$objDrawing->setCoordinates('C'.$numrow);
			$objDrawing->setHeight(80); // logo height
			$objDrawing->setWorksheet($excel->getActiveSheet());
		}

		$numrow += 4;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $petugas_ttd);		
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_jabatan);

		$numrow += 1;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $jabatan_petugas_ttd);		
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_jabatan);
		}
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(5); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(0); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("SPT PPN MASA Rekapitulasi");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="SPT PPN MASA Rekapitulasi.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_report_perbandingan()
	{
		$this->template->set('title', 'Lampiran III (Perbandingan)');
		$data['subtitle']   = "Cetak Lampiran III (Perbandingan)";
		$data['activepage'] = "laporan_ekualisasi";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_perbandingan',$data);		
	}

	function cetak_report_perbandingan()
	{

		$tahun 		= $_REQUEST['tahun'];
		
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak Perbandingan")
								->setSubject("Cectakan")
								->setDescription("Cetak Perbandingan")
								->setKeywords("Perbandingan");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  /*'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis*/
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   /*'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis*/
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_bottom = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_ttd = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_jabatan = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_right = array(
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

		$style_row_centre = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		  ),
		);

		$style_row_jud = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
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
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Gabungan Cabang cabang"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('B4:G4');
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "DAFTAR PERBANDINGAN ANTARA DPP SPT PPN DENGAN LAPORAN KEUANGAN"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B4:G4')->applyFromArray($style_row_centre);
		$excel->getActiveSheet()->mergeCells('B5:G5');
		$excel->setActiveSheetIndex(0)->setCellValue('B5', "TAHUN ".$tahun." AUDITED"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B5:G5')->applyFromArray($style_row_centre);
		
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('B7', "No."); // Set kolom A3 dengan tulisan "NO"
		$excel->setActiveSheetIndex(0)->setCellValue('C7', "URAIAN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D7', "DPP"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('D8', "SPT PPN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('E7', "PENDAPATAN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('E8', "LAP.KEUANGAN"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('F7', "SELISIH"); // Set kolom B3 dengan tulisan "NIS"
		$excel->setActiveSheetIndex(0)->setCellValue('G7', "PENJELASAN"); // Set kolom B3 dengan tulisan "NIS"

		$excel->getActiveSheet()->mergeCells('B7:B8');
		$excel->getActiveSheet()->mergeCells('C7:C8');
		$excel->getActiveSheet()->mergeCells('F7:F8');
		$excel->getActiveSheet()->mergeCells('G7:G8');
		$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('C7:C8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D7:D8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('F7:F8')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_row_jud);

		$queryExec	= "SELECT SUM (q_master.balance) balance,
                     SUM (q_pendapatan.sendiri) sendiri,
                     SUM (q_pendapatan.oleh_pemungut) oleh_pemungut,
                     SUM (q_pendapatan.dibebaskan) dibebaskan,
                     SUM (q_pendapatan.bukan_ppn) bukan_ppn
                FROM (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                               SUM (NVL (debit, 0) - 1) - SUM (NVL (credit, 0) - 1) balance,
                               masa_pajak
                          FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                         WHERE SUBSTR (kode_akun, 0, 3) IN
                                     ('701', '702', '703', '704', '705', '706', '707', '708')
                               AND tahun_pajak = '".$tahun."'
                      GROUP BY SUBSTR (kode_akun, 0, 3), masa_pajak) q_master,
                     (SELECT akun_pajak,
                             sendiri,
                             oleh_pemungut,
                             dibebaskan,
                             bukan_ppn,
                             masa_pajak,
                             pembetulan_ke
                        FROM (SELECT SUBSTR (spl.akun_pajak, 0, 3) akun_pajak,
                                     spl.jumlah_potong jumlah_potong,
                                     sph.masa_pajak,
                                     sph.pembetulan_ke,
                                     case
                                       when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
		                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
		                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
		                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                                       else NULL
                                       end kode_faktur
                                FROM    simtax_pajak_headers sph
                                     INNER JOIN
                                        simtax_pajak_lines spl
                                     ON SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                     INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                               WHERE sph.nama_pajak IN ('PPN MASUKAN', 'PPN KELUARAN')
                                     AND sph.tahun_pajak ='".$tahun."'
                                     AND SPL.IS_CHEKLIST = '1'
                                     AND (SPL.NO_FAKTUR_PAJAK IS NOT NULL
                                          OR SPL.NO_DOKUMEN_LAIN IS NOT NULL)
                                     AND SUBSTR (spl.akun_pajak, 0, 3) IN
                                              ('701', '702', '703','704', '705', '706', '707', '708')
                                     ORDER BY SPH.PEMBETULAN_KE ASC
                             )  PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                            WHERE q_master.kode_akun = q_pendapatan.akun_pajak
                             AND q_master.masa_pajak = Q_PENDAPATAN.masa_pajak
                            GROUP BY q_pendapatan.pembetulan_ke, q_master.masa_pajak
                            ORDER BY q_master.masa_pajak DESC";

		$query 		= $this->db->query($queryExec);

			$sendiri		= 0;
			$oleh_pemungut 	= 0;
			$dibebaskan 	= 0;

			foreach($query->result_array() as $row)	{

			$sendiri		+= $row['SENDIRI'];
			$oleh_pemungut 	+= $row['OLEH_PEMUNGUT'];
			$dibebaskan 	+= $row['DIBEBASKAN'];

			$excel->setActiveSheetIndex(0)->setCellValue('D10', $sendiri);
			$excel->setActiveSheetIndex(0)->setCellValue('D12', $oleh_pemungut);
			$excel->setActiveSheetIndex(0)->setCellValue('D15', $dibebaskan);

			$excel->getActiveSheet()->getStyle('D10')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D12')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D15')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			}

                    $queryExec1 ="
			select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) IN
                                   ('701', '702', '703', '704', '705', '706', '707', '708')
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.jumlah_potong
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                      --  LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                      --  AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                      --  LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                      --  AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) in  ('701', '702', '703', '704', '705', '706', '707', '708')
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1
		";

		$query1 	= $this->db->query($queryExec1);

		$sendiri 		= 0;
		$pemungut 		= 0;
		$dibebaskan 	= 0;
		$bukan_objek 	= 0;

		$ttl_sendiri 		= 0;
		$ttl_pemungut 		= 0;
		$ttl_dibebaskan 	= 0;
		$ttl_bukan_objek 	= 0;

		foreach($query1->result_array() as $row1)	{	

				$sendiri 		+= $row1['SENDIRI'];
				$pemungut 		+= $row1['OLEH_PEMUNGUT'];
				$dibebaskan 	+= $row1['DIBEBASKAN'];
				$bukan_objek 	+= $row1['BUKAN_PPN'];
				$total 			= $sendiri + $pemungut + $dibebaskan;

			}

			$queryExec2	="
			select
                                  q_master.kode_akun
                                , substr(q_master.kode_akun,0,3)  akun
                                , q_master.description_akun
                                , q_master.balance
                                , q_pendapatan.sendiri
                                , q_pendapatan.oleh_pemungut
                                , q_pendapatan.dibebaskan
                                , q_pendapatan.bukan_ppn
                                from 
                            (  SELECT kode_akun kode_akun,
                                            akun_description,
                                         (select ffvt.DESCRIPTION
                              from fnd_flex_values ffv
                                 , fnd_flex_values_tl ffvt
                                 , fnd_flex_value_sets ffvs
                            where ffv.flex_value_id = ffvt.flex_value_id     
                              and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                              and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                              and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3)) description_akun,
                                          SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                                FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                               WHERE kode_akun IN
                                           ('79101111','79101121','79101131','79101141'
                                            ,'79101161','79101172','79101181','79101182','79199999')
                               and tahun_pajak = '".$tahun."'
                            GROUP BY kode_akun,  akun_description) q_master
                            ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select spl.akun_pajak akun_pajak
                                   , spl.jumlah_potong
                                  , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                              from simtax_pajak_headers sph
                              inner join simtax_pajak_lines spl
                                  on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                 INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                                 -- LEFT JOIN SIMTAX_MASTER_SUPPLIER SMS ON SMS.VENDOR_ID = SPL.VENDOR_ID
                                 -- AND SMS.VENDOR_SITE_ID = SPL.VENDOR_SITE_ID
                                 -- LEFT JOIN SIMTAX_MASTER_PELANGGAN SMPEL ON SMPEL.CUSTOMER_ID = SPL.CUSTOMER_ID
                                --    AND SMPEL.ORGANIZATION_ID = SPL.ORGANIZATION_ID
                            where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                               and sph.tahun_pajak = '".$tahun."'
                               AND SPL.IS_CHEKLIST = '1'
                               --and sph.kode_cabang = '".$cabang."'
                               and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                               --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                               )
                               PIVOT (SUM (jumlah_potong*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))
															
														) q_pendapatan
                            where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                            order by 1
		";
		$query2 	= $this->db->query($queryExec2);

		$sendiri2 		= 0;
		$pemungut2 		= 0;
		$dibebaskan2 	= 0;
		$bukan_objek2 	= 0;

		foreach($query2->result_array() as $row2)	{
			$sendiri2 		+= $row2['SENDIRI'];
			$pemungut2 		+= $row2['OLEH_PEMUNGUT'];
			$dibebaskan2	+= $row2['DIBEBASKAN'];
			$bukan_objek2 	+= $row2['BUKAN_PPN'];
		}

		$queryExec3	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '311'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.jumlah_potong
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                           INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '311'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query3 	= $this->db->query($queryExec3);

		$sendiri3 		= 0;
		$pemungut3 		= 0;
		$dibebaskan3 	= 0;
		$bukan_objek3 	= 0;

		foreach($query3->result_array() as $row3)	{
			$sendiri3 		+= $row3['SENDIRI'];
			$pemungut3 		+= $row3['OLEH_PEMUNGUT'];
			$dibebaskan3	+= $row3['DIBEBASKAN'];
			$bukan_objek3 	+= $row3['BUKAN_PPN'];
		}

		$queryExec4	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '405'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.jumlah_potong
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '405'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query4 	= $this->db->query($queryExec4);

		$sendiri4 		= 0;
		$pemungut4 		= 0;
		$dibebaskan4 	= 0;
		$bukan_objek4 	= 0;

		foreach($query4->result_array() as $row4)	{
			$sendiri4 		+= $row4['SENDIRI'];
			$pemungut4 		+= $row4['OLEH_PEMUNGUT'];
			$dibebaskan4	+= $row4['DIBEBASKAN'];
			$bukan_objek4 	+= $row4['BUKAN_PPN'];
		}

		$queryExec5	= "select
                          q_master.kode_akun
                        , q_master.kode_akun || '00000' akun
                        , q_master.description_akun
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                                 (select ffvt.DESCRIPTION
                      from fnd_flex_values ffv
                         , fnd_flex_values_tl ffvt
                         , fnd_flex_value_sets ffvs
                    where ffv.flex_value_id = ffvt.flex_value_id     
                      and ffvs.FLEX_VALUE_SET_ID = ffv.FLEX_VALUE_SET_ID
                      and ffvs.FLEX_VALUE_SET_NAME = 'PI2_ACCOUNT'
                      and ffv.FLEX_VALUE like SUBSTR (kode_akun, 0, 3) || '00000') description_akun,
                                  SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) = '111'
                       and tahun_pajak = '".$tahun."'
                    GROUP BY SUBSTR (kode_akun, 0, 3)) q_master
                    ,(select akun_pajak, sendiri, oleh_pemungut, dibebaskan, bukan_ppn from (select substr(spl.akun_pajak,0,3) akun_pajak
                           , spl.jumlah_potong
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
					   --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) = '111'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                             FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)    
                    order by 1";
		$query5 	= $this->db->query($queryExec5);

		$sendiri5 		= 0;
		$pemungut5 		= 0;
		$dibebaskan5 	= 0;
		$bukan_objek5 	= 0;

		foreach($query5->result_array() as $row5)	{
			$sendiri5 		+= $row5['SENDIRI'];
			$pemungut5 		+= $row5['OLEH_PEMUNGUT'];
			$dibebaskan5	+= $row5['DIBEBASKAN'];
			$bukan_objek5 	+= $row5['BUKAN_PPN'];
		}

				$ttl_sendiri 		= $sendiri + $sendiri2 + $sendiri3 + $sendiri4 + $sendiri4;
				$ttl_pemungut 		= $pemungut + $pemungut2 + $pemungut3 + $pemungut4 + 					  $pemungut5;
				$ttl_dibebaskan 	= $dibebaskan + $dibebaskan2 + $dibebaskan3 + $dibebaskan4 					 + $dibebaskan5;
				$ttl_bukan_objek 	= $bukan_objek + $bukan_objek2 + $bukan_objek3 + 						  $bukan_objek4 + $bukan_objek5;

				$excel->setActiveSheetIndex(0)->setCellValue('B9', "1");	
				$excel->setActiveSheetIndex(0)->setCellValue('C9', "Penyerahan kepada bukan pemungut");
				$excel->setActiveSheetIndex(0)->setCellValue('C10', "(PPN harus dipungut sendiri)");
				$excel->setActiveSheetIndex(0)->setCellValue('E10', $ttl_sendiri);
				$excel->setActiveSheetIndex(0)->setCellValue('F10', '=D10-E10');
				$excel->getActiveSheet()->getStyle('E10')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F10')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->setActiveSheetIndex(0)->setCellValue('B12', "2");	
				$excel->setActiveSheetIndex(0)->setCellValue('C12', "Penyerahan kepada pemungut");
				$excel->setActiveSheetIndex(0)->setCellValue('E12', $ttl_pemungut);
				$excel->setActiveSheetIndex(0)->setCellValue('F12', '=D12-E12');
				$excel->getActiveSheet()->getStyle('E12')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F12')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->setActiveSheetIndex(0)->setCellValue('B15', "3");	
				$excel->setActiveSheetIndex(0)->setCellValue('C15', "Penyerahan yang PPN-nya :");
				$excel->setActiveSheetIndex(0)->setCellValue('C16', "Dibebaskan/Tidak Dipungut");
				$excel->setActiveSheetIndex(0)->setCellValue('E15', $ttl_dibebaskan);
				$excel->setActiveSheetIndex(0)->setCellValue('F15', '=D15-E15');
				$excel->getActiveSheet()->getStyle('E15')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F15')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->setActiveSheetIndex(0)->setCellValue('B20', "4");	
				$excel->setActiveSheetIndex(0)->setCellValue('C20', "Penyerahan yang tidak terutang PPN");
				$excel->setActiveSheetIndex(0)->setCellValue('C21', "& bukan Objek PPN");
				$excel->setActiveSheetIndex(0)->setCellValue('E20', $ttl_bukan_objek);
				$excel->setActiveSheetIndex(0)->setCellValue('F20', '=D20-E20');
				$excel->getActiveSheet()->getStyle('E20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F20')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->setActiveSheetIndex(0)->setCellValue('B24', "a.");	
				$excel->setActiveSheetIndex(0)->setCellValue('C24', "Pendapatan Diluar Usaha");
				$excel->setActiveSheetIndex(0)->setCellValue('C25', "Laba Selisih Kurs");
				$excel->setActiveSheetIndex(0)->setCellValue('C26', "Bunga Deposito");
				$excel->setActiveSheetIndex(0)->setCellValue('C26', "Jasa Giro");
				$excel->setActiveSheetIndex(0)->setCellValue('C28', "Denda");
				$excel->setActiveSheetIndex(0)->setCellValue('C29', "Bagian Laba Perusahaan Asosiasi(JICT&EDI)& PT Lainnya");
				$excel->setActiveSheetIndex(0)->setCellValue('C30', "Bagian Laba KSO Koj");
				$excel->setActiveSheetIndex(0)->setCellValue('C30', "Bagian Laba PT Lain");
				$excel->setActiveSheetIndex(0)->setCellValue('C30', "Unrealized Foreign");
				$excel->setActiveSheetIndex(0)->setCellValue('B32', "b.");
				$excel->setActiveSheetIndex(0)->setCellValue('C32', "Penyesuaian Rekening Neraca");
				$excel->setActiveSheetIndex(0)->setCellValue('C33', "Pendapatan yg Diterima Di Muka Jangka Panjang");
				$excel->setActiveSheetIndex(0)->setCellValue('C34', "Pendapatan Yang Masih Akan Diterima");
				$excel->setActiveSheetIndex(0)->setCellValue('D18', '=SUM(D10:D12:D15)');
				$excel->setActiveSheetIndex(0)->setCellValue('E18', '=SUM(E10:E15)');
				$excel->setActiveSheetIndex(0)->setCellValue('F18', '=SUM(F10:F12:F15)');

				$excel->getActiveSheet()->getStyle('B35:B37')->applyFromArray($style_row_bottom);
				$excel->setActiveSheetIndex(0)->setCellValue('C36', "JUMLAH");
				$excel->getActiveSheet()->getStyle('C35:C37')->applyFromArray($style_row_bottom);
				$excel->setActiveSheetIndex(0)->setCellValue('D36', '=D20+D18');
				$excel->getActiveSheet()->getStyle('D35:D37')->applyFromArray($style_row_bottom);
				$excel->setActiveSheetIndex(0)->setCellValue('E36', '=E20+E18');
				$excel->getActiveSheet()->getStyle('E35:E37')->applyFromArray($style_row_bottom);
				$excel->setActiveSheetIndex(0)->setCellValue('F36', '=F20+F18');
				$excel->getActiveSheet()->getStyle('F35:F37')->applyFromArray($style_row_bottom);
				$excel->getActiveSheet()->getStyle('G35:G37')->applyFromArray($style_row_bottom);

				$excel->getActiveSheet()->getStyle('D36')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E36')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F36')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->getActiveSheet()->getStyle('D18')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E18')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F18')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				
				$excel->getActiveSheet()->getStyle('B9')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B10')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B11')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B12')->applyFromArray($style_row);	
				$excel->getActiveSheet()->getStyle('B13')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B14')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B15')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B16')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B17')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B18')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B19')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B20')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B21')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B22')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B23')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B24')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B25')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B26')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B27')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B28')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B29')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B30')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B31')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B32')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B33')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('B34')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C9')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C10')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C11')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C12')->applyFromArray($style_row);	
				$excel->getActiveSheet()->getStyle('C13')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C14')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C15')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C16')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C17')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C18')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C19')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C20')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C21')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C22')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C23')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C24')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C25')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C26')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C27')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C28')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C29')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C30')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C31')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C32')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C33')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C34')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D9')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D10')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D11')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D12')->applyFromArray($style_row);	
				$excel->getActiveSheet()->getStyle('D13')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D14')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D15')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D16')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D17')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D18')->applyFromArray($style_row_bottom);
				$excel->getActiveSheet()->getStyle('D19')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D20')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D21')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D22')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D23')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D24')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D25')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D26')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D27')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D28')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D29')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D30')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D31')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D32')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D33')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D34')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E9')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E10')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E11')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E12')->applyFromArray($style_row);	
				$excel->getActiveSheet()->getStyle('E13')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E14')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E15')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E16')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E17')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E18')->applyFromArray($style_row_bottom);
				$excel->getActiveSheet()->getStyle('E19')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E20')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E21')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E22')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E23')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E24')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E25')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E26')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E27')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E28')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E29')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E30')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E31')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E32')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E33')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E34')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F9')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F10')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F11')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F12')->applyFromArray($style_row);	
				$excel->getActiveSheet()->getStyle('F13')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F14')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F15')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F16')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F17')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F18')->applyFromArray($style_row_bottom);
				$excel->getActiveSheet()->getStyle('F19')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F20')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F21')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F22')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F23')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F24')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F25')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F26')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F27')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F28')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F29')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F30')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F31')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F32')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F33')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F34')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G9')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G10')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G11')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G12')->applyFromArray($style_row);	
				$excel->getActiveSheet()->getStyle('G13')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G14')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G15')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G16')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G17')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G18')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G19')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G20')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G21')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G22')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G23')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G24')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G25')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G26')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G27')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G28')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G29')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G30')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G31')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G32')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G33')->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G34')->applyFromArray($style_row);	

		$excel->setActiveSheetIndex(0)->setCellValue('C39', "MENGETAHUI :");		
		$excel->getActiveSheet()->getStyle('C39')->applyFromArray($style_row_ttd);

		$cabang		= $this->session->userdata('kd_cabang');
		$queryExec1	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = 'DVP Pajak'
                            and nama_pajak = 'SPT PPN Masa'
                            and document_type = 'Ekualisasi' 
                            and kode_cabang ='".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
			$query1 	= $this->db->query($queryExec1);
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
			$objDrawing->setCoordinates('C41');
			$objDrawing->setHeight(80); // logo height
			$objDrawing->setWorksheet($excel->getActiveSheet());
		}

		$excel->setActiveSheetIndex(0)->setCellValue('C45', $petugas_ttd);
		$excel->setActiveSheetIndex(0)->setCellValue('C46', $jabatan_petugas_ttd);
		}
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(5); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(65); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(80); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Perbandingan");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Perbandingan.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_lap_keu()
	{
		$this->template->set('title', 'Lampiran II (Rincian)');
		$data['subtitle']   = "Cetak Lampiran II (Rincian)";
		$data['activepage'] = "laporan_ekualisasi";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_keu',$data);		
	}

	function cetak_lap_keu()
	{

		$tahun 		= $_REQUEST['tahun'];
		$cabang		= $_REQUEST['cabang'];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak Lap Keu P12")
								->setSubject("Cetakan")
								->setDescription("Cetak Lap Keu P12A")
								->setKeywords("Laporan");
								
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

		$style_col1 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col3 = array(
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_col5 = array(
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

		$style_colhead = array(
			'font' => array('bold' => true), // Set font nya jadi bold
		);

		$style_coltotal = array(
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_colselisih = array(
			'font' => array('bold' => true), // Set font nya jadi bold
			'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		);
		
		$noBorder_Bold_Tengah = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);		
		
		$border_noBold_kiri = array(
		        'font' => array('bold' => false), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi di tengah secara vertical (middle)
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_hsl = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		);

		$border_Bold = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  ),
		);
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Ekualisasi dengan pendapatan"); 
		$excel->getActiveSheet()->mergeCells('B2:B6');		
		$excel->getActiveSheet()->getStyle('B2:H6')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('C2:H6')->applyFromArray($border_noBold_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('C2', "Nama WP");
		$excel->setActiveSheetIndex(0)->setCellValue('C3', "NPWP"); 
		$excel->setActiveSheetIndex(0)->setCellValue('C4', "Jenis Pajak"); 
		$excel->setActiveSheetIndex(0)->setCellValue('C5', "Tahun Pajak");
		$excel->setActiveSheetIndex(0)->setCellValue('C6', "Cabang"); 
		
		$excel->setActiveSheetIndex(0)->setCellValue('D2', ":  PT. (PERSERO) PELABUHAN INDONESIA II"); 
		$excel->setActiveSheetIndex(0)->setCellValue('D3', ":  01.061.005.3-093.000"); 
		$excel->setActiveSheetIndex(0)->setCellValue('D4', ":  PPN"); 
		$excel->setActiveSheetIndex(0)->setCellValue('D5', ":  ".$tahun."");
		$excel->setActiveSheetIndex(0)->setCellValue('D6', ":  Cabang cabang");

		/*$excel->getActiveSheet()->getStyle('B151:B151')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B151:B151')->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('B151:D151')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('E151:E151')->applyFromArray($style_col5);
		$excel->getActiveSheet()->getStyle('F151:F151')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('G151:G151')->applyFromArray($style_col1);
		$excel->getActiveSheet()->getStyle('H151:H151')->applyFromArray($style_col3);*/

		$excel->setActiveSheetIndex(0)->setCellValue('B7', "URAIAN"); 
		$excel->getActiveSheet()->mergeCells('B7:B8');		
		$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Jumlah menurut");
		$excel->setActiveSheetIndex(0)->setCellValue('C8', "Sub B.B");		
		$excel->getActiveSheet()->getStyle('C7:C8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('D7', "PPN Dipungut Sendiri");
		$excel->getActiveSheet()->mergeCells('D7:D8');		
		$excel->getActiveSheet()->getStyle('D7:D8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('E7', "PPN Dipungut Oleh");
		$excel->setActiveSheetIndex(0)->setCellValue('E8', "Pemungut");
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('F7', "PPN Dibebaskan/DTP"); 
		$excel->getActiveSheet()->mergeCells('F7:F8');
		$excel->getActiveSheet()->getStyle('F7:F8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('G7', "PPN Tidak Dipungut"); 
		$excel->getActiveSheet()->mergeCells('G7:G8');
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($style_col);

		$excel->setActiveSheetIndex(0)->setCellValue('H7', "Tidak terutang PPN");
		$excel->getActiveSheet()->mergeCells('H7:H8');
		$excel->getActiveSheet()->getStyle('H7:H8')->applyFromArray($style_col);
		
		$no = 1;
		$numrow = 12;
		$queryExec	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =701
                       and substr(kode_jasa,3,2) in ('11','12','13','14','15','19')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                           INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                       and substr(spl.kode_pst_pelayanan,3,2) in ('11','12','13','14','15','19')
                       and sph.tahun_pajak = '".$tahun."'
                       AND SPL.IS_CHEKLIST = '1'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =701
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";
		$query 		= $this->db->query($queryExec);
		$balance701 		= 0;
		$sendiri701 		= 0;
		$oleh_pemungut701 	= 0;
		$dibebaskan701 		= 0;
		$bukan_ppn701 		= 0;

		foreach($query->result_array() as $row)	{
			$balance701 		+= $row['BALANCE'];
			$sendiri701 		+= $row['SENDIRI'];
			$oleh_pemungut701 	+= $row['OLEH_PEMUNGUT'];
			$dibebaskan701 		+= $row['DIBEBASKAN'];
			$bukan_ppn701 		+= $row['BUKAN_PPN'];

			$excel->setActiveSheetIndex(0)->setCellValue('B10',"PELAYANAN JASA KAPAL");
			$excel->setActiveSheetIndex(0)->setCellValue('B11',"Pelabuhan Umum");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$no++;
		$numrow++;
		}

		$no1 = 1;
		$numrow += 0;
		$queryExec1	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =701
                       and substr(kode_jasa,3,2) in ('21','22','23','24','25','29')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                           , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,3,2) in ('21','22','23','24','25','29')
                       and sph.tahun_pajak = '".$tahun."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =701
                      AND SPL.IS_CHEKLIST = '1'
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query1 	= $this->db->query($queryExec1);
		$balance701d 		= 0;
		$sendiri701d 		= 0;
		$oleh_pemungut701d 	= 0;
		$dibebaskan701d 	= 0;
		$bukan_ppn701d 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"DUKS & Pelsus");

		foreach($query1->result_array() as $row1)	{
			$balance701d += $row1['BALANCE'];
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row1['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row1['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row1['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row1['OLEH_PEMUNGUT']);

			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row1['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row1['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$no1++;
		$numrow++;
		}

		$total701 					= 0;
		$total_sendiri701 			= 0;
		$total_oleh_pemungut701 	= 0;
		$total_dibebaskan701 		= 0;
		$total_bukan_ppn701 		= 0;

		$total701 					= $balance701 + $balance701d;
		$total_sendiri701 			= $sendiri701 + $sendiri701d;
		$total_oleh_pemungut701 	= $oleh_pemungut701 + $oleh_pemungut701d;
		$total_dibebaskan701 		= $dibebaskan701 + $dibebaskan701d;
		$total_bukan_ppn701 		= $bukan_ppn701 + $bukan_ppn701d;
		
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow,"JUMLAH PEND. JASA KAPAL  ( A )");
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow,$total701);
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow,$total_sendiri701);
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow,$total_oleh_pemungut701);
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow,$total_dibebaskan701);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow,$total_bukan_ppn701);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);
		/*$excel->setActiveSheetIndex(0)->setCellValue('C25','=SUM(C12:C13:C14:C15:C16:C19:C20:C21:C22:C23)');
		$excel->setActiveSheetIndex(0)->setCellValue('D25','=SUM(D12:D13:D14:D15:D16:D19:D20:D21:D22:D23)');
		$excel->setActiveSheetIndex(0)->setCellValue('E25','=SUM(E12:E13:E14:E15:E16:E19:E20:E21:E22:E23)');
		$excel->setActiveSheetIndex(0)->setCellValue('F25','=SUM(F12:F13:F14:F15:F16:F19:F20:F21:F22:F23)');
		$excel->setActiveSheetIndex(0)->setCellValue('G25','=SUM(G12:G13:G14:G15:G16:G19:G20:G21:G22:G23)');*/

		$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$numrow += 2;
		$queryExec2	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =702
                       and substr(kode_jasa,2,3) in ('211','212','213','219')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in ('211','212','213','219')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =702
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query2 		= $this->db->query($queryExec2);
		$balance702 		= 0;
		$sendiri702 		= 0;
		$oleh_pemungut702 	= 0;
		$dibebaskan702 		= 0;
		$bukan_ppn702 		= 0;

		$jasa_description = "";

		$i=1;

		foreach($query2->result_array() as $row2)	{
			$balance702 		+= $row2['BALANCE'];
			$sendiri702 		+= $row2['SENDIRI'];
			$oleh_pemungut702 	+= $row2['OLEH_PEMUNGUT'];
			$dibebaskan702 		+= $row2['DIBEBASKAN'];
			$bukan_ppn702 		+= $row2['BUKAN_PPN'];

			if($i == 1){
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"PELAYANAN JASA BARANG");
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"Pelabuhan Umum");
				$numrow2 = $numrow;
			}

			// $numrow +=1;
			
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow2, $row2['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow2, $row2['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow2, $row2['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow2, $row2['OLEH_PEMUNGUT']);

			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow2, $row2['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow2, $row2['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow2)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow2)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow2)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow2)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow2)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow2)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow2)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow2)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow2)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow2)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow2)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow2)->applyFromArray($style_row_hsl);

			$jasa_description = $row2['JASA_DESCRIPTION'];

		$numrow2++;
		$i++;
		$numrow = $numrow2;
		}

		$numrow += 0;
		$queryExec3	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =702
                       and substr(kode_jasa,2,3) in ('221','222','223','229','230')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in ('221','222','223','229','230')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =702
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query3 		= $this->db->query($queryExec3);
		$balance702d 		= 0;
		$sendiri702d 		= 0;
		$oleh_pemungut702d 	= 0;
		$dibebaskan702d 	= 0;
		$bukan_ppn702d 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"DUKS & Pelsus");
		foreach($query3->result_array() as $row3)	{
			$balance702d 		+= $row3['BALANCE'];
			$sendiri702d 		+= $row3['SENDIRI'];
			$oleh_pemungut702d 	+= $row3['OLEH_PEMUNGUT'];
			$dibebaskan702d 	+= $row3['DIBEBASKAN'];
			$bukan_ppn702d 		+= $row3['BUKAN_PPN'];
			//$excel->setActiveSheetIndex(0)->setCellValue('B33',"DUKS & Pelsus");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row3['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row3['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row3['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row3['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row3['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row3['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

		$total702 					= 0;
		$total_sendiri702 			= 0;
		$total_oleh_pemungut702 	= 0;
		$total_dibebaskan702 		= 0;
		$total_dbukan_ppn702 		= 0;

		$total702 					= $balance702 + $balance702d;
		$total_sendiri702 			= $sendiri702 + $sendiri702d;
		$total_oleh_pemungut702 	= $oleh_pemungut702 + $oleh_pemungut702d;
		$total_dibebaskan702 		= $dibebaskan702 + $dibebaskan702d;
		$total_dbukan_ppn702 		= $bukan_ppn702 + $bukan_ppn702d;

		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. JASA BARANG  ( B )");
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $total702);
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $total_sendiri702);
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $total_oleh_pemungut702);
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $total_dibebaskan702);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $total_dbukan_ppn702);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);
		/*$excel->setActiveSheetIndex(0)->setCellValue('C40', '=SUM(C29:C38)');
		$excel->setActiveSheetIndex(0)->setCellValue('D40', '=SUM(D29:D38)');
		$excel->setActiveSheetIndex(0)->setCellValue('E40', '=SUM(E29:E38)');
		$excel->setActiveSheetIndex(0)->setCellValue('F40', '=SUM(F29:F38)');
		$excel->setActiveSheetIndex(0)->setCellValue('G40', '=SUM(G29:G38)');*/

		$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$numrow += 2;
		$queryExec4	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,2,3) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =703
                       and substr(kode_jasa,2,3) in 
                       ('311','312','313','314','319'
                       ,'321','322','323','324','325'
                       ,'326','327','330','331','332'
                       ,'335','336','337','338','339'
                       ,'340','341','342','343','344'
                       ,'345','350','351','352','353'
                       ,'354','355','356','357','358'
                       ,'359','370','371','372','380'
                       ,'381','382','399')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,2,3), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in
                       ('311','312','313','314','319'
                       ,'321','322','323','324','325'
                       ,'326','327','330','331','332'
                       ,'335','336','337','338','339'
                       ,'340','341','342','343','344'
                       ,'345','350','351','352','353'
                       ,'354','355','356','357','358'
                       ,'359','370','371','372','380'
                       ,'381','382','399')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =703
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query4 		= $this->db->query($queryExec4);
		$balance703 		= 0;
		$sendiri703 		= 0;
		$oleh_pemungut703 	= 0;
		$dibebaskan703 		= 0;
		$bukan_ppn703 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"PENGUSAHAAN ALAT");
		foreach($query4->result_array() as $row4)	{
			$balance703 		+= $row4['BALANCE'];
			$sendiri703 		+= $row4['SENDIRI'];
			$oleh_pemungut703 	+= $row4['OLEH_PEMUNGUT'];
			$dibebaskan703 		+= $row4['DIBEBASKAN'];
			$bukan_ppn703 		+= $row4['BUKAN_PPN'];
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row4['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row4['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row4['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row4['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row4['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row4['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. PENGUSHN ALAT ( C )");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance703);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri703);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut703);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan703);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppn703);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);/*
			$excel->setActiveSheetIndex(0)->setCellValue('C87', '=SUM(C43:C85)');
			$excel->setActiveSheetIndex(0)->setCellValue('D87', '=SUM(D43:D85)');
			$excel->setActiveSheetIndex(0)->setCellValue('E87', '=SUM(E43:E85)');
			$excel->setActiveSheetIndex(0)->setCellValue('F87', '=SUM(F43:F85)');
			$excel->setActiveSheetIndex(0)->setCellValue('G87', '=SUM(G43:G85)');*/

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$numrow += 2;
		$queryExec5	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,2,3) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =704
                       and substr(kode_jasa,2,3) in 
                       ('401','402','403','404','405'
                       ,'406','407','408','409','410'
                       ,'411','412','413','414','415'
                       ,'416','417','418','419','420'
                       ,'421','422','423','424','431'
                       ,'432','433','434','435','436'
                       ,'437','438','499')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,2,3), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in
                       ('401','402','403','404','405'
                       ,'406','407','408','409','410'
                       ,'411','412','413','414','415'
                       ,'416','417','418','419','420'
                       ,'421','422','423','424','431'
                       ,'432','433','434','435','436'
                       ,'437','438','499')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =704
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query5 		= $this->db->query($queryExec5);

		$balance704 		= 0;
		$sendiri704 		= 0;
		$oleh_pemungut704 	= 0;
		$dibebaskan704 		= 0;
		$bukan_ppn704 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"PELAYANAN TERMINAL");
		foreach($query5->result_array() as $row5)	{
			$balance704 		+= $row5['BALANCE'];
			$sendiri704 		+= $row5['SENDIRI'];
			$oleh_pemungut704 	+= $row5['OLEH_PEMUNGUT'];
			$dibebaskan704 		+= $row5['DIBEBASKAN'];
			$bukan_ppn704 		+= $row5['BUKAN_PPN'];	
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row5['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row5['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row5['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row5['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row5['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row5['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. PEL TERMINAL ( D )");
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance704);
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri704);
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut704);
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan704);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppn704);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);/*
			$excel->setActiveSheetIndex(0)->setCellValue('C87', '=SUM(C43:C85)');
			$excel->setActiveSheetIndex(0)->setCellValue('D87', '=SUM(D43:D85)');
			$excel->setActiveSheetIndex(0)->setCellValue('E87', '=SUM(E43:E85)');
			$excel->setActiveSheetIndex(0)->setCellValue('F87', '=SUM(F43:F85)');
			$excel->setActiveSheetIndex(0)->setCellValue('G87', '=SUM(G43:G85)');*/

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$numrow += 2;
		$queryExec6	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,2,3) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =705
                       and substr(kode_jasa,2,3) in 
                       ('501','502','503','510','511'
                       ,'512','513','514','515','516'
                       ,'517','530','531','532','533'
                       ,'534','535','536','537','538'
                       ,'539','560','561','562','563'
                       ,'564','565','567','568','569'
                       ,'570','571','572' ,'573','574'
                       ,'575','576','580' ,'581','582'
                       ,'583')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,2,3), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in
                       ('501','502','503','510','511'
                       ,'512','513','514','515','516'
                       ,'517','530','531','532','533'
                       ,'534','535','536','537','538'
                       ,'539','560','561','562','563'
                       ,'564','565','567','568','569'
                       ,'570','571','572' ,'573','574'
                       ,'575','576','580' ,'581','582'
                       ,'583')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =705
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query6 		= $this->db->query($queryExec6);
		$balance705 		= 0;
		$sendiri705 		= 0;
		$oleh_pemungut705 	= 0;
		$dibebaskan705 		= 0;
		$bukan_ppn705 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"PELAYANAN TERMINAL PETIKEMAS");
		foreach($query6->result_array() as $row6)	{
			$balance705 		+= $row6['BALANCE'];
			$sendiri705 		+= $row6['SENDIRI'];
			$oleh_pemungut705 	+= $row6['OLEH_PEMUNGUT'];
			$dibebaskan705 		+= $row6['DIBEBASKAN'];
			$bukan_ppn705 		+= $row6['BUKAN_PPN'];	
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row6['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row6['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row6['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row6['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row6['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row6['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. TPK ( E )");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance705);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri705);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut705);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan705);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppn705);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);/*
			$excel->setActiveSheetIndex(0)->setCellValue('C87', '=SUM(C43:C85)');
			$excel->setActiveSheetIndex(0)->setCellValue('D87', '=SUM(D43:D85)');
			$excel->setActiveSheetIndex(0)->setCellValue('E87', '=SUM(E43:E85)');
			$excel->setActiveSheetIndex(0)->setCellValue('F87', '=SUM(F43:F85)');
			$excel->setActiveSheetIndex(0)->setCellValue('G87', '=SUM(G43:G85)');*/

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$numrow += 2;
		$queryExec7	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,2,3) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =706
                       and substr(kode_jasa,2,3) in 
                       ('601','602','603','604','605'
                       ,'606','607','608','609','610'
                       ,'611','620','621','622','623'
                       ,'699')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,2,3), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in
                       ('601','602','603','604','605'
                       ,'606','607','608','609','610'
                       ,'611','620','621','622','623'
                       ,'699')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =706
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query7 		= $this->db->query($queryExec7);
		$balance706 		= 0;
		$sendiri706 		= 0;
		$oleh_pemungut706 	= 0;
		$dibebaskan706 		= 0;
		$bukan_ppn706 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"PENGUSAH TANAH, BANGUNAN, AIR & LISTRIK");
		foreach($query7->result_array() as $row7)	{
			$balance706 		+= $row7['BALANCE'];
			$sendiri706 		+= $row7['SENDIRI'];
			$oleh_pemungut706 	+= $row7['OLEH_PEMUNGUT'];
			$dibebaskan706 		+= $row7['DIBEBASKAN'];
			$bukan_ppn706 		+= $row7['BUKAN_PPN'];	
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row7['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row7['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row7['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row7['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row7['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row7['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. PENG. TBAL ( F )");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance706);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri706);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut706);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan706);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppn706);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);/*
			$excel->setActiveSheetIndex(0)->setCellValue('C87', '=SUM(C43:C85)');
			$excel->setActiveSheetIndex(0)->setCellValue('D87', '=SUM(D43:D85)');
			$excel->setActiveSheetIndex(0)->setCellValue('E87', '=SUM(E43:E85)');
			$excel->setActiveSheetIndex(0)->setCellValue('F87', '=SUM(F43:F85)');
			$excel->setActiveSheetIndex(0)->setCellValue('G87', '=SUM(G43:G85)');*/

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$numrow += 2;
		$queryExec8	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,2,3) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =707
                       and substr(kode_jasa,2,3) in 
                       ('701','702','711','712','713'
                       ,'719','721','722','723','724'
                       ,'730','731','732','733','734'
                       ,'741','742','751','752','753'
                       ,'754','755','759','761','762'
                       ,'763','765','766','767','768'
                       ,'769','770','771','772','773'
                       ,'774','775','776','777','778'
                       ,'779','780','781','782','783'
                       ,'784','785','786','787','788'
                       ,'789','790','791','792','793'
                       ,'794','795','796','799')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,2,3), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in
                        ('701','702','711','712','713'
                       ,'719','721','722','723','724'
                       ,'730','731','732','733','734'
                       ,'741','742','751','752','753'
                       ,'754','755','759','761','762'
                       ,'763','765','766','767','768'
                       ,'769','770','771','772','773'
                       ,'774','775','776','777','778'
                       ,'779','780','781','782','783'
                       ,'784','785','786','787','788'
                       ,'789','790','791','792','793'
                       ,'794','795','796','799')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =707
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query8 		= $this->db->query($queryExec8);
		$balance707 		= 0;
		$sendiri707 		= 0;
		$oleh_pemungut707 	= 0;
		$dibebaskan707 		= 0;
		$bukan_ppn707 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"FASILITAS RUPA-RUPA USAHA");
		foreach($query8->result_array() as $row8)	{
			$balance707 		+= $row8['BALANCE'];
			$sendiri707 		+= $row8['SENDIRI'];
			$oleh_pemungut707 	+= $row8['OLEH_PEMUNGUT'];
			$dibebaskan707 		+= $row8['DIBEBASKAN'];
			$bukan_ppn707 		+= $row8['BUKAN_PPN'];	
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row8['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row8['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row8['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row8['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row8['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row8['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. FAS. RP2 USAHA ( G )");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance707);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri707);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut707);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan707);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppn707);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);/*
			$excel->setActiveSheetIndex(0)->setCellValue('C87', '=SUM(C43:C85)');
			$excel->setActiveSheetIndex(0)->setCellValue('D87', '=SUM(D43:D85)');
			$excel->setActiveSheetIndex(0)->setCellValue('E87', '=SUM(E43:E85)');
			$excel->setActiveSheetIndex(0)->setCellValue('F87', '=SUM(F43:F85)');
			$excel->setActiveSheetIndex(0)->setCellValue('G87', '=SUM(G43:G85)');*/

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$numrow += 2;
		$queryExec9	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,2,3) kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =708
                       and substr(kode_jasa,2,3) in 
                       ('802','803','804','805','806'
                       ,'807','808','809','810','811'
                       ,'812')
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,2,3), jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and substr(spl.kode_pst_pelayanan,2,3) in
                        ('802','803','804','805','806'
                       ,'807','808','809','810','811'
                       ,'812')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =708
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                     where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    and q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query9 		= $this->db->query($queryExec9);
		$balance708 		= 0;
		$sendiri708 		= 0;
		$oleh_pemungut708 	= 0;
		$dibebaskan708 		= 0;
		$bukan_ppn708 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"KERJASAMA DENGAN MITRA USAHA");
		foreach($query9->result_array() as $row9)	{
			$balance708 		+= $row9['BALANCE'];
			$sendiri708 		+= $row9['SENDIRI'];
			$oleh_pemungut708 	+= $row9['OLEH_PEMUNGUT'];
			$dibebaskan708 		+= $row9['DIBEBASKAN'];
			$bukan_ppn708 		+= $row9['BUKAN_PPN'];	
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row9['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row9['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row9['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row9['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row9['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row9['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PEND. KSMU ( H )");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance708);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri708);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut708);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan708);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppn708);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);/*
			$excel->setActiveSheetIndex(0)->setCellValue('C87', '=SUM(C43:C85)');
			$excel->setActiveSheetIndex(0)->setCellValue('D87', '=SUM(D43:D85)');
			$excel->setActiveSheetIndex(0)->setCellValue('E87', '=SUM(E43:E85)');
			$excel->setActiveSheetIndex(0)->setCellValue('F87', '=SUM(F43:F85)');
			$excel->setActiveSheetIndex(0)->setCellValue('G87', '=SUM(G43:G85)');*/

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$numrow +=2;
		$total701_708 					= 0;
		$total_sendiri_701_708 			= 0;
		$total_oleh_pemungut_701_708 	= 0;
		$total_dibebaskan_701_708 		= 0;
		$total_bukan_ppn_701_708 		= 0;

		$total701_708 					= $total701 + $total702 + $balance703 + $balance704 + $balance705 + 							 $balance706 + $balance707 + $balance708;
		$total_sendiri_701_708 			= $total_sendiri701 + $total_sendiri702 + $sendiri703 + $sendiri704 							 + $sendiri705 + $sendiri706 + $sendiri707 + $sendiri708;
		$total_oleh_pemungut_701_708 	= $total_oleh_pemungut701 + $total_oleh_pemungut702 + 										  $oleh_pemungut703 + $oleh_pemungut704 + $oleh_pemungut705 + 								  $oleh_pemungut706 + $oleh_pemungut707 + $oleh_pemungut708;
		$total_dibebaskan_701_708 		= $total_oleh_pemungut701 + $total_oleh_pemungut702 + 								$oleh_pemungut703 + $oleh_pemungut704 + $oleh_pemungut705 + $oleh_pemungut706 + $oleh_pemungut707 + $oleh_pemungut708;
		$total_bukan_ppn_701_708 		= $total_bukan_ppn701 + $total_dbukan_ppn702 + 								$bukan_ppn703 + $bukan_ppn704 + $bukan_ppn705 + $bukan_ppn706 + $bukan_ppn707 + $bukan_ppn708;

		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PENDAPATAN I ( 701 s/d 708 )");
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $total701_708);
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $total_sendiri_701_708);
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $total_oleh_pemungut_701_708);
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $total_dibebaskan_701_708);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $total_bukan_ppn_701_708);

		$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);

		$numrow += 2;
		$queryExec10	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   kode_jasa,
                                   jasa_description,
                                   kode_akun,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE kode_akun in 
                       ('7911111','7911121','7911131','7911141','7911151'
                       ,'7911152','7911161','7911171','7911181','7911182'
                       ,'7911189','7912111','7912112','7913999','7912113'
                       ,'7913111','7911901','79199999')
                       and tahun_pajak ='".$tahun."'
                       and kode_jasa not in ('0000')
                    GROUP BY kode_jasa, jasa_description, kode_akun) q_master
                    ,(select akun_pajak, kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , kode_pst_pelayanan
                           , spl.jumlah_potong
                           , spl.akun_pajak
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and spl.akun_pajak in
                       ('7911111','7911121','7911131','7911141','7911151'
                       ,'7911152','7911161','7911171','7911181','7911182'
                       ,'7911189','7912111','7912112','7913999','7912113'
                       ,'7913111','7911901','79199999')
                       and sph.tahun_pajak ='".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                     -- and substr(spl.akun_pajak,0,3) =791
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    --where q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    where q_master.kode_akun = q_pendapatan.akun_pajak (+)
                    order by q_master.kode_jasa ASC";

		$query10 		= $this->db->query($queryExec10);
		$balance791 		= 0;
		$sendiri791 		= 0;
		$oleh_pemungut791 	= 0;
		$dibebaskan791 		= 0;
		$bukan_ppn791 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow++,"PENDAPATAN DILUAR USAHA");
		foreach($query10->result_array() as $row10)	{
			$balance791 		+= $row10['BALANCE'];
			$sendiri791 		+= $row10['SENDIRI'];
			$oleh_pemungut791 	+= $row10['OLEH_PEMUNGUT'];
			$dibebaskan791 		+= $row10['DIBEBASKAN'];
			$bukan_ppn791 		+= $row10['BUKAN_PPN'];
			//$excel->setActiveSheetIndex(0)->setCellValue('B42',"PENGUSAHAAN ALAT");
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row10['JASA_DESCRIPTION']);
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row10['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row10['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row10['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row10['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row10['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_hsl);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_hsl);

		$numrow++;
		}

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH PENDAPATAN DILUAR USAHA");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $balance791);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiri791);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungut791);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskan791);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$numrow +=3;
			$ttl_pen_balance = 0;
			$ttl_pen_sendiri = 0;
			$ttl_pen_oleh_pemungut = 0;
			$ttl_pen_dibebaskan = 0;
			$ttl_pen_bukan_ppn = 0;

			$ttl_pen_balance = $balance791 + $total701_708;
			$ttl_pen_sendiri = $sendiri791 + $total_sendiri_701_708;
			$ttl_pen_oleh_pemungut = $oleh_pemungut791 + $total_oleh_pemungut_701_708;
			$ttl_pen_dibebaskan = $dibebaskan791 + $total_dibebaskan_701_708;
			$ttl_pen_bukan_ppn = $bukan_ppn791 + $total_bukan_ppn_701_708;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "TOTAL PENDAPATAN ( USAHA  + DILUAR USAHA)");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $ttl_pen_balance);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $ttl_pen_sendiri);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ttl_pen_oleh_pemungut);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $ttl_pen_dibebaskan);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ttl_pen_bukan_ppn);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);

			$numrow +=2;
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "REKENING NERACA :");

			$numrow +=2;
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Pendapatan Yang Di Terima Di Muka");

			$numrow +=1;
			$queryExec311	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =311
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description) q_master
                    ,(select kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =311
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query311 		= $this->db->query($queryExec311);
			$balance311 		= 0;
			$sendiri311 		= 0;
			$oleh_pemungut311 	= 0;
			$dibebaskan311 		= 0;
			$bukan_ppn311 		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Jangka Pendek (So.Akhir - So. Awal)");
			foreach($query311->result_array() as $row311)	{
			$balance311 		= $row311['BALANCE'];
			$sendiri311			= $row311['SENDIRI'];
			$oleh_pemungut311	= $row311['OLEH_PEMUNGUT'];
			$dibebaskan311		= $row311['DIBEBASKAN'];
			$bukan_ppn311		= $row311['BUKAN_PPN'];

			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row311['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row311['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row311['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row311['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row311['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			}

			$numrow +=2;
			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Pendapatan Yang Di Terima Di Muka");

			$numrow +=1;
			$queryExec405	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =405
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description) q_master
                    ,(select kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =405
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query405 		= $this->db->query($queryExec405);
			$balance405 		= 0;
			$sendiri405			= 0;
			$oleh_pemungut405	= 0;
			$dibebaskan405		= 0;
			$bukan_ppn405		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Jangka Panjang (So. Akhir - So. Awal)");
			foreach($query405->result_array() as $row405)	{
			$balance405 		= $row405['BALANCE'];
			$sendiri405			= $row405['SENDIRI'];
			$oleh_pemungut405	= $row405['OLEH_PEMUNGUT'];
			$dibebaskan405		= $row405['DIBEBASKAN'];
			$bukan_ppn405		= $row405['BUKAN_PPN'];

			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row405['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row405['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row405['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row405['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row405['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			}

			$numrow +=2;
			$queryExec111	= "select                 
                          q_master.kode_jasa
                        , q_master.jasa_description
                        , q_master.balance
                        , q_pendapatan.sendiri
                        , q_pendapatan.oleh_pemungut
                        , q_pendapatan.dibebaskan
                        , q_pendapatan.bukan_ppn
                        from 
                    (  SELECT 
                                   substr(kode_jasa,3,2) kode_jasa,
                                   jasa_description,
                                   SUM (NVL (debit, 0)*-1) - SUM (NVL (credit, 0)*-1) balance
                        FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                       WHERE SUBSTR (kode_akun, 0, 3) =111
                       and tahun_pajak ='".$tahun."'
                    GROUP BY substr(kode_jasa,3,2), jasa_description) q_master
                    ,(select kode_pst_pelayanan, pst_pelayanan_desc, sendiri, oleh_pemungut, dibebaskan, bukan_ppn
                            from (select spl.pst_pelayanan_desc
                           , substr(spl.kode_pst_pelayanan,3,2) kode_pst_pelayanan
                           , spl.jumlah_potong
                          , case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                      from simtax_pajak_headers sph
                      inner join simtax_pajak_lines spl
                          on SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                          INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                    where sph.nama_pajak in ('PPN MASUKAN','PPN KELUARAN')
                       and sph.tahun_pajak = '".$tahun."'
                       --and sph.kode_cabang = '".$cabang."'
                       and (SPL.NO_FAKTUR_PAJAK is not null or SPL.NO_DOKUMEN_LAIN is not null)
                      and substr(spl.akun_pajak,0,3) =111
                      AND SPL.IS_CHEKLIST = '1'
                       --group by spl.akun_pajak, substr(nvl(SPL.NO_FAKTUR_PAJAK, SPL.NO_DOKUMEN_LAIN),0,2)
                       )
                       PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                    where q_master.kode_jasa = q_pendapatan.kode_pst_pelayanan (+)
                    order by q_master.kode_jasa ASC";

		$query111 		= $this->db->query($queryExec111);
			$balance111 		= 0;
			$sendiri111			= 0;
			$oleh_pemungut111	= 0;
			$dibebaskan111		= 0;
			$bukan_ppn111		= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Pendapatan Y.M.A Diterima (So.Awal - So Akhir)");
			foreach($query111->result_array() as $row111)	{
			$balance111 		= $row111['BALANCE'];
			$sendiri111			= $row111['SENDIRI'];
			$oleh_pemungut111	= $row111['OLEH_PEMUNGUT'];
			$dibebaskan111		= $row111['DIBEBASKAN'];
			$bukan_ppn111		= $row111['BUKAN_PPN'];

			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row111['BALANCE']);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row111['SENDIRI']);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row111['OLEH_PEMUNGUT']);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row111['DIBEBASKAN']);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row111['BUKAN_PPN']);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			}

			$numrow +=2;
			$tot_neraca_balance = 0;
			$tot_neraca_sendiri = 0;
			$tot_neraca_oleh_pemungut = 0;
			$tot_neraca_dibebaskan = 0;
			$tot_neraca_bukan_ppn = 0;

			$tot_neraca_balance 		= $balance311 + $balance405 + $balance111;
			$tot_neraca_sendiri 		= $sendiri311 + $sendiri405 + $sendiri111;
			$tot_neraca_oleh_pemungut 	= $oleh_pemungut311 + $oleh_pemungut405 + $oleh_pemungut111;
			$tot_neraca_dibebaskan 		= $dibebaskan311 + $dibebaskan405 + $dibebaskan111;
			$tot_neraca_bukan_ppn 		= $bukan_ppn311 + $bukan_ppn405 + $bukan_ppn111;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "JUMLAH REKENING NERACA");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $tot_neraca_balance);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $tot_neraca_sendiri);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $tot_neraca_oleh_pemungut);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $tot_neraca_dibebaskan);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $tot_neraca_bukan_ppn);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);

			$numrow +=2;
			$tot_omset_balance = 0;
			$tot_omset_sendiri = 0;
			$tot_omset_oleh_pemungut = 0;
			$tot_omset_dibebaskan = 0;
			$tot_omset_bukan_ppn = 0;

			$tot_omset_balance 			= $tot_neraca_balance + $ttl_pen_balance;
			$tot_omset_sendiri 			= $tot_neraca_sendiri + $ttl_pen_sendiri;
			$tot_omset_oleh_pemungut 	= $tot_neraca_oleh_pemungut + $ttl_pen_oleh_pemungut;
			$tot_omset_dibebaskan 		= $tot_neraca_dibebaskan + $ttl_pen_dibebaskan;
			$tot_omset_bukan_ppn 		= $tot_neraca_bukan_ppn + $ttl_pen_bukan_ppn;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "TOTAL OMZET");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $tot_omset_balance);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $tot_omset_sendiri);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $tot_omset_oleh_pemungut);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $tot_omset_dibebaskan);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $tot_omset_bukan_ppn);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				
			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);

			$numrow +=3;
			$queryExecdpp	= "SELECT SUM (q_master.balance) balance,
                     SUM (q_pendapatan.sendiri) sendiri,
                     SUM (q_pendapatan.oleh_pemungut) oleh_pemungut,
                     SUM (q_pendapatan.dibebaskan) dibebaskan,
                     SUM (q_pendapatan.bukan_ppn) bukan_ppn
                FROM (  SELECT SUBSTR (kode_akun, 0, 3) kode_akun,
                               SUM (NVL (debit, 0) - 1) - SUM (NVL (credit, 0) - 1) balance,
                               masa_pajak
                          FROM SIMTAX_RINCIAN_BL_PPH_BADAN
                         WHERE SUBSTR (kode_akun, 0, 3) IN
                                     ('701', '702', '703', '704', '705', '706', '707', '708')
                               AND tahun_pajak = '".$tahun."'
                      GROUP BY SUBSTR (kode_akun, 0, 3), masa_pajak) q_master,
                     (SELECT akun_pajak,
                             sendiri,
                             oleh_pemungut,
                             dibebaskan,
                             bukan_ppn,
                             masa_pajak,
                             pembetulan_ke
                        FROM (SELECT SUBSTR (spl.akun_pajak, 0, 3) akun_pajak,
                                     spl.jumlah_potong jumlah_potong,
                                     sph.masa_pajak,
                                     sph.pembetulan_ke,
                                     case
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('1','4','6','9'))  then 'Sendiri'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI IN ('2','3'))  then 'oleh_pemungut'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '7')  then 'bukan_ppn'
                               when sph.nama_pajak = 'PPN KELUARAN' AND (SPL.KD_JENIS_TRANSAKSI = '8')  then 'dibebaskan'
                               else NULL
                               end kode_faktur
                                FROM    simtax_pajak_headers sph
                                     INNER JOIN
                                        simtax_pajak_lines spl
                                     ON SPH.PAJAK_HEADER_ID = SPL.PAJAK_HEADER_ID
                                      INNER JOIN SIMTAX_MASTER_PERIOD SMP ON SPH.PERIOD_ID = SMP.PERIOD_ID
                               WHERE sph.nama_pajak IN ('PPN MASUKAN', 'PPN KELUARAN')
                                     AND sph.tahun_pajak ='".$tahun."'
                                      AND SPL.IS_CHEKLIST = '1'
                                     AND (SPL.NO_FAKTUR_PAJAK IS NOT NULL
                                          OR SPL.NO_DOKUMEN_LAIN IS NOT NULL)
                                     AND SUBSTR (spl.akun_pajak, 0, 3) IN
                                              ('701', '702', '703','704', '705', '706', '707', '708')
                                     ORDER BY SPH.PEMBETULAN_KE ASC
                             ) PIVOT (SUM (jumlah_potong*1)
                       FOR kode_faktur IN ('Sendiri' AS sendiri, 'oleh_pemungut' AS oleh_pemungut,'dibebaskan' AS dibebaskan,'bukan_ppn' AS bukan_ppn))) q_pendapatan
                            WHERE q_master.kode_akun = q_pendapatan.akun_pajak
                             AND q_master.masa_pajak = Q_PENDAPATAN.masa_pajak
                            GROUP BY q_pendapatan.pembetulan_ke, q_master.masa_pajak
                            ORDER BY q_master.masa_pajak DESC";

		$querydpp 		= $this->db->query($queryExecdpp);
			$balancedpp 		= 0;
			$sendiridpp			= 0;
			$oleh_pemungutdpp	= 0;
			$dibebaskandpp		= 0;
			$bukan_ppndpp		= 0;
			$tot_dpp			= 0;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "MENURUT SPT PPN (DPP)");

			foreach($querydpp->result_array() as $rowdpp)	{
			$balancedpp 		+= $rowdpp['BALANCE'];
			$sendiridpp			+= $rowdpp['SENDIRI'];
			$oleh_pemungutdpp	+= $rowdpp['OLEH_PEMUNGUT'];
			$dibebaskandpp		+= $rowdpp['DIBEBASKAN'];
			$bukan_ppndpp		+= $rowdpp['BUKAN_PPN'];

			$tot_dpp			+= $rowdpp['SENDIRI'] + $rowdpp['OLEH_PEMUNGUT'] + $rowdpp['DIBEBASKAN'];

			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $tot_dpp);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $sendiridpp);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $oleh_pemungutdpp);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dibebaskandpp);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $bukan_ppndpp);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);
			}

			$numrow +=3;
			$selisih_bb 			= 0;
			$selisih_sendiri 		= 0;
			$selisih_oleh_pemungut	= 0;
			$selisih_dibebaskan		= 0;
			$selisih_bukan_ppn		= 0;

			$selisih_bb 			= $tot_omset_balance - $tot_dpp;
			$selisih_sendiri 		= $tot_omset_sendiri - $sendiridpp;
			$selisih_oleh_pemungut 	= $tot_omset_oleh_pemungut - $oleh_pemungutdpp;
			$selisih_dibebaskan 	= $tot_omset_dibebaskan - $dibebaskandpp;
			$selisih_bukan_ppn 		= $tot_omset_bukan_ppn - $bukan_ppndpp;

			$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "SELISIH");
			$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $selisih_bb);
			$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $selisih_sendiri);
			$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $selisih_oleh_pemungut);
			$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $selisih_dibebaskan);
			$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $selisih_bukan_ppn);

			$excel->getActiveSheet()->getStyle('C'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('D'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('E'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('F'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
			$excel->getActiveSheet()->getStyle('H'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

			$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_col);

			$numrow +=0;
			$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('B9:B'.$numrow)->applyFromArray($style_col1);
			$excel->getActiveSheet()->getStyle('C7:C8')->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('C9:C'.$numrow)->applyFromArray($style_col1);
			$excel->getActiveSheet()->getStyle('D9:D'.$numrow)->applyFromArray($style_col1);
			$excel->getActiveSheet()->getStyle('D7:D8')->applyFromArray($style_col);
			$excel->getActiveSheet()->getStyle('E9:E'.$numrow)->applyFromArray($style_col5);
			$excel->getActiveSheet()->getStyle('F9:F'.$numrow)->applyFromArray($style_col1);
			$excel->getActiveSheet()->getStyle('G9:G'.$numrow)->applyFromArray($style_col1);
			$excel->getActiveSheet()->getStyle('H9:H'.$numrow)->applyFromArray($style_col3);

		$numrow += 2;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "MENGETAHUI :");

		$cabang		= $this->session->userdata('kd_cabang');
		$queryExecttd	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = 'DVP Pajak'
                            and nama_pajak = 'SPT PPN Masa'
                            and document_type = 'Ekualisasi' 
                            and kode_cabang ='".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
			$queryttd 		= $this->db->query($queryExecttd);
			$rowCount 		= $queryttd->num_rows();

		if($rowCount > 0){

			$rowbttd		= $queryttd->row();

			$ttd 					= $rowbttd->URL_TANDA_TANGAN;
			$petugas_ttd			= $rowbttd->NAMA_PETUGAS_PENANDATANGAN;
			$jabatan_petugas_ttd	= $rowbttd->JABATAN_PETUGAS_PENANDATANGAN;

		$numrow += 1;
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$logo = $ttd; // Provide path to your logo file
		if(file_exists($logo)){
			$objDrawing->setPath($logo);  //setOffsetY has no effect
			$objDrawing->setCoordinates('B'.$numrow);
			$objDrawing->setHeight(80); // logo height
			$objDrawing->setWorksheet($excel->getActiveSheet());
		}

		$numrow += 4;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $petugas_ttd);

		$numrow += 1;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $jabatan_petugas_ttd);
		}
		//
		/*$excel->setActiveSheetIndex(0)->setCellValue('D100',"MENGETAHUI :");
		$excel->setActiveSheetIndex(0)->setCellValue('D102',"1. AGUS WAHYUDI :");
		$excel->setActiveSheetIndex(0)->setCellValue('D103',"DVP PAJAK");

		//
		$excel->setActiveSheetIndex(0)->setCellValue('I102',"PETUGAS PUSAT :");
		$excel->setActiveSheetIndex(0)->setCellValue('I103',"");*/
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(2); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(45); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20)	; // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom A

		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Laporan Keuangan");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Laporan Keuangan.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_rekap_kurang_lebih()
	{
		$this->template->set('title', 'Rekap Kurang (Lebih) Bayar');
		$data['subtitle']   = "Cetak Rekap Kurang (Lebih) Bayar";
		$data['activepage'] = "ppn_masa";
		$data['error']      = "";
		$this->template->load('template', 'laporan/lap_kurang_lebih',$data);		
	}	
	
	function cetak_rekap_kurang_lebih()
	{
		
		$tahun 			= $_REQUEST['tahun'];
		$bulan 			= $_REQUEST['bulan'];
		$pembetulanKe 	= $_REQUEST['pembetulanKe'];
		$cabang 		= $_REQUEST['kd_cabang'];

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
		} else{
			$kd_cabang = '';
		}

		$shortMonthArr 		= array("", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");
		$bulanTeks			= $shortMonthArr[$bulan];
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak SPT Setahun")
								->setSubject("Cectakan")
								->setDescription("Cetak SPT Setahun")
								->setKeywords("WAPU");
								
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_ttd = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_jabatan = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_right = array(
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

		$style_row_centre = array(
			'font' 	   	 => array('bold' => true),
		   'alignment'   => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		  ),
		);

		$style_row_jud = array(
			'font' 	   	 => array('bold' => true),
		   'alignment'   => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
		 	'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_no_atas = array(
			'font' 	   	 => array('size' => 6),
		   'alignment'   => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
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
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "PT. PELABUHAN INDONESIA II (Persero)"); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->mergeCells('B3:K3');
		$excel->setActiveSheetIndex(0)->setCellValue('B3', "REKAPITULASI SETORAN PPN MASA ".$bulanTeks." ".$tahun); // Set kolom A1 dengan tulisan "DATA SISWA"
		$excel->getActiveSheet()->getStyle('B3:L3')->applyFromArray($style_row_centre);
		
		
		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('B5', "No.");
		$excel->getActiveSheet()->mergeCells('B5:B6');
		$excel->getActiveSheet()->getStyle('B5:B6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('C5', "NAMA CABANG/UNIT");
		$excel->getActiveSheet()->mergeCells('C5:C6');
		$excel->getActiveSheet()->getStyle('C5:C6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('D5', "PPN DIPUNGUT OLEH PEMUNGUT");
		$excel->getActiveSheet()->mergeCells('D5:E5');
		$excel->setActiveSheetIndex(0)->setCellValue('D6', "DPP");
		$excel->setActiveSheetIndex(0)->setCellValue('E6', "PPN");
		$excel->getActiveSheet()->getStyle('D5:E5')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('F5', "PPN KELUARAN TIDAK DIPUNGUT");
		$excel->getActiveSheet()->mergeCells('F5:G5');
		$excel->setActiveSheetIndex(0)->setCellValue('F6', "DPP");
		$excel->setActiveSheetIndex(0)->setCellValue('G6', "PPN");
		$excel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('H5', "PPN KELUARAN DIBEBASKAN");
		$excel->getActiveSheet()->mergeCells('H5:I5');
		$excel->setActiveSheetIndex(0)->setCellValue('H6', "DPP");
		$excel->setActiveSheetIndex(0)->setCellValue('I6', "PPN");
		$excel->getActiveSheet()->getStyle('H5:I5')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('J5', "PPN KELUARAN DIPUNGUT SENDIRI");
		$excel->getActiveSheet()->mergeCells('J5:K5');
		$excel->setActiveSheetIndex(0)->setCellValue('J6', "DPP");
		$excel->setActiveSheetIndex(0)->setCellValue('K6', "PPN");
		$excel->getActiveSheet()->getStyle('J5:K5')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('L5', "PPN MASUKAN");
		$excel->getActiveSheet()->mergeCells('L5:L6');
		$excel->getActiveSheet()->getStyle('L5:L6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('M5', "PMK 78");
		$excel->getActiveSheet()->mergeCells('M5:M6');
		$excel->getActiveSheet()->getStyle('M5:M6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('N5', "PPN MASUKAN");
		$excel->setActiveSheetIndex(0)->setCellValue('N6', "DAPAT DIKREDITKAN");
		$excel->getActiveSheet()->getStyle('N5:N6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('O5', "KURANG/(LEBIH)");
		$excel->setActiveSheetIndex(0)->setCellValue('O6', "BAYAR");
		$excel->getActiveSheet()->getStyle('O5:O6')->applyFromArray($style_row_jud);

		$excel->getActiveSheet()->getStyle('B5')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('C5')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('F6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('G6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('H6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('I6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('J6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('K6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('L6')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('M6')->applyFromArray($style_row_jud);

		if ($kd_cabang ==""){
			$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
		} else{
			$whereCabang = "'".$kd_cabang."'";
		}

			$queryExec	= "
			  select skc.kode_cabang
                                 , case skc.nama_cabang
                                    when 'Kantor Pusat' then skc.nama_cabang
                                   else 'Cabang ' || skc.nama_cabang
                                   end nama_cabang
                                 , nvl(ppn_oleh_pemungut.dpp,0) dpp_ppn_oleh_pemungut
                                 , nvl(ppn_oleh_pemungut.jumlah_potong,0) ppn_ppn_oleh_pemungut                                 
                                 , nvl(ppn_tidak_dipungut.dpp,0) dpp_ppn_tidak_dipungut
                                 , nvl(ppn_tidak_dipungut.jumlah_potong,0) ppn_ppn_tidak_dipungut
                                 , nvl(ppn_dipungut_sendiri.dpp,0) dpp_ppn_dipungut_sendiri
                                 , nvl(ppn_dipungut_sendiri.jumlah_potong,0) ppn_ppn_dipungut_sendiri
                                  , nvl(ppn_dibebaskan.dpp,0) dpp_ppn_dibebaskan
                                 , nvl(ppn_dibebaskan.jumlah_potong,0) ppn_ppn_dibebaskan
                                 , nvl(ppn_masukan.jumlah_potong,0) PPN_MASUKAN
                                 , abs(nvl(ppn_masukan.pmk78,0)) pmk78
                                 , abs(pmk_78.pmk_78) pmk_78
                              from simtax_kode_cabang skc
                            , (select 
                                   skc.NAMA_CABANG
                                 , sphh.KODE_CABANG
                                 , sphh.TAHUN_PAJAK
                                 , sphh.BULAN_PAJAK
                                 , sphh.MASA_PAJAK
                              from simtax_pajak_headers sphh
                                 , simtax_pajak_lines splh
                                 , simtax_kode_cabang skc
                             where sphh.nama_pajak in ('PPN KELUARAN','PPN MASUKAN')
                               and sphh.PAJAK_HEADER_ID = splh.PAJAK_HEADER_ID
                               and nvl(splh.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphh.KODE_CABANG
                               and sphh.tahun_pajak = '".$tahun."'
                               and sphh.bulan_pajak = ".$bulan."
                               and sphh.pembetulan_ke = ".$pembetulanKe."
                            group by skc.NAMA_CABANG, sphh.KODE_CABANG, sphh.TAHUN_PAJAK, sphh.BULAN_PAJAK, sphh.MASA_PAJAK) ppn_header
                            ,(select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = '".$bulan."'
                               and sphm.pembetulan_ke = ".$pembetulanKe." 
                                and splm.kd_jenis_transaksi IN (2,3)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_oleh_pemungut
                            ,(select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                                 , sum(splm.JUMLAH_POTONG*-1) JUMLAH_POTONG
                                 , min(abs(nvl(sphm.PMK78,0))) PMK78
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN MASUKAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = ".$bulan."
                               and sphm.pembetulan_ke = ".$pembetulanKe."
                               and ((splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and splm.dl_fs = 'dokumen_lain') or (splm.kd_jenis_transaksi in (1,2,3,4,5,6,9,11,12) and (dl_fs is null or splm.dl_fs = 'faktur_standar') and splm.is_creditable = '1'))
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_masukan,
                            (  SELECT skc.NAMA_CABANG,
				                   sphm.KODE_CABANG,
				                   sphm.TAHUN_PAJAK,
				                   sphm.BULAN_PAJAK,
				                   sphm.MASA_PAJAK,
				                   ceil(abs(SUM (NVL (splm.JUMLAH_POTONG * -1, 0)) * (95.08 / 100)
				                   - SUM (NVL (splm.JUMLAH_POTONG * -1, 0))))
				                      PMK_78
				              FROM simtax_pajak_headers sphm,
				                   simtax_pajak_lines splm,
				                   simtax_kode_cabang skc
				             WHERE     sphm.nama_pajak = 'PPN MASUKAN'
				                   AND sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
				                   AND NVL (splm.IS_CHEKLIST, 0) = 1
				                   and splm.is_pmk = 1
				                   AND skc.KODE_CABANG = sphm.KODE_CABANG
	                               and sphm.tahun_pajak = '".$tahun."'
	                               and sphm.bulan_pajak = ".$bulan."
	                               and sphm.pembetulan_ke = ".$pembetulanKe."
				          GROUP BY skc.NAMA_CABANG,
				                   sphm.KODE_CABANG,
				                   sphm.TAHUN_PAJAK,
				                   sphm.BULAN_PAJAK,
				                   sphm.MASA_PAJAK) PMK_78,
                             (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                                 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.bulan_pajak = ".$bulan."
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.pembetulan_ke = ".$pembetulanKe."
                               and splm.kd_jenis_transaksi IN (1,4,6,9)
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dipungut_sendiri,
                            (select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                                 , sum(nvl(splm.DPP,0)) DPP
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = ".$bulan."
                               and sphm.pembetulan_ke =".$pembetulanKe."                               
                               and splm.kd_jenis_transaksi = '7' 
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_tidak_dipungut
                            ,(select skc.NAMA_CABANG
                                 , sphm.KODE_CABANG
                                 , sphm.TAHUN_PAJAK
                                 , sphm.BULAN_PAJAK
                                 , sphm.MASA_PAJAK
                                 , sum(nvl(splm.DPP,0)) DPP
                                 , sum(splm.JUMLAH_POTONG) JUMLAH_POTONG
                              from simtax_pajak_headers sphm
                                 , simtax_pajak_lines splm
                                 , simtax_kode_cabang skc
                             where sphm.nama_pajak = 'PPN KELUARAN'
                               and sphm.PAJAK_HEADER_ID = splm.PAJAK_HEADER_ID
                               and nvl(splm.IS_CHEKLIST,0) = 1
                               and skc.KODE_CABANG = sphm.KODE_CABANG
                               and sphm.tahun_pajak = '".$tahun."'
                               and sphm.bulan_pajak = ".$bulan."
                               and sphm.pembetulan_ke =".$pembetulanKe."
                               and splm.kd_jenis_transaksi = '8'       
                            group by skc.NAMA_CABANG, sphm.KODE_CABANG, sphm.TAHUN_PAJAK, sphm.BULAN_PAJAK, sphm.MASA_PAJAK) ppn_dibebaskan
                            where 1=1
                            and skc.KODE_CABANG = ppn_header.kode_cabang (+)
                            and ppn_header.nama_cabang = ppn_masukan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_masukan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_masukan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_masukan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_masukan.masa_pajak (+)
                            AND ppn_header.nama_cabang = pmk_78.nama_cabang(+)
				            AND ppn_header.kode_cabang = pmk_78.kode_cabang(+)
				         	AND ppn_header.tahun_pajak = pmk_78.tahun_pajak(+)
				         	AND ppn_header.bulan_pajak = pmk_78.bulan_pajak(+)
				         	AND ppn_header.masa_pajak = pmk_78.masa_pajak(+)                            
                            and ppn_header.nama_cabang = ppn_dipungut_sendiri.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dipungut_sendiri.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dipungut_sendiri.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dipungut_sendiri.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dipungut_sendiri.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_tidak_dipungut.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_tidak_dipungut.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_tidak_dipungut.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_tidak_dipungut.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_tidak_dipungut.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_oleh_pemungut.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_oleh_pemungut.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_oleh_pemungut.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_oleh_pemungut.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_oleh_pemungut.masa_pajak (+)
                            and ppn_header.nama_cabang = ppn_dibebaskan.nama_cabang (+)
                            and ppn_header.kode_cabang = ppn_dibebaskan.kode_cabang (+)
                            and ppn_header.tahun_pajak = ppn_dibebaskan.tahun_pajak (+)
                            and ppn_header.bulan_pajak = ppn_dibebaskan.bulan_pajak (+)
                            and ppn_header.masa_pajak  = ppn_dibebaskan.masa_pajak (+)
                            and skc.KODE_CABANG in (".$whereCabang.")
                            order by 1";
			
			$query 		= $this->db->query($queryExec);
			//$rowb		= $query->row();

			$no = 1; // Untuk penomoran tabel, di awal set dengan 1
			$numrow = 8; // Set baris pertama untuk isi tabel adalah baris ke 4
			$kurang_lebih 		= 0;
			$dpp_pemungut 		= 0;
			$ppn_pemungut 		= 0;
			$dpp_dipungut 		= 0;
			$ppn_dipungut 		= 0;
			$dpp_dibebaskan 	= 0;
			$ppn_dibebaskan 	= 0;
			$dpp_sendiri 		= 0;
			$ppn_sendiri 		= 0;
			$ppn_masukan 		= 0;
			$pmk78 				= 0;
			$ttl_kurang_lebih 	= 0;
			$dikreditkan 	 	= 0;
			$tot_dikreditkan 	= 0;

			foreach($query->result_array() as $row)	{
				$dpp_pemungut     += $row['DPP_PPN_OLEH_PEMUNGUT'];
				$ppn_pemungut     += $row['PPN_PPN_OLEH_PEMUNGUT'];
				$dpp_dipungut     += $row['DPP_PPN_TIDAK_DIPUNGUT'];
				$ppn_dipungut     += $row['PPN_PPN_TIDAK_DIPUNGUT'];
				$dpp_dibebaskan   += $row['DPP_PPN_DIBEBASKAN'];
				$ppn_dibebaskan   += $row['PPN_PPN_DIBEBASKAN'];
				$dpp_sendiri      += $row['DPP_PPN_DIPUNGUT_SENDIRI'];
				$ppn_sendiri      += $row['PPN_PPN_DIPUNGUT_SENDIRI'];
				$ppn_masukan      += $row['PPN_MASUKAN'];
				$pmk78            += $row['PMK_78'];
				// $kurang_lebih  = $row['PPN_MASUKAN'] - $row['PPN_PPN_DIPUNGUT_SENDIRI'];
				$kurang_lebih     = $row['PPN_PPN_DIPUNGUT_SENDIRI'] -($row['PPN_MASUKAN']-$row['PMK_78']);
				$ttl_kurang_lebih += $kurang_lebih;
				$dikreditkan      = $row['PPN_MASUKAN'] - $row['PMK_78'];
				$tot_dikreditkan  += $row['PPN_MASUKAN'] - $row['PMK_78'];

				$excel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('K')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('M')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('N')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->getActiveSheet()->getStyle('O')->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $no);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['NAMA_CABANG']);	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['DPP_PPN_OLEH_PEMUNGUT']);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['PPN_PPN_OLEH_PEMUNGUT']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['DPP_PPN_TIDAK_DIPUNGUT']);
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['PPN_PPN_TIDAK_DIPUNGUT']);	
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['DPP_PPN_DIBEBASKAN']);
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['PPN_PPN_DIBEBASKAN']);
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $row['DPP_PPN_DIPUNGUT_SENDIRI']);
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $row['PPN_PPN_DIPUNGUT_SENDIRI']);
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['PPN_MASUKAN']);
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $row['PMK_78']);
				$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $dikreditkan);
				$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $kurang_lebih);

				$excel->setActiveSheetIndex(0)->setCellValue('B7', '1');	
				$excel->setActiveSheetIndex(0)->setCellValue('C7', '2');	
				$excel->setActiveSheetIndex(0)->setCellValue('D7', '3');	
				$excel->setActiveSheetIndex(0)->setCellValue('E7', '4');	
				$excel->setActiveSheetIndex(0)->setCellValue('F7', '5');
				$excel->setActiveSheetIndex(0)->setCellValue('G7', '6');	
				$excel->setActiveSheetIndex(0)->setCellValue('H7', '7');
				$excel->setActiveSheetIndex(0)->setCellValue('I7', '8');
				$excel->setActiveSheetIndex(0)->setCellValue('J7', '9');
				$excel->setActiveSheetIndex(0)->setCellValue('K7', '10');
				$excel->setActiveSheetIndex(0)->setCellValue('L7', '11');
				$excel->setActiveSheetIndex(0)->setCellValue('M7', '12');
				$excel->setActiveSheetIndex(0)->setCellValue('N7', '13');
				$excel->setActiveSheetIndex(0)->setCellValue('O7', '14');
				
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_right);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_right);	
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row);

				$excel->getActiveSheet()->getStyle('B7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('C7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('D7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('E7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('F7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('G7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('H7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('I7')->applyFromArray($style_row_no_atas);	
				$excel->getActiveSheet()->getStyle('J7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('K7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('L7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('M7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('N7')->applyFromArray($style_row_no_atas);
				$excel->getActiveSheet()->getStyle('O7')->applyFromArray($style_row_no_atas);	
				
				$no++; // Tambah 1 setiap kali looping
				$numrow++; // Tambah 1 setiap kali looping					
			}

		$numrow +=0;
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);	
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row);

	    $numrow +=1;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow , "JUMLAH DISETOR");
		$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $dpp_pemungut);
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $ppn_pemungut);
		$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $dpp_dipungut);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $ppn_dipungut);
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $dpp_dibebaskan);
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $ppn_dibebaskan);
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $dpp_sendiri);
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $ppn_sendiri);
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $ppn_masukan);
		$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $pmk78);
		$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $tot_dikreditkan);
		$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $ttl_kurang_lebih);

		$numrow +=0;
		$excel->getActiveSheet()->getStyle('B'.$numrow.':C'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row_jud);

		$numrow += 2;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, "MENGETAHUI :");		
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_ttd);

		$cabang		= $this->session->userdata('kd_cabang');
		$queryExec1	= "select * from SIMTAX_PEMOTONG_PAJAK
                            where JABATAN_PETUGAS_PENANDATANGAN = 'DVP Pajak'
                            and nama_pajak = 'SPT PPN Masa'
                            and document_type = 'Ekualisasi' 
                            and kode_cabang = '".$cabang."'
                            and end_effective_date >= sysdate
                            and start_effective_date <= sysdate ";
			
			$query1 	= $this->db->query($queryExec1);
			$rowCount 	= $query1->num_rows();

		if($rowCount > 0){

			$rowb1		= $query1->row();

			$ttd 					= $rowb1->URL_TANDA_TANGAN;
			$petugas_ttd			= $rowb1->NAMA_PETUGAS_PENANDATANGAN;
			$jabatan_petugas_ttd	= $rowb1->JABATAN_PETUGAS_PENANDATANGAN;

		$numrow += 1;
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$logo = $ttd; // Provide path to your logo file
		if(file_exists($logo)){
			$objDrawing->setPath($logo);  //setOffsetY has no effect
			$objDrawing->setCoordinates('C'.$numrow);
			$objDrawing->setHeight(80); // logo height
			$objDrawing->setWorksheet($excel->getActiveSheet());
		}

		$numrow += 4;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $petugas_ttd);		
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_jabatan);

		$numrow += 1;
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $jabatan_petugas_ttd);		
		$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_jabatan);
		}
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(5); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("SPT PPN MASA Rekapitulasi");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="SPT PPN MASA Rekapitulasi.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}

	function show_penjualan()
	{
		$this->template->set('title', 'Daftar Penjualan Lokal');
		$data['subtitle']   = "Cetak Penjualan Lokal";
		$data['activepage'] = "ppn_masa";
		$data['error']      = "";
		$this->template->load('template', 'laporan/penjualan',$data);		
	}	
	
	function cetak_penjualan()
	{
		
		$tahun 		= $_REQUEST['tahun'];
		$bulan 		= $_REQUEST['bulan'];
		$cabang 	= $_REQUEST['cabang'];
		//$cabang		= $this->session->userdata('kd_cabang');

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
		}else{
			$kd_cabang = '';
		}
		
		$date	    = date("Y-m-d H:i:s");
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak Penjualan")
								->setSubject("Cetakan")
								->setDescription("Cetak Penjualan")
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_ttd = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_jabatan = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_right = array(
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

		$style_row_centre = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		  ),
		);

		$style_row_jud = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "ORGANIZATION_NAME");
		$excel->getActiveSheet()->getStyle('B2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('C2', "MASA_TAHUN");
		$excel->getActiveSheet()->getStyle('C2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('D2', "NOMOR_INVOICE");
		$excel->getActiveSheet()->getStyle('D2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('E2', "TANGGAL_INVOICE");
		$excel->getActiveSheet()->getStyle('E2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('F2', "NOMOR_FAKTUR_PAJAK");
		$excel->getActiveSheet()->getStyle('F2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('G2', "TANGGAL_FAKTUR_PAJAK");
		$excel->getActiveSheet()->getStyle('G2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('H2', "KODE_MODUL");
		$excel->getActiveSheet()->getStyle('H2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('I2', "JENIS_NOTA");
		$excel->getActiveSheet()->getStyle('I2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('J2', "CUSTOMER");
		$excel->getActiveSheet()->getStyle('J2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('K2', "BARANG");
		$excel->getActiveSheet()->getStyle('K2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('L2', "KETERANGAN");
		$excel->getActiveSheet()->getStyle('L2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('M2', "QTY_BARANG");
		$excel->getActiveSheet()->getStyle('M2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('N2', "MATA_UANG");
		$excel->getActiveSheet()->getStyle('N2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('O2', "EXCHANGE_RATE");
		$excel->getActiveSheet()->getStyle('O2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('P2', "JUMLAH_NILAI_BARANG");
		$excel->getActiveSheet()->getStyle('P2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('Q2', "JUMLAH_PPN");
		$excel->getActiveSheet()->getStyle('Q2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('R2', "TGL_PENERIMAAN_PEMBAYARAN");
		$excel->getActiveSheet()->getStyle('R2')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('S2', "PENERIMAAN_PEMBAYARAN");
		$excel->getActiveSheet()->getStyle('S2')->applyFromArray($style_row_jud);

		$excel->getActiveSheet()->getStyle('B2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('C2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('D2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('E2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('F2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('G2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('H2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('I2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('J2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('K2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('L2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('M2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('N2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('O2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('P2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('Q2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('R2')->applyFromArray($style_row_jud);
		$excel->getActiveSheet()->getStyle('S2')->applyFromArray($style_row_jud);

		if ($kd_cabang == ""){
			$whereCabang = "'000','010','020','030','040','050', '060','070','080','090','100','110','120'"; 
		} else{
			$whereCabang = "'".$kd_cabang."'";
		}

			$numrow = 3;			
			$queryExec	= " select * from V_SIMTAX_PENJUALAN where bulan_pajak='".$bulan."' and tahun_pajak='".$tahun."' and kode_cabang in (".$whereCabang.") ";
			$query 		= $this->db->query($queryExec);									
			foreach($query->result_array() as $row)	{			 
				$masa_tahun	=$row['BULAN_PAJAK'].'-'.$row['TAHUN_PAJAK'];
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['ORGANIZATION_NAME']);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $masa_tahun);	
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['INVOICE_NUMBER']);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['TANGGAL_INVOICE']);	
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['NOMOR_FAKTUR_PAJAK']);
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['TANGGAL_FAKTUR_PAJAK']);	
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['KODE_MODUL']);
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['JENIS_NOTA']);
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $row['CUSTOMER']);
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $row['BARANG']);
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['KETERANGAN']);
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $row['QTY_BARANG']);
				$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $row['MATA_UANG']);
				$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $row['EXCHANGE_RATE']);
				$excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $row['JUMLAH_NILAI_BARANG']);
				$excel->getActiveSheet()->getStyle('P'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->setActiveSheetIndex(0)->setCellValue('Q'.$numrow, $row['JUMLAH_PPN']);
				$excel->getActiveSheet()->getStyle('Q'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->setActiveSheetIndex(0)->setCellValue('R'.$numrow, $row['TGL_PENERIMAAN_PEMBAYARAN']);
				$excel->setActiveSheetIndex(0)->setCellValue('S'.$numrow, $row['PENERIMAAN_PEMBAYARAN']);
				$excel->getActiveSheet()->getStyle('S'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('Q'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('R'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('S'.$numrow)->applyFromArray($style_row);
				
				$numrow++; 				
			}
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); 
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(30); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(30); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('O')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('P')->setWidth(30); 
		$excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('R')->setWidth(30); 
		$excel->getActiveSheet()->getColumnDimension('S')->setWidth(30); 
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("PENJUALAN");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="PENJUALAN.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}

	function show_pembelian()
	{
		$this->template->set('title', 'Daftar Pembelian Lokal');
		$data['subtitle']   = "Cetak Pembelian Lokal";
		$data['activepage'] = "ppn_masa";
		$data['error']      = "";
		$this->template->load('template', 'laporan/pembelian',$data);		
	}	
	
	function cetak_pembelian()
	{
		
		$tahun 			= $_REQUEST['tahun'];
		$bulan 			= $_REQUEST['bulan'];
		$cabang 		= $_REQUEST['cabang'];
		//$cabang			= $this->session->userdata('kd_cabang');
		$nama_cabang 	= strtoupper(get_nama_cabang($cabang));
		$bulan_			= $bulan;
		if(substr($bulan,0,1)=='0'){
			$bulan_ = substr($bulan,-1);
		}
		
		$nama_bulan 	= strtoupper(get_masa_pajak($bulan_,'id',true));
		$date	    = date("Y-m-d H:i:s");

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
		}else{
			$kd_cabang = '';
		}
		
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Cetak Pembelian")
								->setSubject("Cetakan")
								->setDescription("Cetak Pembelian")
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
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$style_row_ttd = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_jabatan = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$style_row_right = array(
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

		$style_row_centre = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		  ),
		);

		$style_row_jud = array(
		   'alignment' => array(
		 	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		// Buat header tabel nya pada baris ke 3
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "REKAPITULASI PEMBELIAN LOKAL ".$nama_bulan." ".$tahun);
		$excel->setActiveSheetIndex(0)->setCellValue('B2', $nama_cabang);
		$excel->setActiveSheetIndex(0)->setCellValue('B3', "PT PELABUHAN INDONESIA II (PERSERO)");

		$excel->setActiveSheetIndex(0)->setCellValue('A5', "NO.");
		$excel->getActiveSheet()->mergeCells('A5:A6');
		$excel->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('B5', "MASA/TAHUN");
		$excel->getActiveSheet()->mergeCells('B5:B6');
		$excel->getActiveSheet()->getStyle('B5:B6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('C5', "NOMOR INVOICE");
		$excel->getActiveSheet()->mergeCells('C5:C6');
		$excel->getActiveSheet()->getStyle('C5:C6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('D5', "TANGGAL");
		$excel->setActiveSheetIndex(0)->setCellValue('D6', "INVOICE");
		$excel->getActiveSheet()->getStyle('D5:D6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('E5', "NOMOR FAKTUR PAJAK");
		$excel->getActiveSheet()->mergeCells('E5:E6');
		$excel->getActiveSheet()->getStyle('E5:E6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('F5', "TANGGAL");
		$excel->setActiveSheetIndex(0)->setCellValue('F6', "FAKTUR PAJAK");
		$excel->getActiveSheet()->getStyle('F5:F6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('G5', "COSTUMER");
		$excel->getActiveSheet()->mergeCells('G5:G6');
		$excel->getActiveSheet()->getStyle('G5:G6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('H5', "REFF TRANSAKSI");
		$excel->setActiveSheetIndex(0)->setCellValue('H6', "(UNTUK DAPAT DI LINK KE GL)");
		$excel->getActiveSheet()->getStyle('H5:H6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('I5', "JENIS BARANG (PER JENIS BARANG)");
		$excel->getActiveSheet()->mergeCells('I5:I6');
		$excel->getActiveSheet()->getStyle('I5:I6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('J5', "JUMLAH NILAI BARANG ");
		$excel->getActiveSheet()->mergeCells('J5:J6');
		$excel->getActiveSheet()->getStyle('J5:J6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('K5', "JUMLAH PPN");
		$excel->getActiveSheet()->mergeCells('K5:K6');
		$excel->getActiveSheet()->getStyle('K5:K6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('L5', "TANGGAL");
		$excel->setActiveSheetIndex(0)->setCellValue('L6', "PEMBAYARAN");
		$excel->getActiveSheet()->getStyle('L5:L6')->applyFromArray($style_row_jud);

		$excel->setActiveSheetIndex(0)->setCellValue('M5', "REFF");
		$excel->setActiveSheetIndex(0)->setCellValue('M6', "PEMBAYARAN");
		$excel->getActiveSheet()->getStyle('M5:M6')->applyFromArray($style_row_jud);

			if ($kd_cabang == ""){
				$whereCabang = "'000','010','020','030','040','050', '060','070','080','090','100','110','120'";
			} else{
				$whereCabang = "'".$kd_cabang."'";
			}

			$no = 1;
			$numrow = 7;
			$queryExec	= " Select * from V_SIMTAX_PEMBELIAN where bulan_pajak='".$bulan."' and tahun_pajak='".$tahun."' and kode_cabang in (".$whereCabang.") ";			
			$query 		= $this->db->query($queryExec);

			$total_dpp = 0;
			$total_ppn = 0;
						
			foreach($query->result_array() as $row)	{				
				$total_dpp += $row['JML_NILAI_BARANG'];
				$total_ppn += $row['JML_PPN'];
				$masa_tahun	=$row['BULAN_PAJAK'].'-'.$row['TAHUN_PAJAK'];
				$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);
				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $masa_tahun);	
				$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['INVOICE_NUMBER']);
				$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['TANGGAL_INVOICE']);	
				$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['NOMOR_FAKTUR_PAJAK']);
				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['TANGGAL_FAKTUR_PAJAK']);
				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['CUSTOMER']);	
				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['REF_TRANSAKSI']);
				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['JENIS_BARANG']);
				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $row['JML_NILAI_BARANG']);
				$excel->getActiveSheet()->getStyle('J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $row['JML_PPN']);
				$excel->getActiveSheet()->getStyle('K'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
				$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['TGL_PEMBAYARAN']);
				$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $row['REF_PEMBAYARAN']);
				
				$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
				$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);

				$no++;
				$numrow++; // Tambah 1 setiap kali looping					
			}

		$numrow += 0;
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, "TOTAL");
		$excel->getActiveSheet()->getStyle('A'.$numrow.':I'.$numrow)->applyFromArray($style_row_jud);
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $total_dpp);
		$excel->getActiveSheet()->getStyle('J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $total_ppn);
		$excel->getActiveSheet()->getStyle('K'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($style_row);
		$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($style_row);
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5); // Set width kolom A
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(15); // Set width kolom B
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(45); // Set width kolom C
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); // Set width kolom D
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(40); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(30); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(70); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(25); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20); // Set width kolom E
		$excel->getActiveSheet()->getColumnDimension('O')->setWidth(20); // Set width kolom E
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("PEMBELIAN");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="PEMBELIAN.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}

	function cetak_pmk()
	{
		$kd_cabang        = ($_REQUEST['kd_cabang'] != "") ? $_REQUEST['kd_cabang'] : $this->session->userdata('kd_cabang');
		$tahun            = $_REQUEST['tahun'];
		$bulan            = $_REQUEST['bulan'];
		$pembetulan       = $_REQUEST['pembetulanKe'];
		$nama_bulan       = get_masa_pajak($bulan,'id');
		$nama_bulan_upper = strtoupper(get_masa_pajak($bulan,'id',true));
		$masa_pajak       = $nama_bulan."-".substr($tahun, 2);
				
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("PMK")
								->setSubject("PMK")
								->setDescription("PMK")
								->setKeywords("PMK");
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$center_bold_border = array(
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

		$left_bold_border_no_right = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$right_no_bold_border_no_left = array(
		        'font' => array('bold' => false),
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border bottom dengan garis tipis
		  )
		);
		
		$center_no_bold_border = array(
		        'font' => array('bold' => false),
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

		$left_no_bold_border = array(
		        'font' => array('bold' => false),
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$right_no_bold_border = array(
		        'font' => array('bold' => false),
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$center_bold_noborder = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$center_nobold_noborder = array(
		       'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11),
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);	
		
		$border_kika_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$borderfull_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);			
		
		$border_kika_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$noborder_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);			
		
		$noborder_nobold_rata_kanan = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$parent_col = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11,
								'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kiri = array(
		    'borders' => array(				  	 
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kanan = array(
		    'borders' => array(				
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis					  
		  )
		);
		
		$border_bawah = array(
		    'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('A1', "PT. PELABUHAN INDONESIA II (PERSERO)");
		$excel->getActiveSheet()->mergeCells('A1:C1');
		$excel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('A2', "GABUNGAN");
		$excel->getActiveSheet()->mergeCells('A2:C2');
		$excel->getActiveSheet()->getStyle('A2:C2')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('A5', "PENGKREDITAN PAJAK MASUKAN SESUAI PMK 78/PMK.03/2010");
		$excel->getActiveSheet()->mergeCells('A5:L5');
		$excel->getActiveSheet()->getStyle('A5:L5')->applyFromArray($center_bold_noborder);
		
		$excel->setActiveSheetIndex(0)->setCellValue('A6', "MASA PAJAK ".$nama_bulan_upper." TAHUN ".$tahun);
		$excel->getActiveSheet()->mergeCells('A6:L6');
		$excel->getActiveSheet()->getStyle('A6:L6')->applyFromArray($center_bold_noborder);

		$excel->setActiveSheetIndex(0)->setCellValue('A7', "No.");
		$excel->setActiveSheetIndex(0)->setCellValue('A8', "1");	
		$excel->getActiveSheet()->getStyle('A7:A8')->applyFromArray($center_no_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('B7', "Nama Perusahaan");
		$excel->setActiveSheetIndex(0)->setCellValue('B8', "2");
		$excel->getActiveSheet()->getStyle('B7:B8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('C7', "Uraian Pekerjaan");
		$excel->setActiveSheetIndex(0)->setCellValue('C8', "3");	
		$excel->getActiveSheet()->getStyle('C7:C8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('D7', "No. Faktur");
		$excel->setActiveSheetIndex(0)->setCellValue('D8', "4");	
		$excel->getActiveSheet()->getStyle('D7:D8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('E7', "Tgl Faktur");
		$excel->setActiveSheetIndex(0)->setCellValue('E8', "5");	
		$excel->getActiveSheet()->getStyle('E7:E8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('F7', "DPP");
		$excel->setActiveSheetIndex(0)->setCellValue('F8', "6");	
		$excel->getActiveSheet()->getStyle('F7:F8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('G7', "PPN (PM)");
		$excel->setActiveSheetIndex(0)->setCellValue('G8', "7");	
		$excel->getActiveSheet()->getStyle('G7:G8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('H7', "Z (%)");
		$excel->setActiveSheetIndex(0)->setCellValue('H8', "8");	
		$excel->getActiveSheet()->getStyle('H7:H8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('I7', "P (SPT Masa)");
		$excel->setActiveSheetIndex(0)->setCellValue('I8', "9= 7x8");	
		$excel->getActiveSheet()->getStyle('I7:I8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('J7', "Koreksi PM");
		$excel->setActiveSheetIndex(0)->setCellValue('J8', "10=9-7");	
		$excel->getActiveSheet()->getStyle('J7:J8')->applyFromArray($center_bold_border);

		$excel->setActiveSheetIndex(0)->setCellValue('K7', "SPT Masa");
		$excel->setActiveSheetIndex(0)->setCellValue('K8', "11");	
		$excel->getActiveSheet()->getStyle('K7:K8')->applyFromArray($center_bold_border);
									
				$numrow 			= 9;
				$no 				= 1;
				$spt_masa 			= 0;
				$koreksi_pm 		= 0;
				$total_dpp 			= 0;
				$total_jumlah_ptg 	= 0;
				$total_z 			= 0;
				$total_spt 			= 0;
				$total_koreksi 		= 0;

				if($kd_cabang == "all"){
					$whereCabang = "";
					$nama_cabang = "Kompilasi";
				}
				else{
					$whereCabang = " AND kode_cabang = '".$kd_cabang."'";
					$nama_cabang = get_nama_cabang($kd_cabang);
				}

				$query = "SELECT NVL (sms.vendor_name, spl.nama_wp) vendor_name,
						       NVL (uraian_pekerjaan, invoice_num) uraian_pekerjaan,
						       no_faktur_pajak,
						       tanggal_faktur_pajak,
						       dpp,
						       CEIL(ABS(jumlah_potong * -1)) jumlah_potong_ppn,
						       '95,08%' z_percent,
						       CEIL(ABS(NVL (JUMLAH_POTONG * -1, 0) * (95.08 / 100)
						                - NVL (JUMLAH_POTONG * -1, 0)))
						          koreksi_pm,
						          spl.kode_cabang
						  FROM    simtax_pajak_lines spl
						       LEFT JOIN
						          simtax_master_supplier sms
						       ON     sms.vendor_id = spl.vendor_id
						          AND sms.organization_id = spl.organization_id
						          AND sms.vendor_site_id = spl.vendor_site_id
						 WHERE     is_pmk = 1
						       AND is_cheklist = 1
						       AND bulan_pajak = '".$bulan."'
						       AND tahun_pajak = '".$tahun."'
						       AND pembetulan_ke = '".$pembetulan."'
						       ".$whereCabang."
						       order by kode_cabang asc, invoice_num desc";

				
				$sql 	   = $this->db->query($query);
				foreach($sql->result_array() as $row)	{

					$spt_masa 		= '=G'.$numrow.'*H'.$numrow;
					$koreksi_pm 	= '=I'.$numrow.'-G'.$numrow;

					$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $no);
					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['VENDOR_NAME']);
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['URAIAN_PEKERJAAN']);
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, $row['NO_FAKTUR_PAJAK']);	
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['TANGGAL_FAKTUR_PAJAK']);	
					$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $row['DPP']);
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['JUMLAH_POTONG_PPN']);
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['Z_PERCENT']);
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $spt_masa);
					$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $koreksi_pm);
					$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $masa_pajak);
					$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, get_nama_cabang($row['KODE_CABANG']));
					
					$excel->getActiveSheet()->getStyle('F'.$numrow.':G'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
					$excel->getActiveSheet()->getStyle('I'.$numrow.':J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

					$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($center_no_bold_border);
					$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($left_no_bold_border);
					$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($left_no_bold_border);
					$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($center_no_bold_border);
					$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($center_no_bold_border);
					$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($right_no_bold_border);
					$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($right_no_bold_border);
					$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($right_no_bold_border);
					$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($right_no_bold_border);
					$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($right_no_bold_border);
					$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($right_no_bold_border);

					$total_dpp 			+= $row['DPP'];
					$total_jumlah_ptg 	+= $row['JUMLAH_POTONG_PPN'];
					$total_z 			+= $row['Z_PERCENT'];

					$numrow++;
					$no++;
				}

				$lastNumrow = $numrow-1;


				$total_spt 			= '=SUM(I9:I'.$lastNumrow.')';
				$total_koreksi 		= '=SUM(J9:J'.$lastNumrow.')';


				$excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($right_no_bold_border);

				$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Jumlah");
				$excel->getActiveSheet()->mergeCells('B'.$numrow.':E'.$numrow);	
				$excel->getActiveSheet()->getStyle('B'.$numrow.':E'.$numrow)->applyFromArray($left_bold_border_no_right);

				$excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $total_dpp);
				$excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($right_no_bold_border_no_left);

				$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $total_jumlah_ptg);
				$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($right_no_bold_border);

				$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, ceil($total_z));
				$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($right_no_bold_border);

				$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $total_spt);
				$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($right_no_bold_border);

				$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $total_koreksi);
				$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($right_no_bold_border);

				$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, "");
				$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($right_no_bold_border);

				$excel->getActiveSheet()->getStyle('F'.$numrow.':J'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');

		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20); 	
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("PMK");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Eksport_PMK_'.$tahun.'_'.$nama_bulan.'_'.$nama_cabang.'_'.$pembetulan.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}

	function show_equal_pph_gab()
	{
		$this->template->set('title', 'Laporan Ekualisasi Semua PPh');
		$data['subtitle']	= "Cetak Laporan Ekualisasi Semua PPh";
		$data['activepage'] = "laporan_ekualisasi";
		$this->template->load('template', 'laporan/lap_equalisasi_gab',$data);		
	}

	function cetak_equal_pph_gab()
	{
		$tahun 		= $_REQUEST['tahun'];
		$bulan		= $_REQUEST['bulan'];
		$masa		= $_REQUEST['namabulan'];
		$cabang		= $_REQUEST['kd_cabang'];
		$pajak		= $_REQUEST['pajak'];
		//$header_id	= $this->Pph_mdl->get_header_id_max($pajak,$bulan,$tahun,$cabang);
		$where		= "";	
		$where_23	= "";
		$nama_cabang 	= strtoupper(get_nama_cabang($cabang));

		$nama_bulan 	= get_masa_pajak($bulan,'id',true);

		if ($cabang != 'all'){
			$kd_cabang = $cabang;
		} else{
			$kd_cabang = "";
		}
					
		include APPPATH.'third_party/PHPExcel.php';
		
		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		
		// Settingan awal fil excel
		$excel->getProperties()	->setCreator('SIMTAX')
								->setLastModifiedBy('SIMTAX')
								->setTitle("Laporan Ekualisasi Semua PPh")
								->setSubject("Ekualisasi")
								->setDescription("Laporan Ekualisasi Semua PPh")
								->setKeywords("Gabungan PPh");
								
		// Buat sebuah variabel untuk menampung pengaturan style dari header tabel
		$center_bold_border = array(
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
		
		$center_no_bold_border = array(
		        'font' => array('bold' => false),
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
		
		$center_bold_noborder = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);

		$center_nobold_noborder = array(
		        'font' => array('bold' => false), 
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  )
		);	
		
		$center_bold_border_bottom_left = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN)
		  )
		);
		
		$center_bold_border_top = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(				
			 'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
			 'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis			
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$center_bold_border_bottom = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(			 
			 'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$left_bold_border = array(
		        'font' => array('bold' => true), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, // Set text jadi ditengah secara horizontal (center)
		    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kika_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$borderfull_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
				'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);			
		
		$border_kika_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$noborder_bold_rata_kiri = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
		
		$noborder_nobold_rata_kiri = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);			
		
		$noborder_nobold_rata_kanan = array(
		        'font' => array('bold' => false, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  )
		);	
			
		
		$border_top_buttom_bold_rata_kanan = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
			  'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		$parent_col = array(
		        'font' => array('bold' => true, 
								'name' => 'Calibri', 
								'size' => 11,
								'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  ),
			'borders' => array(
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);	
		
		// Buat sebuah variabel untuk menampung pengaturan style dari isi tabel
		$style_row = array(
		   'alignment' => array(
		 	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
		  ),
		  'borders' => array(
			  'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
		    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
		   'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
			 'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kiri = array(
		    'borders' => array(				  	 
			   'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);
		
		$border_kanan = array(
		    'borders' => array(				
			  'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis					  
		  )
		);
		
		$border_bawah = array(
		    'borders' => array(				
			 'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
		  )
		);

		$noborder_rata_kiri = array(
		        'font' => array('name' => 'Calibri', 
								'size' => 11), // Set font nya jadi bold
		   'alignment' => array(
		  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT // Set text jadi ditengah secara horizontal (center)
		  )
		);

		$center_border = array(
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

		$kantor = "";	

		if($cabang == 'all'){
			$kantor = "PT PELABUHAN INDONESIA II (PERSERO)";
		}else{
			$kantor = "PT PELABUHAN INDONESIA II CABANG ".$nama_cabang;
		}			
		
		//buat header cetakan
		$excel->setActiveSheetIndex(0)->setCellValue('B1', $kantor);
		$excel->getActiveSheet()->mergeCells('B1:D1');	
		$excel->getActiveSheet()->getStyle('B1:D1')->applyFromArray($noborder_bold_rata_kiri);

		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Ekualisasi Objek Semua PPh");
		$excel->getActiveSheet()->mergeCells('B2:D2');	
		$excel->getActiveSheet()->getStyle('B2:D2')->applyFromArray($noborder_rata_kiri);
		
		$excel->setActiveSheetIndex(0)->setCellValue('B3', "Bulan ".$nama_bulan);
		$excel->getActiveSheet()->mergeCells('B3:E3');
		$excel->getActiveSheet()->getStyle('B3:E3')->applyFromArray($noborder_rata_kiri);		
		
		$excel->setActiveSheetIndex(0)->setCellValue('F4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('F4:H4');	
		$excel->getActiveSheet()->getStyle('F4:H4')->applyFromArray($center_border);
		
		/*$excel->setActiveSheetIndex(0)->setCellValue('I4', "Keterangan");
		$excel->getActiveSheet()->mergeCells('I4:I5');	
		$excel->getActiveSheet()->getStyle('I4:I5')->applyFromArray($center_border);*/
		
		$excel->setActiveSheetIndex(0)->setCellValue('B4', "Account");
		$excel->getActiveSheet()->mergeCells('B4:B5');
		$excel->getActiveSheet()->getStyle('B4:B5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('C4', "Uraian");
		$excel->getActiveSheet()->mergeCells('C4:C5');	
		$excel->getActiveSheet()->getStyle('C4:C5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('D4', "Klas");
		$excel->getActiveSheet()->mergeCells('D4:D5');	
		$excel->getActiveSheet()->getStyle('D4:D5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('E4', "Jumlah Biaya");
		$excel->getActiveSheet()->mergeCells('E4:E5');
		$excel->getActiveSheet()->getStyle('E4:E5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('G5', "Objek PPh Pasal 21");	
		$excel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "Bukan Objek");
		$excel->getActiveSheet()->getStyle('H5')->applyFromArray($center_border);
		
		$excel->setActiveSheetIndex(0)->setCellValue('H5', "PPh Pasal 21");			
		$excel->getActiveSheet()->getStyle('H5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('I4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('I4:J4');	
		$excel->getActiveSheet()->getStyle('I4:J4')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('I5', "Objek PPh Pasal 15");	
		$excel->getActiveSheet()->getStyle('I5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('J5', "PPh Pasal 15");			
		$excel->getActiveSheet()->getStyle('J5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('K4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('K4:L4');	
		$excel->getActiveSheet()->getStyle('K4:L4')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('K5', "Objek PPh Pasal 22");	
		$excel->getActiveSheet()->getStyle('K5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('L5', "PPh Pasal 22");			
		$excel->getActiveSheet()->getStyle('L5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('M4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('M4:N4');	
		$excel->getActiveSheet()->getStyle('M4:N4')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('M5', "Objek PPh Pasal 23");	
		$excel->getActiveSheet()->getStyle('M5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('N5', "PPh Pasal 23");			
		$excel->getActiveSheet()->getStyle('N5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('O4', "Penjelasan Menurut WP");
		$excel->getActiveSheet()->mergeCells('O4:P4');	
		$excel->getActiveSheet()->getStyle('O4:P4')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('O5', "Objek PPh Pasal 42");	
		$excel->getActiveSheet()->getStyle('O5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('P5', "PPh Pasal 42");			
		$excel->getActiveSheet()->getStyle('P5')->applyFromArray($center_border);

		$excel->setActiveSheetIndex(0)->setCellValue('Q4', "Keterangan");
		$excel->getActiveSheet()->mergeCells('Q4:Q4');			
		$excel->getActiveSheet()->getStyle('Q4:Q5')->applyFromArray($center_border);
		
		// end header	
			$no = 1; 
			$numrow = 6;					
			$numrowBorderStart = 6;

			if ($kd_cabang == ""){
				$whereCabang = " '000','010','020','030','040','050', '060','070','080','090','100','110','120'";
			} else{
				$whereCabang = "'".$kd_cabang."'";
			}					
				
					$where		.= " and kode_cabang in (".$whereCabang.") ";
					$where_23	.= " and sph.kode_cabang in (".$whereCabang.") ";
				if($bulan) {
					$where 		.= " and bulan_pajak= '".$bulan."' ";
					$where_23 	.= " and sph.bulan_pajak= '".$bulan."' ";
				}				
				$queryExecSub8 = "Select tb.kode_akun	
									, o23.kode_akun kd23
									, tb.akun_description
									, tb.jumlah_tb1 jumlah_tb
									, nvl(O23.nil_objek_23,0) nil23
									, nvl(O15.nil_objek_15,0) nil15
									, nvl(O22.nil_objek_22,0) nil22
									, nvl(O42.nil_objek_42,0) nil42
									, nvl(O21.nil_objek_21,0) nil21
									, case	
										when SUBSTR(TB.kode_akun,1,1)=8 then 1
										when SUBSTR(TB.kode_akun,1,2)=31 then 2
										when SUBSTR(TB.kode_akun,1,3)=207 then 3
										when SUBSTR(TB.kode_akun,1,3) between 103 and 106 then 4
										when SUBSTR(TB.kode_akun,1,3) in (203,110,107) then 5
										when SUBSTR(TB.kode_akun,1,3) in (208,209) then 6
										else 7										
									  end
										urut
									from
										(
										Select kode_akun, akun_description
												, (sum(nvl(DEBIT,0)) - sum(nvl(CREDIT,0))) jumlah_tb1 	
											from SIMTAX_RINCIAN_BL_PPH_BADAN 
											where tahun_pajak= '".$tahun."' 
												".$where." 
												and SUBSTR(kode_akun,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)												 
											group by kode_akun, akun_description 		
										) tb,
										(
											select kode_akun,sum(begin_balance) begin_balance from (
												select kode_akun, kode_cabang, begin_balance												
												from simtax_rincian_bl_pph_badan
												where tahun_pajak = '".$tahun."' 
												".$where." 
												and SUBSTR(kode_akun,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)
												group by kode_akun, kode_cabang, begin_balance
											)
											group by kode_akun
										) bb,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_23 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."'
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1
												AND sph.nama_pajak = 'PPH PSL 23 DAN 26'
												and Substr(SPL.GL_ACCOUNT,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)													
											group by SPL.GL_ACCOUNT
										) o23,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_15 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."' 
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1
												AND sph.nama_pajak = 'PPH PSL 15'
												and Substr(SPL.GL_ACCOUNT,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)													
											group by SPL.GL_ACCOUNT
										) o15,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_22 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."' 
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1
												AND sph.nama_pajak = 'PPH PSL 22'
												and Substr(SPL.GL_ACCOUNT,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)													
											group by SPL.GL_ACCOUNT
										) o22,
										(
											select SPL.GL_ACCOUNT kode_akun, nvl(sum(nvl(spl.NEW_DPP,spl.DPP)),0) nil_objek_42 
											from SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
											where SPL.PAJAK_HEADER_ID=SPH.PAJAK_HEADER_ID
												and SPH.TAHUN_PAJAK= '".$tahun."' 
												".$where_23."
												and upper(SPL.IS_CHEKLIST) =1
												AND sph.nama_pajak = 'PPH PSL 4 AYAT 2'
												and Substr(SPL.GL_ACCOUNT,1,3) in (107,109,199,301,302,305,306,310,721,791,801,891)													
											group by SPL.GL_ACCOUNT
										) o42,
																	(SELECT SPL.GL_ACCOUNT kode_akun,
							                   NVL (SUM (NVL (spl.NEW_DPP, spl.DPP)), 0) nil_objek_21
							              FROM SIMTAX_PAJAK_LINES spl, SIMTAX_PAJAK_HEADERS sph
							             WHERE     SPL.PAJAK_HEADER_ID = SPH.PAJAK_HEADER_ID
							                   and SPH.TAHUN_PAJAK= '".$tahun."' 
											   ".$where_23."
							                   AND UPPER (SPL.IS_CHEKLIST) = 1
							                   AND sph.nama_pajak = 'PPH PSL 21'
							                   AND SUBSTR (SPL.GL_ACCOUNT, 1, 3) IN
							                   	   (107,109,199,301,302,305,306,310,721,791,801,891)
							          GROUP BY SPL.GL_ACCOUNT) o21
								 where tb.KODE_AKUN=O23.KODE_AKUN (+)
								 	   and tb.KODE_AKUN=O15.KODE_AKUN (+)
								 	   and tb.KODE_AKUN=O22.KODE_AKUN (+)
								 	   and tb.KODE_AKUN=O42.KODE_AKUN (+)
								 	   and tb.KODE_AKUN=O21.KODE_AKUN (+)
									   and tb.KODE_AKUN=bb.KODE_AKUN (+)
								 order by TB.KODE_AKUN";
				
				$querySub8			= $this->db->query($queryExecSub8);
				$sum_tb            	= 0;
				$sum_bukan_objek15 	= 0;			
				$sum_objek15       	= 0;
				$sum_bukan_objek22 	= 0;			
				$sum_objek22       	= 0;
				$sum_bukan_objek23 	= 0;			
				$sum_objek23       	= 0;
				$sum_bukan_objek42 	= 0;			
				$sum_objek42       	= 0;
				$sum_bukan_objek21  = 0;
				$sum_objek21       	= 0;
				foreach($querySub8->result_array() as $row)	{							
					// List Akun		
					$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $row['KODE_AKUN']);								
					$excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, $row['AKUN_DESCRIPTION']);								
					$excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, "");							
					$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $row['JUMLAH_TB']);
					$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $row['NIL21']);
					$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $row['JUMLAH_TB']-$row['NIL21']);	
					$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $row['NIL15']);
					$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $row['JUMLAH_TB']-$row['NIL15']);				
					$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $row['NIL22']);
					$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $row['JUMLAH_TB']-$row['NIL22']);
					$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $row['NIL23']);
					$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $row['JUMLAH_TB']-$row['NIL23']);
					$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $row['NIL42']);
					$excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $row['JUMLAH_TB']-$row['NIL42']);		
					
					$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);					
					$excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);					
					$excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($noborder_nobold_rata_kiri);
					$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('K'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('L'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('M'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('N'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('O'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
					$excel->getActiveSheet()->getStyle('P'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);			
					
					$excel->getActiveSheet()->getStyle('E'.$numrow.':P'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
										
					$sum_tb            += $row['JUMLAH_TB'];
					$sum_bukan_objek15 += ($row['JUMLAH_TB']-$row['NIL15']);		
					$sum_objek15       += $row['NIL15'];
					$sum_bukan_objek22 += ($row['JUMLAH_TB']-$row['NIL22']);		
					$sum_objek22       += $row['NIL22'];
					$sum_bukan_objek23 += ($row['JUMLAH_TB']-$row['NIL23']);		
					$sum_objek23       += $row['NIL23'];
					$sum_bukan_objek42 += ($row['JUMLAH_TB']-$row['NIL42']);		
					$sum_objek42       += $row['NIL42'];
					$sum_bukan_objek21 += ($row['JUMLAH_TB']-$row['NIL21']);		
					$sum_objek21       += $row['NIL21'];	
					$numrow++;
			}
						
		
		$numrow+=1;
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, "Total Objek PPh");
		$excel->getActiveSheet()->mergeCells('B'.$numrow.':D'.$numrow);	
		$excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $sum_tb);
		$excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $sum_objek21);
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $sum_bukan_objek21);	
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $sum_objek15);	
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $sum_bukan_objek15);
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$numrow, $sum_objek22);	
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$numrow, $sum_bukan_objek22);
		$excel->setActiveSheetIndex(0)->setCellValue('M'.$numrow, $sum_objek23);	
		$excel->setActiveSheetIndex(0)->setCellValue('N'.$numrow, $sum_bukan_objek23);
		$excel->setActiveSheetIndex(0)->setCellValue('O'.$numrow, $sum_objek42);	
		$excel->setActiveSheetIndex(0)->setCellValue('P'.$numrow, $sum_bukan_objek42);	
				
		$excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($noborder_rata_kiri);
		$excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);
		$excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($noborder_nobold_rata_kanan);		
		$excel->getActiveSheet()->getStyle('E'.$numrow.':P'.$numrow)->getNumberFormat()->setFormatCode('_(#,##_);_(\(#,##\);_("-"??_);_(@_)');
		
		// Set width kolom
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(2); 
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(10); 
		$excel->getActiveSheet()->getColumnDimension('C')->setWidth(60); 
		$excel->getActiveSheet()->getColumnDimension('D')->setWidth(8); 
		$excel->getActiveSheet()->getColumnDimension('E')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('F')->setWidth(2); 
		$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('H')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20); 
		$excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
		$excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
		
		
		
		// Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
		$excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
		
		// Set orientasi kertas jadi LANDSCAPE
		$excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set judul file excel nya
		$excel->getActiveSheet(0)->setTitle("Lap Equal Semua PPh");
		$excel->setActiveSheetIndex(0);
		
		// Proses file excel
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Laporan Ekualisasi Semua PPh '.$masa.' '.$tahun.'.xls"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
		
	}
		
}
