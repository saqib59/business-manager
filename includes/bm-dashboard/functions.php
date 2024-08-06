<?php

if ( ! function_exists( 'bm_dashboard_field_html' ) ) {
	function bm_dashboard_field_html() {
		switch ( bm_dashboard_get_the_type() ) {
			case 'tab':
				bm_dashboard_tab_html();
				break;
			case 'taxonomy':
				do_action( 'bm_dashboard_before_box_field' );
				bm_dashboard_taxonomy_box_header_html( bm_dashboard_get_the_key() );
				do_action( 'bm_dashboard_after_box_field' );
				break;
			case 'meta':
				do_action( 'bm_dashboard_before_box_field' );
				bm_dashboard_meta_box_header_html( bm_dashboard_get_the_key() );
				do_action( 'bm_dashboard_after_box_field' );
				break;
			case 'post_table':
				do_action( 'bm_dashboard_before_post_table_field' );
				bm_dashboard_post_table_html( bm_dashboard_get_the_key() );
				do_action( 'bm_dashboard_after_post_table_field' );
				break;
		}
	}
}

if ( ! function_exists( 'BM_Dashboard_List_table' ) ) {
	function bm_dashboard_post_table_html() {
		$db = new BM_Dashboard_List_table();
		$db->prepare_items(
			'bm-job',
			array(
				'cb'         => '<input type="checkbox" name="id[]" />',
				'post_title' => 'Title',
			),
			array( 'post_title' => array( 'Title', false ) )
		);
		$db->display();
	}
}

if ( ! function_exists( 'bm_dashboard_tab_html' ) ) {
	function bm_dashboard_tab_html() {
		$cur_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : null;
		// if tab parameter is not there, we may be on this page via taxonomy parameter
		if ( ! $cur_tab ) {
			$cur_tab = isset( $_GET['taxonomy'] ) ? sanitize_text_field( $_GET['taxonomy'] ) : null;
		}
		// if taxonomy parameter is not there, we may be on this page via post_type
		if ( ! $cur_tab ) {
			$cur_tab = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null;
		}
		?>
	<nav class="nav-tab-wrapper">
			<?php
			foreach ( bm_dashboard_get_tab_fields() as $tab ) {
				// support arrays of keys
				if ( is_array( $tab['key'] ) ) {
					$is_current_tab = in_array( $cur_tab, $tab['key'] );
				} else {
					$is_current_tab = $cur_tab == $tab['key'];
				}

				?>
				<a href="<?php echo esc_url( $tab['link'] ); ?>" class="nav-tab 
									<?php
									if ( $is_current_tab ) :
										?>
					nav-tab-active<?php endif; ?>"><?php echo esc_html( $tab['title'] ); ?></a>
				<?php
			}
			?>
		</nav>
		<p></p>
		<div class="tab-content">
			<?php

			foreach ( bm_dashboard_get_tab_fields() as $tab ) {
				// support arrays of keys
				if ( is_array( $tab['key'] ) ) {
					$is_current_tab = in_array( $cur_tab, $tab['key'] );
				} else {
					$is_current_tab = $cur_tab == $tab['key'];
				}

				if ( $is_current_tab ) {
					bm_dashboard_set_the_field( $tab );
					foreach ( bm_dashboard_get_tab_fields() as $field ) {
						bm_dashboard_set_the_field( $field );
						bm_dashboard_field_html();
					}
					break;
				}
			}
			?>
		</div>
		<?php
	}
}


if ( ! function_exists( 'bm_dashboard_before_field_loop_html' ) ) {
	function bm_dashboard_before_field_loop_html( $key ) {
		?>
		<div id="dashboard-widgets-wrap">
			<div id="dashboard-widgets">
		<?php
	}
}
if ( ! function_exists( 'bm_dashboard_before_field_html' ) ) {
	function bm_dashboard_before_field_html( $key ) {
		?>
		<div class="postbox-container" id="postbox-container-1">
				<div class="meta-box-sortables">
					<div class="postbox">
						<div class="inside">
		<?php
	}
}
if ( ! function_exists( 'bm_dashboard_after_field_html' ) ) {
	function bm_dashboard_after_field_html( $key ) {
		?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
if ( ! function_exists( 'bm_dashboard_after_field_loop_html' ) ) {
	function bm_dashboard_after_field_loop_html( $key ) {
		?>
			</div><!--<div id="dashboard-widgets-wrap">-->
		</div><!--<div id="dashboard-widgets">-->
		<?php
	}
}


if ( ! function_exists( 'bm_dashboard_taxonomy_box_header_html' ) ) {

	function bm_dashboard_taxonomy_box_header_html( $key ) {
		$taxonomy = get_taxonomy( $key );
		$args     = array(
			'taxonomy'   => $key,
			'hide_empty' => false,
		);
		$terms    = count( get_terms( $args ) );
		?>
		<a class="button button-secondary" href="<?php echo admin_url( bm_dashboard_meta_box_link() ); ?>"><?php _e( $taxonomy->labels->view_items, 'business-manager-recruiting' ); ?></a>
		<h2><?php printf( _n( '%s' . $taxonomy->labels->singular_name, '%s' . $taxonomy->labels->name, $terms, 'business-manager-recruiting' ), '<span>' . $terms . '</span> ' ); ?></h2>
		<?php
	}
}

if ( ! function_exists( 'bm_dashboard_meta_box_header_html' ) ) {
	function bm_dashboard_meta_box_header_html( $key ) {
		$query = business_manager_generate_item_count_meta_query( $key );
		$args  = array(
			'numberposts' => -1,
			'post_status' => 'publish',
			'post_type'   => $key,
			'meta_query'  => array(
				$query,
			),
		);
		$posts = count( get_posts( $args ) );
		$obj   = get_post_type_object( $key );
		?>
		<a class="button button-secondary" href="<?php echo admin_url( bm_dashboard_meta_box_link() ); ?>"><?php _e( $obj->labels->view_items, 'business-manager-recruiting' ); ?></a>
		<h2><?php printf( _n( '%s' . $obj->labels->singular_name, '%s' . $obj->labels->name, $posts, 'business-manager' ), '<span>' . $posts . '</span> ' ); ?></h2>
		<?php
	}
}

if ( ! function_exists( 'bm_dashboard_set_the_field' ) ) {
	function bm_dashboard_set_the_field( $box ) {
		$GLOBALS['bm_dashboard_box'] = (object) $box;
	}
}

if ( ! function_exists( 'bm_dashboard_get_the_field' ) ) {
	function bm_dashboard_get_the_field() {
		return $GLOBALS['bm_dashboard_box'];
	}
}

if ( ! function_exists( 'bm_dashboard_get_tab_fields' ) ) {
	function bm_dashboard_get_tab_fields() {
		if ( ! isset( bm_dashboard_get_the_field()->fields ) ) {
			return array();
		}
		return bm_dashboard_get_the_field()->fields;
	}
}

if ( ! function_exists( 'bm_dashboard_meta_box_link' ) ) {
	function bm_dashboard_meta_box_link() {
		return bm_dashboard_get_the_field()->link;
	}
}

if ( ! function_exists( 'bm_dashboard_get_the_type' ) ) {
	function bm_dashboard_get_the_type() {
		return bm_dashboard_get_the_field()->type;
	}
}


if ( ! function_exists( 'bm_dashboard_get_the_key' ) ) {
	function bm_dashboard_get_the_key() {
		return bm_dashboard_get_the_field()->key;
	}
}

if ( ! function_exists( 'bm_dashboard_get_the_classes' ) ) {
	function bm_dashboard_get_the_classes() {
		return bm_dashboard_get_the_field()->classes;
	}
}
