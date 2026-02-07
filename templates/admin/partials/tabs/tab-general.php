<?php
/**
 * CTA Form - General Tab
 *
 * Contains: CTA Name, Schedule
 *
 * @package CTA_Manager
 * @since 1.0.0
 *
 * Expected variables:
 * @var array|null $editing_cta Current CTA data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- CTA Name & Status Section -->
<div class="cta-form-section cta-no-mt">
	<div class="cta-form-row">
		<div class="cta-form-group">
			<label for="cta-name">
				<?php esc_html_e( 'CTA Name', 'cta-manager' ); ?>
				<span style="color: #dc3545;">*</span>
			</label>
			<input
				type="text"
				id="cta-name"
				name="cta_name"
				value="<?php echo esc_attr( $editing_cta['name'] ?? '' ); ?>"
				placeholder=""
				required
				maxlength="100"
			/>
		</div>
		<div class="cta-form-group">
			<label for="cta-status">
				<?php esc_html_e( 'Status', 'cta-manager' ); ?>
			</label>
			<select id="cta-status" name="cta_status" required>
				<?php
				$current_status = $editing_cta['status'] ?? 'draft';
				$is_editing = ! empty( $editing_cta );

				// Base options for all CTAs
				$base_options = [
					'draft'    => __( 'Draft', 'cta-manager' ),
					'publish'  => __( 'Publish', 'cta-manager' ),
					'schedule' => __( 'Schedule', 'cta-manager' ),
				];

				// Additional options only for existing CTAs
				$edit_options = [
					'trashed'  => __( 'Trashed', 'cta-manager' ),
					'archived' => __( 'Archived', 'cta-manager' ),
				];

				$all_options = $is_editing ? array_merge( $base_options, $edit_options ) : $base_options;

				foreach ( $all_options as $status_value => $status_label ) :
					$selected = ( $current_status === $status_value ) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr( $status_value ); ?>" <?php echo $selected; ?>>
						<?php echo esc_html( $status_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<!-- Schedule Section (shown when status is 'schedule') -->
	<div class="cta-form-row cta-schedule-fields" style="<?php echo ( $current_status === 'schedule' ) ? '' : 'display: none;'; ?>">
		<!-- Schedule Type -->
		<div class="cta-form-group">
			<label for="cta-schedule-type">
				<?php esc_html_e( 'Schedule Type', 'cta-manager' ); ?>
				<span style="color: #dc3545;">*</span>
			</label>
			<?php
			$schedule_type = $editing_cta['schedule_type'] ?? 'date_range';
			$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
			?>
			<select id="cta-schedule-type" name="schedule_type" class="cta-select" required>
				<option value="date_range" <?php selected( $schedule_type, 'date_range' ); ?>>
					<?php esc_html_e( 'Start & End Dates', 'cta-manager' ); ?>
				</option>
				<?php if ( $is_pro ) : ?>
					<option value="business_hours" <?php selected( $schedule_type, 'business_hours' ); ?>>
						<?php esc_html_e( 'Business Hours', 'cta-manager' ); ?>
					</option>
				<?php endif; ?>
			</select>
		</div>

		<!-- Date Range Fields -->
		<div class="cta-form-group">
			<div class="cta-date-range-header">
				<span class="cta-form-label">
					<?php esc_html_e( 'Date Range', 'cta-manager' ); ?>
					<span style="color: #dc3545;">*</span>
				</span>
				<label class="cta-time-toggle">
					<input type="checkbox" id="cta-include-times" name="include_times" value="1" <?php checked( ! empty( $editing_cta['include_times'] ) ); ?> />
					<span class="cta-time-toggle-label"><?php esc_html_e( 'Include Times', 'cta-manager' ); ?></span>
				</label>
			</div>
			<div class="cta-date-range">
				<!-- Start Date/Time -->
				<div class="cta-date-time-group">
					<?php
					$input_id    = 'cta-schedule-start';
					$input_name  = 'schedule_start';
					$label       = __( 'Start', 'cta-manager' );
					$value       = $editing_cta['schedule_start'] ?? '';
					include CTA_PLUGIN_DIR . 'templates/admin/partials/date-input.php';
					unset( $input_id, $input_name, $label, $value );
					?>
					<div class="cta-time-inputs" style="<?php echo empty( $editing_cta['include_times'] ) ? 'display: none;' : ''; ?>">
						<input type="number"
						       id="cta-schedule-start-hour"
						       name="schedule_start_hour"
						       min="1"
						       max="12"
						       value="<?php echo esc_attr( $editing_cta['schedule_start_hour'] ?? '12' ); ?>"
						       placeholder="HH" />
						<span class="cta-time-separator">:</span>
						<input type="number"
						       id="cta-schedule-start-minute"
						       name="schedule_start_minute"
						       min="0"
						       max="59"
						       value="<?php echo esc_attr( $editing_cta['schedule_start_minute'] ?? '00' ); ?>"
						       placeholder="MM" />
						<select id="cta-schedule-start-period" name="schedule_start_period">
							<option value="AM" <?php selected( ( $editing_cta['schedule_start_period'] ?? 'AM' ), 'AM' ); ?>>AM</option>
							<option value="PM" <?php selected( ( $editing_cta['schedule_start_period'] ?? 'AM' ), 'PM' ); ?>>PM</option>
						</select>
					</div>
				</div>

				<!-- End Date/Time -->
				<div class="cta-date-time-group">
					<?php
					$input_id    = 'cta-schedule-end';
					$input_name  = 'schedule_end';
					$label       = __( 'End', 'cta-manager' );
					$value       = $editing_cta['schedule_end'] ?? '';
					include CTA_PLUGIN_DIR . 'templates/admin/partials/date-input.php';
					unset( $input_id, $input_name, $label, $value );
					?>
					<div class="cta-time-inputs" style="<?php echo empty( $editing_cta['include_times'] ) ? 'display: none;' : ''; ?>">
						<input type="number"
						       id="cta-schedule-end-hour"
						       name="schedule_end_hour"
						       min="1"
						       max="12"
						       value="<?php echo esc_attr( $editing_cta['schedule_end_hour'] ?? '11' ); ?>"
						       placeholder="HH" />
						<span class="cta-time-separator">:</span>
						<input type="number"
						       id="cta-schedule-end-minute"
						       name="schedule_end_minute"
						       min="0"
						       max="59"
						       value="<?php echo esc_attr( $editing_cta['schedule_end_minute'] ?? '59' ); ?>"
						       placeholder="MM" />
						<select id="cta-schedule-end-period" name="schedule_end_period">
							<option value="AM" <?php selected( ( $editing_cta['schedule_end_period'] ?? 'PM' ), 'AM' ); ?>>AM</option>
							<option value="PM" <?php selected( ( $editing_cta['schedule_end_period'] ?? 'PM' ), 'PM' ); ?>>PM</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( ! $is_pro ) : ?>
		<!-- Pro Schedule Features Upsell -->
		<div class="cta-form-row cta-full-width cta-schedule-pro-upsell" style="margin-top: 20px; <?php echo ( $current_status === 'schedule' ) ? '' : 'display: none;'; ?>">
			<?php
			$icon        = 'clock';
			$title       = __( 'Unlock Business Hours Scheduling', 'cta-manager' );
			$description = __( 'Upgrade to Pro to schedule your CTAs based on business hours, ensuring they only display during specific days and times of the week.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
			unset( $icon, $title, $description );
			?>
		</div>
	<?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	'use strict';

	// Toggle schedule fields based on status selection
	$('#cta-status').on('change', function() {
		var status = $(this).val();
		var $scheduleFields = $('.cta-schedule-fields');
		var $scheduleInputs = $scheduleFields.find('input[type="datetime-local"]');
		var $scheduleProUpsell = $('.cta-schedule-pro-upsell');

		if (status === 'schedule') {
			$scheduleFields.slideDown(200);
			$scheduleInputs.prop('required', true);
			$scheduleProUpsell.slideDown(200);
		} else {
			$scheduleFields.slideUp(200);
			$scheduleInputs.prop('required', false);
			$scheduleProUpsell.slideUp(200);
		}
	});

	// Trigger on page load in case of edit mode
	$('#cta-status').trigger('change');

	// Toggle time inputs based on "Include Times" checkbox
	$('#cta-include-times').on('change', function() {
		var $timeInputs = $('.cta-time-inputs');
		var $timeFields = $timeInputs.find('input[type="number"], select');

		if ($(this).is(':checked')) {
			$timeInputs.slideDown(150);
			$timeFields.prop('required', true);
		} else {
			$timeInputs.slideUp(150);
			$timeFields.prop('required', false);
		}
	});

	// Set initial required state on page load
	if ($('#cta-include-times').is(':checked')) {
		$('.cta-time-inputs').find('input[type="number"], select').prop('required', true);
	}
});
</script>
