<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load library phpspreadsheet
require('./excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// End load library phpspreadsheet

class Laporan extends CI_Controller
{

    // Load model
    public function __construct()
    {
        parent::__construct();
        $this->load->model('provinsi_model');
        $this->load->helper('form');
    }

    // Main page
    public function index()
    {
        $provinsi = $this->provinsi_model->listing();
        $data = array(
            'title' => 'Laporan Exel - Java Media',
            'provinsi' => $provinsi
        );
        $this->load->view('laporan_view', $data, FALSE);
    }

    // Export ke excel
    public function export()
    {
        $provinsi = $this->provinsi_model->listing();
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'KODE PROVINSI')
            ->setCellValue('B1', 'NAMA PROVINSI');

        // Miscellaneous glyphs, UTF-8
        $i = 2;
        foreach ($provinsi as $provinsi) {

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $provinsi->id_provinsi)
                ->setCellValue('B' . $i, $provinsi->nama_provinsi);
            $i++;
        }

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Report Excel ' . date('d-m-Y H'));

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Report Excel.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function do_upload()
    {
        $config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'xlsx';
        $config['max_size']             = 100;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            echo "File nya ga dapet";
        } else {
            $data = array('upload_data' => $this->upload->data());
            $M = $this->upload->data();
            //print_r($M);
            $this->import($M['file_name']);
        }
    }

    public function import($nf)
    { //$nf nama filenya
        //BAGIAN BACA DULU----------------------------------------------------
        //INGET ya uploads harus setara dengan application
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load('uploads/' . $nf);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        print("ini mengimport");
        //var_dump($sheetData);
        print(count($sheetData));
        //Bagian data mulai ke dua, berarti indeks 1
        for ($i = 1; $i < count($sheetData); $i++) {
            $id = $sheetData[$i][0]; #id
            $nama_prov = $sheetData[$i][1]; #nama
            echo "<br>" . $id . " " . $nama_prov;
        }

        //menulis ke database nih baru, sebenernya bisa disisipkan di bagian atas pas looping
        //tapi gapapa biar jelas
        for ($i = 1; $i < count($sheetData); $i++) {
            //perhatikan indeks harus sama dengan field atau column di database
            $data[$i]['id_provinsi'] = $sheetData[$i][0]; #id
            $data[$i]['nama_provinsi'] = $sheetData[$i][1]; #nama
        }
        print_r($data);
        $this->provinsi_model->tuliskedb($data);
    }
}
