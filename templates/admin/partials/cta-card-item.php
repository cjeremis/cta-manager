<?php
/**
 * Admin Partial Template - Cta Card Item
 *
 * Handles markup rendering for the cta card item admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	
// ============================================
// CTA Data
// ============================================
$cta 					= isset( $cta ) ? $cta : [];
$cta_id           		= $cta['id'] ?? 0;
$cta_status 			= $cta['status'] ?? 'draft';
$is_cta_enabled 		= isset( $cta['enabled'] ) ? (bool) $cta['enabled'] : true;
$first_active_at    	= $cta['first_active_at'] ?? ( $cta['published_at'] ?? '' );
$cta_list_card_badge 	= false;
$created_at   			= $cta['created_at'] ?? '';
$updated_at   			= $cta['updated_at'] ?? '';
$date_format  			= 'M j, y';
$created_by_id 			= $cta['created_by'] ?? ( $cta['author_id'] ?? null );
if ( $created_by_id && ! isset( $cta['created_by_name'] ) ) {
	$author = get_userdata( $created_by_id );
	$cta['created_by_name'] = $author ? $author->display_name : '';
}

// ============================================
// Search Data
// ============================================
$searchable_parts = [
	$cta['name'] ?? '',
	$cta['title'] ?? '',
	$cta['button_text'] ?? '',
	$cta['description'] ?? '',
	$cta['link_url'] ?? '',
	$cta['phone_number'] ?? '',
	$cta['email_address'] ?? '',
	$cta_id,
	'[cta-manager id="' . $cta_id . '"]',
];
$filter_search = strtolower( implode( ' ', array_filter( $searchable_parts ) ) );	

// ============================================
// Filter Data
// ============================================
$filter_type             = $cta['type'] ?? 'phone';
$filter_layout           = $cta['layout'] ?? 'button';
$filter_visibility       = $cta['visibility'] ?? 'all_devices';
$filter_icon             = $cta['icon'] ?? 'none';
$filter_button_animation = $cta['button_animation'] ?? 'none';
$filter_icon_animation   = $cta['icon_animation'] ?? 'none';
$filter_demo             = ! empty( $cta['_demo'] ) ? '1' : '0';
$filter_name             = strtolower( $cta['name'] ?? '' );
if ( $cta_status === 'trash' ) {
	$filter_status = 'trash';
} elseif ( $cta_status === 'archived' ) {
	$filter_status = 'archived';
} elseif ( $cta_status === 'draft' ) {
	$filter_status = 'draft';
} elseif ( $cta_status === 'scheduled' ) {
	$filter_status = 'scheduled';
} elseif ( $cta_status === 'publish' && ! empty( $cta_schedule_start ) && strtotime( $cta_schedule_start ) > current_time( 'timestamp' ) ) {
	$filter_status = 'scheduled';
} elseif ( $cta_status === 'publish' ) {
	$filter_status = 'published';
} else {
	$filter_status = 'draft';
}

// ============================================
// Scheduled CTA Data
// ============================================
$schedule_type 			= $cta['schedule_type'] ?? 'date_range';
$cta_schedule_start 	= $cta['schedule_start'] ?? null;
$cta_schedule_end   	= $cta['schedule_end'] ?? null;
$schedule_phase 		= null; // null, 'upcoming', 'active', 'expired'
$schedule_phase_label 	= '';
$countdown_target 		= null;
$countdown_label 		= '';
$countdown_done_label 	= '';
if ( $filter_status === 'scheduled' && ! empty( $cta_schedule_start ) ) {
	$countdown_target = strtotime( $cta_schedule_start . ' 00:00:00' );
	$countdown_label = __( 'Publishes in:', 'cta-manager' );
	$countdown_done_label = __( 'Publishing now...', 'cta-manager' );
} elseif ( $filter_status === 'published' && ! empty( $cta_schedule_end ) ) {
	$end_ts = strtotime( $cta_schedule_end . ' 23:59:59' );
	if ( $end_ts && $end_ts > current_time( 'timestamp' ) ) {
		$countdown_target = $end_ts;
		$countdown_label = __( 'Ends in:', 'cta-manager' );
		$countdown_done_label = __( 'Ending now...', 'cta-manager' );
	}
}

// ============================================
// Scheduled Status by Date Range
// ============================================
if ( 'date_range' === $schedule_type ) {
	$schedule_start = $cta['schedule_start'] ?? '';
	$schedule_end 	= $cta['schedule_end'] ?? '';

	if ( ! empty( $schedule_start ) || ! empty( $schedule_end ) ) {
		$now 				= current_time( 'timestamp' );
		$start_timestamp 	= ! empty( $schedule_start ) ? strtotime( $schedule_start . ' 00:00:00' ) : 0;
		$end_timestamp 		= ! empty( $schedule_end ) ? strtotime( $schedule_end . ' 23:59:59' ) : 0;
		$upcoming_threshold = $start_timestamp - ( 24 * HOUR_IN_SECONDS );

		if ( $start_timestamp && $now < $start_timestamp ) {
			// Before start date
			if ( $now >= $upcoming_threshold ) {
				$schedule_phase 		= 'upcoming';
				$schedule_phase_label 	= __( 'Starting Soon', 'cta-manager' );
			}
		} elseif ( $start_timestamp && $end_timestamp && $now >= $start_timestamp && $now <= $end_timestamp ) {
			// Between start and end
			$schedule_phase 		= 'active';
			$schedule_phase_label 	= __( 'Live Now', 'cta-manager' );
		} elseif ( $end_timestamp && $now > $end_timestamp ) {
			// After end date
			$schedule_phase			= 'expired';
			$schedule_phase_label 	= __( 'Expired', 'cta-manager' );
		} elseif ( $start_timestamp && ! $end_timestamp && $now >= $start_timestamp ) {
			// Only start date, no end date
			$schedule_phase 		= 'active';
			$schedule_phase_label 	= __( 'Live Now', 'cta-manager' );
		}
	}
} 

// ============================================
// Scheduled Status for Business Hours
// ============================================
elseif ( 'business_hours' === $schedule_type ) {
	// Check if currently within business hours (Pro feature)
	if ( class_exists( 'CTA_Pro_Schedule' ) ) {
		$schedule_instance = CTA_Pro_Schedule::get_instance();
		if ( method_exists( $schedule_instance, 'is_cta_within_business_hours' ) ) {
			$is_within_hours = $schedule_instance->is_cta_within_business_hours( $cta );
			if ( $is_within_hours ) {
				$schedule_phase = 'active';
				$schedule_phase_label = __( 'Open Now', 'cta-manager' );
			} else {
				$schedule_phase = 'closed';
				$schedule_phase_label = __( 'Closed', 'cta-manager' );
			}
		}
	}
}

// Clear countdown when CTA is already live — no countdown needed
if ( 'active' === $schedule_phase ) {
	$countdown_target     = null;
	$countdown_label      = '';
	$countdown_done_label = '';
}

// ============================================
// CTA Card
// ============================================ ?>
<?php
$card_classes = 'cta-cta-item cta-card';
if ( $filter_status === 'scheduled' ) {
	$card_classes .= ' cta-card--scheduled';
}
if ( in_array( $filter_status, [ 'trash', 'archived' ], true ) ) {
	$card_classes .= ' is-filter-hidden';
}
?>
<div class="<?php echo esc_attr( $card_classes ); ?>"
	<?php if ( in_array( $filter_status, [ 'trash', 'archived' ], true ) ) : ?>style="display:none;"<?php endif; ?>
	data-cta-type="<?php echo esc_attr( $filter_type ); ?>"
	data-cta-layout="<?php echo esc_attr( $filter_layout ); ?>"
	data-cta-status="<?php echo esc_attr( $filter_status ); ?>"
	data-cta-visibility="<?php echo esc_attr( $filter_visibility ); ?>"
	data-cta-icon="<?php echo esc_attr( $filter_icon ); ?>"
	data-cta-button-animation="<?php echo esc_attr( $filter_button_animation ); ?>"
	data-cta-icon-animation="<?php echo esc_attr( $filter_icon_animation ); ?>"
	data-cta-demo="<?php echo esc_attr( $filter_demo ); ?>"
	data-cta-name="<?php echo esc_attr( $filter_name ); ?>"
	data-cta-search="<?php echo esc_attr( $filter_search ); ?>"
	<?php if ( ! empty( $cta_schedule_start ) ) : ?>
		data-cta-schedule-start="<?php echo esc_attr( $cta_schedule_start ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $cta_schedule_end ) ) : ?>
		data-cta-schedule-end="<?php echo esc_attr( $cta_schedule_end ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $cta['include_times'] ) ) : ?>
		data-cta-include-times="1"
		data-cta-start-hour="<?php echo esc_attr( $cta['schedule_start_hour'] ?? '12' ); ?>"
		data-cta-start-minute="<?php echo esc_attr( $cta['schedule_start_minute'] ?? '00' ); ?>"
		data-cta-start-period="<?php echo esc_attr( $cta['schedule_start_period'] ?? 'AM' ); ?>"
		data-cta-end-hour="<?php echo esc_attr( $cta['schedule_end_hour'] ?? '11' ); ?>"
		data-cta-end-minute="<?php echo esc_attr( $cta['schedule_end_minute'] ?? '59' ); ?>"
		data-cta-end-period="<?php echo esc_attr( $cta['schedule_end_period'] ?? 'PM' ); ?>"
	<?php endif; ?>
>
	<?php
	// ============================================
	// DEMO Badge
	// ============================================
	if ( ! empty( $cta['_demo'] ) ) :
		$variant     = 'demo';
		$text        = __( 'DEMO', 'cta-manager' );
		$cta_list_card_badge = false;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text, $pulse_class );
	endif; 

	// ============================================
	// Schedule Phase Status Badge
	// ============================================
	if ( ! empty( $schedule_phase ) ) :
		switch ( $schedule_phase ) {
			case 'upcoming':
				$variant     = 'info';
				$text        = $schedule_phase_label;
				$pulse_class = 'cta-pulse-info cta-phase-badge';
				break;
			case 'active':
				$variant     = 'success';
				$text        = $schedule_phase_label;
				$pulse_class = 'cta-pulse-success cta-phase-badge';
				break;
			case 'expired':
				$variant = 'danger';
				$text    = $schedule_phase_label;
				$pulse_class = 'cta-phase-badge';
				break;
			case 'closed':
				$variant = 'secondary';
				$text    = $schedule_phase_label;
				$pulse_class = 'cta-phase-badge';
				break;
			default:
				$variant = 'secondary';
				$text    = $schedule_phase_label;
				$pulse_class = 'cta-phase-badge';
		}
		$cta_list_card_badge = false;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text, $pulse_class );
	endif;

	// ============================================
	// Scheduled Status Badge
	// ============================================
	if ( $filter_status === 'scheduled' ) : 
		$variant     = 'scheduled';
		$text        = __( 'Scheduled', 'cta-manager' );
		$cta_list_card_badge = true;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text, $pulse_class );
	
	// ============================================
	// Archived Status Badge
	// ============================================
	elseif ( $filter_status === 'archived' ) : ?>
		<?php
		$variant = 'archived';
		$text    = __( 'Archived', 'cta-manager' );
		$cta_list_card_badge = true;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text );
	
	// ============================================
	// Draft Status Badge
	// ============================================
	elseif ( $filter_status === 'draft' ) : ?>
		<?php
		$variant = 'draft';
		$text    = __( 'Draft', 'cta-manager' );
		$cta_list_card_badge = true;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text );
	
	// ============================================
	// Trashed Status Badge
	// ============================================
	elseif ( $filter_status === 'trash' ) :
		$variant = 'trashed';
		$text    = __( 'Trashed', 'cta-manager' );
		$cta_list_card_badge = true;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text );
	
	// ============================================
	// Active Status Badge
	// ============================================
	else : 
		$variant = $is_cta_enabled ? 'enabled' : 'disabled';
		$text    = $is_cta_enabled ? __( 'Active', 'cta-manager' ) : __( 'Disabled', 'cta-manager' );
		$cta_list_card_badge = true;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
		unset( $variant, $text );
	endif; 

	// ============================================
	// CTA Card Header
	// ============================================ ?>
	<div class="cta-cta-header">

		<?php
		// ============================================
		// CTA Name
		// ============================================ ?>
		<h3 class="cta-cta-name">
			<?php echo esc_html( $cta['name'] ?? '' ); ?>
		</h3>

		<?php
		// ============================================
		// CTA Shortcode
		// ============================================ ?>
		<span class="cta-shortcode-wrapper">
			<code class="cta-manager-shortcode">[cta-manager id="<?php echo esc_attr( $cta['id'] ?? 0 ); ?>"]</code>
			<button type="button" class="cta-copy-shortcode" data-shortcode="[cta-manager id=&quot;<?php echo esc_attr( $cta['id'] ?? 0 ); ?>&quot;]" aria-label="<?php esc_attr_e( 'Copy shortcode', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-clipboard"></span>
			</button>
		</span>

	</div>

	<?php
	// ============================================
	// CTA Card Body
	// ============================================ ?>
	<div class="cta-card-body">

		<?php
		// ============================================
		// CTA Details
		// ============================================ ?>	
		<div class="cta-cta-details">
			<div class="cta-cta-detail">
				<span class="cta-cta-label"><?php esc_html_e( 'Type:', 'cta-manager' ); ?></span>
				<span class="cta-cta-value"><?php echo esc_html( ucfirst( $cta['type'] ?? 'phone' ) ); ?></span>
			</div>
			<div class="cta-cta-detail">
				<span class="cta-cta-label"><?php esc_html_e( 'Layout:', 'cta-manager' ); ?></span>
				<?php
				$layout_value = $cta['layout'] ?? 'button';
				$layout_labels = [
					'button'      => __( 'Button', 'cta-manager' ),
					'card-top'    => __( 'Card Top', 'cta-manager' ),
					'card-left'   => __( 'Card Left', 'cta-manager' ),
					'card-right'  => __( 'Card Right', 'cta-manager' ),
					'card-bottom' => __( 'Card Bottom', 'cta-manager' ),
				];
				$layout_display = $layout_labels[ $layout_value ] ?? ucfirst( str_replace( '-', ' ', $layout_value ) );
				?>
				<span class="cta-cta-value"><?php echo esc_html( $layout_display ); ?></span>
			</div>
			<div class="cta-cta-detail">
				<span class="cta-cta-label"><?php esc_html_e( 'Visibility:', 'cta-manager' ); ?></span>
				<span class="cta-cta-value"><?php echo esc_html( ($cta['visibility'] ?? '') === 'mobile_only' ? __( 'Mobile Only', 'cta-manager' ) : __( 'All Devices', 'cta-manager' ) ); ?></span>
			</div>

		<?php
		// ============================================
		// Countdown Timer for Scheduled CTAs
		// ============================================
		if ( $countdown_target ) : ?>
			<div class="cta-cta-detail cta-scheduled-countdown">
				<span class="dashicons dashicons-clock"></span>
				<span class="cta-countdown-label"><?php echo esc_html( $countdown_label ); ?></span>
				<span class="cta-countdown-time"
					data-timestamp="<?php echo esc_attr( $countdown_target ); ?>"
					data-expired-label="<?php echo esc_attr( $countdown_done_label ); ?>">
					<?php
						$time_diff = human_time_diff( current_time( 'timestamp' ), $countdown_target );
						echo esc_html( $time_diff );
					?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<?php 
		// ============================================
		// CTA Card Actions
		// ============================================ ?>
		<div class="cta-card-actions">
			<?php
			$button_text  = '';
			$icon         = 'visibility';
			$button_class = 'cta-button-link cta-preview-cta-btn cta-card-action';
			$button_type  = 'button';
			$extra_attrs  = 'data-cta-id="' . esc_attr( $cta['id'] ?? 0 ) . '" data-cta-data="' . esc_attr( wp_json_encode( $cta ) ) . '"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-icon.php';
			unset( $button_text, $icon, $button_class, $button_type, $extra_attrs );
			?>
			<?php
			$button_text  = '';
			$icon         = 'edit';
			$button_class = 'cta-button-link cta-card-action cta-edit-trigger';
			$button_type  = 'button';
			$extra_attrs  = 'data-open-modal="#cta-global-form-modal" data-cta-id="' . esc_attr( $cta['id'] ?? 0 ) . '" data-cta-data="' . esc_attr( wp_json_encode( $cta ) ) . '" aria-label="' . esc_attr__( 'Edit', 'cta-manager' ) . '"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-icon.php';
			unset( $button_text, $icon, $button_class, $button_type, $extra_attrs );
			?>
			<form method="post" style="display: inline;" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this CTA?', 'cta-manager' ); ?>');">
				<?php wp_nonce_field( 'cta_cta_action', 'cta_cta_nonce' ); ?>
				<input type="hidden" name="cta_action" value="delete" />
				<input type="hidden" name="cta_id" value="<?php echo esc_attr( $cta['id'] ?? 0 ); ?>" />
				<?php
				$button_text  = '';
				$icon         = 'trash';
				$button_class = 'cta-button-link cta-button-danger cta-card-action';
				$button_type  = 'submit';
				$extra_attrs  = 'aria-label="' . esc_attr__( 'Delete', 'cta-manager' ) . '"';
				include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-icon.php';
				unset( $button_text, $icon, $button_class, $button_type, $extra_attrs );
				?>
			</form>
		</div>

	</div>

	<?php
	// ============================================
	// Footer
	// ============================================ ?>
	<div class="cta-cta-dates">
		<div class="cta-cta-dates-left">
			<div class="cta-cta-date">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Created:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value"><?php echo $created_at ? esc_html( date_i18n( $date_format, strtotime( $created_at ) ) ) : '—'; ?></span>
			</div>
			<div class="cta-cta-date">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Updated:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value"><?php echo $updated_at ? esc_html( date_i18n( $date_format, strtotime( $updated_at ) ) ) : '—'; ?></span>
			</div>
			<div class="cta-cta-date">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Activated:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">
					<?php echo $first_active_at ? esc_html( date_i18n( $date_format, strtotime( $first_active_at ) ) ) : '—'; ?>
				</span>
			</div>
			<div class="cta-cta-date">
				<span class="cta-cta-date-label"><?php esc_html_e( 'By:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value"><?php echo esc_html( $cta['created_by_name'] ?? '—' ); ?></span>
			</div>
			<?php if ( $filter_status === 'scheduled' && ! empty( $cta_schedule_start ) ) : ?>
				<div class="cta-cta-date">
					<span class="cta-cta-date-label"><?php esc_html_e( 'Starts:', 'cta-manager' ); ?></span>
					<span class="cta-cta-date-value"><?php echo esc_html( date_i18n( $date_format, strtotime( $cta_schedule_start ) ) ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $cta_schedule_end ) && in_array( $filter_status, [ 'scheduled', 'published' ], true ) ) : ?>
				<div class="cta-cta-date">
					<span class="cta-cta-date-label"><?php esc_html_e( 'Ends:', 'cta-manager' ); ?></span>
					<span class="cta-cta-date-value"><?php echo esc_html( date_i18n( $date_format, strtotime( $cta_schedule_end ) ) ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>

</div>
