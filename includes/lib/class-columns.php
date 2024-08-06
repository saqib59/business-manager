<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BM_columns' ) ) {
	/**
	 * This class is heavily, heavily based on CPT_Columns by Ohad Raz.
	 */
	class BM_columns {

		public $cpt_columns          = array();
		public $cpt_remove_columns   = array();
		public $cpt_sortable_columns = array();
		public $cpt_name             = '';
		public $replace              = false;
		public $filters              = array();
		public $access               = array();
		/**
		 * @var array
		 * Contains all the columns which should be included in search
		 */
		public $cpt_meta_columns = array();

		public function __construct( $cpt = '', $replace = false ) {
			$this->cpt_name = $cpt;
			$this->replace  = $replace;

			// Add columns
			add_filter( "manage_{$cpt}_posts_columns", array( $this, '_cpt_columns' ), 50 );

			// Remove columns
			add_filter( "manage_{$cpt}_posts_columns", array( $this, '_cpt_columns_remove' ), 60 );

			// Display columns
			add_action( "manage_{$cpt}_posts_custom_column", array( $this, '_cpt_custom_column' ), 50, 2 );

			// Sortable columns
			add_filter( "manage_edit-{$cpt}_sortable_columns", array( $this, '_sortable_columns' ), 50 );

			// Sort order
			add_filter( 'pre_get_posts', array( $this, '_column_orderby' ), 50 );

			// Filters
			add_action( 'restrict_manage_posts', array( $this, 'filter_dropdown' ) );
			add_filter( 'pre_get_posts', array( $this, 'posts_filter' ) );

			add_action( 'posts_search', array( $this, 'extend_search' ), 10, 2 );
		}

		/**
		 * @param $search
		 * @param $wp_query
		 * @return string
		 * This code is taken from core wp_query class and is modified to add custom field search.
		 * Must be updated if WordPress updates
		 */
		function extend_search( $search, $wp_query ) {
			if ( ! $wp_query->is_search || $wp_query->get( 'post_type' ) != $this->cpt_name ) {
				return $search;
			}

			global $wpdb;
			$q      = $wp_query->query_vars;
			$search = '';
			// Added slashes screw with quote grouping when done early, so done later.
			$q['s'] = stripslashes( $q['s'] );
			if ( empty( $_GET['s'] ) && $wp_query->is_main_query() ) {
				$q['s'] = urldecode( $q['s'] );
			}
			// There are no line breaks in <input /> fields.
			$q['s']                  = str_replace( array( "\r", "\n" ), '', $q['s'] );
			$q['search_terms_count'] = 1;
			if ( ! empty( $q['sentence'] ) ) {
				$q['search_terms'] = array( $q['s'] );
			} elseif ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches ) ) {
					$q['search_terms_count'] = count( $matches[0] );
					$q['search_terms']       = $wp_query->parse_search_terms( $matches[0] );
					// If the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
				if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 ) {
					$q['search_terms'] = array( $q['s'] );
				}
			} else {
				$q['search_terms'] = array( $q['s'] );
			}

			$n                         = ! empty( $q['exact'] ) ? '' : '%';
			$searchand                 = '';
			$q['search_orderby_title'] = array();

			/**
			 * Filters the prefix that indicates that a search term should be excluded from results.
			 *
			 * @since 4.7.0
			 *
			 * @param string $exclusion_prefix The prefix. Default '-'. Returning
			 *                                 an empty value disables exclusions.
			 */
			$exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );

			foreach ( $q['search_terms'] as $term ) {
				// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
				$exclude = $exclusion_prefix && ( substr( $term, 0, 1 ) === $exclusion_prefix );
				if ( $exclude ) {
					$like_op  = 'NOT LIKE';
					$andor_op = 'AND';
					$term     = substr( $term, 1 );
				} else {
					$like_op  = 'LIKE';
					$andor_op = 'OR';
				}

				if ( $n && ! $exclude ) {
					$like                        = '%' . $wpdb->esc_like( $term ) . '%';
					$q['search_orderby_title'][] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
				}

				$like = $n . $wpdb->esc_like( $term ) . $n;

				$meta_search = '';
				foreach ( $this->cpt_meta_columns as $meta_column ) {
					$meta_search .= $wpdb->prepare( " OR EXISTS (SELECT * FROM $wpdb->postmeta WHERE meta_key = '{$meta_column}' AND meta_value $like_op %s AND {$wpdb->postmeta}.post_id={$wpdb->posts}.ID )", $like );
				}

				$search   .= $wpdb->prepare( "{$searchand}(({$wpdb->posts}.post_title $like_op %s) $andor_op ({$wpdb->posts}.post_excerpt $like_op %s) $andor_op ({$wpdb->posts}.post_content $like_op %s) $meta_search )", $like, $like, $like );
				$searchand = ' AND ';
			}

			if ( ! empty( $search ) ) {
				$search = " AND ({$search}) ";
				if ( ! is_user_logged_in() ) {
					$search .= " AND ({$wpdb->posts}.post_password = '') ";
				}
			}
			return $search;
		}

		public function _cpt_columns( $defaults ) {
			global $typenow;

			if ( $this->cpt_name == $typenow ) {
				$tmp = array();

				foreach ( $this->cpt_columns as $key => $args ) {
					$tmp[ $key ] = $args['label'];
				}

				if ( $this->replace ) {
					return $tmp;
				} else {
					$defaults = array_merge( $defaults, $tmp );
				}
			}

			return $defaults;
		}

		public function _cpt_columns_remove( $columns ) {
			foreach ( $this->cpt_remove_columns as $key ) {
				if ( isset( $columns[ $key ] ) ) {
					unset( $columns[ $key ] );
				}
			}

			return $columns;
		}

		public function _sortable_columns( $columns ) {
			global $typenow;

			if ( $this->cpt_name == $typenow ) {
				foreach ( $this->cpt_sortable_columns as $key => $args ) {
					$columns[ $key ] = $key;
				}
			}

			return $columns;
		}

		/**
		 * _cpt_custom_column.
		 *
		 * calls do_column() when the column is set
		 */
		public function _cpt_custom_column( $column_name, $post_id ) {
			if ( isset( $this->cpt_columns[ $column_name ] ) ) {
				$this->do_column( $post_id, $this->cpt_columns[ $column_name ], $column_name );
			}
		}

		/**
		 * do_column.
		 *
		 * used to display the column
		 */
		public function do_column( $post_id, $column, $column_name ) {
			if ( in_array( $column['type'], array( 'text', 'thumb', 'post_meta', 'custom_tax' ) ) ) {
				echo isset( $column['prefix'] ) ? $column['prefix'] : '';
				$default = isset( $column['def'] ) ? $column['def'] : '';
			}

			switch ( $column['type'] ) {
				case 'text':
					echo apply_filters( 'cpt_columns_text_' . $column_name, $column['text'], $post_id, $column, $column_name );
					break;

				case 'native':
					if ( isset( $column['key'] ) ) {
						switch ( $column['key'] ) {
							case 'title':
									get_the_title( $post_id );
								break;
							case 'date':
								echo business_manager_date_format( get_the_date( '', $post_id ) );
								break;
						}
					}
					break;

				case 'button':
					echo '<a target="new" class="button" href="' . esc_url( $column['link'] ) . '">' . $column['text'] . '</a>';
					break;

				case 'thumb':
					if ( has_post_thumbnail( $post_id ) ) {
						the_post_thumbnail( $column['size'] );
					} else {
						echo 'N/A';
					}
					break;

				case 'linked':
					if ( $column['format'] === 'leave' ) {
						business_manager_upcoming_leave_html_column();
					}
					break;

				case 'id':
					echo get_the_ID();
					break;
				case 'callback':
					if ( $column['callback'] ) {
						$func = $column['callback'];
						echo $func( $column );
					}
					break;
				case 'post_meta':
					$val = get_post_meta( $post_id, $column['meta_key'], true );
					if ( isset( $column['format'] ) ) {
						switch ( $column['format'] ) {

							case 'date':
								$val = business_manager_date_format( $val );
								break;

							case 'price':
								$val = business_manager_money_format( $val );
								break;

							case 'user':
								$val = business_manager_employee_full_name( $val );
								break;

							case 'post':
								$val = ( $val != 0 ? get_the_title( $val ) : '' );
								break;

							case 'image':
								if ( is_array( $val ) ) {
									$val = '<img src="' . esc_attr( array_values( $val )[0] ) . '">';
								} elseif ( ! is_array( $val ) ) {
									$val = '<img src="' . esc_attr( $val ) . '">';
								}
								break;

							case 'image-wrapped':
								// Add a div wrapper so we can style
								if ( is_array( $val ) ) {
									$val = '<div style="width:100%; height: 50px;"><img style="width:auto; height:50px;" src="' . esc_attr( array_values( $val )[0] ) . '" /></div>';
								} elseif ( ! is_array( $val ) ) {
									$val = '<div style="width:100%; height: 50px;"><img style="width:auto; height:50px;" src="' . esc_attr( $val ) . '" /></div>';
								}
								break;

							case 'address':
								$val = $this->address( 'post_type', $post_id, $column );
								break;

							case 'country':
								$val = business_manager_get_country( $val );
								break;

							case 'person':
								$val = $this->person( 'post_type', $post_id, $column );
								break;

							case 'person_meta':
								$persons = $val;
								$val     = '';
								if ( is_array( $persons ) ) {
									foreach ( $persons as $person ) {
										$val .= $this->person(
											'meta_value',
											$person,
											array_merge(
												$column,
												array(
													'post_type' => $this->cpt_name,
													'column' => $column_name,
												)
											)
										);
									}
								} else {
									$val .= $this->person(
										'meta_value',
										$persons,
										array_merge(
											$column,
											array(
												'post_type' => $this->cpt_name,
												'column' => $column_name,
											)
										)
									);
								}

								break;

							case 'client':
								$val = $this->client( 'post_type', $post_id, $column );
								break;

							case 'client_meta':
								$val = $this->client(
									'meta_value',
									$val,
									array_merge(
										$column,
										array(
											'post_type' => $this->cpt_name,
											'column'    => $column_name,
										)
									)
								);
								break;

							case 'count':
								if ( is_array( $val ) ) {
									$val = count( $val );
								} else {
									$val = __( 'No', 'business-manager' );
								}
								break;

							case 'link':
								if ( isset( $column['text'] ) && $val ) {
									$val = '<a target="new" href="' . $val . '">' . $column['text'] . '</a>';
								} else {
									$val = '<a target="new" href="' . $val . '">' . $val . '</a>';
								}
								break;

							case 'email':
								$val = '<a href="mailto:' . $val . '">' . $val . '</a>';
								break;

							case 'doc_version':
								$val = $val['version'];
								break;

							case 'doc_date':
								$val = $val['date'];
								break;

							case 'doc_employee_name':
								$val = $this->person(
									'meta_value',
									$val['employee_id'],
									array_merge(
										$column,
										array(
											'post_type' => $this->cpt_name,
											'column'    => $column_name,
										)
									)
								);
								break;

							case 'doc_file':
								$file = pathinfo( $val['file'] );
								$val  = $file['basename'];
								break;

							case 'doc_download':
								$val = esc_attr( $val['file'] ) ? '<a class="button button-secondary button-small" href="' . esc_attr( $val['file'] ) . '" target="_blank" download>' . __( 'Download', 'business-manager' ) . '</a>' : '';
								break;

							case 'term':
								if ( $val ) {
									$val = get_term( $val )->name;
								}
								break;

							case 'pipeline_stages':
								$stages = '';
								foreach ( $val as $term_array ) {
									$get_term = get_term( $term_array['_bm_pipeline_stage_type'] );
									if ( $get_term ) {
										$stages .= $get_term->name . '<br />';
									}
								}
								$val = $stages;
								break;
						}

						$val = apply_filters( 'business_manager_columns_type_post_meta', $val, $column );
					}

					if ( isset( $column['title_link'] ) && $column['title_link'] === true ) {
						$val = '<a class="row-title" href="' . get_edit_post_link( $post_id ) . '">' . $val . '</a>';
					}

					if ( get_post_type_object( $val ) ) {
						$val = get_post_type_object( $val )->labels->singular_name;
					}

					if ( '_bm_custom_field_metabox' === $column['meta_key'] ) {
						$post_type = get_post_meta( $post_id, '_bm_custom_field_post_type', true );
						$metabox   = get_post_meta( $post_id, "_bm_custom_field_{$post_type}_metabox", true );
						if ( isset( $post_type, $metabox ) && function_exists( 'business_manager_cpt_metabox_ids' ) ) {
							$val = business_manager_cpt_metabox_ids( $post_type )[ $metabox ] ?? '';
						}
					}
					
					echo ( ! empty( $val ) ) ? $val : $default;

					break;

				case 'custom_tax':
					$post_type = get_post_type( $post_id );
					$terms     = get_the_terms( $post_id, $column['taxonomy'] );

					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							$href         = "edit.php?post_type={$post_type}&{$column['taxonomy']}={$term->slug}";
							$name         = esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $column['taxonomy'], 'edit' ) );
							$post_terms[] = "<a href='{$href}'>{$name}</a>";
						}

						echo join( ', ', $post_terms );
					} else {
						echo '';
					}

					break;
			}

			if ( in_array( $column['type'], array( 'text', 'thumb', 'post_meta', 'custom_tax' ) ) ) {
				echo isset( $column['suffix'] ) ? $column['suffix'] : '';
			}
		}

		public function _column_orderby( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby' );
			$keys    = array_keys( (array) $this->cpt_sortable_columns );

			if ( in_array( $orderby, $keys ) ) {
				// order by meta
				if ( 'post_meta' == $this->cpt_sortable_columns[ $orderby ]['type'] ) {
					if ( ! empty( $this->cpt_sortable_columns[ $orderby ]['meta_key'] ) ) {
						$query->set( 'meta_key', $orderby );
						$query->set( 'orderby', $this->cpt_sortable_columns[ $orderby ]['orderby'] );
					} else {
						$query->set( 'orderby', 'title' );
					}
				}
			}
		}

		public function add_meta_search_column( $key ) {
			$this->cpt_meta_columns[] = $key;
		}

		public function add_column( $key, $args ) {
			$def = array(
				'label'    => 'column label',
				'size'     => array( '80', '80' ),
				'taxonomy' => '',
				'meta_key' => '',
				'sortable' => false,
				'text'     => '',
				'type'     => 'native', // 'native', 'post_meta', 'custom_tax', 'text'
				'orderby'  => 'meta_value',
				'prefix'   => '',
				'suffix'   => '',
				'std'      => '',
			);

			$this->cpt_columns[ $key ] = array_merge( $def, $args );

			if ( $this->cpt_columns[ $key ]['sortable'] ) {
				$this->cpt_sortable_columns[ $key ] = $this->cpt_columns[ $key ];
			}
		}

		public function remove_column( $key ) {
			$this->cpt_remove_columns[] = $key;
		}

		public function remove_filter( $filter ) {
			$type = $this->cpt_name;

			if ( isset( $_GET['post_type'] ) ) {
				$type = sanitize_text_field( $_GET['post_type'] );
			}

			// add filter to the post type you want
			if ( $this->cpt_name == $type ) {
				if ( $filter == 'date' ) {
					add_filter( 'months_dropdown_results', '__return_empty_array' );
				}
			}
		}

		public function add_filter( $key, $args ) {
			$def = array(
				'label'    => 'column label',
				'size'     => array( '80', '80' ),
				'taxonomy' => '',
				'meta_key' => '',
				'sortable' => false,
				'text'     => '',
				'type'     => 'native', // 'native', 'post_meta', 'custom_tax', 'text'
				'orderby'  => 'meta_value',
				'prefix'   => '',
				'suffix'   => '',
				'std'      => '',
				'format'   => '',
			);

			$this->filters[ $key ] = $args;
		}

		public function filter_dropdown() {
			global $wpdb;

			$employee_id = business_manager_employee_id( get_current_user_id() );

			$type = $this->cpt_name;

			if ( isset( $_GET['post_type'] ) ) {
				$type = sanitize_text_field( $_GET['post_type'] );
			}

			if ( $this->cpt_name == $type ) {
				foreach ( $this->filters as $key => $args ) {
					$filter_by_access_field = false;

					// Handle employee permissions and restrictions
					if ( isset( $args['access'] ) && current_user_can( 'bm_employee' ) ) {
						$bm_access = business_manager_employee_access( get_current_user_id(), $args['access']['type'] );

						if ( $bm_access == 'limited' ) {
							if ( $args['access']['field'] == $key ) {
								continue;
							} else {
								$filter_by_access_field = true;
							}
						}
					}

					$format = ( isset( $args['format'] ) ? $args['format'] : '' );
					$values = array();

					switch ( $format ) {
						case 'clients':
							$sql_filter_options = $wpdb->prepare(
								'SELECT DISTINCT(meta_value) AS meta_value, post_title AS label
                                                                    FROM ' . $wpdb->prefix . 'postmeta 
                                                                    LEFT JOIN ' . $wpdb->prefix . 'posts ON ' . $wpdb->prefix . 'posts.ID = ' . $wpdb->prefix . 'postmeta.meta_value AND ' . $wpdb->prefix . "posts.post_status = 'publish' 
                                                                    WHERE meta_key = %s 
                                                                    ORDER BY post_title ASC",
								$key
							);
							$filter_options     = $wpdb->get_results( $sql_filter_options );

							break;

						case 'dates':
							$sql_filter_options = $wpdb->prepare(
								'SELECT DISTINCT(' . $wpdb->prefix . 'postmeta.meta_value) AS meta_value, ' . $wpdb->prefix . 'postmeta.meta_value AS label 
                                                                    FROM ' . $wpdb->prefix . 'postmeta 
                                                                    ' . ( $filter_by_access_field ? 'LEFT JOIN ' . $wpdb->prefix . 'postmeta AS wp_postmeta_access_filter ON wp_postmeta_access_filter.post_id = ' . $wpdb->prefix . 'postmeta.post_id ' : '' ) . '
                                                                    WHERE ' . $wpdb->prefix . 'postmeta.meta_key = %s 
                                                                    ' . ( $filter_by_access_field ? "AND (wp_postmeta_access_filter.meta_key = '" . $args['access']['field'] . "' AND wp_postmeta_access_filter.meta_value = " . $employee_id . ' ) ' : '' ) . '
                                                                    ORDER BY ' . $wpdb->prefix . 'postmeta.meta_value ASC',
								$key
							);
							$filter_options     = $wpdb->get_results( $sql_filter_options );

							break;

						case 'employees':
							$sql_filter_options = $wpdb->prepare(
								'SELECT DISTINCT(meta_value) AS meta_value, post_title AS label
                                                                    FROM ' . $wpdb->prefix . 'postmeta 
                                                                    LEFT JOIN ' . $wpdb->prefix . 'posts ON ' . $wpdb->prefix . 'posts.ID = ' . $wpdb->prefix . 'postmeta.meta_value AND ' . $wpdb->prefix . "posts.post_status = 'publish' 
                                                                    WHERE meta_key = %s 
                                                                    ORDER BY post_title ASC",
								$key
							);
							$results            = $wpdb->get_results( $sql_filter_options );

							$filter_options = array();
							if ( ! empty( $results ) && is_array( $results ) ) {
								foreach ( $results as $result ) {
										$filter_option             = new stdClass();
										$filter_option->meta_value = $result->meta_value;
										$filter_option->label      = wp_kses_post( get_the_title( $result->meta_value ) );
										$filter_options[]          = $filter_option;
								}
							}

							break;

						case 'percentages':
							$filter_options = array();

							for ( $percentage = 0; $percentage <= 100; $percentage += 5 ) {
								$filter_option             = new stdClass();
								$filter_option->meta_value = $percentage;
								$filter_option->label      = $percentage . '%';
								$filter_options[]          = $filter_option;
							}

							break;

						case 'taxonomies':
							$filter_options = array();
							$terms          = get_terms(
								array(
									'taxonomy'   => $args['taxonomy'],
									'hide_empty' => false,
								)
							);

							if ( ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
									$filter_option             = new stdClass();
									$filter_option->meta_value = $term->slug;
									$filter_option->label      = $term->name;
									$filter_options[]          = $filter_option;
								}
							}

							break;

						default:
							$sql_filter_options = $wpdb->prepare(
								'SELECT DISTINCT(' . $wpdb->prefix . 'postmeta.meta_value) AS meta_value, ' . $wpdb->prefix . 'postmeta.meta_value AS label 
                                                                    FROM ' . $wpdb->prefix . 'postmeta 
                                                                    ' . ( $filter_by_access_field ? 'LEFT JOIN ' . $wpdb->prefix . 'postmeta AS wp_postmeta_access_filter ON wp_postmeta_access_filter.post_id = ' . $wpdb->prefix . 'postmeta.post_id ' : '' ) . '
                                                                    WHERE ' . $wpdb->prefix . 'postmeta.meta_key = %s 
                                                                    ' . ( $filter_by_access_field ? "AND (wp_postmeta_access_filter.meta_key = '" . $args['access']['field'] . "' AND wp_postmeta_access_filter.meta_value = " . $employee_id . ' ) ' : '' ) . '
                                                                    ORDER BY ' . $wpdb->prefix . 'postmeta.meta_value ASC',
								$key
							);
							$filter_options     = $wpdb->get_results( $sql_filter_options );

							break;
					}
					$filter_options = apply_filterS( "bm_{$type}_filter_options", $filter_options, $args );

					if ( isset( $filter_options ) ) {
						$date_format = get_option( 'date_format' );

						foreach ( $filter_options as $i => $option ) {
							$label = '';

							switch ( $format ) {
								case 'dates':
									$label = date( $date_format, $option->label );
									break;

								case 'post':
									$label = get_post( $option->label )->post_title;
									break;
								case 'taxonomy':
									$tax = '';
									if ( isset( $args['taxonomy'] ) ) {
										$tax = $args['taxonomy'];
									}
									$label = get_term( $option->label, $tax )->name;
									break;
								default:
									$label = $option->label;
									break;
							}
							$values[ $option->meta_value ] = $label;
						}
					}

					$values = array_filter( $values );
					?>
					<select name="<?php echo esc_attr( $key ); ?>">
					<option value=""><?php printf( __( 'All %s', 'business-manager' ), $args['label'] ); ?></option>
					<?php
						$current_v = '';

					if ( isset( $_GET[ $key ] ) ) {
						$current_v = sanitize_text_field( $_GET[ $key ] );
					}

					if ( strpos( $key, 'taxonomy-bm' ) !== false ) {
						$key_without_taxonomy = str_replace( 'taxonomy-', '', $key );

						if ( isset( $_GET[ $key_without_taxonomy ] ) ) {
							$current_v = sanitize_text_field( $_GET[ $key_without_taxonomy ] );
						}
					}

					foreach ( $values as $value => $label ) {
						printf(
							'<option value="%s"%s>%s</option>',
							$value,
							( $current_v != '' && $value == $current_v ? ' selected="selected"' : '' ),
							$label
						);
					}
					?>
					</select>
					<?php
				}
			}
		}

		public function posts_filter( $query ) {
			global $pagenow;

			$type       = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '' );
			$meta_query = array();
			$tax_query  = array();

			foreach ( $this->filters as $key => $args ) {
				$value = ( isset( $_GET[ $key ] ) ? sanitize_text_field( $_GET[ $key ] ) : '' );

				if ( $this->cpt_name == $type && is_admin() && $pagenow == 'edit.php' && isset( $_GET[ $key ] ) && $value != '' ) {
					if ( strpos( $key, 'taxonomy-bm' ) !== false ) {
						$tax_query[] = array(
							array(
								'taxonomy'         => str_replace( 'taxonomy-', '', $key ),
								'terms'            => $value,
								'field'            => 'slug',
								'include_children' => true,
								'operator'         => 'IN',
							),
						);
					} if ( '_bm_project_assigned_to' === $key ) {
						$meta_query[] = array(
							'key'     => $key,
							'value'   => $value,
							'compare' => 'LIKE',
						);
					} else {
						$meta_query[] = array(
							'key'   => $key,
							'value' => $value,
						);
					}
				}

				// Handle employee permissions and restrictions
				if ( isset( $args['access'] ) && current_user_can( 'bm_employee' ) ) {
					$employee_id = business_manager_employee_id( get_current_user_id() );

					if ( business_manager_employee_access( get_current_user_id(), $args['access']['type'] ) == 'limited' ) {
						if ( '_bm_project_assigned_to' === $args['access']['field'] ) {
							$meta_query[] = array(
								'key'     => $args['access']['field'],
								'value'   => $employee_id,
								'compare' => 'LIKE',
							);
						} else {
							$meta_query[] = array(
								'key'   => $args['access']['field'],
								'value' => $employee_id,
							);
						}
					}
				}

				$meta_query = apply_filters( 'bm_posts_filter_meta_query', $meta_query, $args );
				if ( isset( $args['access'] ) && isset( $args['access']['field'] ) && current_user_can( 'bm_employee' ) ) {
					$meta_query = apply_filters( "bm_{$args['access']['field']}_meta_query", $meta_query, $args );
				}
			}

			if ( $this->cpt_name == $type ) {
				if ( count( $meta_query ) > 0 ) {
					$query->set( 'meta_query', $meta_query );
				}
				if ( count( $tax_query ) > 0 ) {
					$query->set( 'tax_query', $tax_query );
				}
			}
		}

		/**
		 * Outputs details of an address.
		 */
		private function address( $type, $post_id, $column = null ) {
			$postmeta = get_post_meta( $post_id );
			$html     = '';

			if ( $post_id === 0 || empty( $post_id ) ) {
				return;
			}

			// Address/Street
			if ( isset( $postmeta[ $column['meta_fields']['address'] ][0] ) ) {
				$html .= $postmeta[ $column['meta_fields']['address'] ][0] . '<br>';
			}

			// City
			if ( isset( $postmeta[ $column['meta_fields']['city'] ][0] ) ) {
				$html .= $postmeta[ $column['meta_fields']['city'] ][0];
			}

			// State/Province
			if ( isset( $postmeta[ $column['meta_fields']['state_province'] ][0] ) ) {
				if ( isset( $postmeta[ $column['meta_fields']['city'] ][0] ) ) {
					$html .= ', ';
				}
				$html .= $postmeta[ $column['meta_fields']['state_province'] ][0] . ' ';
			}

			// Zipcode
			if ( isset( $postmeta[ $column['meta_fields']['zipcode'] ][0] ) ) {
				$html .= ' ' . $postmeta[ $column['meta_fields']['zipcode'] ][0];
			}

			// Country
			if ( isset( $postmeta[ $column['meta_fields']['country'] ][0] ) ) {
				$html .= '<br>' . $postmeta[ $column['meta_fields']['country'] ][0];
			}

			return $html;
		}

		/**
		 * Outputs details of a person with image.
		 */
		private function person( $type, $post_id, $column = null ) {
			$postmeta = get_post_meta( $post_id );
			if ( ! $postmeta ) {
				return; // @since 1.5.5 - to solve warnings of Trying to access array offset on value of type bool
			}
			$html     = '';
			$link     = '';
			$name     = trim( $postmeta[ $column['meta_fields']['first_name'] ][0] . ' ' . $postmeta[ $column['meta_fields']['last_name'] ][0] );
			$photo_id = ( isset( $postmeta[ $column['meta_fields']['photo_id'] ][0] ) ? $postmeta[ $column['meta_fields']['photo_id'] ][0] : null );

			if ( $post_id === 0 || empty( $post_id ) ) {
				return;
			}

			// Set link based on type
			if ( $type == 'post_type' ) {
				$link = get_edit_post_link( $post_id );
			} elseif ( $type == 'meta_value' && $column !== null ) {
				$link = 'edit.php?post_type=' . $column['post_type'] . '&' . $column['column'] . '=' . $post_id;
			}

			// Generate HTML for column
			$html     .= '<div class="bm-person-column-container">';
				$html .= '<div class="bm-person-column">';
			if ( $link != '' ) {
				$html .= '<a href="' . $link . '">' . business_manager_employee_photo( $photo_id ) . '</a>';
			} else {
				$html .= business_manager_employee_photo( $photo_id );
			}
				$html .= '</div>';

				$html .= '<div class="bm-person-column">';
			if ( $link != '' ) {
				$html .= '<a href="' . $link . '">' . $name . '</a>';
			} else {
				$html .= $name;
			}
				$html .= '</div>';
			$html     .= '</div>';

			return $html;
		}

		/**
		 * Outputs details of a client with logo.
		 */
		private function client( $type, $post_id, $column = null ) {
			$postmeta = get_post_meta( $post_id );
			$html     = '';
			$link     = '';
			$name     = get_the_title( $post_id );
			$logo_id  = ( isset( $postmeta['_bm_client_logo_id'][0] ) ? $postmeta['_bm_client_logo_id'][0] : '' );

			if ( $post_id === 0 || empty( $post_id ) ) {
				return;
			}

			// Set link based on type
			if ( $type == 'post_type' ) {
				$link = get_edit_post_link( $post_id );
			} elseif ( $type == 'meta_value' && $column !== null ) {
				$link = 'edit.php?post_type=' . $column['post_type'] . '&' . $column['column'] . '=' . $post_id;
			}

			// Generate HTML for column
			$html .= '<div class="bm-client-column">';
			if ( $link != '' ) {
				$html .= '<a href="' . $link . '">';
			}

			$html .= business_manager_client_logo( $logo_id );

			if ( $link != '' ) {
				$html .= '</a>';
			}
			$html .= '</div>';

			$html .= '<div class="bm-client-column">';
			if ( $link != '' ) {
				$html .= '<a href="' . $link . '">' . $name . '</a>';
			} else {
				$html .= $name;
			}
			$html .= '</div>';

			return $html;
		}
	}
}
