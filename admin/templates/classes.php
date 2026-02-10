<?php
/**
 * Classes admin template.
 *
 * @package School_Management_System
 */

use School_Management_System\Classm;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$class = null;
$is_edit = false;
$action = sanitize_text_field( $_GET['action'] ?? '' );
$class_id = intval( $_GET['id'] ?? 0 );

if ( 'edit' === $action && $class_id ) {
	$class = Classm::get( $class_id );
	if ( ! $class ) {
		wp_die( esc_html__( 'Class not found', 'school-management-system' ) );
	}
	$is_edit = true;
}

$message = '';
if ( isset( $_GET['sms_message'] ) ) {
	$sms_message = sanitize_text_field( $_GET['sms_message'] );
	if ( 'classes_bulk_deleted' === $sms_message ) {
		$count = intval( $_GET['count'] ?? 0 );
		$message = sprintf( __( '%d classes deleted successfully.', 'school-management-system' ), $count );
	}
}

$total_classes = Classm::count();
$active_classes = Classm::count( array( 'status' => 'active' ) );
$inactive_classes = Classm::count( array( 'status' => 'inactive' ) );

?>
<style>
 .sms-classes-page { max-width: 100%; }
 .sms-classes-header {
 	display: flex;
 	justify-content: space-between;
 	align-items: flex-start;
 	gap: 16px;
 	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
 	color: #fff;
 	padding: 22px;
 	border-radius: 16px;
 	box-shadow: 0 10px 30px rgba(102, 126, 234, 0.22);
 	margin: 10px 0 18px;
 }
 .sms-classes-title h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }
 .sms-classes-subtitle { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }
 .sms-classes-header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
 .sms-cta-btn {
 	background: rgba(255,255,255,0.16);
 	border: 1px solid rgba(255,255,255,0.26);
 	color: #fff;
 	padding: 10px 14px;
 	border-radius: 10px;
 	font-weight: 700;
 	text-decoration: none;
 	display: inline-flex;
 	align-items: center;
 	gap: 8px;
 	cursor: pointer;
 }
 .sms-cta-btn:hover { background: rgba(255,255,255,0.24); color: #fff; }

 .sms-class-stats {
 	display: grid;
 	grid-template-columns: repeat(3, minmax(0, 1fr));
 	gap: 16px;
 	margin-bottom: 18px;
 }
 .sms-class-stat {
 	background: #fff;
 	border-radius: 16px;
 	padding: 18px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.08);
 	border: 1px solid #eef1f5;
 	display: flex;
 	align-items: center;
 	gap: 14px;
 }
 .sms-class-stat-icon {
 	width: 44px;
 	height: 44px;
 	border-radius: 12px;
 	display: flex;
 	align-items: center;
 	justify-content: center;
 	color: #fff;
 }
 .sms-class-stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #a8b8f8 100%); }
 .sms-class-stat-icon.active { background: linear-gradient(135deg, #00d2d3 0%, #54a0ff 100%); }
 .sms-class-stat-icon.inactive { background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%); }
 .sms-class-stat-icon .dashicons { font-size: 20px; width: 20px; height: 20px; }
 .sms-class-stat-number { font-size: 20px; font-weight: 800; color: #2c3e50; line-height: 1.1; }
 .sms-class-stat-label { font-size: 12px; color: #6c757d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }

 .sms-panel {
 	background: #fff;
 	border: 1px solid #e9ecef;
 	border-radius: 16px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.06);
 	overflow: hidden;
 	margin-bottom: 18px;
 }
 .sms-panel-header {
 	background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
 	color: #fff;
 	padding: 14px 18px;
 	display: flex;
 	justify-content: space-between;
 	align-items: center;
 	gap: 12px;
 }
 .sms-panel-header h2 { margin: 0; font-size: 15px; font-weight: 800; color: #fff; }
 .sms-panel-body { padding: 18px; }

 .sms-classes-search {
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	justify-content: flex-end;
 	margin: 0;
 }
 .sms-classes-search input[type="search"] { min-width: 260px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 10px; }
 .sms-search-btn {
 	background: #2c3e50;
 	color: #fff;
 	border: none;
 	padding: 10px 20px;
 	border-radius: 8px;
 	font-weight: 600;
 	cursor: pointer;
 	transition: all 0.2s;
 	box-shadow: 0 2px 5px rgba(0,0,0,0.1);
 }
 .sms-search-btn:hover {
 	background: #1a252f;
 	transform: translateY(-1px);
 	box-shadow: 0 4px 8px rgba(0,0,0,0.15);
 }
 .sms-reset-btn {
 	background: #fff;
 	color: #e74c3c;
 	border: 1px solid #e74c3c;
 	padding: 9px 19px;
 	border-radius: 8px;
 	font-weight: 600;
 	text-decoration: none;
 	transition: all 0.2s;
 }
 .sms-reset-btn:hover {
 	background: #e74c3c;
 	color: #fff;
 }
 .sms-classes-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

 .sms-status-pill {
 	display: inline-flex;
 	align-items: center;
 	gap: 6px;
 	padding: 6px 10px;
 	border-radius: 999px;
 	font-size: 11px;
 	font-weight: 800;
 	text-transform: uppercase;
 	letter-spacing: 0.4px;
 	border: 1px solid;
 }
 .sms-status-pill.active { background: rgba(40, 167, 69, 0.12); color: #155724; border-color: rgba(40, 167, 69, 0.28); }
 .sms-status-pill.inactive { background: rgba(220, 53, 69, 0.12); color: #721c24; border-color: rgba(220, 53, 69, 0.28); }

 .sms-row-actions { display: inline-flex; gap: 8px; flex-wrap: wrap; }
 .sms-row-action-btn {
 	background: #fff;
 	border: 1px solid #dee2e6;
 	border-radius: 10px;
 	padding: 7px 10px;
 	text-decoration: none;
 	font-weight: 700;
 	font-size: 12px;
 	color: #2c3e50;
 	display: inline-flex;
 	align-items: center;
 	gap: 6px;
 }
 .sms-row-action-btn:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }
 .sms-row-action-btn.edit { border-color: rgba(102, 126, 234, 0.35); color: #4b5bdc; }
 .sms-row-action-btn.delete { border-color: rgba(220, 53, 69, 0.35); color: #dc3545; }

 @media (max-width: 782px) {
 	.sms-classes-header { flex-direction: column; align-items: flex-start; }
 	.sms-classes-header-actions { width: 100%; justify-content: flex-start; }
 	.sms-class-stats { grid-template-columns: 1fr; }
 	.sms-classes-search { justify-content: flex-start; }
 	.sms-classes-search input[type="search"] { min-width: 0; width: 100%; }
 }
</style>
<div class="wrap">
	<div class="sms-classes-page">
		<div class="sms-classes-header">
			<div class="sms-classes-title">
				<h1><?php esc_html_e( 'Classes', 'school-management-system' ); ?></h1>
				<div class="sms-classes-subtitle"><?php esc_html_e( 'Manage class sections, codes, capacity, and enrollment.', 'school-management-system' ); ?></div>
			</div>
			<div class="sms-classes-header-actions">
				<a class="sms-cta-btn" href="#sms-class-form">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php echo $is_edit ? esc_html__( 'Edit Mode', 'school-management-system' ) : esc_html__( 'Add Class', 'school-management-system' ); ?>
				</a>
				<?php if ( $is_edit ) : ?>
					<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-classes' ) ); ?>">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

		<div class="sms-class-stats">
			<div class="sms-class-stat">
				<div class="sms-class-stat-icon total"><span class="dashicons dashicons-welcome-learn-more"></span></div>
				<div>
					<div class="sms-class-stat-number"><?php echo intval( $total_classes ); ?></div>
					<div class="sms-class-stat-label"><?php esc_html_e( 'Total Classes', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-class-stat">
				<div class="sms-class-stat-icon active"><span class="dashicons dashicons-yes-alt"></span></div>
				<div>
					<div class="sms-class-stat-number"><?php echo intval( $active_classes ); ?></div>
					<div class="sms-class-stat-label"><?php esc_html_e( 'Active', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-class-stat">
				<div class="sms-class-stat-icon inactive"><span class="dashicons dashicons-minus"></span></div>
				<div>
					<div class="sms-class-stat-number"><?php echo intval( $inactive_classes ); ?></div>
					<div class="sms-class-stat-label"><?php esc_html_e( 'Inactive', 'school-management-system' ); ?></div>
				</div>
			</div>
		</div>

	<!-- Add/Edit Form -->
		<div class="sms-panel" id="sms-class-form">
			<div class="sms-panel-header">
				<h2><?php echo $is_edit ? esc_html__( 'Edit Class', 'school-management-system' ) : esc_html__( 'Add New Class', 'school-management-system' ); ?></h2>
			</div>
			<div class="sms-panel-body">

		<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="class_name"><?php esc_html_e( 'Class Name *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="class_name" id="class_name" required value="<?php echo $class ? esc_attr( $class->class_name ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="class_code"><?php esc_html_e( 'Class Code *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="class_code" id="class_code" required value="<?php echo $class ? esc_attr( $class->class_code ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="capacity"><?php esc_html_e( 'Capacity', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="number" name="capacity" id="capacity" value="<?php echo $class ? intval( $class->capacity ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Status', 'school-management-system' ); ?></label>
					</th>
					<td>
						<select name="status" id="status">
							<option value="active" <?php echo ! $class || 'active' === $class->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Active', 'school-management-system' ); ?>
							</option>
							<option value="inactive" <?php echo $class && 'inactive' === $class->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Inactive', 'school-management-system' ); ?>
							</option>
						</select>
					</td>
				</tr>
			</table>

			<?php if ( $is_edit ) : ?>
				<input type="hidden" name="class_id" value="<?php echo intval( $class->id ); ?>" />
				<button type="submit" name="sms_edit_class" class="button button-primary">
					<?php esc_html_e( 'Update Class', 'school-management-system' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-classes' ) ); ?>" class="button">
					<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
				</a>
			<?php else : ?>
				<button type="submit" name="sms_add_class" class="button button-primary">
					<?php esc_html_e( 'Add Class', 'school-management-system' ); ?>
				</button>
			<?php endif; ?>
		</form>
			</div>
		</div>

	<!-- Classes List -->
		<div class="sms-panel">
			<div class="sms-panel-header">
				<h2><?php esc_html_e( 'Classes List', 'school-management-system' ); ?></h2>
				<form method="get" action="" class="sms-classes-search">
					<input type="hidden" name="page" value="sms-classes" />
					<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search classes...', 'school-management-system' ); ?>" />
					<button type="submit" class="sms-search-btn"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
					<?php if ( ! empty( $_GET['s'] ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-classes' ) ); ?>" class="sms-reset-btn"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
					<?php endif; ?>
				</form>
			</div>
			<div class="sms-panel-body">

	<form method="post" action="">
	<?php wp_nonce_field( 'sms_bulk_delete_classes_nonce', 'sms_bulk_delete_classes_nonce' ); ?>
	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<select name="action">
				<option value="-1"><?php esc_html_e( 'Bulk Actions', 'school-management-system' ); ?></option>
				<option value="bulk_delete_classes"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></option>
			</select>
			<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'school-management-system' ); ?>">
		</div>
	</div>
	<div class="sms-classes-table-wrap">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-classes" type="checkbox"></td>
				<th><?php esc_html_e( 'ID', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Class Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Class Code', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Capacity', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			if ( ! empty( $search_term ) ) {
				$classes = Classm::search( $search_term );
			} else {
				$classes = Classm::get_all( array(), 50 );
			}
			if ( ! empty( $classes ) ) {
				foreach ( $classes as $class ) {
					?>
					<tr>
						<th scope="row" class="check-column"><input type="checkbox" name="class_ids[]" value="<?php echo intval( $class->id ); ?>"></th>
						<td><?php echo intval( $class->id ); ?></td>
						<td><?php echo esc_html( $class->class_name ); ?></td>
						<td><?php echo esc_html( $class->class_code ); ?></td>
						<td><?php echo intval( $class->capacity ); ?></td>
						<td>
							<span class="sms-status-pill <?php echo 'inactive' === $class->status ? 'inactive' : 'active'; ?>">
								<?php echo esc_html( $class->status ); ?>
							</span>
						</td>
						<td>
							<div class="sms-row-actions">
								<a class="sms-row-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-classes&action=edit&id=' . $class->id ) ); ?>">
									<span class="dashicons dashicons-edit"></span>
									<?php esc_html_e( 'Edit', 'school-management-system' ); ?>
								</a>
							</div>
						</td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No classes found', 'school-management-system' ); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	</div>
	</form>
			</div>
		</div>
	</div>
</div>
