<?php
/*
 * dl-file.php
 *
 * Protect uploaded files with login.
 *
 * @link http://wordpress.stackexchange.com/questions/37144/protect-wordpress-uploads-if-user-is-not-logged-in
 *
 * @author hakre <http://hakre.wordpress.com/>
 * @license GPL-3.0+
 * @registry SPDX
 */

require_once('wp-load.php');

// if not logged in, prompt user to login
is_user_logged_in() || auth_redirect();

// init variables
// ensure params included only number, letters, underscores, and dashes
$file_id = isset($_GET['file']) ? (int) preg_replace('/[^-a-zA-Z0-9_]/', '', $_GET['file']) : null;
$bulk_id = isset($_GET['bulk']) ? (int) preg_replace('/[^-a-zA-Z0-9_]/', '', $_GET['bulk']) : null;
$print_id = isset($_GET['print']) ? (int) preg_replace('/[^-a-zA-Z0-9_]/', '', $_GET['print']) : null;
$application_id = null;
$file = null;
$bulk = null;
$print = null;
$download = null;

// get query params and sanitize
if (!is_int($file_id) && !is_int($bulk_id) && !is_int($print_id)) {
	status_header(404);
	die('404 &#8212; You don\'t know what you want do you?');
}

// get the application id
if ($file_id) {
	// check if it's a file
	$file = get_attached_file($file_id);
	if (!is_file($file)) {
		status_header(404);
		die('404 &#8212; File not found.');
	}

	// get the Application ID from filepath
	$pattern = '/\/app(\d+)\//';
	preg_match($pattern, $file, $matches);
	$application_id = $matches[1];

} elseif ($bulk_id) {
	$application_id = $bulk_id;
} else {
	$application_id = $print_id;
}

// check that user has access to the Application that file is attached to
$access_type = fo_user_has_access_to_application($application_id);
if (!$access_type) {
	status_header(404);
	die('404 &#8212; File not found.');
}

// decide what to do
if ($file_id) {
	fo_prompt_file_download($file);
} elseif ($bulk_id) {
	fo_prompt_bulk_download($bulk_id);
} elseif ($print_id) {
	fo_generate_print_application($print_id);
}



//
// Helper Functions
//



function fo_prompt_file_download($file) {
	// TODO
	// Refactor the below code.
	// How much is needed compared to fo_prompt_bulk_download()?

	$mime = wp_check_filetype($file);
	if( false === $mime[ 'type' ] && function_exists( 'mime_content_type' ) )
		$mime[ 'type' ] = mime_content_type( $file );

	if( $mime[ 'type' ] )
		$mimetype = $mime[ 'type' ];
	else
		$mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );

	header('Content-Type: ' . $mimetype); // always send this
	// header('Content-Disposition: attachment; filename="myfile.pdf.pdf"');
	$filename = basename($file);
	header('Content-Disposition: inline; filename="'. $filename.'"');

	if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' )) {
		header( 'Content-Length: ' . filesize( $file ) );
	}

	$last_modified = gmdate('D, d M Y H:i:s', filemtime($file));
	$etag = '"' . md5( $last_modified ) . '"';
	header( "Last-Modified: $last_modified GMT" );
	header( 'ETag: ' . $etag );
	// header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );

	// Support for Conditional GET
	$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;

	if( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
		$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;

	$client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
	// If string is empty, return 0. If not, attempt to parse into a timestamp
	$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

	// Make a timestamp for our most recent modification...
	$modified_timestamp = strtotime($last_modified);

	if ( ( $client_last_modified && $client_etag )
		? ( ( $client_modified_timestamp >= $modified_timestamp) && ( $client_etag == $etag ) )
		: ( ( $client_modified_timestamp >= $modified_timestamp) || ( $client_etag == $etag ) )
		) {
		status_header( 304 );
		exit;
	}

	// If we made it this far, just serve the file
	readfile( $file );
	exit;
}

function fo_prompt_bulk_download($application_id, $from_dir = false) {

	// TODO
	// Check if a ZIP has already been generated?
	// This will only work if we generate the zip on Save or something.
	// Generate ZIP on application submission, and on subsequent edits.

	$wkhtmltopdf_path = isset($_ENV['PANTHEON_ENVIRONMENT']) ? '/srv/bin/wkhtmltopdf' : 'wkhtmltopdf';
	$application_dir = isset($_ENV['PANTHEON_ENVIRONMENT']) ? "/private/app$application_id/" : "";

	$company_name = get_field('company_name', $application_id) ? get_field('company_name', $application_id) : 'funding-organizer-application.zip';
	$company_filename = preg_replace('/[^a-z0-9]+/', '-', strtolower($company_name));
	$zip_name = $company_filename . '.zip';
	$pdf_name = $company_filename . '.pdf';
	$pdf_path = $application_dir . $pdf_name;
	$html_name = $company_filename . '.html';
	$html_path = $application_dir . $html_name;

	$zip = new ZipArchive();
	$zip_sub_dir = 'files/';
	$file_paths = array();

	// generate PDF of application
	$application_html = fo_get_application_html($application_id, $zip_sub_dir);
	$file_pointer = fopen($html_path, "w");
	$bytes = fwrite($file_pointer, $application_html);
	$result = fclose($file_pointer);

	$command = "$wkhtmltopdf_path --keep-relative-links --enable-local-file-access $html_path $pdf_path";
	$result = exec($command);

	// Make sure we can open the zipfile.
	if ($zip->open($zip_name, ZipArchive::CREATE) !== true) {
		status_header(500);
		die('500 &#8212; Something went wrong creating the zip file.');
	}

	// Add PDF & HTML files to zipfile
	$zip->addFile($pdf_path);
	$zip->addFile($html_path);

	if ($from_dir) {
		// TODO
		// This is not implemented yet.
		// Come back to allow downloading of all files in the dir,
		// instead of grabbing all attachments from WP.
		fo_add_to_zip($zip, null, $application_dir);
	} else {
		// Get all the file attachments to the application
		$files = get_children(array(
				'post_parent' => $application_id,
				'post_type' => 'attachment',
				'posts_per_page' => -1
			), ARRAY_A);

		$file_paths = array_map( function($file) {
			return get_attached_file($file['ID']);
			// $file['id'];
		}, $files);

		fo_add_to_zip($zip, $file_paths, null, $zip_sub_dir);
	}

	$zip->close();

	// prompt download
	fo_download_zipfile($zip_name);

	// delete tmp files
	$delete_after = true;
	if ($delete_after) {
		unlink($zip_name);
		unlink($html_path);
		unlink($pdf_path);
	}
}


// Create the zip file
// Should be able to pass
function fo_add_to_zip($zip, $files = null, $from_dir = null, $sub_dir = '') {
	if (!is_null($files) && is_array($files)) {
		foreach($files as $fullpath) {
			if (is_file($fullpath)) {
				$zip->addFile($fullpath, $sub_dir.basename($fullpath));
			}
		}
	} elseif (!is_null($from_dir)) {
		if (is_dir($from_dir)) {
			if ($dh = opendir($from_dir)) {
				// read files from the directory
				while (($file = readdir($dh)) !== false) {
					// if it's a file
					if (is_file($from_dir.$file)) {
						if ($file != '' && $file != '.' && $file != '..') {
							$zip->addFile($from_dir.$file);
						}
					} else {
						// if directory
						if (is_dir($from_dir.$file)) {
							if ($file != '' && $file != '.' && $file != '..') {
								// add empty directory
								$zip->addEmptyDir($from_dir.$file);
								$folder = $from_dir.$file.'/';

								// read the data from the folder
								fo_add_to_zip($zip, null, $folder);
							}
						}
					}
				}
				closedir($dh);
			}
		}
	}
	return true;
}


// Download the zip file
function fo_download_zipfile($zipfile) {
	if (file_exists($zipfile)) {
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="'.basename($zipfile).'"');
		header('Content-Length: '.filesize($zipfile));

		flush();
		readfile($zipfile);
	}
}

function fo_generate_print_application($application_id) {
	echo fo_get_application_html($application_id);
}

/*
delete post meta

- delete the file in /private/appxxx/
- delete the acf row from post
- delete media post in db
*/