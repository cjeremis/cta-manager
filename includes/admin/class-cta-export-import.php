<?php
/**
 * Export Import Handler
 *
 * Handles AJAX export and import operations for CTA Manager data.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Export_Import {

	use CTA_Singleton;

	public function ajax_export(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$start  = CTA_Debug::start_timer();
		$data   = CTA_Data::get_instance();
		$export = $data->export_all();

		CTA_Debug::timing( 'Data export', $start, [
			'ctas_count'     => isset( $export['ctas'] ) ? count( $export['ctas'] ) : 0,
			'settings_count' => isset( $export['settings'] ) ? count( $export['settings'] ) : 0,
		] );

		wp_send_json_success( $export );
	}

	public function handle_export_request(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		if ( ! check_admin_referer( 'cta_admin_nonce', 'nonce' ) ) {
			wp_die( esc_html__( 'Invalid nonce', 'cta-manager' ) );
		}

		$data   = CTA_Data::get_instance();
		$export = $data->export_all();

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="cta-manager-export-' . current_time( 'Y-m-d-His' ) . '.json"' );
		echo wp_json_encode( $export );
		exit;
	}

	public function ajax_validate_import(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		if ( ! isset( $_FILES['file'] ) ) {
			wp_send_json_error( [ 'message' => 'No file provided' ], 400 );
		}

		$max_size = 5 * 1024 * 1024;
		if ( $_FILES['file']['size'] > $max_size ) {
			wp_send_json_error( [ 'message' => 'File size exceeds 5MB limit' ], 400 );
		}

		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $_FILES['file']['tmp_name'] );
		finfo_close( $finfo );

		if ( 'application/json' !== $mime_type && 'text/plain' !== $mime_type ) {
			wp_send_json_error( [ 'message' => 'Invalid file type. Only JSON files are allowed' ], 400 );
		}

		$file_contents = file_get_contents( $_FILES['file']['tmp_name'] );
		$data          = json_decode( $file_contents, true );

		if ( null === $data ) {
			wp_send_json_error( [ 'message' => 'Invalid JSON file' ], 400 );
		}

		$errors = CTA_Validator::validate_import_data( $data );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ], 400 );
		}

		wp_send_json_success( [
			'message' => __( 'File is valid and ready to import.', 'cta-manager' ),
			'data'    => $data,
		] );
	}

	public function ajax_import(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$start  = CTA_Debug::start_timer();
		$result = $this->process_import_data();

		if ( is_wp_error( $result ) ) {
			CTA_Debug::error( 'Import failed: ' . $result->get_error_message(), 'import' );
			wp_send_json_error( [ 'message' => $result->get_error_message() ], 400 );
		}

		if ( $result ) {
			CTA_Debug::timing( 'Data import', $start );
			CTA_Debug::info( 'Import completed successfully', 'import' );
			wp_send_json_success( [
				'message' => __( 'Settings imported successfully.', 'cta-manager' ),
			] );
		} else {
			CTA_Debug::error( 'Import failed with unknown error', 'import' );
			wp_send_json_error( [ 'message' => 'Failed to import settings' ], 500 );
		}
	}

	public function handle_import_request(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		if ( ! check_admin_referer( 'cta_admin_nonce', 'nonce' ) ) {
			wp_safe_redirect( add_query_arg( 'message', 'invalid_nonce', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		$result = $this->process_import_data();

		$message = 'import_failed';
		if ( is_wp_error( $result ) ) {
			$message = 'invalid_file';
		} elseif ( $result ) {
			$message = 'imported';
		}

		wp_safe_redirect( add_query_arg( 'message', $message, CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
		exit;
	}





	private function process_import_data() {
		$data_raw = null;

		if ( ! empty( $_FILES['file']['tmp_name'] ) ) {
			$contents = file_get_contents( $_FILES['file']['tmp_name'] );
			$data_raw = json_decode( $contents, true );
		} elseif ( isset( $_POST['data'] ) ) {
			$data_raw = json_decode( stripslashes( $_POST['data'] ), true );
		} elseif ( isset( $_POST['settings'] ) ) {
			$data_raw = json_decode( stripslashes( $_POST['settings'] ), true );
		}

		if ( null === $data_raw ) {
			return new WP_Error( 'invalid_data', 'Invalid data provided' );
		}

		$import = isset( $_POST['import'] ) && is_array( $_POST['import'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['import'] ) )
			: [];

		$mode          = $import['mode'] ?? 'replace';
		$create_backup = ! empty( $import['backup'] ) && '1' === $import['backup'];

		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		if ( ! $is_pro ) {
			$mode          = 'replace';
			$create_backup = false;
		}

		$errors = CTA_Validator::validate_import_data( $data_raw );

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', 'Import data validation failed' );
		}

		$data = CTA_Data::get_instance();
		return $data->import_all( $data_raw, 'merge' === $mode, $create_backup );
	}
}
