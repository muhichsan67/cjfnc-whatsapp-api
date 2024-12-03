<?php
	function create_slug($string){
	   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($string)));
	   return $slug;
	}
	function dd($r=array(),$f=TRUE){
		echo "<pre>";
		print_r($r);
		echo "</pre>";
		
		if($f==TRUE)
			die;
	}

	function formatRupiah($value) {
		return number_format($value,0,",",".");
	}

	function route($url) {
		return base_url().$url;
	}

	function asset($path) {
		return base_url().'assets/'.$path;
	}

	function cleanformat($value) {
		return str_replace(".", "", str_replace(",", "", $value));
	}

	function admin_url($url) {
		return base_url().'dashboard/'.$url;
	}

	function dbClean($input){
		$input = str_replace('"', ' ', str_replace("'", " ", $input));
		$inputer = trim(stripslashes(html_escape(htmlspecialchars($input))));
		return $inputer;
	}

	function clean($string) {
	   $string = strtolower(str_replace(' ', '_', $string)); // Replaces all spaces with hyphens.

	   return preg_replace('/[^A-Za-z0-9\_]/', '', $string); // Removes special chars.
	}

	function convMonth($vardate, $full = FALSE) {
		if($vardate!=''){
			$BulanIndo = array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEPT", "OCT", "NOV", "DEC");
			if ($full) {
				$BulanIndo = array("JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER");
			}
			return $BulanIndo[(int)$vardate-1];
		} else {
			return '-';
		}
	}

	function convDate($vardate) {
		if($vardate!=''){
			$vardate = date("Y-m-d", strtotime($vardate));
			$pecah = explode("-", $vardate);

			$tahun = $pecah[0];
			$bulan = $pecah[1];
			$tgl   = $pecah[2];
			
			$BulanIndo = array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEPT", "OCT", "NOV", "DEC");
			return $tgl . " " . $BulanIndo[(int)$bulan-1] . " ". $tahun ;
		} else {
			return '-';
		}
	}

	function convertNumberToWord($num = false){
	    $num = str_replace(array(',', ' '), '' , trim($num));
	    if(! $num) {
	        return false;
	    }
	    $num = (int) $num;
	    $words = array();
	    $list1 = array('', 'satu', 'dua', 'tiga', 'empta', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
	    );
	    $list2 = array('', 'sepuluh', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh', 'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh', 'ratus');
	    $list3 = array('', 'ribu', 'juta', 'miliyar', 'triliun', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
	        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
	        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
	    );
	    $num_length = strlen($num);
	    $levels = (int) (($num_length + 2) / 3);
	    $max_length = $levels * 3;
	    $num = substr('00' . $num, -$max_length);
	    $num_levels = str_split($num, 3);
	    for ($i = 0; $i < count($num_levels); $i++) {
	        $levels--;
	        $hundreds = (int) ($num_levels[$i] / 100);
	        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' ratus' . ' ' : '');
	        $tens = (int) ($num_levels[$i] % 100);
	        $singles = '';
	        if ( $tens < 20 ) {
	            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
	        } else {
	            $tens = (int)($tens / 10);
	            $tens = ' ' . $list2[$tens] . ' ';
	            $singles = (int) ($num_levels[$i] % 10);
	            $singles = ' ' . $list1[$singles] . ' ';
	        }
	        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
	    } //end for loop
	    $commas = count($words);
	    if ($commas > 1) {
	        $commas = $commas - 1;
	    }
	    return trim(ucwords(implode(' ', $words)));
	}

	function random_char($length = 64){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }

	    return $randomString;
	}

	function random_number($length = 64){
		$characters = '0123456789';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }

	    return $randomString;
	}

	function auth() {
		$CI =& get_instance();
		$session = $CI->session->userdata('user_dashboard');
		$user_session = $session['user'];

		return $user_session;
	}

	function is_admin() {
		$CI =& get_instance();
		$session = $CI->session->userdata('user_dashboard');
		$user_session = $session['user'];

		if ($user_session['user_group_id'] == 1) {
			return true;
		}
		return false;
	}

	function menu_data($session, $is_config = 0, $judul_parent = null, $subjudul = null) {
		$CI =& get_instance();

		$user_access = $session['user_access'];
		$CI->db->select("id, name, routes, icon");
		$CI->db->where('is_config', $is_config);
		$CI->db->where('menu_parent_id', 0);
		$CI->db->where_in("id", $user_access);
		$CI->db->order_by("precedence", "asc");
		$data = $CI->db->get('m_menu')->result_array();
		
		$html = '';
		foreach ($data as $menu) {
			$id 		= $menu['id'];
			$name 		= $menu['name'];
			$url 		= base_url($menu['routes']);
			$icon 		= $menu['icon'];
			$is_parent 	= 0;
			$has_treeview	= "";
			$active 	= $judul_parent == $name ? "active" : "";
			$active_li 	= $judul_parent == $name ? "menu-open" : "";
			$style 		= ($judul_parent == $name || $active_li == $name) ? "style='color:#FFF'": "style='color:#BFBFBF'";		

			$data_child = menu_data_child($id, $user_access);
			if (count($data_child) > 0) {
				$is_parent 		= 1;
				$url 			= "javascript:void(0)";
				$has_treeview 	= "has-treeview";
				$name 			.= ' <i class="right fas fa-angle-left"></i>';
			}
			$html .= '<li class="nav-item '.$has_treeview.' '.$active_li.'">';
				$html .= '<a '.$style.' href="'.$url.'" class="nav-link '.$active.'">';
              		$html .= '<i class="nav-icon fas '.$icon.'"></i>';
              		$html .= '<p>'.$name.'</p>';
            	$html .= '</a>';
            	if ($is_parent == 1) {
            		$html .= '<ul class="nav nav-treeview">';
            		foreach ($data_child as $menu_child) {
            			$name 		= $menu_child['name'];
						$url 		= base_url($menu_child['routes']);
						$icon 		= $menu_child['icon'];
						$active 	= $subjudul == $name ? "active" : "";
            			$html .= '<li class="nav-item">';
		                	$html .= '<a href="'.$url.'" class="nav-link '.$active.'">';
		                    	$html .= '<i class="nav-icon far '.$icon.'"></i>';
		                    	$html .= '<p>'.$name.'</p>';
		                  	$html .= '</a>';
		                $html .= '</li>';
            		}
            		$html .= '</ul>';
            	}
	        $html .= '</li>';
		}

		return $html;
	}

	function menu_data_child($menu_parent_id, $user_access) {
		$CI =& get_instance();

		$CI->db->select("name, routes, icon");
		$CI->db->where('menu_parent_id', $menu_parent_id);
		$CI->db->where_in("id", $user_access);
		$data = $CI->db->get('m_menu')->result_array();

		return $data;
	}

	function notifikasi(){
		$CI =& get_instance();

		$CI->db->select('*');
		$CI->db->where('is_read', 0);
		$CI->db->order_by('id','desc');
		$query = $CI->db->get('mt_notifikasi');
		
		return $query->result_array();
	}

	function get_profile_admin($id) {
		$CI = &get_instance();

		$CI->db->select('m_pegawai.id, m_pegawai.name, m_pegawai.email, m_pegawai.password_raw');
        $CI->db->from("m_pegawai");
        $CI->db->where('m_pegawai.deleted_at IS NULL');
        $CI->db->where('m_pegawai.user_group_id !=', 1);
        $CI->db->where("m_pegawai.id", $id);
        $user = $CI->db->get()->row();

    	$result = [
    		"email" => $user->email, 
    		"password" => $user->password_raw, 
    		"name" => $user->name, 
    	];
        
        return $result;
	}

	function send_email($to, $subject, $message, $cc = '') {
		$is_active = 1;

		if ($is_active == 1) {
			$CI = &get_instance();
			$CI->load->library('email');
			$CI->email->initialize(array(
			  	'protocol'  => "smtp",
				'smtp_host' => "mail.cj.co.id",
				'smtp_user' => "dms.cjfnl@cj.co.id",
				'smtp_pass'   => "init1234!!",
				'smtp_crypto' => "ssl",
				'smtp_port'   => 465,
				'crlf' => "\r\n",
				'newline' => "\r\n"
			));

			$CI->email->set_mailtype('html');
			$CI->email->from('dms.cjfnl@cj.co.id', 'E-DMS CJFNC');
			$CI->email->to($to);
			if (!empty($cc)) {
				$CI->email->cc($cc);
			}
			// $CI->email->bcc('them@their-example.com');
			$CI->email->attach("https://cj.co.id/assets/images/favicon.png", "inline");
			$CI->email->subject($subject);
			$CI->email->message($message);
			if ($CI->email->send()) {
				// debugCode("Berhasil");
				// echo "Berhasil";
			} else {
				 $errors = $CI->email->print_debugger();
	    		print_r($errors);
				// debugCode("GAGAL");
			}
		}

		// echo "Gagal";
	}

	function send_email2($to, $subject, $message) {
		/* Endpoint */
        $url = 'https://10.137.62.5/CJ/send_email';
   		// $url = 'google.com';
        /* eCurl */
        $curl = curl_init($url);
   
        /* Data */
        $data = [
            'to' 		=> $to, 
            'subject' 	=> $subject,
            'message'	=> $message
        ];

        $data = json_encode($data);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        /* Define content type */
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Cookie:XSRF-TOKEN=eyJpdiI6IlhhMlBpczgyeWxVRG52M3ZBV1FkRFE9PSIsInZhbHVlIjoiQ1BuK1UxQVwvc09icUNwU3ZJTUNcL3M3dnFIdzJmejVDRGxUQlltU1JiRGpKR3JRYVdHQzNvVUdHZkpuRlZocURnIiwibWFjIjoiYTc2YWYxYjE1Zjg5NmRiMjM1MTY4ODg4ZTJkYmQ3ODdmNDAwYTA3OTI4YTI1M2JjMDE4NWIzYTM0NTY1ZmZmZCJ9; laravel_session=eyJpdiI6IjYweDRKSm4zTEh0cXNSQjN6SkE2SHc9PSIsInZhbHVlIjoiR2VEdDdWc016czdkWGRKMnNueEhIRFVJQlQyckdnMHkzR2JlaDMxeWNCMVpKOTNTRFFrc3l5b2ttSEoweHFJUVZBY2w3ajgyZU1xVWFjYzlYNjdTMWw5Nzhza1wvSlZYZWxqaStoNEJRQVdwR25aeExvMGFUbldxNENCSlJtWmd4IiwibWFjIjoiZGM3M2RjNTRkMjkwZTQyNTA3OWYwOGYzNmRjOTVhNjEwMzQxMmM5NTIzMDYxNDQwZmRmOTJlN2ZiYjU0MTdhYyJ9'));
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        /* Return json */
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        /* make request */
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
		    $error_msg = curl_error($curl);
		}
        /* close curl */
        curl_close($curl);
        if (isset($error_msg)) {
		    echo $error_msg;
		}
        echo "<pre>" . $result. "</pre>";
	}

	if (!function_exists('str_contains')) {
	    function str_contains(string $haystack, string $needle): bool
	    {
	        return '' === $needle || false !== strpos($haystack, $needle);
	    }
	}

	function encrypt($string) {
		$key = "CCTCJFNC";
		$cipher = "AES-256-CBC";
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext = openssl_encrypt($string, $cipher, $key, OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext, $key, true);
		return base64_encode($iv . $hmac . $ciphertext);
	}

	function decrypt($string) {
		$key = "CCTCJFNC";
		$cipher = "AES-256-CBC";
		$c = base64_decode($string);
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len = 32);
		$ciphertext = substr($c, $ivlen + $sha2len);
		$original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext, $key, true);
		if (hash_equals($hmac, $calcmac)) {
				return $original_plaintext;
		}
		return false;
	}

?>