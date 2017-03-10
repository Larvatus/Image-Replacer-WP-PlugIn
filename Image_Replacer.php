<?php
/**
 * @package Hello_Dolly
 * @version 1.6
 */
/*
Plugin Name: Image Replacer
Plugin URI: http://lexlarvatus.com/lex
Description: Скрипт для перемещения фотографий из папки по умолчанию в персональную папку каждой статьи.
Author: Lex Larvatus
Version: 0.4
Author URI: http://lexlarvatus.com/lex
*/

//include('/wp-content/plugins/test_001/simplehtmldom_1_5/simple_html_dom.php');
//include('simplehtmldom_1_5/simple_html_dom.php');
//include_once('simple_html_dom.php');

function test_add_admin_page() {
	//add a new submenu under Options:
	add_options_page('Image Replacer', 'Перенос изображений', 8, 'test_postcreator', 'test_options_page' );
	
}
	
function pre($obj) {
	echo '<pre>';
	var_dump($obj);
	echo '</pre>';
}	
	
function test_options_page() {
	$blog_details = get_blog_details();
	echo "<h2>Перенос изображений в индивидуальные папки из общих папок загрузки</h2>";
	//Форма
	echo
	"
		<form name='test_base_setup' method='post' action='".substr($blog_details->path, 0, -1).$_SERVER['PHP_SELF']."?page=test_postcreator'>
	";
	//Для отображения сообщения "Настройки сохранены"
	//<form name='test_base_setup' method='post' action='".$_SERVER['PHP_SELF']."?page=test_postcreator&amp;updated=true'>
	if (function_exists ('wp_nonce_field') )
	{
		wp_nonce_field('test_base_setup_form');
	}
	echo
	"Статьи для проверки: <input type='text' maxlength='2' size='3' name='per_check' value='".$_POST['per_check']."'/>
	<input type='submit' name='test_base_setup_btn' value='Проверить' />
	<input type='submit' name='check_processing_table' value='Статистика' />
	<input type='submit' name='clear_processing_table' value='Обновить таблицу отчетов' />
	<!--<input type='submit' name='fill_processing_table' value='Заполнить' />-->
	</form>
	";
	
	global $wpdb;
	$table_process = $wpdb->prefix.'postprocessing';
	
	if($wpdb->get_var("SHOW TABLES LIKE '$table_process'") != $table_process) {
		
		//echo '</br>Table not exist.';
		echo '</br>Table '.$table_process.' are created.';
		$sql = "CREATE TABLE " .$table_process. " (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
		  log TEXT NULL,
		  post_id bigint(20) NOT NULL,
		  processed tinyint(1) default '0',
		  processmistake tinyint(1) default '0',
		  mistake_log TEXT NULL,
		  UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //log TEXT NULL,
		  
		dbDelta( $sql );
		
		IR_createOptionCell();
		
	} else { 
		//echo '</br>Table <b>'.$table_process.'</b> exist.</br>';
		//test_parseposts();
		$table_posts = $wpdb->prefix.'posts';
		$id_table_posts = $wpdb->get_results("SELECT ID FROM $table_posts WHERE post_status='publish' AND post_type='post'");
		foreach ( $id_table_posts as $main_post ) { $ID_form_posts[] = $main_post->ID; }
		
		$id_table_processing = $wpdb->get_results("SELECT post_id FROM $table_process");
		if (count($id_table_processing) != 0) {
			foreach ( $id_table_processing as $main_post ) { $ID_form_proc[] = $main_post->post_id; }
			
			$difference = array_diff ($ID_form_posts, $ID_form_proc);
			$qeue = implode(",", $difference);
			$mass_dif = explode(",", $qeue);
			
		} else {
			$difference = count($ID_form_posts);
			$qeue = implode(",", $ID_form_posts);
			$mass_dif = explode(",", $qeue);
		}
		
		//pre($difference);
		
		if (count($difference) > 0) {
			echo 'Обнаружены новые записи: '.count($mass_dif);
			for ($i = 0; $i < count($mass_dif); $i++) {
				echo '<br>diff[i] - '.$i.' = '.$mass_dif[$i];
				$wpdb->query("INSERT INTO $table_process(post_id) VALUES( $mass_dif[$i] )");
			}
		}
	}
	
	
	if (isset($_POST['test_base_setup_btn']))
	{	//echo 'clear_isset';
		if (function_exists('current_user_can') &&
		!current_user_can('manage_options'))
			die ( _e('Hacker?', 'test'));
		if (function_exists ('check_admin_referer'))
		{
			check_admin_referer('test_base_setup_form');
		}
		$test_base_setup_btn = $_POST['test_base_setup_btn'];
		test_parseposts();
		
		
	} else { //echo '<br>clear_not_isset';
	}
	
if (isset($_POST['check_processing_table']))
	{	//echo 'clear_isset';
		if (function_exists('current_user_can') &&
		!current_user_can('manage_options'))
			die ( _e('Hacker?', 'test'));
		if (function_exists ('check_admin_referer'))
		{
			check_admin_referer('test_base_setup_form');
		}
		$check_processing_table = $_POST['check_processing_table'];
		check_processing_table();
		
		
	} else { //echo '<br>test_not_isset';
	}
	
	if (isset($_POST['clear_processing_table']))
	{	//echo 'clear_isset';
		if (function_exists('current_user_can') &&
		!current_user_can('manage_options'))
			die ( _e('Hacker?', 'test'));
		if (function_exists ('check_admin_referer'))
		{
			check_admin_referer('test_base_setup_form');
		}
		$clear_processing_table = $_POST['clear_processing_table'];
		clear_processing_table();
		
		
	} else { //echo '<br>test_not_isset';
	}
	//=====================================================  Test code 
	
	//$file = 'W:\home\lexlarvatus.com\www/wp-content/uploads/sites/3/2017/02/Koala.jpg';
	//IR_resetStat();
	//$dir = "W:\home\lexlarvatus.com\www/wp-content/uploads/sites/3/2011/2011.09.08_testovaya-podsvetka-trk-magistrat";
	
		
	
	
	//$per_check = 0;
	//$table_posts = $wpdb->prefix.'posts';
	//$table_process = $wpdb->prefix.'postprocessing';
	/* вытаскивает из базы данных заголовки и содержимое всех опубликованных страниц без ошибок и проверенных ранее*/ 
	//FROM $wpdb->posts
	/* $pages = $wpdb->get_results( 
		"
		SELECT post_title, post_content, ID, post_date, post_name
		FROM $table_posts
		WHERE post_status = 'publish' 
		AND post_type = 'post'
		"
	);
		
	
		
	if( $pages ) {
		echo '<table>';
		$i=0;
		foreach ( $pages as $page ) {
			//generate array {y,m,d}
			$i++;
			$pdate = IR_getPostDate($page->post_date);
			
			$projfolder_name = IR_create_projfolder_name($pdate, $page->post_name, $page->post_title);
			
			echo '<tr>';
			echo '<td>'.$i.'.</td><td>';
			if (strstr($page->post_name, '%')==false){
				echo $page->ID.'</td><td>'.$pdate['YMD'].'</td><td>'.$page->post_title.'</td><td>'.$projfolder_name.'</td>';
			} else {
				echo '<span style="color: #f33;">'.$page->ID.'</span>'.'</td><td>'.$pdate['YMD'].'</td><td>'.$page->post_title.'</td><td><span style="color: #f33;">'.$projfolder_name.'</span></td>';
			}
				
			
			echo '</tr>';
			
		}
		echo '</table>';
	} */
	
	 
}
function IR_createOptionCell() {
	
	global $wpdb;
	
	$table_options = $wpdb->prefix.'options';
	$IR_beginData = '{"Total_filecount":0,"Removed_filecount":0,"Removed_filesize":0}';
	$wpdb->query("INSERT INTO $table_options(option_name, option_value, autoload) VALUES( 'Image_Replacer_filedata' , '$IR_beginData' , 'no' )");
}

function IR_sc($size, $id) {// return string :: "23,45 Мбайт"
	switch ($id) {
	case 0:
		//байт
		$sizestring = $size.' байт';
		break;
	case 1:
		//Кбайт
		$sizestring = round(($size/1024), 2).' Кбайт';
		break;
	case 2:
	   //Мбайт
	   $sizestring = round(($size/1024/1024), 2).' Мбайт';
	   break;
	case 3:
	   //Гбайт
	   $sizestring = round(($size/1024/1024/1024), 2).' Гбайт';
	   break;
	//default:
	   //Do
	}
	// return string :: "23,45 Мбайт"
	return $sizestring;
}
function IR_shorturl($link, $dir){ //return shortlink
	global $wpdb;
	//generate site subdir like 'site/3' for log
	$site_subdir = str_replace( 'wp_', 'site/', substr($wpdb->prefix, 0, -1) );
	$upload_dir = wp_upload_dir();
	
	if ($dir == 1) {
		$short =  str_replace($upload_dir['basedir'], $site_subdir, $link);
	} else {
		$short =  str_replace($upload_dir['baseurl'], $site_subdir, $link);
	}
	//return shortlink
	return $short;
}

function IR_genPostLog($remarr) {  // return string for log 
	
	$log = 'Файлы: ';
	
	foreach($remarr as $file) {
		$elem = IR_shorturl($file, 1);
		$log = $log.$elem.' | ';
	}
	// return string for log 
	return $log;
}


function IR_genClonelist($arr) {  //return array dir clones
	
	$upload_dir = wp_upload_dir();
	
	foreach($arr as $img) {
		
		$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img);
		$filedir = str_replace(basename($file), '', $file);
		
		$mass_sas = find_img_copy($file);
		foreach($mass_sas as $base) {
			$remarr[] = $filedir.$base;
		}
		
	}

	// return array dir clones
	return $remarr;
	unset($remarr);
}

function IR_genTHlinks($post_id, $thumb_link, $proj_dir) {// return array of thumbnail links
	
	$thumb_id = get_post_thumbnail_id( $post_id );
	$upload_dir = wp_upload_dir();
	
	$nwlink = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $proj_dir).'/'.basename($thumb_link);
	
	$th_links = array (
		"name" => basename($thumb_link),
		"owlink" => $thumb_link,
		"oflink" => str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $thumb_link),
		"nflink" => $proj_dir.'/'.basename($thumb_link),
		"nwlink" => $nwlink,
		"nshortlink" => str_replace($upload_dir['baseurl'].'/', '', $nwlink),
	);

	// return array of thumbnail links
	return $th_links;
}

function IR_updateContent($imgs, $content) { //return updated post content
	
	//parsing post content
	include_once('simple_html_dom.php');
	$html = str_get_html($content);
	
	$element = $html->find('img');
	
	foreach($element as $image) {
		$old_src = $image->src;
		$key = array_search($old_src, array_column($imgs, 'owlink'));
		$image->setAttribute('src', $imgs[$key]['nwlink']);
		
		if ($image->parent()->tag == 'a') { 
			$pos = strripos($image->parent()->href, home_url()); //проверка на соответствие домену
			if ($pos === false) {
				//Out link 
			} else {
				$a = substr($image->parent()->href, -4); // read extension like ".jpg"
				if ( $a == '.jpg' || $a == '.JPG' || $a == '.png' || $a == '.PNG' || $a == '.gif' || $a == '.GIF') {
					$old_href = $image->parent()->href;
					$key = array_search($old_href, array_column($imgs, 'owlink'));
					$image->parent()->setAttribute('href', $imgs[$key]['nwlink']);
				} else { //Not img link
				}
			}
		}
	}
	$new_content = $html;
	
	// return updated post content
	return $new_content;
	
	// подчищаем за собой
	$html->clear(); 
	unset($html);
}

function IR_genNewLinks($array, $dir) {
	
	$upload_dir = wp_upload_dir();
	
	foreach ($array as $oldlink) {
		
		$gen_links = array (
			"name" => basename($oldlink),
			"owlink" => $oldlink,
			"oflink" => str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $oldlink),
			"nflink" => $dir.'/'.basename($oldlink),
			"nwlink" => str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $dir).'/'.basename($oldlink),
		);
		
		$total_array[] = $gen_links;
		unset($gen_links);
		
	}
	//return array
	return $total_array;
	
	unset($total_array);
}

function IR_extract_thumb_link ($post_id) { //return thumb link

	//Извлечение ссылки на превью поста
	$ID_TH = get_post_thumbnail_id( $post_id );
	$thurl = wp_get_attachment_image_src( $ID_TH, 'full', false );
	
	//return thumb link
	return $thurl[0];
}

function IR_check_exist($array, $post_id) { // return total filesise or mistake array
	
	$upload_dir = wp_upload_dir();
	$exist = 0;
	$total_filesize = 0;
	$missing = '';
	foreach($array as $link) {
		//get dir link
		$old_file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $link );
		
		if (file_exists($old_file) ) { 
			$exist++;
			$total_filesize = $total_filesize + filesize($old_file);
		} else { 
			$missing = $missing.' | '.IR_shorturl($old_file, 1);
		}
	}
		
	// return total filesise or missing files array
	if (count($array) == $exist) { 
		return $total_filesize; 
	} else { 
		return $missing;
	}
}

function IR_getIMGarray($post_content) { //return array old unique links from post
	
	//parsing post content
	include_once('simple_html_dom.php');
	$html = str_get_html($post_content);
	
	$element = $html->find('img');
	
	switch (count($element)) {
		case 0:
			$old_img[] = '';
			echo '<br><div style="color: #944; font-size: 1.4em;">В контенте нет изображений.</div>';
			break;
		default:
			foreach($element as $image) {
				$old_img[] = $image->src;
				
				if ($image->parent()->tag == 'a') { 
					$pos = strripos($image->parent()->href, home_url()); //проверка на соответствие домену
					if ($pos === false) {
						/* Out link */
					} else {
						$a = substr($image->parent()->href, -4); // read extension like ".jpg"
						if ( $a == '.jpg' || $a == '.JPG' || $a == '.png' || $a == '.PNG' || $a == '.gif' || $a == '.GIF') {
							$old_img[] = $image->parent()->href;
						} else { /* Not img link */}
					}
				}
			}
			$old_img = array_unique($old_img); //delete repeated elements
	}
	
	//return array old links
	return $old_img;
}

function IR_getStat() { //return statistic
	
	//select data from table
	global $wpdb;
	$table_options = $wpdb->prefix.'options';
	$IR_filedata = $wpdb->get_results("SELECT option_value FROM $table_options WHERE option_name='Image_Replacer_filedata'");
	
	if (count($IR_filedata)==0) {
		IR_createOptionCell();
		$IR_filedata = $wpdb->get_results("SELECT option_value FROM $table_options WHERE option_name='Image_Replacer_filedata'");
	}
	
	//convert data <-
	$IR_stat_array = json_decode($IR_filedata[0]->option_value);
	
	//prepare data
	$IR_stat_string = '<br>Всего файлов обработано: '.$IR_stat_array->Total_filecount.'<br>Всего файлов удалено: '.$IR_stat_array->Removed_filecount.'<br>Всего освобождено: '.IR_sc($IR_stat_array->Removed_filesize, 2);
	
	//return statistic
	return $IR_stat_string;
}
function IR_updateStat($total_filecount, $remove_filecount, $remove_filesize) { //update data in table
	//select data from table {"Total_filecount":0,"Removed_filecount":0,"Removed_filesize":0}
	global $wpdb;
	$table_options = $wpdb->prefix.'options';
	$IR_filedata = $wpdb->get_results("SELECT option_value FROM $table_options WHERE option_name='Image_Replacer_filedata'");
	
	if (count($IR_filedata)==0) {
		IR_createOptionCell();
		$IR_filedata = $wpdb->get_results("SELECT option_value FROM $table_options WHERE option_name='Image_Replacer_filedata'");
	}
	
	//convert data <-
	$IR_stat_array = json_decode($IR_filedata[0]->option_value);
	
	//increase value
	$IR_stat_array->Total_filecount = $IR_stat_array->Total_filecount + $total_filecount;
	$IR_stat_array->Removed_filecount = $IR_stat_array->Removed_filecount + $remove_filecount;
	$IR_stat_array->Removed_filesize = $IR_stat_array->Removed_filesize + $remove_filesize;
	
	//convert data ->
	$IR_stat = json_encode($IR_stat_array);
	
	//update data in table
	$data['option_value'] = $IR_stat;
	$where['option_name'] = 'Image_Replacer_filedata';
	$wpdb->update($table_options, $data, $where, array('%s','%s'));
}

function IR_newfilecount($dir) { //unusable function
	
	$dir = opendir($dir);
	$count = 0;
	while($file = readdir($dir)){
		if($file == '.' || $file == '..' || is_dir($dir . $file)){
			continue;
		}
		$count++;
	}
	// Type your code here
	return $count;
}

function IR_resetStat() { //reset & update data in table
	
	global $wpdb;
	$table_options = $wpdb->prefix.'options';
	
	//reset data
	$IR_stat = '{"Total_filecount":0,"Removed_filecount":0,"Removed_filesize":0}';
	
	//update data in table
	$data['option_value'] = $IR_stat;
	$where['option_name'] = 'Image_Replacer_filedata';
	$wpdb->update($table_options, $data, $where, array('%s','%s'));
}
function check_processing_table(){
	global $wpdb;
	$table_process = $wpdb->prefix.'postprocessing';
	$count_v_post = $wpdb->query("SELECT * FROM $table_process WHERE processed='0' AND processmistake='0' ");
	//$answer = $wpdb->query("INSERT INTO $table_process(post_id) SELECT ID FROM $wpdb->posts  WHERE post_status='publish' AND post_type='post'");
	echo '<br>Необработанных записей: '.$count_v_post;
	echo IR_getStat();
}

function clear_processing_table(){
	global $wpdb;
	$table_process = $wpdb->prefix.'postprocessing';
	$wpdb->query("DELETE FROM $table_process");
	$answer = $wpdb->query("INSERT INTO $table_process(post_id) SELECT ID FROM $wpdb->posts  WHERE post_status='publish' AND post_type='post'");
	echo '<br>Обновлено полей для записей: '.$answer;
}
function post_publish_email_send($post_ID){
    $to = 'larvatuslex@gmail.com';  //EMAIL получателя
    $subject = 'Новый пост создан'; //Тема письма
    $message = 'У нас новая статья на сайте. Запись имеет id='.$post_ID; //Тело письма с указанием ID записи
    wp_mail($to, $subject, $message);
    return $post_ID;
}


function translit($s) {
  $s = (string) $s; // преобразуем в строковое значение
  $s = strip_tags($s); // убираем HTML-теги
  $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
  $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
  $s = trim($s); // убираем пробелы в начале и конце строки
  $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
  $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
  $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
  return $s; // возвращаем результат
}

function IR_get_links($old_link, $IRL) {
	
	$gen_links = array (
		"name" => basename($old_link),
		"owlink" => $old_link,
		"oflink" => str_replace($IRL['up_url'], $IRL['up_dir'], $old_link),
		"nflink" => $IRL['proj_folder_dir'].'/'.basename($old_link),
		"nwlink" => $IRL['proj_folder_url'].'/'.basename($old_link),
	);
	
	return $gen_links;
}

function find_img_copy($old_file_link) {
	
	$dir = opendir(dirname($old_file_link));// где - откроем
	$string_to_search = preg_replace( "/[-][0-9]+[x][0-9]+.[a-z]{3}/", '',basename($old_file_link)); //начинается на имя картинки без суффикса
	$string_to_search2 = preg_replace( "/[.][a-z]{3}/", '',$string_to_search)."-";
	$ext = substr(basename($old_file_link), -4);
	$genImg = substr(basename($old_file_link), -4);
	
	while(($file = readdir($dir)) !== false) {
		$pos = strpos($file, $string_to_search2);
		if ($pos === false) {} else {
			if ($pos == 0) { 
				$mass_sa = $file; 
				if($mass_sa != "") {
					if (substr($mass_sa, -4) == $ext) {
						$mass_sas[] = $mass_sa;
					}
				}
			}
		}
	}
	$mass_sas[] = basename($old_file_link);
	$mass_sas_unic = array_unique($mass_sas);
	closedir($dir);
		
	return $mass_sas_unic;
}
function IR_create_post_folder($year, $proj_name) { //return project folder dir
	global $upload_dir;
	$upload_dir = wp_upload_dir();
	$year_dir = $upload_dir['basedir'].'/'.$year;
	$proj_dir = $year_dir.'/'.$proj_name;
	
	//create year & project folders
	if (file_exists($year_dir)) {} else {	mkdir($year_dir, 0755);	}
	if (file_exists($proj_dir)) {} else {	mkdir($proj_dir, 0755);	}
	
	//return project folder dir
	return $proj_dir;
}

function IR_getPostDate($postdate){ //return array {y,m,d}
	
	$date['Y'] = substr( $postdate, 0, 4 );
	$date['M'] = substr( $postdate, 5, 2 );
	$date['D'] = substr( $postdate, 8, 2 );
	$date['YMD'] = $date['Y'].'.'.$date['M'].'.'.$date['D'];
	
	return $date;
}
function IR_create_projfolder_name ($pdate, $post_name, $post_title) { //return project folder name
	
	//if (substr($post_name, 0, 1) == "%"  ) {
	if (strstr($page->post_name, '%')==false){
		$proj_name = translit($post_title);
	} else {
		$proj_name = $post_name;
	}
	
	$proj_name = $pdate['YMD']."_".$proj_name;
	
	//return project folder name
	return $proj_name;
}

function test_parseposts() {
	
	
	global $upload_dir;
	$upload_dir = wp_upload_dir();
		
	global $wpdb;
	
	$per_check = $_POST['per_check'];
	$table_posts = $wpdb->prefix.'posts';
	$table_process = $wpdb->prefix.'postprocessing';
	/* вытаскивает из базы данных заголовки и содержимое всех опубликованных страниц без ошибок и проверенных ранее*/ 
	//FROM $wpdb->posts
	$pages = $wpdb->get_results( 
		"
		SELECT post_title, post_content, ID, post_date, post_name
		FROM $table_posts
		WHERE post_status = 'publish' 
		AND post_type = 'post' AND ID IN (SELECT post_id FROM $table_process WHERE processmistake = 0 AND processed = 0)
		LIMIT $per_check
		"
	);
	
	if (count($pages) != $per_check ) {
		echo '<br>Найдено и обработано: '.count($pages).'/'.$per_check;
	}
	
	if( $pages ) {
		foreach ( $pages as $page ) {
			global $wpdb;
			$mistake_log = '';
			
			//extract thumb - 'http....jpg'
			$thumb_link = IR_extract_thumb_link($page->ID);
			
			//check existance of thumb - filesize or mistake
			$thmb_file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $thumb_link );
			if (!file_exists($thmb_file) ) { $mistake_log = 'Post id - '.$page->ID.' | Thumb not found - '.IR_shorturl($thmb_file, 1); }
			
			//extract image from post (unique elements)
			$oldIMGarray = IR_getIMGarray($page->post_content);
			
			if (count($oldIMGarray)== 1 and $oldIMGarray[0]=='') { 
				unset($oldIMGarray);	
			} else {
				$exist_array = IR_check_exist($oldIMGarray, $page->ID); 
				if (gettype($exist_array) !== integer) { $mistake_log = $mistake_log.' || Missing content: '.$exist_array;	}
			} 
				
			//generate array {y,m,d}
			$pdate = IR_getPostDate($page->post_date);
			
			if ($mistake_log !== '') { //stop cycle
				echo '<br><span style="color: #f33; font-size: 1.2em">MISTAKE!</span><br>';
				
				edit_post_link( 'Редактировать', '', '', $page->ID, '' );
				echo '<br> Post ID: '.$page->ID.' | '.$mistake_log;
				
				
				$table_process = $wpdb->prefix.'postprocessing';
				$data = array();
				$data['log'] = $post_log;
				$data['processmistake'] = '1';
				$data['processed'] = '0';
				$data['mistake_log'] = $mistake_log;
				$where['post_id'] = $page->ID;
				
				$wpdb->update($table_process, $data, $where, array( '%s', '%d', '%d' ));
				
			} else { //continue
				
				$projfolder_name = IR_create_projfolder_name($pdate, $page->post_name, $page->post_title);
				$proj_dir = IR_create_post_folder($pdate['Y'], $projfolder_name);
				
				//merge img array & thumb link
				$oldIMGarray[] = $thumb_link;
				$commonIMGarray = array_unique($oldIMGarray); //delete repeated elements
				$IRS['unic_img'] = count($commonIMGarray );
				
				$imgs = IR_genNewLinks($commonIMGarray, $proj_dir);
				$existfilesize = 0;
				$IRS['post_img_prev'] = '';
				foreach ($imgs as $copy) {
					if (copy($copy['oflink'], $copy['nflink'])) {
						$existfilesize = $existfilesize+filesize($copy['nflink']);
						$IRS['post_img_prev'] = $IRS['post_img_prev'].'<img height="50" src="'.$copy['nwlink'].'"> ';
					} else {
						$mistake_log = $mistake_log.' || Copy error: '.$copy['oflink'];
					}
					
				}
				$IRS['oldfs'] = $existfilesize;
				$html = IR_updateContent($imgs, $page->post_content);
				
				// Создаем массив данных
				$my_post = array();
				$my_post['ID'] = $page->ID;
				//замена содержимого статьи измененной информацией
				$my_post['post_content'] =  $html->outertext;
				wp_update_post( $my_post ); 
				
				//generate new thumbnails & metadata, update metadata in db
				$th_links = IR_genTHlinks($page->ID, $thumb_link, $proj_dir);
				$ID_TH = get_post_thumbnail_id( $page->ID );
				$newthumb_dir = $proj_dir.'/'.basename($thumb_link);
				$newdata = wp_generate_attachment_metadata( $ID_TH, $newthumb_dir );
				// update thumbnail metadata
				wp_update_attachment_metadata( $ID_TH, $newdata );
								
				//update GUID in db
				$table_posts = $wpdb->prefix.'posts';
				$where_th['ID'] = $ID_TH;
				$data_th['guid'] = $th_links['nwlink']; //$newthumb_dir['nwlink']
				$wpdb->update($table_posts, $data_th, $where_th, '%s');
				
				//update _wp_attached_file
				update_attached_file( $ID_TH, $th_links['nshortlink'] );//$newthumb_dir['
				
				
				$remarr = IR_genClonelist($commonIMGarray);
				$IRS['clone_img'] = count($remarr);
				$IRS['rem_img'] = $IRS['clone_img'] - $IRS['unic_img']-3;
				$IRS['totalfs'] = IR_check_exist($remarr, $page->ID);
				$IRS['remfs'] = $IRS['totalfs'] - $IRS['oldfs'];
				$post_log = IR_genPostLog($remarr);
				$post_stat = 'Файлов удалено: <b>'.$IRS['rem_img'].'</b> | Было занято: <b>'.IR_sc($IRS['totalfs'], 2).'</b> | Без изменения: '.IR_sc($IRS['oldfs'], 2).' | Освобождено: <b>'.IR_sc($IRS['remfs'], 2).'</b>';
				$post_log = $post_stat.' || '.$post_log;
				
				// удаление клонов
				echo '<ol>';
				foreach($remarr as $img) { 
					
					$simg = IR_shorturl($img, 1);
					
					if (unlink($img)){
						//echo '<li>Удален - '.$simg.'</li>';
					} else {
						echo '<li>Не удален - '.$simg.'</li>';
					}
				} 
				echo '</ol>';
				
				//update processing
				//Запись отчета в  ------------postprocessing
				$table_process = $wpdb->prefix.'postprocessing';
				$data = array();
				$data['log'] = $post_log;
				$data['processmistake'] = '0';
				$data['processed'] = '1';
				$data['mistake_log'] = $mistake_log;
				$where['post_id'] = $page->ID;
				
				$wpdb->update($table_process, $data, $where,  array( '%s', '%d', '%d' ) ); //array( '%s', '%s', '%d' )
				
				IR_updateStat($IRS['clone_img'], $IRS['rem_img'], $IRS['remfs']);
				
				//preview script work
				check_processing_table();
	
				echo '<br></hr><a href="'.home_url().'/?p='.$page->ID.'"><h3>'.$page->post_title.'</h3></a>';
				echo '<span style="color: #666; font-size: 0.9em">Опубликовано: '.$pdate['YMD'];
				echo ' | Изображений проекта: '.$IRS['unic_img'].' ('.IR_sc($IRS['oldfs'], 2).')';
				echo '<br>'.$post_stat.'<br><br><img width="100" src="'.$th_links['nwlink'].'">';
				echo '<br>'.$proj_dir.'<br><br>'.$IRS['post_img_prev'].'<br>[ POST ID: '.$page->ID;
				echo ' TH ID: '.$ID_TH.' ]<br>Удалено:';
				echo '<ol>';
				foreach($remarr as $img) {
					echo '<li>'.IR_shorturl($img, 1).'</li>';
				}
				echo '</ol></span>';
			
			}
			
		}
	}
	
}

    
function test_install() {
	//Установка значений по умолчанию при отсутствии опций
	//add_option('test_time_to_sync', '2'); //in hours
	//add_option('test_directory_to_media', 'uploads/temp/');
}

function test_uninstall() {
	//Установка значений по умолчанию при отсутствии опций
	//delete_option('test_time_to_sync', '2'); //in hours
	//delete_option('test_directory_to_media', 'uploads/temp/');
}


register_activation_hook( __FILE__, 'test_install');
register_deactivation_hook( __FILE__, 'test_uninstall');

add_action( 'admin_menu', 'test_add_admin_page' );
//add_action( 'admin_head', 'dolly_css' );

//=========================================================================================

?>