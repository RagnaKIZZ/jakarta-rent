<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author		Wanda Azhar
 * @link        wandaazhar@gmail.com
 * @copyright	(c) 2018
 * 
 */

class Wandalibs
{
    public function __construct()
    {
        $CI = &get_instance();
        $this->db = $CI->load->database('default', TRUE);
    }

    function _checkLoginSession()
    {
        $CI = &get_instance();
        if (!empty($CI->session->userdata('email'))) {
            // redirect('dashboard');
        } else {
            redirect('auth/login');
        }
    }

    function _setSessionUser()
    {
        $CI = &get_instance();
        $email      = htmlspecialchars($CI->input->post('email'), true);
        $query = $CI->db->get_where('tb_user_admin', ['email' => $email])->row_array();
        $data = [
            'id'                => $query['id'],
            'email'             => $query['email'],
            'nama'              => $query['nama'],
            'no_hp'             => $query['no_hp'],
            'user_access'       => $query['user_access'],
            'foto'              => $query['foto'],
            'bidang'            => $query['bidang'],
            'active'            => $query['active'],
            'date_created'      => $query['date_created']
        ];
        $CI->session->set_userdata($data);
    }

    function _insertLoginTime()
    {
        $CI = &get_instance();
        $email      = htmlspecialchars($CI->input->post('email'), true);
        $query = $CI->db->get_where('tb_user_admin', ['email' => $email])->row_array();
        $nama = $query['nama'];

        $data = [
            'nama'          => $nama,
            'email'         => $email,
            'date_created'  => time()
        ];

        $CI->db->insert('history_login', $data);
    }

    function redirectLoginExist()
    {
        $CI = &get_instance();
        if ($CI->session->userdata('nama')) {
            redirect('dashboard');
        }
    }

    function _loginProcess()
    {
        $CI = &get_instance();
        $email      = htmlspecialchars($CI->input->post('email'), true);
        $password   = htmlspecialchars($CI->input->post('password'), true);

        $query = $CI->db->get_where('tb_user_admin', ['email' => $email])->row_array();
        $nama = $query['nama'];

        if ($query['active'] == 'tidak aktif') {
            $CI->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-close"></i><small><b>Maaf!</b>. Akun Anda tidak aktif, Silahkan cek Email untuk verifikasi</small>
            </div>');
            redirect('auth/login');
        }
        if (password_verify($password, $query['password'])) {
            $CI->wandalibs->_setSessionUser();
            $CI->wandalibs->_insertLoginTime();
            $CI->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i><b>Berhasil</b> Login! &nbsp;
            Selamat Datang <b>' . $nama . '</b>
            </div>');
            redirect('dashboard');
        } elseif ($query['email'] != $email) {
            $CI->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-close"></i><small><b>Ups!</b>. Email belum terdaftar</small>
            </div>');
            redirect('auth/login');
        } elseif ($query['password'] != $password) {
            $CI->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-close"></i><small><b>Ups!</b>. Password Anda salah!</small>
            </div>');
            redirect('auth/login');
        }
    }

    function _loginProcessFirstTime()
    {
        $CI = &get_instance();
        $email      = htmlspecialchars($CI->input->post('email'), true);
        $password   = htmlspecialchars($CI->input->post('password'), true);

        $query = $CI->db->get_where('tb_user_admin', ['email' => $email])->row_array();
        $nama = $query['nama'];

        if ($query['active'] == 'tidak aktif') {
            $CI->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i><small><b>Status</b>. Akun Anda sudah aktif sejak tanggal ' . date('d F Y', $query['date_created']) . '</small>
            </div>');
            redirect('auth/loginFirstTime');
        } elseif (password_verify($password, $query['password'])) {
            $CI->wandalibs->_setSessionUser();
            $CI->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h6><i class="icon fas fa-check"></i> Berhasil Login!</h6>
            Selamat datang <b>' . $nama . '</b>
            </div>');
            redirect('dashboard');
        } elseif ($query['email'] != $email) {
            $CI->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-close"></i><small><b>Ups!</b>. Email belum terdaftar</small>
            </div>');
            redirect('auth/loginFirstTime');
        } elseif ($query['password'] != $password) {
            $CI->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-close"></i><small><b>Ups!</b>. Password Anda salah!</small>
            </div>');
            redirect('' . $_SERVER['HTTP_REFERER'] . '');
        }
    }

    function _doLogout()
    {
        $CI = &get_instance();
        $email  = $CI->session->userdata('email');
        $query  = $CI->db->get_where('tb_user', ['email' => $email])->row_array();
        $dataSession = [
            'id'        => $query['id'],
            'email'     => $query['email'],
            'nama'      => $query['nama'],
            'no_hp'      => $query['no_hp'],
            'foto'      => $query['foto']
        ];
        $CI->session->unset_userdata('id');
        $CI->session->unset_userdata('email');
        $CI->session->unset_userdata('nama');
        $CI->session->unset_userdata('no_hp');
        $CI->session->unset_userdata('foto');
        $CI->session->unset_userdata('date_created');
        // $CI->session->sess_destroy($dataSession);
        $CI->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible fade show">
            <button type="button" aria-hidden="true" class="close" data-dismiss="alert" aria-label="Close">
              <i class="nc-icon nc-simple-remove"></i>
            </button>
            <span><small><b>Terimakasih - </b> Anda telah berhasil logout </small></span>
          </div>');
        redirect('auth/login');
    }

    function _sendEmail($token, $type)
    {
        $CI = &get_instance();
        //load library send email CodeIgniter
        $CI->load->library('email');
        $config = [
            'protocol'      => 'smtp',
            'smtp_host'     => 'ssl://smtp.googlemail.com',
            'smtp_user'     => 'promkesrsutangsel@gmail.com',
            'smtp_pass'     => 'PkRs2017',
            'smtp_port'     =>  465,
            'mailtype'      => 'html',
            'charset'       => 'utf-8'
        ];

        //verify/inisialisasi smtp port di server localhost
        $CI->email->initialize($config);
        $CI->email->set_newline("\r\n");

        $CI->email->from('promkesrsutangsel@gmail.com', 'Promkes RSU Tangsel');
        $CI->email->to($CI->input->post('email'));
        $pesan_keluar   = $CI->input->post('pesan_keluar');
        //cek kondisi tipe
        if ($type == 'verify') {
            $CI->email->subject('Verifikasi Akun RSU Kota Tangsel');
            $CI->email->message('<h3 style="color: blue;">Terimakasih Anda telah mendaftar <br> Klik Link ini untuk verifikasi akun Anda : <h3> <br> <h3>Password Anda adalah: </h3> <a href="' . base_url() . 'auth/pageVerifikasiAkun?email=' . $CI->input->post('email') . '&token=' . urlencode($token) . '"><button style="color: #fff; background-color: blue;" >Aktikan</button></a>');
        } else if ($type == 'forgot') {
            $CI->email->subject('Reset Password');
            $CI->email->message('<h3 style="color: blue;">Hallo<br> Klik Link ini untuk mereset password kamu: <h3> <a href="' . base_url() . 'register/resetPassword?email=' . $CI->input->post('email') . '&token=' . urlencode($token) . '"><button style="color: #fff; background-color: blue;" >Reset Password</button></a>');
        } else if ($type == 'compose') {
            $CI->email->subject('Layanan Pengaduan RSU Kota Tangerang Selatan');
            $CI->email->message($pesan_keluar);
        } else if ($type == 'balas_inbox') {
            $CI->email->subject('Layanan Pengaduan RSU Kota Tangerang Selatan');
            $CI->email->message($pesan_keluar);
        }

        if ($CI->email->send()) {
            return true;
        } else {
            echo $CI->email->print_debugger();
            die;
        }
    }

    function _getToken($length = 6)
    {
        $characters = '0123456789';
        $characters_length = strlen($characters);
        $output = '';
        for ($i = 0; $i < $length; $i++)
            $output .= $characters[rand(0, $characters_length - 1)];

        return $output;
    }


    function countNotif($email)
    {
        $CI = &get_instance();
        $email    = $CI->session->userdata('email');
        $query = $CI->db->get_where('pesan', ['email' => $email]);
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    function getNotif($email)
    {
        $CI = &get_instance();
        return $CI->db->query("SELECT COUNT(`pesan`.`email`) FROM `pesan` WHERE `pesan`.`email` = '$email'")->row_array();
    }

    function regHash($par, $length = 6)
    {

        $keyHash = '';
        $chars     = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        for ($i = 0; $i < $length; $i++) {
            $x = mt_rand(0, strlen($chars) - 1);
            $keyHash .= $chars{
                $x};
        }
        $return = '' . $par . '_' . $keyHash . '';
        return $return;
    }

    function _lastLoginUserById($email)
    {
        $CI = &get_instance();
        return $CI->db->query("SELECT `history_login`.`date_created` FROM `history_login` WHERE `email` = '$email' ORDER BY `history_login`.`id` DESC")->row_array();
    }

    function countLoginUserById($email)
    {
        $CI = &get_instance();
        $query = $CI->db->query("SELECT `history_login`.`id` FROM `history_login` WHERE `email` = '$email'");
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    function getAllPesanPenunjang()
    {
        $CI = &get_instance();
        $query = $CI->db->query("SELECT `pesan`.`id` FROM `pesan` WHERE `pesan`.`bidang` = 'penunjang'");
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    function getAllPesanKeperawatan()
    {
        $CI = &get_instance();
        $query = $CI->db->query("SELECT `pesan`.`id` FROM `pesan` WHERE `pesan`.`bidang` = 'keperawatan'");
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    function getAllPesanYanmed()
    {
        $CI = &get_instance();
        $query = $CI->db->query("SELECT `pesan`.`id` FROM `pesan` WHERE `pesan`.`bidang` = 'yanmed'");
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }
    function getAllPesanUmum()
    {
        $CI = &get_instance();
        $query = $CI->db->query("SELECT `pesan`.`id` FROM `pesan` WHERE `pesan`.`bidang` = 'umum'");
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    function selisihWaktuBalasByDay($tgl_masuk, $tgl_balas)
    {
        // $dateDiff = '';
        $tgl_masuk = strtotime($tgl_masuk);
        $tgl_balas = strtotime($tgl_balas);
        $diff = $tgl_balas - $tgl_masuk;
        return round($diff / (60 * 60 * 24));
    }

    function selisihWaktuBalasByHour($jam_masuk, $jam_balas)
    {
        $jam_masuk = strtotime($jam_masuk);
        $jam_balas = strtotime($jam_balas);
        // $diff = date_diff($jam_balas, $jam_masuk);
        // return $diff;
        $diff      = $jam_balas - $jam_masuk;
        return round($diff / (60 * 60));
        // return round($diff / (60 * 60 * 24));
    }
}
