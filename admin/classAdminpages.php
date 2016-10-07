<?php
	require_once('\..\hooks\classProductpublish.php');
	class classAdminpages {

		function __construct() {
			error_reporting(0);
			if (isset($_POST['savesettingsgrey'])) {
			$this->savePostvalues();
			}
			add_action('admin_menu', array($this, 'register_submenu_page_for_woocommerce'));
		}

		function register_submenu_page_for_woocommerce() {
			add_submenu_page('woocommerce', 'Greyferret', 'Greyferret', 'manage_options', 'greyferret-settings-page', array($this, 'greyferret_settings_page_callback'));
		}

		function savePostvalues() {
			$greyFerretUsername = ( $_POST['greyFerretUsername'] != '' ) ? $_POST['greyFerretUsername'] : '';
			$greyFerretPassword = ( $_POST['greyFerretPassword'] != '' ) ? $_POST['greyFerretPassword'] : '';
			$greyFerretApiUrl = ( $_POST['greyFerretApiUrl'] != '' ) ? $_POST['greyFerretApiUrl'] : '';
			$greyFerretPublishDate = ( $_POST['greyFerretPublishDate'] != '' ) ? $_POST['greyFerretPublishDate'] : '';
			$greyFerretPublishTime = ( $_POST['greyFerretPublishTime'] != '' ) ? $_POST['greyFerretPublishTime'] : '';
			$greyFerretEnabled = ( $_POST['greyFerretEnabled'] != '' ) ? $_POST['greyFerretEnabled'] : '';
			if (get_option('jobStatus')) {
				$jobStatus = get_option('jobStatus');
			}
			if($greyFerretEnabled == '') {
				$jobStatus = 'Not Scheduled';
				update_option('jobStatus', $jobStatus);
			}
			update_option('greyFerretUsername', $greyFerretUsername);
			update_option('greyFerretPassword', $greyFerretPassword);
			update_option('greyFerretApiUrl', $greyFerretApiUrl);
			update_option('greyFerretPublishDate', $greyFerretPublishDate);
			update_option('greyFerretPublishTime', $greyFerretPublishTime);
			update_option('greyFerretEnabled', $greyFerretEnabled);			
			if($_POST['greyFerretEnabled'] != '' && $jobStatus != 'Completed' && $jobStatus != 'Running') {
				$pub = new classPublish();
				$res = $pub->publishJob();
				echo $res;
			}
		}
		
		function greyferret_settings_page_callback() {
			$greyFerretUsername = '';
			$greyFerretPassword = '';
			$greyFerretApiUrl = '';
			$greyFerretPublishDate = '';	
			$greyFerretPublishTime = '';
			$greyFerretEnabled = '';
			$jobStatus = '';			
			if (get_option('greyFerretUsername')) {
				$greyFerretUsername = get_option('greyFerretUsername');
			}
			if (get_option('greyFerretApiUrl')) {
				$greyFerretApiUrl = get_option('greyFerretApiUrl');
			}
			if (get_option('greyFerretPublishDate')) {
				$greyFerretPublishDate = get_option('greyFerretPublishDate');
			}
			if (get_option('greyFerretPublishTime')) {
				$greyFerretPublishTime = get_option('greyFerretPublishTime');
			}
			if (get_option('greyFerretEnabled')) {
				$greyFerretEnabled = get_option('greyFerretEnabled');
			}
			if (get_option('jobStatus')) {
				$jobStatus = get_option('jobStatus');
			} else {
				$jobStatus = 'No jobs scheduled';
			}
			
			$out = '';
			$out .= '<br><br><a href="https://greyferret.com/greyferret-logo.png"><img src="https://greyferret.com/greyferret-logo.png"></a>';
			$out .= '<h1 style="padding-left:50px">Greyferret</h1>';
			$out .= '<form action="" method="post">';
			$out .= '<p>Enter the API Username : <input type="text" required name="greyFerretUsername" placeholder="Enter your api username here" size="40" value=' . $greyFerretUsername . '></p>';
			$out .= '<p>Enter the API Password : <input type="password" required name="greyFerretPassword" placeholder="Enter your api password here" size="40" value=' . $greyFerretPassword . '></p>';
			$out .= '<p>Enter the API URL : <input type="text" required name="greyFerretApiUrl" placeholder="Enter your api url here" size="40" value=' . $greyFerretApiUrl . '></p>';
			$out .= '<p>Enter the date to schedule cron : <input type="date" required name="greyFerretPublishDate" value=' . $greyFerretPublishDate . '></p>';
			$out .= '<p>Enter the time (UTC) to schedule cron : <input type="time" required name="greyFerretPublishTime" value=' . $greyFerretPublishTime . '></p>';
			$out .= '<p>Enable cron job ? <input type="checkbox" name="greyFerretEnabled" value="Yes" checked></p>';
			$out .= '<p>Current Cron job Status : <span><b><i>' . $jobStatus . '</i></b></span>';
			if($jobStatus == 'Completed')
				$out.= '<p><i>*Already completed publishing Customers, Categories and Products</i></p>';
			if($jobStatus == 'Running')
				$out.= '<p><i>*Already running!!</i></p>';
			$out .= '<p><input type="submit" name="savesettingsgrey" value="Save Settings"></p>';
			$out .= '</form>';
			$out .= '<p>For uninterrupted service, please make sure you schedule the cron when the server usage is very low.</p>';
			$out .= '<br><i>*For security reasons we are not populating the API password</i>';
			$out .= '<p>Have queries in installing the module ? Please mail us at <a href="mailto:query@greyferret.com?Subject=Prestashop install" target="_top">query@greyferret.com</a><br>Our Team will reach out to you within 1 Business day </p>';
			echo $out;
		}

	}

	new classAdminpages;
