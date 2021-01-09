<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Provinsi_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Listing
    public function listing()
    {
        $this->db->select('*');
        $this->db->from('provinsi');
        $this->db->order_by('id_provinsi', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function tuliskedb($databanyak)
    {
        echo "<p>Sampe di model nih<p>";
        print_r($databanyak);
        foreach ($databanyak as $baris) {
            print_r($baris);
            print("<p>");
            $this->db->insert('provinsi', $baris);
        }
    }
}
