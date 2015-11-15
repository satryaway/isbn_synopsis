<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Buku extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        //$this->methods['buku_get']['limit'] = 500; // 500 requests per hour per user/key
        //$this->methods['buku_post']['limit'] = 100; // 100 requests per hour per user/key
        //$this->methods['buku_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

    public function buku_get()
    {
		$response = array();
		$data = array();
		
		$key = $this->get('key');
        $value = $this->get('value');
		
		if ($key === NULL) {
			$query = $this->db->query("SELECT * FROM book");
            if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
				array_push($data, array(
                        'id' => $row->id,
                        'kode' => $row->kode,
                        'judul' => $row->judul,
                        'penulis' => $row->penulis,
                        'isbn' => $row->isbn,
                        'sinopsis' => $row->sinopsis,
						'cover' => $row->cover
						));
				}
				$response['status'] = 1;
                $response['message'] = "Data retreived";
                $response['return_data'] = $data;
				$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			} else {
				$response['status'] = 0;
                $response['message'] = "Book not found";                 
				$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}
		} else {
			if ($key == 'judul') {
				$query = $this->db->query("SELECT * FROM book WHERE $key LIKE '%$value%'");
			} else {
				$query = $this->db->query("SELECT * FROM book WHERE $key = '$value'");
			}
			
			if ($query->num_rows() >  0) {
					foreach ($query->result() as $row) {
					array_push($data, array(
							'id' => $row->id,
							'kode' => $row->kode,
							'judul' => $row->judul,
							'penulis' => $row->penulis,
							'isbn' => $row->isbn,
							'sinopsis' => $row->sinopsis,
							'cover' => $row->cover
							));
					}				
					$response['status'] = 1;
					$response['message'] = "Data retreived";
					$response['return_data'] = $data;
					$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			} else {
					$response['status'] = 0;
					$response['message'] = "Book not found";                
					$this->response($response, REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
			}
		}
    }

    public function buku_post()
    {
        $response = array();
		$data = array();
		
		$kode = $this->post('kode');
		$judul = $this->post('judul');
		$penulis = $this->post('penulis');
		$isbn = $this->post('isbn');
		$sinopsis = $this->post('sinopsis');
		$isUpdate = $this->post('isUpdate');
		
		if ($isUpdate == true) 
		{
			$this->update_book();
		} 
		else 
		{	
			//---------
			if (isset($_FILES['cover']) && !empty($_FILES['cover']['name'])) 
			{
				$filename = uniqid();
				$config['upload_path'] = "./assets/images/";
				$config['file_name'] = $filename;
				$config['allowed_types'] = "gif|jpg|png|jpeg";
				$config['max_size'] = '5120';
				$config['overwrite'] = false;
				
				$this->load->library('upload', $config);
				
				if ($this->upload->do_upload('cover')) {
					$upload_data = $this->upload->data();
					$_POST['cover'] = $upload_data;

					$name = $upload_data['file_name'];
					$ext = end((explode(".",$name)));

					$cover = $config['file_name'].'.'.$ext;
				} else {
				$response['status'] = 0;
				$response['message'] = "Failed uploading cover image";
				$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
				}
			}
			//---------
			
			$query = $this->db->query('
					INSERT INTO `book` (
						`kode`,
						`judul`,
						`penulis`,
						`isbn`,
						`sinopsis`,
						`cover`
					) VALUES (
						"'.$kode.'",
						"'.$judul.'",
						"'.$penulis.'",
						"'.$isbn.'",
						"'.$sinopsis.'",
						"'.$cover.'"
					)
				');

			if ($this->db->affected_rows() == 1) {
				$response['status'] = 1;
				$response['message'] = "You have successfully inputted a book";
				$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code	
			} else {
				$response['status'] = 0;
				$response['message'] = "Failed inputting a book";
				$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}
		}
    }
	
	public function update_book()
    {
        $response = array();
		$data = array();
		
		$id = $this->post('id');
		$kode = $this->post('kode');
		$judul = $this->post('judul');
		$penulis = $this->post('penulis');
		$isbn = $this->post('isbn');
		$sinopsis = $this->post('sinopsis');
		
		//---------
		if (isset($_FILES['cover']) && !empty($_FILES['cover']['name'])) 
		{
			$filename = uniqid();
			$config['upload_path'] = "./assets/images/";
			$config['file_name'] = $filename;
			$config['allowed_types'] = "gif|jpg|png|jpeg";
			$config['max_size'] = '5120';
			$config['overwrite'] = false;
			
			$this->load->library('upload', $config);
			
			if ($this->upload->do_upload('cover')) {
				$upload_data = $this->upload->data();
				$_POST['cover'] = $upload_data;

				$name = $upload_data['file_name'];
				$ext = end((explode(".",$name)));

				$cover = $config['file_name'].'.'.$ext;
				$q = "UPDATE `book` SET 
                    `kode` = '$kode',
                    `judul` = '$judul',
                    `penulis` = '$penulis',
                    `isbn` = '$isbn',
                    `sinopsis` = '$sinopsis',
                    `cover` = '$cover'
					WHERE id = '$id'";
			} else {
				$response['status'] = 0;
				$response['message'] = "Failed uploading cover image";
				$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}
		} else {
			$q = "UPDATE `book` 
					SET 
                    `kode` = $kode,
                    `judul` = '$judul',
                    `penulis` = '$penulis',
                    `isbn` = '$isbn',
                    `sinopsis` = '$sinopsis'
					WHERE id = '$id'";
		}
		//---------
		
		//die($q);
		
		$query = $this->db->query($q);

        if ($this->db->affected_rows() == 1) {
			$response['status'] = 1;
			$response['message'] = "You have successfully updated a book";
			$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code	
		} else {
			$response['status'] = 0;
			$response['message'] = "Failed updating a book";
			$this->response($response, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		}
    }

    public function buku_delete()
    {
        $id = (int) $this->get('id');

        // Validate the id.
        if ($id <= 0)
        {
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];

        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }

}
