<?php
/**
 * Displays course progress rows for a user
 *
 * Available:
 * $courses_registered: course
 * $course_progress: Progress in courses
 *
 * @since 2.5.5
 *
 * @package LearnDash\Course
 */

foreach ( $course_progress as $course_id => $coursep ) {

	$course = get_post( $course_id );
	if ( ( !( $course instanceof WP_Post ) ) || ( $course->post_type != 'sfwd-courses' ) || ( empty( $course->post_title ) ) )
		continue;

	?>
	<div class='ld-course-info-my-courses'>
		<div class="ld-course-img">
			<?php
			if ( has_post_thumbnail( $course_id )) {
				echo get_the_post_thumbnail( $course_id );
			} else {
				?>
				<img src="http://placehold.it/360x250&text=Course" alt="" />
				<?php
			} ?>
		</div>
		<div class="ld-course-info">
			<h2 class="ld-entry-title entry-title"><a href="<?php echo get_permalink( $course_id ) ?>"  rel="bookmark"><?php echo get_the_title( $course_id ) ?></a></h2>
			<?php
			$course_status = learndash_course_status( $course_id, $user_id );
			?> <span class="leardash-course-status leardash-course-status-<?php echo sanitize_title_with_dashes($course_status) ?>"><?php echo $course_status ?></span>
			<?php

			$course_steps_count = learndash_get_course_steps_count( $course_id );
			$course_steps_completed = learndash_course_get_completed_steps( $user_id, $course_id, $coursep );

			$completed_on = get_user_meta( $user_id, 'course_completed_' . $course_id, true );
			if ( !empty( $completed_on ) ) {

				$coursep['completed'] = $course_steps_count;
				$coursep['total'] = $course_steps_count;

			} else {
				$coursep['total'] = $course_steps_count;
				$coursep['completed'] = $course_steps_completed;

				if ( $coursep['completed'] > $coursep['total'] )
					$coursep['completed'] = $coursep['total'];
			}
			?>
			<span class="learndash-course-progress"><?php echo $coursep['completed'] ?>/<?php echo $coursep['total'] ?></span>
		</div>
	</div>
	<?php
}