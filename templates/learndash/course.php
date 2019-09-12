<?php
/**
 * Displays a course
 *
 * Available Variables:
 * $course_id 		: (int) ID of the course
 * $course 		: (object) Post object of the course
 * $course_settings : (array) Settings specific to current course
 *
 * $courses_options : Options/Settings as configured on Course Options page
 * $lessons_options : Options/Settings as configured on Lessons Options page
 * $quizzes_options : Options/Settings as configured on Quiz Options page
 *
 * $user_id 		: Current User ID
 * $logged_in 		: User is logged in
 * $current_user 	: (object) Currently logged in user object
 *
 * $course_status 	: Course Status
 * $has_access 	: User has access to course or is enrolled.
 * $materials 		: Course Materials
 * $has_course_content		: Course has course content
 * $lessons 		: Lessons Array
 * $quizzes 		: Quizzes Array
 * $lesson_progression_enabled 	: (true/false)
 * $has_topics		: (true/false)
 * $lesson_topics	: (array) lessons topics
 *
 * @since 2.1.0
 *
 * @package LearnDash\Course
 */
?>

<?php global $course_pager_results; ?>

<!-- Container added by boss -->
<div class="lms-post-content">
	<?php if ( $logged_in ) : ?>
		<?php
			/**
			 * Filter to add custom content after the Course Status section of the Course template output.
			 * @since 2.3
			 * See https://bitbucket.org/snippets/learndash/7oe9K for example use of this filter.
			 */
			echo apply_filters('ld_after_course_status_template_container', '', learndash_course_status_idx( $course_status ), $course_id, $user_id );
		?>

		<?php  if ( ! empty( $course_certficate_link ) ) : ?>
			<div id="learndash_course_certificate">
				<a href='<?php echo esc_attr( $course_certficate_link ); ?>' class="btn-blue" target="_blank"><?php echo apply_filters('ld_certificate_link_label', __( 'PRINT YOUR CERTIFICATE', 'boss-learndash' ), $user_id, $post->ID ); ?></a>
			</div>
			<br />
		<?php endif; ?>
	<?php endif; ?>

	<?php echo $content; ?>
</div>

<?php
/**
 * Payment Buttons
 */
if ( !$has_access ) :
	echo boss_edu_payment_buttons( $post );
endif;

/**
 * Course Content
 */
if ( $has_course_content ) {

	$show_course_content = true;

	if ( !$has_access ) :
		if ( isset( $course_meta[ 'sfwd-courses_course_disable_content_table' ] ) && $course_meta[ 'sfwd-courses_course_disable_content_table' ] == 'on' ) :
			$show_course_content = false;
		endif;
	endif;

	if ( $show_course_content ) :

		/**
		 * Course Materials
		 */
        if ( ( isset( $materials ) ) && ( !empty( $materials ) ) ) :
			?>
			<div id="learndash_course_materials">
				<h4><?php printf( _x( '%s Materials', 'Course Materials Label', 'boss-learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h4>
				<div class="materials-content"><?php echo $materials; ?></div>
			</div><?php
		endif;
		?>

		<div id='learndash_course_content'>

			<?php
			/* Show Lesson List */
			if ( !empty( $lessons ) ) :
				?>

				<div id="learndash_lessons" class="learndash_lessons">

					<div id="lesson_heading">
						<span><?php echo LearnDash_Custom_Label::get_label( 'lessons' ) ?></span>

						<?php if ( $has_topics ) : ?>
							<div class="expand_collapse">
								<a href="#" onClick='jQuery( "#learndash_post_<?php echo $course_id; ?> .learndash_topic_dots" ).slideDown();
				                        return false;'><?php _e( 'Expand All', 'boss-learndash' ); ?></a> |
								<a href="#" onClick='jQuery( "#learndash_post_<?php echo esc_attr( $course_id ); ?> .learndash_topic_dots" ).slideUp();
				                        return false;'><?php _e( 'Collapse All', 'boss-learndash' ); ?></a>
							</div>
                            <?php if ( apply_filters('learndash_course_steps_expand_all', false, $course_id, 'course_lessons_listing_main' ) ) { ?>
                            <script>
                                jQuery(document).ready(function(){
                                    jQuery("#learndash_post_<?php echo $course_id; ?> .learndash_topic_dots").slideDown();
                                });
                            </script>
                        <?php } ?>
						<?php endif; ?>
					</div>

					<div id="lessons_list" class="lessons_list">
						<?php 
                        foreach ( $lessons as $lesson ) { ?>
							<?php
							/* Lesson Topis */
							$topics	 = @$lesson_topics[ $lesson[ 'post' ]->ID ];
							$meta	 = get_post_meta( $lesson[ "post" ]->ID, '_' . $lesson[ "post" ]->post_type );
                            $in_progress = false;

                            if ( !empty( $topics ) ) {
                                foreach ( $topics as $key => $topic ) {
                                    if ( !empty( $topic->completed ) )
                                        $in_progress = true;
                                }
                            }

							/* Lesson meta info */
							$lesson_meta_data = array();

							if ( is_array( $topics ) && 0 < count( $topics ) ) {
								$lesson_topics_dots  = learndash_topic_dots( $lesson['post']->ID, false, 'array', null, $course_id );
								if( $lesson_topics_dots && !empty($lesson_topics_dots) ) {
									$lesson_topics_count = count( $lesson_topics_dots );
                                } else {
									$lesson_topics_count = 0;
                                }
								$lesson_meta_data[] = sprintf(
										_n( '%s %2$s', '%s %3$s', $lesson_topics_count, 'boss-learndash' ), $lesson_topics_count,
										LearnDash_Custom_Label::get_label( 'topic' ),
										LearnDash_Custom_Label::get_label( 'topics' )
								);
							}

							if ( !empty( $meta[ 0 ][ $lesson[ "post" ]->post_type . '_forced_lesson_time' ] ) ) {
								$lesson_meta_data[] = $meta[ 0 ][ $lesson[ "post" ]->post_type . '_forced_lesson_time' ];
							}
							?>

							<div class="lesson post-<?php echo $lesson[ "post" ]->ID; ?> <?php echo $lesson[ "sample" ]; ?> <?php echo (empty( $topics )) ? 'no-topics' : 'has-topics' ?>">
								<h4>
									<a class="<?php echo ($in_progress && $lesson[ "status" ] == 'notcompleted') ? 'in-progress' : $lesson[ "status" ]; ?>" href="<?php echo $lesson[ "permalink" ] ?>"><?php echo $lesson[ "post" ]->post_title; ?></a>
									<p class="lesson-meta">
										<span class="lesson-meta-data"><?php echo implode( ', ', $lesson_meta_data ); ?></span>
										<span class="drop-list fa fa-chevron-down"></span>
									</p>
								</h4>

								<?php
								/* Not available message for drip feeding lessons */
								if ( !empty( $lesson[ "lesson_access_from" ] ) ) {
                                    SFWD_LMS::get_template(
                                        'learndash_course_lesson_not_available',
                                        array(
                                            'user_id'					=>	$user_id,
                                            'course_id'					=>	learndash_get_course_id( $lesson['post']->ID ),
                                            'lesson_id'					=>	$lesson['post']->ID,
                                            'lesson_access_from_int'	=>	$lesson['lesson_access_from'],
                                            'lesson_access_from_date'	=>	learndash_adjust_date_time_display( $lesson['lesson_access_from'] ),
                                            'context'					=>	'course'
                                        ), true
                                    );
								}

							    $paged_values = learndash_get_lesson_topic_paged_values();

								if ( !empty( $topics ) ) {
									?>
                                    <div id='learndash_topic_dots-<?php echo esc_attr( $lesson['post']->ID ); ?>' class="learndash_topic_dots type-list" <?php
									if ( $paged_values['lesson'] == $lesson['post']->ID ) {
										echo ' style="display:block;"';
									}
									?>>
                                        <ul>
											<?php $odd_class = ''; ?>
											<?php foreach ( $topics as $key => $topic ) : ?>
												<?php $odd_class       = empty( $odd_class ) ? 'nth-of-type-odd' : ''; ?>
												<?php $completed_class = empty( $topic->completed ) ? 'topic-notcompleted' : 'topic-completed'; ?>
                                                <li class='<?php echo esc_attr( $odd_class ); ?>'>
													<span class="topic_item">
														<a class='<?php echo esc_attr( $completed_class ); ?>' href='<?php echo esc_attr( learndash_get_step_permalink( $topic->ID, $course_id ) ); ?>' title='<?php echo esc_attr( $topic->post_title ); ?>'>
															<span><?php echo $topic->post_title; ?></span>
														</a>
													</span>
                                                </li>
											<?php endforeach; ?>
                                        </ul>
										<?php
										if ( isset( $course_pager_results[ $lesson['post']->ID ]['pager'] ) ) {
											echo SFWD_LMS::get_template(
												'learndash_pager.php',
												array(
													'pager_results' => $course_pager_results[ $lesson['post']->ID ]['pager'],
													'pager_context' => 'course_topics',
													'href_query_arg' => 'ld-topic-page',
													'href_val_prefix' => $lesson['post']->ID . '-'
												)
											);
										}
										?>
                                    </div><?php
								}
								?>

							</div><?php
						}
						?>
					</div>

				</div>
				<?php
				global $course_pager_results;
				if ( isset( $course_pager_results['pager'] ) ) {
					echo SFWD_LMS::get_template(
						'learndash_pager.php',
						array(
							'pager_results' => $course_pager_results['pager'],
							'pager_context' => 'course_lessons'
						)
					);
				}
				?>
                <?php
			endif;

			if ( ! empty( $lessons ) ) {
				if ( ( isset( $course_pager_results['pager'] ) ) && ( !empty( $course_pager_results['pager'] ) ) ) {
					if ( $course_pager_results['pager']['paged'] == $course_pager_results['pager']['total_pages'] ) {
						$show_course_quizzes = true;
					} else {
						$show_course_quizzes = false;
					}
				} else {
					$show_course_quizzes = true;
				}
			} else {
				$show_course_quizzes = true;
			}
			/* Show Quiz List */
		if ( $show_course_quizzes == true ) {
			if (!empty($quizzes)) {
				?>
                <div id='learndash_quizzes'>
                    <div id="quiz_heading"><span><?php echo LearnDash_Custom_Label::get_label('quizzes') ?></span></div>
                    <div id="quiz_list">
						<?php foreach ($quizzes as $quiz) { ?>
                            <div id="post-<?php echo $quiz["post"]->ID; ?>" class="<?php echo $quiz["sample"]; ?>">
                                <h4>
                                    <a class="<?php echo $quiz["status"]; ?>"
                                       href="<?php echo esc_attr(learndash_get_step_permalink($quiz['post']->ID, $course_id)); ?>"><?php echo $quiz["post"]->post_title; ?></a>
                                </h4>
                            </div>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
		}
			?>

		</div><?php
	endif;
}
