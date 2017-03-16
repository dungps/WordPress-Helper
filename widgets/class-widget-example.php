<?php

if ( !class_exists( 'KP_Widget' ) ) {
	require_once( 'class-widget.php' );
}

class KP_Widget_Example extends KP_Widget {
	public function __construct() {
		$this->widget_class = 'kp_widget_example';
		$this->widget_desc = __( 'This is an example widget class.', 'kp-widget' );
		$this->widget_id = 'kp_widget_example';
		$this->widget_name = __( 'KP Widget Example', 'kp-widget' );
		$this->settings = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'KP Widget Example', 'kp-widget' ),
				'label' => __( 'Title', 'kp-widget' )
			),
			'content' => array(
				'type' => 'textarea',
				'std' => '<p>'. __( 'This is an example widget class.', 'kp-widget' ) .'</p>',
				'label' => __( 'Content', 'kp-widget' )
			)
		);

		parent::__construct();
	}

	public function widget( $args, $instances ) {
		$this->widget_before( $args, $instance );
		echo wp_kses( trim( wp_unslash( $new_instance['content'] ) ), wp_kses_allowed_html( 'post' ) );
		$this->widget_after( $args, $instance );
	}
}