<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Course Teacher Widget
 *
 * A Course Teacher Widget widget to display a progress of current Course.
 *
 * @package WordPress
 * @subpackage Boss for LearnDash
 * @category Widgets
 * @author BuddyBoss
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * protected $boss_edu_widget_cssclass
 * protected $boss_edu_widget_description
 * protected $boss_edu_widget_idbase
 * protected $boss_edu_widget_title
 *
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - load_component()
 */
class Boss_LearnDash_Course_Teacher_Widget extends WP_Widget {
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
		$this->boss_edu_widget_cssclass = 'widget_course_teacher';
		$this->boss_edu_widget_description = sprintf( __( 'This widget will output details about %s teacher', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) );
		$this->boss_edu_widget_idbase = 'widget_course_trogress';
		$this->boss_edu_widget_title =  sprintf( __( '(BuddyBoss) - %s Teacher', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->boss_edu_widget_cssclass, 'description' => $this->boss_edu_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->boss_edu_widget_idbase );

		/* Create the widget. */
        parent::__construct( $this->boss_edu_widget_idbase, $this->boss_edu_widget_title, $widget_ops, $control_ops );
	} // End __construct()

	/**
	 * Display the widget on the frontend.
	 * @since  1.1.0
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Widget settings for this instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );
        if( ( is_singular( array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic') ) ) || groups_get_groupmeta( bp_get_group_id(), 'bp_course_attached', true )) {
            /* Before widget (defined by themes). */
            echo $before_widget;

            /* Display the widget title if one was input (before and after defined by themes). */
            if ( $title ) { echo $before_title . $title . $after_title; }

            /* Widget content. */
            // Add actions for plugins/themes to hook onto.
            do_action( $this->boss_edu_widget_cssclass . '_top' );

    //		if ( 0 < intval( $instance['course_category'] ) ) {
            $this->load_component( $instance );
    //		} // End If Statement

            // Add actions for plugins/themes to hook onto.
            do_action( $this->boss_edu_widget_cssclass . '_bottom' );

            /* After widget (defined by themes). */
            echo $after_widget;
        }

	} // End widget()

	/**
	 * Method to update the settings from the form() method.
	 * @since  1.1.0
	 * @param  array $new_instance New settings.
	 * @param  array $old_instance Previous settings.
	 * @return array               Updated settings.
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	} // End update()

	/**
	 * The form on the widget control in the widget administration area.
	 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff.
	 * @since  1.1.0
	 * @param  array $instance The settings for this instance.
	 * @return void
	 */
    public function form( $instance ) {

		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'title' => ''
					);

		$instance = wp_parse_args( (array) $instance, $defaults );
?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title (optional):', 'boss-learndash' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"  value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" />
		</p>

<?php
	} // End form()
	/**
	 * Load the output.
	 * @param  array $instance.
	 * @since  1.1.0
	 * @return void
	 */
	protected function load_component ( $instance ) {

        $text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
        
        $queried_object = get_queried_object();

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

        $post = get_post( $course_id );

        if ( function_exists('bp_is_active') ) {
            $author_avatar = bp_core_fetch_avatar ( array( 'item_id' => $post->post_author, 'type' => 'full', 'width' => '70', 'height' => '70' ) );
            $author_url = bp_core_get_user_domain( $post->post_author );
            $author = '<a href="' . $author_url . '">' . bp_core_get_user_displayname( $post->post_author ) . '</a>';  
        } else {
            $author_avatar = get_avatar( $post->post_author, 70 );
            $author = get_the_author_meta( 'display_name', $post->post_author );
        }

		$course_by_text = sprintf( __( '%s by', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) );
        ?>
        <header id="<?php $id = uniqid('teacher-widget-'); echo $id; ?>">
            <span>
                <p><?php echo apply_filters( 'bb_course_by_text', $course_by_text ); ?></p>
                <p><?php echo $author; ?></p>
            </span>
            <?php echo $author_avatar; ?>
        </header>
        <div class="authors-word">
             <?php echo get_the_author_meta( 'description', $post->post_author ); ?> 
        </div>
        <footer>          
            <?php

            if ( is_user_logged_in() ) {

                if ( isset( $_GET['contact'] ) ) {

                    if ( bp_is_active( 'messages' ) ) {
                        $html = boss_education()->boss_edu_teacher_contact_form( $post );
                    }
                } else {
                    $href = add_query_arg( array( 'contact' => $post->post_type ) );

                    if ( 'lesson' == $post->post_type ) {
                        $contact_button_text = sprintf( __( 'Contact %s Teacher', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) );
                    } else {
                        $contact_button_text = sprintf( __( 'Contact %s Teacher', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) );
                    }

                    $html = '<p><a class="button send-message-button" href="' . esc_url( $href ) . '#' . $id . '">' . apply_filters( 'bb_contact_button_text', $contact_button_text ) . '</a></p>';
                }

                echo $html;
            }

            // Course group button
            if ( ( is_singular( array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic') ) ) ) {
                $group_attached = get_post_meta( $course_id, 'bp_course_group', true );

                if ( function_exists( 'bp_is_active' ) && ! bp_is_active( 'groups' ) || empty($group_attached) || $group_attached == '-1' )	return;

                global $bp;
                $group_data = groups_get_group($group_attached);
                $html = $group_data->is_visible ? '<p class="bp-group-discussion"><a class="btn inverse" href="'. trailingslashit(home_url()).trailingslashit($bp->pages->groups->slug).$group_data->slug .'">'. sprintf( __('%s Group','boss-learndash'), LearnDash_Custom_Label::get_label( 'course' ) ) .'</a></p>' : '';
                echo apply_filters('boss_edu_group_discussion_button', $html);

                // Course home button
            } else if( bp_is_group() ) {
                $html = '<p class="bp-group-discussion"><a class="btn inverse" href="'.get_permalink( $course_id ).'">'. sprintf( __('%s Home','boss-learndash'), LearnDash_Custom_Label::get_label( 'course' ) ).'</a></p>';
                echo apply_filters('boss_edu_course_home_button', $html);
            }
            ?>
        </footer>
        <?php
 
	} // End load_component()
} // End Class
?>