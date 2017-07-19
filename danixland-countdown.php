<?php
/*
Plugin Name: danixland CountDown
Plugin URI: http://danixland.net/?p=3330 
Description: A simple plugin that shows a widget with a countdown
Version: 0.4
Author: Danilo 'danix' Macri
Author URI: http://danixland.net
Text Domain: dnxcd

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/


/**
 * Add plugin i18n domain: dnxcd
 * @since 0.1
 */
load_plugin_textdomain('dnxcd', plugins_url() . '/danixland-countdown/i18n/', 'danixland-countdown/i18n/');

/**
 * Function that installs our widget options
 * @since 0.1
 */
function dnxcd_options_set() {
	add_option( 'dnxcd_future_date', '06-05-2020', '', 'yes' );
	add_option( 'dnxcd_widget_link', '', '', 'yes' );
	add_option( 'dnxcd_use_style', 1, '', 'yes' );
}

/**
 * Function that deletes our widget options
 * @since 0.3
 */
function dnxcd_options_unset() {
	delete_option( 'dnxcd_future_date' );
	delete_option( 'dnxcd_widget_link' );
	delete_option( 'dnxcd_use_style' );
}

/**
 * Use widget's stylesheet if user wants it
 * @since 0.3
 */
function dnxcd_style_handle() {
	wp_register_style('dnxcd_basic', plugins_url() . '/danixland-countdown/inc/style/basic.css');
	if( false == get_option('dnxcd_use_style') )
		return;
	wp_enqueue_style('dnxcd_basic');
}
add_action( 'wp_enqueue_scripts', 'dnxcd_style_handle' );

/**
 * Add function on plugin activation that'll set our plugin options
 * @since 0.1
 */
register_activation_hook( __FILE__, 'dnxcd_options_set' );

/**
 * Add function on plugin deactivation that'll unset our plugin options
 * @since 0.3
 */
register_deactivation_hook( __FILE__, 'dnxcd_options_unset' );

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'danixland_countdown' );

/**
 * Register our widget.
 * 'dnx_CountDown' is the widget class used below.
 *
 * @since 0.1
 */
function danixland_countdown() {
	register_widget( 'dnx_CountDown' );
}

/**
 * dnx_CountDown class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class dnx_CountDown extends WP_Widget {


    /**
     * Widget setup.
     */
    public function __construct() {
        $control_ops = array('width' => 400, 'height' => 350);
        parent::__construct(
            'dnx-countdown', // id_base
            __('danixland CountDown', 'dnxcd' ), // Name
            array( 'description' => __('Use this widget to add a simple Count Down to your Sidebar', 'dnxcd') ),
            $control_ops
        );
    }

	/**
	 * How to display the widget on the public side of the site.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		if ( '' != get_option('dnxcd_future_date') ) {
			echo '<div class="dnx-countdown">';
			$date = gmdate('U', strtotime( get_option('dnxcd_future_date') ));
			$diff = $date - gmdate('U');
			if ( '' != get_option('dnxcd_widget_link') ) {
				echo '<a href="' . get_option('dnxcd_widget_link') . '">';
				echo '-' . floor($diff / (24 * 60 * 60));
				echo '</a>';
			} else {
				echo '-' . floor($diff / (24 * 60 * 60));
			}
			echo '</div> <!-- #dnx-countdown -->';
			echo '<div class="dnx-days">' . __('days to go', 'dnxcd') . '</div>'; 
		} else {
			echo "<div class='dnx-warning'><a href='" . get_admin_url('', 'widgets.php', '') . "' title='" . __('configure widget', 'dnxcd') . "'>" . __('the date is missing<br />configure widget', 'dnxcd') . "</a></div>";
		}

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	//Update the widget 
	 
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['date'] = strip_tags( $new_instance['date'] );
		$instance['link'] = esc_url( $new_instance['link'], array('http', 'https') );
		$instance['style'] = (bool) $new_instance['style'];

		update_option( 'dnxcd_future_date', $instance['date'] );
		update_option( 'dnxcd_widget_link', $instance['link'] );
		update_option( 'dnxcd_use_style', $instance['style'] );
		
		return $instance;
	}

	/**
	 * Displays just a quick notice with a link to the Settings page
	 */
	public function form( $instance ) {
		$defaults = array( 
			'title' => __( 'Count Down', 'dnxcd' ),
			'date' => get_option( 'dnxcd_future_date' ),
			'link' => get_option( 'dnxcd_widget_link' ),
			'style' => get_option( 'dnxcd_use_style' )
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'dnxcd'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Future Date (dd-mm-yyyy):', 'dnxcd'); ?></label>
			<input type="date" class="widefat" id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" value="<?php echo $date ?>" style="width:100%;" />
			<label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e('Link (only http/https allowed):', 'dnxcd'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" value="<?php echo $link; ?>" style="width:100%;" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $style, true ); ?> id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e('Enable Widget Stylesheet?', 'dnxcd'); ?></label>
		</p>
	<?php
	}
}

?>
