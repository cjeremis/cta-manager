<?php
/**
 * Onboarding Handler
 *
 * Handles onboarding wizard behavior and onboarding-related AJAX actions.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Onboarding {

	use CTA_Singleton;

	/**
	 * Total number of onboarding steps
	 */
	private const TOTAL_STEPS = 4;

	/**
	 * Mark a step as completed
	 *
	 * @param int $step Step number (1-4)
	 *
	 * @return void
	 */
	public function mark_step_completed( int $step ): void {
		if ( $step < 1 || $step > self::TOTAL_STEPS ) {
			return;
		}

		$data = CTA_Data::get_instance();
		$onboarding = $data->get_onboarding();
		$completed_steps = isset( $onboarding['completed'] ) ? (array) $onboarding['completed'] : [];

		if ( ! in_array( $step, $completed_steps, true ) ) {
			$completed_steps[] = $step;
		}

		$data->update_onboarding( [ 'completed' => $completed_steps ] );
	}

	/**
	 * Mark onboarding as complete
	 *
	 * @return void
	 */
	public function complete(): void {
		CTA_Data::get_instance()->update_onboarding( [
			'completed'  => true,
			'dismissed'  => true,
			'finished_at' => current_time( 'mysql' ),
		] );
	}

	/**
	 * AJAX handler to mark step as completed
	 *
	 * @return void
	 */
	public function ajax_complete_step(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$step = isset( $_POST['step'] ) ? intval( $_POST['step'] ) : 0;

		if ( $step < 1 || $step > self::TOTAL_STEPS ) {
			wp_send_json_error( [ 'message' => 'Invalid step' ], 400 );
		}

		$this->mark_step_completed( $step );

		wp_send_json_success( [
			'message' => sprintf(
				/* translators: %d: step number */
				__( 'Step %d completed.', 'cta-manager' ),
				$step
			),
		] );
	}

	/**
	 * AJAX handler to complete onboarding wizard
	 *
	 * @return void
	 */
	public function ajax_complete(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$this->complete();

		wp_send_json_success( [ 'message' => __( 'Onboarding completed!', 'cta-manager' ) ] );
	}

	/**
	 * AJAX handler to dismiss onboarding
	 *
	 * @return void
	 */
	public function ajax_dismiss(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		CTA_Data::get_instance()->update_onboarding( [ 'dismissed' => true ] );

		wp_send_json_success( [ 'message' => __( 'Onboarding dismissed.', 'cta-manager' ) ] );
	}

	/**
	 * Check if onboarding should be shown
	 *
	 * Only show onboarding if there are no active non-demo CTAs.
	 * The presence of active non-demo CTAs takes priority over dismissal status.
	 *
	 * @return bool
	 */
	public static function should_show(): bool {
		$data = CTA_Data::get_instance();

		// Check for active non-demo CTAs
		$all_ctas = $data->get_ctas();
		$has_active_non_demo_ctas = false;

		foreach ( $all_ctas as $cta ) {
			// Check if CTA is active (not a draft)
			$is_active = ! isset( $cta['status'] ) || $cta['status'] === 'active';
			// Check if CTA is not demo data
			$is_demo = isset( $cta['_demo'] ) && $cta['_demo'] === true;

			if ( $is_active && ! $is_demo ) {
				$has_active_non_demo_ctas = true;
				break;
			}
		}

		// Don't show onboarding if there are active non-demo CTAs
		if ( $has_active_non_demo_ctas ) {
			return false;
		}

		// Show onboarding when there are no active non-demo CTAs
		// This ignores dismissal status to help users who clear their data
		return true;
	}

	/**
	 * Get the current onboarding state
	 *
	 * @return array {
	 *     @type array $completed_steps Array of completed step numbers
	 *     @type bool  $is_complete     Whether all steps are done
	 *     @type bool  $dismissed       Whether wizard was dismissed
	 * }
	 */
	public static function get_state(): array {
		$onboarding = CTA_Data::get_instance()->get_onboarding();

		$completed_steps = isset( $onboarding['completed'] ) && is_array( $onboarding['completed'] )
			? $onboarding['completed']
			: [];

		return [
			'completed_steps' => $completed_steps,
			'is_complete'     => $onboarding['completed'] === true || count( $completed_steps ) >= self::TOTAL_STEPS,
			'dismissed'       => ! empty( $onboarding['dismissed'] ),
		];
	}
}
