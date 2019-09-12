<?php
/**
 * Course Participants Widget
 *
 *
 * @package WordPress
 * @subpackage Boss for LearnDash
 * @category Widgets
 * @author BuddyBoss
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Boss_LearnDash_Course_Participants_Widget extends WP_Widget {
	protected $boss_edu_widget_cssclass;
	protected $boss_edu_widget_description;
	protected $boss_edu_widget_idbase;
	protected $boss_edu_widget_title;

	/**
	 * Constructor function.
	 * @since  1.1.0
	 * @return  void
	 */
	public function __construct() {
		/* Widget variable settings. */
		$this->boss_edu_widget_cssclass = 'widget_learndash_course_participants';
		$this->boss_edu_widget_description = sprintf( __( 'Displays a list of learners taking the current %s, with links to their profiles (if public).', 'boss-learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) );
		$this->boss_edu_widget_idbase = 'widget_learndash_course_participants';
		$this->boss_edu_widget_title = sprintf( __( '(BuddyBoss) - %s Participants', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) );
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->boss_edu_widget_cssclass, 'description' => $this->boss_edu_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->boss_edu_widget_idbase );

		/* Create the widget. */
		parent::__construct( $this->boss_edu_widget_idbase, $this->boss_edu_widget_title, $widget_ops, $control_ops );
	}

    /**
	 * Display the widget on the frontend.
	 * @since  1.0.0
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Widget settings for this instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {

		extract( $args );

        if ( ( is_singular( array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic') ) ) ) {
			if ( get_post_type() == 'sfwd-courses' ) {
				$course_id = get_the_ID();
			}

			if ( get_post_type() == 'sfwd-lessons' ) {
				$course_id = get_post_meta(get_the_ID(),'course_id',true);
			}

			if ( get_post_type() == 'sfwd-topic' ) {
				$lesson_id = get_post_meta(get_the_ID(),'lesson_id',true);
				$course_id = get_post_meta($lesson_id,'course_id',true);
			}

		} else {
			$course_id = groups_get_groupmeta( bp_get_group_id(), 'bp_course_attached', true );
		}

		if ( isset( $instance['title'] ) ) {
			$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );
		}
		$limit = 5;
		if ( isset( $instance['limit'] ) && is_array( $instance['limit'] ) && 0 < count( $instance['limit'] ) ) {
			$limit = intval( $instance['limit'] );
		}

		// Frontend Output
		echo $before_widget;

		/* Display the widget title if one was input */
		if ( $title ) { echo $before_title . $title . $after_title; }

		// Add actions for plugins/themes to hook onto.
		do_action( $this->boss_edu_widget_cssclass . '_top' );

        $all_course_participant     = boss_edu_course_participants( array( 'course_id' => $course_id, 'number' => -1 ) );
        $paged_course_participants  = boss_edu_course_participants( array( 'course_id' => $course_id, 'number' => $limit ) );

        $html = '';
		if( empty( $all_course_participant ) ) {
			$html .= '<p>' . __( 'There are no other learners currently taking this course. Be the first!', 'boss-learndash' ) . '</p>';
		} else {

			$list_class = 'list';
			$html .= '<ul class="learndash-course-participants-list' . ' ' . $list_class . '">';

            if( $paged_course_participants ) {

                // single <li></li>
                $html .= boss_edu_course_participants_li($paged_course_participants);

                $html .= '</ul>';

                // Display a view all link if not all learners are displayed.
                if ( sizeof($all_course_participant) > sizeof($paged_course_participants) ) {
                    $html .= '<div class="learndash-see-more-participants"><a href="#" data-course_id="'. $course_id .'" data-paged="'. 2 .'" data-number="'. $limit .'" data-total="'. sizeof($all_course_participant) .'">' .
                        __( 'See more', 'boss-learndash' ) . '</a></div>';
                }

            } else {
                $html .= '<p>' . __( 'There are no other learners currently taking this course. Be the first!', 'boss-learndash' ) . '</p>';
            }

		}

		echo $html;

		// Add actions for plugins/themes to hook onto.
		do_action( $this->boss_edu_widget_cssclass . '_bottom' );

		echo $after_widget;
	} // End widget()

	/**
	 * Method to update the settings from the form() method.
	 * @since  1.0.0
	 * @param  array $new_instance New settings.
	 * @param  array $old_instance Previous settings.
	 * @return array               Updated settings.
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and limit to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['size'] = intval( $new_instance['size'] );

		/* The select box is returning a text value, so we escape it. */
		$instance['display'] = esc_attr( $new_instance['display'] );

		return $instance;
	} // End update()

	/**
	 * The form on the widget control in the widget administration area.
	 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff.
	 * @since  1.0.0
	 * @param  array $instance The settings for this instance.
	 * @return void
	 */
    public function form( $instance ) {

		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'title' => '',
						'limit' => 5,
						'size' => 50,
						'display' => 'list'
					);

		$instance = wp_parse_args( (array) $instance, $defaults );
?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title (optional):', 'boss-learndash' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"  value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" />
		</p>
		<!-- Widget Limit: Text Input -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php _e( 'Number of Learners (optional):', 'boss-learndash' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>"  value="<?php echo esc_attr( $instance['limit'] ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" />
		</p>

<?php
	} // End form()


	/**
	 * Get an array of the available display options.
	 * @since  1.0.0
	 * @return array
	 */
	protected function get_display_options () {
		return array(
					'list' 			=> __( 'List', 'boss-learndash' ),
					'grid' 			=> __( 'Grid', 'boss-learndash' )
					);
	} // End get_display_options()
}