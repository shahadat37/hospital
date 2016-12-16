<?php

class Settings extends CI_Controller {

    function __construct() {
        parent::__construct();
        
		$this->load->model('menu_model');
        $this->load->model('settings_model');
		$this->load->model('module/module_model');
        
		$this->load->helper('url');
		$this->load->helper('currency_helper');
        $this->load->helper('form');
		$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->helper('unzip_helper');
		
		$this->lang->load('main');
        
		$this->load->library('form_validation');
		$this->load->library('session');
		
    }
	
	
	/** File Upload for Clinic Logo Image */
	function do_logo_upload() {
        $config['upload_path'] = './images/';
		$config['allowed_types'] = 'jpg|png';
		$config['max_size'] = '100';
		$config['max_width'] = '1024';
		$config['max_height'] = '768';
		$config['overwrite'] = TRUE;
		$config['file_name'] = 'logo';

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('clinic_logo')) {
			$error = array('error' => $this->upload->display_errors());
			return $error;
		} else {
			$data = array('upload_data' => $this->upload->data());
			return $data['upload_data'];
		}
    }
	public function clinic() {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$active_modules = $this->module_model->get_active_modules();
			if (in_array("centers", $active_modules)) {
				$data['clinics'] = $this->settings_model->get_all_clinics();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('centers/all_centers',$data);
				$this->load->view('templates/footer');
			}else{
				$this->form_validation->set_rules('clinic_name', 'Clinic Name', 'required');
				$this->form_validation->set_rules('start_time', 'Clinic Start Time', 'required');
				$this->form_validation->set_rules('end_time', 'Clinic End Time', 'required');
				$this->form_validation->set_rules('email', 'Email', 'valid_email');

				if ($this->form_validation->run() === FALSE) {
					$data['active_modules'] = $this->module_model->get_active_modules();
					$data['clinic'] = $this->settings_model->get_clinic_settings();
					$data['def_timeformate']=$this->settings_model->get_time_formate();
					$this->load->view('templates/header');
					$this->load->view('templates/menu');
					$this->load->view('settings/clinic', $data);
					$this->load->view('templates/footer');
				} else {
					$this->settings_model->save_clinic_settings();
					$file_upload = $this->do_logo_upload(); 
					
					//Error uploading the file
					if(isset($file_upload['error']) && $file_upload['error']!='<p>You did not select a file to upload.</p>'){
						$data['error'] = $file_upload['error'];		
					}elseif(isset($file_upload['file_name'])){
						$file_name = $file_upload['file_name'];
						$this->settings_model->update_clinic_logo($file_name);	
					}
					$data['active_modules'] = $this->module_model->get_active_modules();
					$data['clinic'] = $this->settings_model->get_clinic_settings();
					$data['def_timeformate']=$this->settings_model->get_time_formate();
					$this->load->view('templates/header');
					$this->load->view('templates/menu');
					$this->load->view('settings/clinic', $data);
					$this->load->view('templates/footer');
				}
			}
        }
    }
	public function remove_clinic_logo(){
		$this->settings_model->remove_clinic_logo();	
		$this->clinic();
	}
    public function working_days() {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$data['def_dateformate']=$this->settings_model->get_date_formate();
			$data['working_days'] = $this->settings_model->get_working_days();
			$data['exceptional_days'] = $this->settings_model->get_exceptional_days();
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('settings/working_days',$data);
			$this->load->view('templates/footer');
		}
	}
	public function save_working_days(){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$this->settings_model->save_working_days();
			$this->working_days();
		}
	}
	public function save_exceptional_days(){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$this->form_validation->set_rules('working_date', 'Date', 'required');
			$this->form_validation->set_rules('working_status', 'Status', 'required');
            if ($this->form_validation->run() === FALSE) {
				
			}else{
				$this->settings_model->save_exceptional_days();	
			}
			$this->working_days();
		}
	}
	public function update_exceptional_days(){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$uid = $this->input->post('uid');
			$this->form_validation->set_rules('working_date', 'Date', 'required');
			$this->form_validation->set_rules('working_status', 'Status', 'required');
            if ($this->form_validation->run() === FALSE) {
				$this->edit_exceptional_days($uid);
			}else{
				$this->settings_model->update_exceptional_days($uid);	
				$this->working_days();
			}
			
		}
	}
	public function delete_exceptional_days($uid){
		$this->settings_model->delete_exceptional_days($uid);
		$this->working_days();
	}
	public function edit_exceptional_days($uid){
		$data['exceptional'] = $this->settings_model->get_exceptional_day($uid);
		$data['def_dateformate']=$this->settings_model->get_date_formate();
		$this->load->view('templates/header');
		$this->load->view('templates/menu');
		$this->load->view('settings/edit_working_days',$data);
		$this->load->view('templates/footer');
	}
	public function invoice() {
        if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
            $this->load->helper('form');
            $this->load->library('form_validation');

            $this->form_validation->set_rules('left_pad', 'Left Pad', 'required');
            $this->form_validation->set_rules('currency_symbol', 'Currency Symbol', 'required');

            if ($this->form_validation->run() === FALSE) {
                $data['invoice'] = $this->settings_model->get_invoice_settings();
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('invoice', $data);
                $this->load->view('templates/footer');
            } else {
                $this->settings_model->save_invoice_settings();
                $data['invoice'] = $this->settings_model->get_invoice_settings();
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('invoice', $data);
                $this->load->view('templates/footer');
            }
        }
    }
	public function change_settings() {
		/*if ( $this->is_session_started() === FALSE ){
			session_start();
		}
        if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {*/
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$data['def_timezone']=$this->settings_model->get_time_zone();
			$data['def_timeformate']=$this->settings_model->get_time_formate();
			$data['def_dateformate']=$this->settings_model->get_date_formate();
			$data['enable_ad']=$this->settings_model->get_data_value('enable_ad');
			$data['default_language']= $this->settings_model->get_data_value("default_language");
			$data['languages']=directory_map('./application/language/');		
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('settings',$data);
			$this->load->view('templates/footer');
		}
	}
	public function enable_ad(){
		$this->settings_model->set_data_value("enable_ad", $this->input->post('enable_ad'));
		$this->change_settings();
	}
	public function save_lang(){
		$language = $this->input->post('default_language');
		$language = str_replace("\\","",$language);
		$config_file = "application/config/config.php";
		$line_array = file($config_file);

		for ($i = 0; $i < count($line_array); $i++) {

			if (strstr($line_array[$i], "config['language']")) {
				$line_array[$i] = '$config[\'language\'] = \'' . $language . '\';' . "\r\n";
			}
		}
		file_put_contents($config_file, $line_array);
			
		//$this->change_settings();
		redirect('settings/change_settings');
	}
	
	public function save_timezone(){
		$this->settings_model->save_timezone("default_timezone",$this->input->post('timezones'));
		$this->change_settings();
	}
	
	public function save_time_formate(){
		$this->settings_model->save_timezone("default_timeformate",$this->input->post('timeformate'));
		$this->change_settings();
	}
	
	public function save_date_formate(){
		$this->settings_model->save_timezone("default_dateformate",$this->input->post('dateformate'));
		$this->change_settings();
	}
	
	public function save_display(){
		$this->settings_model->save_timezone("default_display",$this->input->post('display_list'));
		$this->change_settings();
	}
	
	public function backup(){
		/*if ( $this->is_session_started() === FALSE ){
			session_start();
		}
        if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {*/
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('settings/backup');
			$this->load->view('templates/footer');
		}
	}
	
	public function take_backup(){
		$db_prefix =  $this->db->dbprefix;

		// Load the DB utility class
		$this->load->dbutil();

		$tables_array = array($this->db->dbprefix('appointments'),
								$this->db->dbprefix('appointment_log'),
								$this->db->dbprefix('contacts'),
								$this->db->dbprefix('contact_details'),
								$this->db->dbprefix('menu_access'),
								$this->db->dbprefix('patient'),
								$this->db->dbprefix('clinic'),
								$this->db->dbprefix('invoice'),
								$this->db->dbprefix('visit'),
								$this->db->dbprefix('bill'),
								$this->db->dbprefix('bill_detail'),
								$this->db->dbprefix('payment'),
								$this->db->dbprefix('users'),
								$this->db->dbprefix('todos'),
								$this->db->dbprefix('version'),
								$this->db->dbprefix('data'),
								$this->db->dbprefix('payment_transaction'),
								$this->db->dbprefix('followup'),
								$this->db->dbprefix('modules'),
								$this->db->dbprefix('user_categories'),
								$this->db->dbprefix('navigation_menu'),
								$this->db->dbprefix('receipt_template')
								);
		
		//Doctor Extension 
		array_push($tables_array,
					$this->db->dbprefix('department'),
					$this->db->dbprefix('doctor'),
					$this->db->dbprefix('doctor_schedule'),
					$this->db->dbprefix('fee_master')
					);
		//Gallery Extension
		array_push($tables_array,
					$this->db->dbprefix('visit_img')
					);
		//Marking Extension			
		array_push($tables_array,
					$this->db->dbprefix('marking_data')
					);
				
		//Medicine Store
		array_push($tables_array,
					$this->db->dbprefix('item'),
					$this->db->dbprefix('supplier'),
					$this->db->dbprefix('purchase'),
					$this->db->dbprefix('sell'),
					$this->db->dbprefix('sell_detail')
					);
					
		//Treatment
		array_push($tables_array,
					$this->db->dbprefix('treatments')
					);
					
		$prefs = array(
			'tables'        => $tables_array,			    // Array of tables to backup.
			'ignore'        => array(),                     // List of tables to omit from the backup
			'format'        => 'zip',                       // gzip, zip, txt
			'filename'      => 'chikitsa-backup.sql',              // File name - NEEDED ONLY WITH ZIP FILES
			'add_drop'      => TRUE,                        // Whether to add DROP TABLE statements to backup file
			'add_insert'    => TRUE,                        // Whether to add INSERT data to backup file
			'newline'       => "\n"                         // Newline character used in backup file
		);

		// Backup your entire database and assign it to a variable
		$backup = $this->dbutil->backup($prefs);

		// Load the file helper and write the file to your server
		$this->load->helper('file');
		write_file('chikitsa-backup.zip', $backup);

		//Take Backup of Profile Pictures
		$this->load->library('zip');
		$this->zip->read_dir('profile_picture');
		$this->zip->read_dir('patient_images');
		
		$data = $db_prefix;
		$db_prefix_file = "prefix.txt";
		write_file($db_prefix_file, $data);
		$this->zip->read_file($db_prefix_file);
		
		$this->zip->download('chikitsa-backup.zip');
		
		$this->backup();
	}
	
	function do_upload() {
        $config['upload_path'] = './restore_backup/';
		$config['allowed_types'] = '*';
		$config['max_size'] = '4096';
		$config['overwrite'] = TRUE;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('backup')) {
			$error = array('error' => $this->upload->display_errors());
			return $error;
		} else {
			$data = array('upload_data' => $this->upload->data());
			return $data['upload_data'];
		}
    }
	
	public function restore_backup(){
		/*if ( $this->is_session_started() === FALSE ){
			session_start();
		}
        if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {*/
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			//Upload File
			$file_upload = $this->do_upload(); 
			$filename = $file_upload['file_name'];
			$filname_without_ext = pathinfo($filename, PATHINFO_FILENAME);		
			if(isset($file_upload['error'])){
				$data['error'] = $file_upload['error'];		
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('settings/backup',$data);
				$this->load->view('templates/footer');
			}elseif($file_upload['file_ext']!='.zip'){
				$data['error'] = "The file you are trying to upload is not a .zip file. Please try again.";		
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('settings/backup',$data);
				$this->load->view('templates/footer');
			}else{
				$data['file_upload'] = $file_upload;	
				//Unzip
				$full_path = $file_upload['full_path'];
				$file_path = $file_upload['file_path'];
				$raw_name = $file_upload['raw_name'];
				
				$return_code = unzip($full_path,$file_path);			
				if($return_code === TRUE){
					//execute sql file
					$sql_file_name = $file_path . 'chikitsa-backup.sql';
					
					$file_content = file_get_contents($sql_file_name);	
					$query_list = explode(";\n", $file_content);
					
					foreach($query_list as $query){
						//Remove Comments like # # Commment #
						$pos1 = strpos($query,"#\n# ");
						if($pos1 !== FALSE){
							$pos2 = strpos($query,"\n#",$pos1+3);
							$comment = substr($query,$pos1, $pos2-$pos1)."<br/>";
							$query = substr($query, $pos2+2);
						}
						//echo $query."<br/>";
						$this->db->query($query);
					}
					//Move folders to their location
					$this->move_folder("./restore_backup/profile_picture", "./profile_picture");
					$this->move_folder("./restore_backup/patient_images", "./patient_images");
					$data['message'] = "Backup Restored Successfully!";
				}else{
					$data['error'] = $return_code;
				}
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('settings/backup',$data);
				$this->load->view('templates/footer');
			}
		}
	}
	
	function move_folder($source_dir,$destination_dir){
		// Get array of all source files
		$files = scandir($source_dir);
		// Identify directories
		$source = "$source_dir/";
		$destination = "$destination_dir/";
		// Cycle through all source files
		foreach ($files as $file) {
		  if (in_array($file, array(".",".."))) continue;
		  // If we copied this successfully, mark it for deletion
		  if (copy($source.$file, $destination.$file)) {
			$delete[] = $source.$file;
		  }
		}
		// Delete all successfully-copied files
		foreach ($delete as $file) {
		  unlink($file);
		}
	}
	
}

?>