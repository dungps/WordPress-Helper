<?php

if ( !class_exists( 'KP_Widget' ) ) {
	class KP_Widget extends WP_Widget {
		/**
		 * Widget class
		 * @var string
		 */
		public $widget_class;

		/**
		 * Widget description
		 * @var string
		 */
		public $widget_desc;

		/**
		 * Widget ID
		 * @var string
		 */
		public $widget_id;

		/**
		 * Widget Name
		 * @var string
		 */
		public $widget_name;

		/**
		 * Widget Settings
		 * @var array
		 */
		public $settings;

		/**
		 * Class Constructor
		 */
		public function __construct() {
			$widget_ops = array(
				'classname' => $this->widget_class,
				'description' => $this->widget_desc,
				'customize_selective_refresh' => true
			);

			parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );

			add_action( 'save_post', array( $this, 'flush_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_cache' ) );
		}

		/**
		 * Set Widget cache
		 *
		 * @param array $args
		 * @param string $content
		 * @return string
		 */
		public function cache_set( $args, $content ) {
			wp_cache_set( $this->widget_id, array( $args['widget_id'] => $content ), 'widget' );

			return $content;
		}

		/**
		 * Delete widget cache
		 */
		public function flush_cache() {
			wp_cache_delete( $this->widget_id, 'widget' );
		}

		/**
		 * Get widget cache was set
		 *
		 * @param array $args;
		 * @return mixed
		 */
		public function cache_get( $args ) {
			$cache = wp_cache_get( $this->widget_id, 'widget' );

			return $cache;
		}

		/**
		 * Output the html at the start of a widget.
		 *
		 * @param array $args;
		 * @param array $instance;
		 * @return string
		 */
		public function widget_before( $args, $instace ) {
			echo $args['before_widget'];

			if ( $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
		}

		/**
		 * Output the html at the end of a widget.
		 *
		 * @param array $args;
		 * @param array $instance;
		 * @return string
		 */
		public function widget_after( $args, $instance ) {
			echo $args['after_widget'];
		}

		/**
		 * Updates a particular instance of a widget.
		 *
		 * @see    WP_Widget->update
		 * @param  array $new_instance
		 * @param  array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			if ( empty( $this->settings ) ) {
				return $instance;
			}

			foreach( $this->settings as $key => $setting ) {
				if ( !isset( $setting['type'] ) ) {
					continue;
				}

				switch ( $setting['type'] ) {
					case 'number':
						$instance[ $key ] = absint( $new_instance[ $key ] );

						if ( isset( $setting['min'] ) && '' !== $setting['min'] ) {
							$instance[ $key ] = max( $instance[ $key ], $setting['min'] );
						}

						if ( isset( $setting['max'] ) && '' !== $setting['max'] ) {
							$instance[ $key ] = min( $instance[ $key ], $setting['max'] );
						}
						break;
					
					case 'textarea':
						$instance[ $key ] = wp_kses( trim( wp_unslash( $new_instance[ $key ] ) ), wp_kses_allowed_html( 'post' ) );
						break;

					case 'checkbox':
						$instance[ $key ] = empty( $new_instance[ $key ] ) ? 0 : 1;
						break;

					default:
						$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
						break;
				}

				$instance[ $key ] = apply_filters( 'kp_widget_settings_sanitize_option', $instance[ $key ], $new_instance, $key, $setting );
			}

			$this->flush_cache();

			return $instance;
		}

		/**
		 * Outputs the settings update form.
		 *
		 * @see   WP_Widget->form
		 * @param array $instance
		 */
		public function form( $instance ) {
			if ( empty( $this->settings ) ) {
				return;
			}

			foreach ( $this->settings as $key => $setting ) {
				$class = isset( $setting['class'] ) ? $setting['class'] : '';
				$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

				switch ( $setting['type'] ) {

					case 'text' :
						?>
						<p>
							<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
							<input class="widefat <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
						</p>
						<?php
					break;

					case 'number' :
						?>
						<p>
							<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
							<input class="widefat <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						</p>
						<?php
					break;

					case 'select' :
						?>
						<p>
							<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
							<select class="widefat <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>">
								<?php foreach ( $setting['options'] as $option_key => $option_value ) : ?>
									<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $value ); ?>><?php echo esc_html( $option_value ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<?php
					break;

					case 'textarea' :
						?>
						<p>
							<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
							<textarea class="widefat <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" cols="20" rows="3"><?php echo esc_textarea( $value ); ?></textarea>
							<?php if ( isset( $setting['desc'] ) ) : ?>
								<small><?php echo esc_html( $setting['desc'] ); ?></small>
							<?php endif; ?>
						</p>
						<?php
					break;

					case 'checkbox' :
						?>
						<p>
							<input class="checkbox <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="checkbox" value="1" <?php checked( $value, 1 ); ?> />
							<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						</p>
						<?php
					break;

					// Default: run an action
					default :
						do_action( 'kp_widget_field_' . $setting['type'], $key, $value, $setting, $instance );
					break;
				}
			}
		}
	}
}