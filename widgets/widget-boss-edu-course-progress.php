<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Course Progress Widget
 *
 * A Course Progress Widget widget to display a progress of current Course.
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
class Boss_LearnDash_Course_Progress_Widget extends WP_Widget {
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
		$this->boss_edu_widget_cssclass = 'widget_course_progress';
		$this->boss_edu_widget_description = sprintf( __( 'This widget will output a progress of current %s.', 'boss-learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ) );
		$this->boss_edu_widget_idbase = 'widget_course_progress';
		$this->boss_edu_widget_title = sprintf( __( '(BuddyBoss) - %s Progress', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) );

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

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Display the widget title if one was input (before and after defined by themes). */
        if ( $title ) { echo $before_title . $title . $after_title; }

        /* Widget content. */
        // Add actions for plugins/themes to hook onto.
        do_action( $this->boss_edu_widget_cssclass . '_top' );

        $this->load_component( $instance );

        // Add actions for plugins/themes to hook onto.
        do_action( $this->boss_edu_widget_cssclass . '_bottom' );

        /* After widget (defined by themes). */
        echo $after_widget;

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

	    if ( bp_is_current_action('experiences') ) {
            $group      = groups_get_current_group();
            $course_id  = groups_get_groupmeta( $group->id , 'bp_course_attached', true );
	    } else if ( is_singular( array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) ) ) {
            $course_id = learndash_get_course_id();
        }

        if(!empty($course_id)) {
            $course = get_post($course_id);
            ?>
            <section class="course-lessons-widgets">
                <header>
                    <h3><a href="<?php echo get_permalink($course_id); ?>"><?php echo $course->post_title; ?></a></h3>
                    <div class="course_stats">
                        <?php echo do_shortcode('[learndash_course_progress course_id = '.$course_id.' user_id = '.get_current_user_id().']'); ?>
                    </div>
                </header>
            </section>
            <?php

            if ( function_exists('bp_is_active') ) {
                $author_avatar = bp_core_fetch_avatar ( array( 'item_id' => $course->post_author , 'type' => 'full', 'width' => '50', 'height' => '50' ) );
                $author = '<a class="user-link" href="' . bp_core_get_user_domain( $course->post_author ) . '">' . bp_core_get_user_displayname( $course->post_author ) . '</a>';
            } else {
                $author_avatar = get_avatar( $course->post_author, 50 );
                $author = get_the_author_meta( 'display_name', $course->post_author );
            }

            $course_settings = learndash_get_setting($course);
            $lessons = apply_filters( 'boss_edu_course_lessons_list', learndash_get_course_lessons_list($course) );
            ?>

            <div id='course_navigation'>
                <div class='learndash_nevigation_lesson_topics_list'>

                <?php
                global $post;
                $lesson_id = 0;
                if($post->post_type == "sfwd-topic" || $post->post_type == "sfwd-quiz") {
                    $lesson_id = learndash_get_setting($post, "lesson");
                    if($post->post_type == "sfwd-topic") {
                        $topic_id = $post->ID;
                    }
                }
                else if( is_singular('sfwd-lessons') ) {
                    $lesson_id = $post->ID;
                }


                    if(!empty($lessons))
                    foreach($lessons as $course_lesson)
                    {
                        $current_topic_ids = "";
                        $topics =  learndash_topic_dots($course_lesson["post"]->ID, false, 'array');
                        $is_current_lesson = ($lesson_id== $course_lesson["post"]->ID);
                        $lesson_list_class = ($is_current_lesson)? 'active':'inactive';
                        $lesson_lesson_completed = ($course_lesson["status"]=='completed')?'lesson_completed':'lesson_incomplete';
                        $list_arrow_class = ($is_current_lesson && !empty($topics))? 'expand':'collapse';
                        if(!empty($topics))
                            $list_arrow_class .= " flippable";
                        ?>

                        <div class="bb-lesson-list-item <?php echo $lesson_list_class ?>" id="lesson_list-<?php echo $course_lesson["post"]->ID; ?>">
                            <div class="list_arrow <?php echo $list_arrow_class; ?> <?php echo $lesson_lesson_completed; ?>" onClick="return flip_expand_collapse('#lesson_list', <?php echo $course_lesson["post"]->ID; ?>);" >
								<i class="fa fa-sort-down" aria-hidden="true"></i>
                            </div>
                            <div class="list_lessons">
                                <div class="lesson" >
                                    <a href="<?php echo get_permalink($course_lesson["post"]->ID);?>"><?php echo $course_lesson["post"]->post_title ?></a>
                                </div>

                                <?php
                                    if(!empty($topics)) {
                                    ?>
                                    <div id="learndash_topic_dots-<?php echo $course_lesson["post"]->ID; ?>" class="flip learndash_topic_widget_list">
                                        <ul>
                                            <?php
                                            $odd_class = "";
                                            foreach ($topics as $key => $topic) {
                                            //	$odd_class = empty($odd_class)? "nth-of-type-odd":"";
                                                $completed_class = empty($topic->completed)? "topic-notcompleted":"topic-completed";
                                                ?>
                                                <li>
                                                    <span class="topic_item">

                                                        <a class="<?php echo $completed_class; echo (isset($topic_id) && $topic->ID == $topic_id)?' current':''; ?>" href="<?php echo get_permalink($topic->ID); ?>" title="<?php echo $topic->post_title; ?>">
                                                            <span><?php echo $topic->post_title; ?></span>
                                                        </a>
                                                    </span>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                <?php } ?>
                <?php
				global $course_lessons_results;
				if ( isset( $course_lessons_results['pager'] ) ) {
					echo SFWD_LMS::get_template(
						'learndash_pager.php',
						array(
							'pager_results' => $course_lessons_results['pager'],
							'pager_context' => 'course_lessons_widget',
						)
					);
				}
                ?>
                </div> <!-- Closing <div class='learndash_nevigation_lesson_topics_list'> -->
                <?php if($post->ID != $course->ID) { ?>
                <div class="widget_course_return">
                    <?php _e("Return to", "learndash"); ?> <a href="<?php echo get_permalink($course_id); ?>">
                        <?php echo $course->post_title;?>
                    </a>
                </div>
                <?php } ?>
            </div> <!-- Closing <div id='course_navigation'> -->

            <?php
            $html = '<footer>';
                $html .= '<h4>'.sprintf( __('About this %s', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ).'</h4>';
                $html .= '<p>' . $course->post_excerpt . '</p>';
                $html .= $author_avatar;
                $html .= '<span><p>'. sprintf( __( '%s by', 'boss-learndash'  ), LearnDash_Custom_Label::get_label( 'course' ) ).'</p><p>'.$author.'</p></span>';

                $user_id = get_current_user_id();
                    $logged_in = !empty($user_id);

                    if($logged_in) {
                    if( bp_is_active( 'groups' ) ){
                        $button = '';
                        $group_attached = get_post_meta( $course_id, 'bp_course_group', true );
                        if ( !(empty($group_attached) || $group_attached == '-1' ) )	{
                            global $bp;
                            $group_data = groups_get_group($group_attached);
                            $button = $group_data->is_visible ? '<p class="bp-group-discussion"><a class="btn inverse" href="'. trailingslashit(home_url()).trailingslashit($bp->groups->slug).$group_data->slug.'">'. sprintf( __('%s Discussion','boss-learndash'), LearnDash_Custom_Label::get_label( 'course' ) ).'</a></p>' : '';
                            apply_filters('boss_edu_group_discussion_button', $button);
                            $html .= $button;
                        }
                    }
                }

            $html .= '</footer>';

            echo $html;
        }
	} // End load_component()
}