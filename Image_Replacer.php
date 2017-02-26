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
		  post_id bigint(20) NOT NULL,
		  processed tinyint(1) default '0',
		  processmistake tinyint(1) default '0',
		  mistake_log TEXT NULL,
		  UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //log TEXT NULL,
		  
		dbDelta( $sql );
		$table_options = $wpdb->prefix.'options';
		$IR_beginData = '{"Total filecount":0,"Removed filecount":0,"Removed filesize":0}';
		$wpdb->query("INSERT INTO $table_options(option_name, option_value, autoload) VALUES( 'Image_Replacer_filedata' , '$IR_beginData' , 'no' )");
		
		
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
	
	$attach_id = 1639;
	$array = wp_get_attachment_metadata( $attach_id );	
	//pre($array);
	$file = 'W:/home/lexlarvatus.com/www/wp-content/uploads/sites/3/2017/02/Koala.jpg';
	//$bt = filesize($file);
	
		
	$total = 17;
	$count = 5;
	$size = 11002350;
	//IR_resetStat();
	//IR_updateStat($total,$count,$size);
	
	//$post_year = 2006;
	//$post_name = 'Rerwe';
	//echo IR_create_post_folder($post_year, $post_name);
	
	global $wpdb;
	
	$per_check = 15;
	
	$table_posts = $wpdb->prefix.'posts';
	$table_process = $wpdb->prefix.'postprocessing';
	$pages = $wpdb->get_results( 
		"
		SELECT post_title, post_content, ID, post_date, post_name
		FROM $table_posts
		WHERE post_status = 'publish' 
		AND post_type = 'post' AND ID IN (SELECT post_id FROM $table_process WHERE processmistake = 0 AND processed = 0)
		LIMIT $per_check
		"
	);
	$old_img_temp[] = '';
	//echo $upload_dir['baseurl'];
	pre(wp_upload_dir());
	
	$upload_dir = wp_upload_dir();
	$link = "http://lexlarvatus.com/lex/wp-content/uploads/sites/3/2015/08/00112.jpg";
	
	$temp_preg = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $link );
	
	echo $temp_preg.'<br>====';
	
	//$answer = IR_check_exist($old_img_temp);
	if ($answer == 1) {echo '<br> TRUE';} else { echo $answer;} 
	
	echo '<br>====';
	
	echo '<br>'.count($old_img_temp);
	//$old_img = array_unique($old_img);
	
	if( $pages ) {
		foreach ( $pages as $page ) {
			$post_date = $page->post_date;
			$post_id = $page->ID;
			
			//echo '<br>Post date: '.$post_date;
			echo '<br>'.$page->post_name;
			$year = 2007;
			//$projfolder_name = IR_create_projfolder_name($page->post_date, $page->post_name, $page->post_title);
			//IR_create_post_folder($year, $projfolder_name).'<br>';
			$oldIMGarray = IR_getIMGarray($page->post_content);
			//pre($oldIMGarray);
			/* $result = IR_check_exist($oldIMGarray);
			if ($result == 1) { echo '<br>==== Fine';} else { pre($result);} */
			
		}
	}
	
}
function IR_check_exist($array) { // return true or mistake array
	
	$upload_dir = wp_upload_dir();
	$exist = 0;
	foreach($array as $link) {
		//get dir link
		$old_file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $link );
		
		if (file_exists($old_file) ) { 
			$exist++;
		} else { 
			$missing[] = $old_file;
		}
	}
	
	// return 1 or missing files array
	if (count($array) == $exist) { 
		return 1; 
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
			//echo '<br><div style="color: #944; font-size: 1.7em;">Статья пропущена!</div>';
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
		
	//convert data <-
	$IR_stat_array = json_decode($IR_filedata[0]->option_value);
	
	//prepare data
	$IR_size = $IR_stat_array->Removed_filesize;
	$IR_Kb = $IR_size/1024;
	$IR_Kb = round($IR_Kb, 2);
	$IR_Mb = $IR_size/1024/1024;
	$IR_Mb = round($IR_Mb, 2);
	$IR_stat_string = '<br>Файлов обработано: '.$IR_stat_array->Total_filecount.'<br>Файлов удалено: '.$IR_stat_array->Removed_filecount.'<br>Освобождено: '.$IR_Mb.' Мбайт';
	
	//return statistic
	return $IR_stat_string;
}
function IR_updateStat($total_filecount, $remove_filecount, $remove_filesize) {
	
	//select data from table {"Total_filecount":0,"Removed_filecount":0,"Removed_filesize":0}
	global $wpdb;
	$table_options = $wpdb->prefix.'options';
	$IR_filedata = $wpdb->get_results("SELECT option_value FROM $table_options WHERE option_name='Image_Replacer_filedata'");
		
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
function IR_resetStat() {
	
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

function IR_create_projfolder_name ($post_date, $post_name, $post_title) { //return project folder name
	
	if (substr($post_name, 0, 1) == "%"  ) {
		$proj_name = translit($post_title);
	} else {
		$proj_name = $post_name;
	}
	
	$month = substr( $post_date, 5, 2 );
	$day = substr( $post_date, 8, 2 );
	$year = substr( $post_date, 0, 4 );
	$proj_name = $year.".".$month.".".$day."_".$proj_name;
	
	//return project folder name
	return $proj_name;
}

function test_parseposts() {
	
	global $upload_dir;
	$upload_dir = wp_upload_dir();
	
	//cut  /sites/3 = http://lexlarvatus.com/lex/wp-content/uploads
	$IRL['up_url'] = preg_replace( "/\/sites\/[0-9]/", '',  $upload_dir['baseurl'] ); 
	
	//cut  /sites/3 = W:\home\lexlarvatus.com\www/wp-content/uploads
	$IRL['up_dir'] = preg_replace( "/\/sites\/[0-9]/", '',  $upload_dir['basedir'] ); 
	
	$IRL['proj'] = '/projects';
	
	$IRL['proj_dir'] = $IRL['up_dir'].$IRL['proj'];
	
	$home_url = home_url();
	
	//Создание каталога /projects
	if (file_exists($IRL['proj_dir'])) {} else { mkdir($IRL['proj_dir'], 0755); }
	//define user folder 
	switch (get_current_blog_id()) {
		case 1: $projname = '/Common'; break;
		case 2: $projname = '/Elena'; break;
		case 3: $projname = '/Lex'; break;
	}
	
	$IRL['user_proj_dir'] = $IRL['proj_dir'].$projname;
	
	$IRL['user_proj_url'] = $IRL['up_url'].$IRL['proj'].$projname;
	
	//Создание каталога /projects/*User*
	if (file_exists($IRL['user_proj_dir'])) {} else { mkdir($IRL['user_proj_dir'], 0755); }//Создание персонального каталога
	
	
	global $wpdb;
	
	$per_check = $_POST['per_check'];
	
	$table_posts = $wpdb->prefix.'posts';
	$table_process = $wpdb->prefix.'postprocessing';
	/* вытаскивает из базы данных заголовки и содержимое всех опубликованных страниц без ошибок и проверенных ранее*/ //FROM $wpdb->posts
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
			$post_date = $page->post_date;
			$post_id = $page->ID;
			//$source_date = substr($post_date, 0, 10);
			$month = substr( $post_date, 5, 2 );
			$day = substr( $post_date, 8, 2 );
			$year = substr( $post_date, 0, 4 );
			//if( wp_checkdate( $month, $day, $year, $source_date ) ){ } //проверка даты пройдена, дата реальна
			
			if (substr($page->post_name, 0, 1) == "%"  ) {
				$projfolder_name = translit($page->post_title);
			} else {
				$projfolder_name = $page->post_name;
			}
			
			$year_dir = $IRL['user_proj_dir']."/".$year;
			//Создание каталога года
			if (file_exists($year_dir)) {} else { mkdir($year_dir, 0755); }
			
			//$new_folder_name = $proj_folder_name
			$IRL['proj_folder_name'] = "/".$year.".".$month.".".$day."_".$projfolder_name;
			$IRL['proj_folder_dir'] = $year_dir.$IRL['proj_folder_name'];
			$IRL['proj_folder_url'] = $IRL['user_proj_url']."/".$year.$IRL['proj_folder_name'];
			
			//Создание каталога проекта
			if (file_exists($IRL['proj_folder_dir'])) {} else { mkdir($IRL['proj_folder_dir'], 0755);}
			//include_once('simple_html_dom.php');
			
			// get DOM from URL or file
			//$html = new simple_html_dom();
			$temp_post_content = $page->post_content;
			$html = str_get_html($temp_post_content);
			
			//Вывод статистики о статье
			$element = $html->find('img');
			echo '</hr><a href="'.home_url().'/?p='.$post_id.'"><h3>'.$page->post_title.'</h3></a>'.'<span style="color: #999; font-size: 0.7em">Опубликовано: '.$post_date;
			echo '<br>Изображений: '.count($element).'<br></span>'; 
			echo '<br><div style="color: #99c; font-size: 0.7em; width:300px;">'.$html->plaintext.'</div>';
			
			
			$mistake = 0;
			$mistake_log = "";
			$match = 0;
			//Извлечение ссылки на превью поста
			$table_postmeta = $wpdb->prefix.'postmeta';
			$id_thumbs = $wpdb->get_results("SELECT meta_value FROM $table_postmeta WHERE post_id=$post_id AND meta_key ='_thumbnail_id' ");
			$ID_TH = $id_thumbs[0]->meta_value;
			$thumb_links = $wpdb->get_results("SELECT guid FROM $table_posts WHERE ID=$ID_TH");
			$thumb_link = $thumb_links[0]->guid;
			$thumb_name = preg_replace( "/[-][0-9]+[x][0-9]+.[a-z]{3}/", '',basename($thumb_link));
			$thumb_name = preg_replace( "/[.][a-z]{3}/", '',$thumb_name);
			if (count($element) == 1) {
				echo '<br>Element - '.$element->src;
				if (basename($thumb_link)== basename($element->src)) {
					echo '<br>'.basename($thumb_link).' = '.basename($element->src);
					}
			}
			if (count($element) == 0) {
				echo '<br><div style="color: #944; font-size: 1.7em;">Статья пропущена!</div>';
			} else {
				// find all image
				foreach($html->find('img') as $image) {
					
					$IR_old_url = $image->src;//$path_parts['basename'];
					$gen_links = IR_get_links($image->src, $IRL); //генерация старых и новых ссылок для web и сервера
					$names[] = $gen_links; //добавление ссылок в массив
					$image->setAttribute('src', $gen_links["nwlink"]); //замена ссылки в тексте статьи
					unset($gen_links);
					//=============== <a href='?'> <img></a>====
					if ($image->parent()->tag == 'a') { 
						$pos = strripos($image->parent()->href, home_url()); //проверка на соответствие домену
						if ($pos === false) {
							/* echo "<br>Img -> parent a href: - Ссылка на внешний источник."; */
						} else {
							$a = substr($image->parent()->href, -4);
							if ( $a == '.jpg' || $a == '.JPG' || $a == '.png' || $a == '.PNG' || $a == '.gif' || $a == '.GIF') {
								//echo '<br>Valid link find!';
								$i = 0;
								foreach($names as $name) {
									if ($name['name'] == basename($image->parent()->href)) { $i++; /* echo '<br>В массиве уже есть '.basename($image->parent()->href); */}
								}
								$gen_links = IR_get_links($image->parent()->href, $IRL);
								if ( $i > 0 ) {//Проверка присутствия имени картинки в массиве
									//echo '<br>Thumb in array';
								} else {
									$names[] = $gen_links; //добавление ссылок в массив
								}
								$image->parent()->setAttribute('href', $gen_links["nwlink"]); //замена ссылки в тексте статьи
								unset($gen_links);
							} else { /* echo '<br>Img -> parent a href: - Ссылка не на изображение.<br>'; */}
						}
					}
					//==========================================================
					unset($gen_links);
				}
				echo '<br>Каталог: '.$IR_old_url ;
				// add post preview in array
				/* $t = 0;
				if ($thumb_link !== NULL) {
					foreach($names as $tname) {
						if ($tname['name'] == basename($thumb_link)) { $t++;}
					}
					$gen_links = IR_get_links($thumb_link, $IRL);
					$new_thumb_wlink = $gen_links["nwlink"];//сохранение для отдельного запроса
					if ( $t > 0 ) {//Проверка присутствия имени картинки в массиве
					} else {
						$names[] = $gen_links; //добавление ссылок в массив
					}
				} else { 
					$mistake = 1;
					$mistake_log = $mistake_log.' <br>Thumbnail is EMPTY!';
				} */
				//Existing check
				$exist = 0;
				
				foreach($names as $isexist) {
					if (file_exists($isexist['oflink']) ) { 
						//echo ' File exist';
						$exist++;
					} else {
						//echo'ZZ';
						$mistake = 1;
						$mistake_log = $mistake_log.' <br> Не найден файл: '.$isexist['oflink'];
					}
				}
				
				if ($exist == count($names)) {
					//find copy
					foreach($names as $unic) { //ищем колонов
						
						if (isset($name_clone_arr)) { 
							$c = 0;
							foreach($name_clone_arr as $clone) { //выделить из нового массива элемент
								if ($unic['name'] == $clone['name']) {//сравнить его с выбранным в первом элемента 
									$c++;
								} else { 
								}
							} 
							if ($c == 0) {
								$mass_sas = find_img_copy($unic['oflink']);
								foreach($mass_sas as $cloneT) {
									$name_clone_arr[] = array( 
										'name' => $cloneT,
										'ofl' => dirname($unic['oflink']).'/'.$cloneT,
										'nfl' => dirname($unic['nflink']).'/'.$cloneT,
									);
									
								}
							}
						} else {//create array from 1st element of $names array
							$mass_sas = find_img_copy($unic['oflink']);
								foreach($mass_sas as $clone) {
									$name_clone_arr[] = array( 
										'name' => $clone,
										'ofl' => dirname($unic['oflink']).'/'.$clone,
										'nfl' => dirname($unic['nflink']).'/'.$clone,
									);
									
								}
						}
					}
					echo '<div style="color: #777; font-size: 0.85em; "><table><td><b>Найдено клонов - '.count($name_clone_arr).':</b><ol>';
					foreach($name_clone_arr as $clu) { echo '<li>'.$clu['name'].'</li>';} //вывели всех юников
					echo '</ol></td><td><b>Перемещены из /uploads/:</b><ol>';
					//удаляем
					foreach($name_clone_arr as $remfile) { 
								/* unlink($remfile['oflink']); */ 
								
								
								if (copy($remfile['ofl'], $remfile['nfl'])) {
									unlink($remfile['ofl']); // удаление оставшейся копии файла, раскомментировать 
									echo '<li>'.str_replace($upload_dir['basedir'], "", $remfile['ofl']).'</li>'; //
								} else { 
									echo '<br><span style="color: #c55;">Ошибка при копировании! - '.$remfile['owl'].'</span><br>';
									$mistake = 1;
									$mistake_log = $mistake_log.'<br>Ошибка при копировании! - '.$remfile['owl'].'||';
									$error_moving++;
								}
							} // удаление оставшейся копии файла, раскомментировать 
					echo'</ol></td></table></div>';
					//Move
					$error_moving = 0;
					foreach($names as $file) {
						echo '<img width=\'70\' src=\''.$file['nwlink'].'\' title=\''.$file['name'].'\'> ';
					}
					if ($error_moving == 0) {
						//foreach($names as $remfile) { unlink($remfile['oflink']); } // удаление оставшейся копии файла, раскомментировать 
					}
				} else { /* echo '<br>Один из файлов не найден. Exist - '.$exist.' В names '.count($names).' элементов.'; */}
				
				//
				//$json_img = json_encode($j_img);
				echo '</br><span style="color: #c55;">'.$mistake_log.'</span>';
				
				//Запись отчета в  ------------postprocessing
				$table_process = $wpdb->prefix.'postprocessing';
				$data = array();
				//$data['log'] = $json_img;
				$data['processmistake'] = $mistake;
				if ($mistake == 1) {$data['processed'] = '0';} else {$data['processed'] = '1';}
				$data['mistake_log'] = $mistake_log;
				$where['post_id'] = $page->ID;
				
				$wpdb->update($table_process, $data, $where, array( '%s', '%d', '%d' ));
				
				
				// Обновляем данные в БД      WORK
				if ($mistake == 1) {
					echo '<br> Запись с ID = '.$page->ID.' не обновлена в Wordpress.';
				} else {
					// Создаем массив данных
					$my_post = array();
					$my_post['ID'] = $page->ID;
					//замена содержимого статьи измененной информацией
					$my_post['post_content'] =  $html->outertext;
					wp_update_post( $my_post );
					
					
					//Замена ссылки на линк в 
					/* $thumb_table_name = $wpdb->prefix.'post';
					echo '<br>ID_TH: '.$ID_TH;
					$where_th['ID'] = $ID_TH;
					$data_th['guid'] = $new_thumb_wlink;
					$wpdb->update($thumb_table_name, $data_th, $where, '%s'); */
					
					//$wpdb->update()
					//заменяет линк на превью поста в таблице.
					/* $wpdb->update($table_posts,//table
						array('guid' => $new_thumb_wlink), //data ('column' => 'value', 'column' => 'value'),
						array('ID' => $ID_TH),
						array( '%s' ), //format %s - string, %d - число
						array( '%d' ) //where_format
					); */
					//--update _wp_attached_file
					//update_attached_file( $ID_TH, $new_thumb_wlink );
					
					/* $wpdb->update('wp_postmeta',//table
						array('meta_value' => str_replace($upload_dir['baseurl'].'/', '', $new_thumb_wlink) ), //data ('column' => 'value', 'column' => 'value'),
						array('post_id' => $ID_TH, 'meta_key' => '_wp_attached_file'),
						array( '%s' ), //format %s - string, %d - число
						array( '%d' ) //where_format
					); */
					echo '<br>Запись с ID = '.$my_post['ID'].' обновлена в Wordpress.';
				}
				
				 
				//unset($j_img);
			
			}// if post haven't image
			
			$html->clear(); // подчищаем за собой 
			unset($html);
			
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