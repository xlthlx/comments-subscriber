<?php
/**
 * Options page.
 *
 * @package comments_subscriber
 */

/**
 * Set up options page.
 *
 * @return void
 */
function cs_options_page() {
	$options = get_option( 'cs_options' );
	global $wpdb;
	$test = empty( $options['test'] ) ? '' : sanitize_text_field( $options['test'] );

	// Save the options.
	if ( ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'update-lstc-options' ) ) {
		$options      = stripslashes_deep( $_POST['options'] );
		$sane_options = cs_sanitize_settings( $options );
		update_option( 'cs_options', $sane_options );

		// Maybe send a test message, if requested.
		if ( isset( $_POST['savethankyou'] ) ) {
			if ( ! empty( $_POST['options']['test'] ) ) {
				$test = sanitize_email( $_POST['options']['test'] );
			}
			$ty_message            = empty( $options['ty_message'] ) ? '' : $options['ty_message'];
			$cs_data               = new stdClass();
			$cs_data->author       = __( 'Author', 'comments-subscriber' );
			$cs_data->link         = get_option( 'home' );
			$cs_data->comment_link = get_option( 'home' );
			$cs_data->title        = __( 'The post title', 'comments-subscriber' );
			$cs_data->content      = __( 'This is a long comment. Be a yardstick of quality. Some people are not used to an environment where excellence is expected.', 'comments-subscriber' );
			$message               = cs_replace( $ty_message, $cs_data );
			$subject               = $options['ty_subject'];
			$subject               = str_replace(
					array( '{title}', '{author}' ),
					array(
							$cs_data->title,
							$cs_data->author,
					),
					$subject
			);
			cs_mail( $test, $subject, $message );
		}
	}

	// Grab new values after "save and send test email".
	$options         = get_option( 'cs_options' );
	$unsubscribe_url = empty( $options['unsubscribe_url'] ) ? '' : sanitize_text_field( $options['unsubscribe_url'] );
	$length          = empty( $options['length'] ) ? '' : sanitize_text_field( $options['length'] );
	$test            = empty( $options['test'] ) ? '' : sanitize_text_field( $options['test'] );
	$copy            = empty( $options['copy'] ) ? '' : sanitize_text_field( $options['copy'] );
	$label           = empty( $options['label'] ) ? '' : sanitize_text_field( $options['label'] );
	$ty_message      = empty( $options['ty_message'] ) ? '' : sanitize_text_field( $options['ty_message'] );
	$ty_subject      = empty( $options['ty_subject'] ) ? '' : sanitize_text_field( $options['ty_subject'] );
	$thank_you       = empty( $options['thankyou'] ) ? '' : sanitize_text_field( $options['thankyou'] );

	// Removes a single email for all subscriptions.
	if ( isset( $_POST['remove_email'] ) ) {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'remove_email' ) ) {
			die( __( 'Security violated', 'comments-subscriber' ) );
		}
		$email = strtolower( sanitize_email( $_POST['email'] ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}comment_subscriber WHERE email=%s", $email ) );
	}

	if ( isset( $_POST['remove'] ) ) {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'remove' ) ) {
			die( __( 'Security violated', 'comments-subscriber' ) );
		}
		$id = implode( ',', $_POST['s'] );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}comment_subscriber WHERE id IN (%d)", $id ) );
	}
	?>
	<div class="wrap">
		<h1><?php _e( 'Comments Subscriber Settings', 'comments-subscriber' ); ?></h1>
		<form method="post">
			<h2><?php _e( 'Email Management', 'comments-subscriber' ); ?></h2>
			<p><?php _e( 'Remove a specific email from all subscriptions.', 'comments-subscriber' ); ?></p>
			<table class="form-table">
				<tr>
					<th><label for="email"><?php _e( 'Remove email', 'comments-subscriber' ); ?></label></th>
					<td><input type="text" id="email" name="email" size="30"/>
						<p><?php _e( 'Remove this email from all subscriptions.', 'comments-subscriber' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<?php wp_nonce_field( 'remove_email' ); ?>
				<input type="submit" name="remove_email" class="button-primary"
					   value="<?php _e( 'Remove', 'comments-subscriber' ); ?>"/>
			</p>
		</form>

		<hr/>

		<form method="post">
			<h2><?php _e( 'Subscription Checkbox Configuration', 'comments-subscriber' ); ?></h2>
			<p><?php _e( 'Options for the "Notify me" subscription checkbox in the comment form.', 'comments-subscriber' ); ?></p>
			<table class="form-table">
				<tr>
					<th>
						<label for="options[checkbox]"><?php _e( 'Enable The Checkbox', 'comments-subscriber' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="options[checkbox]" name="options[checkbox]"
							   value="1" <?php echo empty( $options['checkbox'] ) ? '' : 'checked'; ?> />
						<?php _e( 'Check this to add the "Notify me" subscription checkbox to the comment form.', 'comments-subscriber' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="options[label]"><?php _e( 'Checkbox Label', 'comments-subscriber' ); ?></label></th>
					<td>
						<input id="options[label]" name="options[label]" type="text" size="50"
							   value="<?php echo $label; ?>"/>
						<p><?php _e( 'Label to be displayed near the subscription checkbox.', 'comments-subscriber' ); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="options[checked]"><?php _e( 'Checkbox Default Status', 'comments-subscriber' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="options[checked]" name="options[checked]"
							   value="1" <?php echo isset( $options['checked'] ) ? 'checked' : ''; ?> />
						<?php _e( 'Check here if you want the "Notify me" subscription checkbox to be checked by default.', 'comments-subscriber' ); ?>
					</td>
				</tr>
			</table>

			<hr/>

			<h2><?php _e( 'Notification Email Settings', 'comments-subscriber' ); ?></h2>
			<p><?php _e( 'Configure the message sent to subscribers to notify them that a new comment was posted.', 'comments-subscriber' ); ?></p>

			<table class="form-table">
				<tr>
					<th><label for="options[name]"><?php _e( 'From Name', 'comments-subscriber' ); ?></label></th>
					<td>
						<input id="options[name]" name="options[name]" type="text" size="50"
							   value="<?php echo esc_html( $options['name'] ); ?>"/>
					</td>
				</tr>
				<tr>
					<th><label for="options[from]"><?php _e( 'From Email', 'comments-subscriber' ); ?></label></th>
					<td>
						<input name="options[from]" id="options[from]" type="text" size="50"
							   value="<?php echo esc_html( $options['from'] ); ?>"/>
					</td>
				</tr>
				<tr>
					<th><label for="ptions[subject]"><?php _e( 'Subject', 'comments-subscriber' ); ?></label></th>
					<td>
						<input name="options[subject]" id="ptions[subject]" type="text" size="70"
							   value="<?php echo esc_html( $options['subject'] ); ?>"/>
						<p>
							<?php
							/* translators: 1: Post title, 2: Subscriber name, 3: Commenter name, 4: Line break. */
							printf( __( 'Tags: %4$s %1$s - the post title %4$s %2$s - the subscriber name %4$s %3$s - the commenter name', 'comments-subscriber' ), '{title}', '{name}', '{author}', '<br />' );
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="options[message]"><?php _e( 'Message Body', 'comments-subscriber' ); ?></label></th>
					<td>
						<textarea name="options[message]" id="options[message]" rows="10" cols="70"><?php echo wp_unslash($options['message']); ?></textarea>
						<p>
							<?php
							/* translators: 1: Subscriber name, 2: Commenter name, 3: Post title, 4: Comment text, 5: Comment link, 6: Post link, 7: Line Break */
							printf( __( 'Tags: %8$s %1$s - the subscriber name %8$s %2$s - the commenter name %8$s %3$s - the post title %8$s %4$s - the comment text (eventually truncated) %8$s %5$s - link to the comment %8$s %6$s - link to the post/page %8$s %7$s - the unsubscribe link', 'comments-subscriber' ), '{name}', '{author}', '{title}', '{content}', '{comment_link}', '{link}', '{unsubscribe}', '<br />' );
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="options[length]"><?php _e( 'Comment Excerpt Length', 'comments-subscriber' ); ?></label>
					</th>
					<td>
						<input name="options[length]" id="options[length]" type="text" size="5" value="<?php echo $length; ?>"/>
						<?php _e( ' characters', 'comments-subscriber' ); ?>
						<p>
							<?php _e( 'The length of the comment excerpt to be inserted in the email notification. If blank, the default is 155 characters.', 'comments-subscriber' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<hr/>

			<h2><?php _e( 'Unsubscribe Settings', 'comments-subscriber' ); ?></h2>
			<p>
				<?php _e( 'Configure what to show to unsubscribing users. You may set an "Unsubscribe page URL" to send the user to a specific page, or configure a specific message.', 'comments-subscriber' ); ?>
			</p>
			<table class="form-table">
				<tr>
					<th>
						<label for="options[unsubscribe_url]"><?php _e( 'Unsubscribe Page URL', 'comments-subscriber' ); ?></label>
					</th>
					<td>
						<input name="options[unsubscribe_url]" id="options[unsubscribe_url]" type="text" size="50"
							   value="<?php echo $unsubscribe_url; ?>"/>
						<p>
						<?php _e( 'If you want to create a page with your content to say "ok, you are unsubscribed", enter the URL here. Otherwise, leave this field blank and the following message will be used.', 'comments-subscriber' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="thankyou"><?php _e( 'Unsubscribe Message', 'comments-subscriber' ); ?></label>
					</th>
					<td>
						<textarea name="options[thankyou]" id="options[thankyou]" rows="10" cols="70"><?php echo esc_textarea($thank_you); ?></textarea>
						<p>
						<?php _e( 'Example: You have unsubscribed successfully. Thank you. I will send you to the home page in 3 seconds.', 'comments-subscriber' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<hr/>

			<h2><?php _e( 'Thank You Message Settings', 'comments-subscriber' ); ?></h2>
			<p><?php _e( 'Configure a thank-you message for <strong>first time commentators</strong>. Messages are sent when comments are approved.', 'comments-subscriber' ); ?></p>

			<table class="form-table">
				<tr>
					<th><?php _e( 'Enable Thank You Message', 'comments-subscriber' ); ?></th>
					<td>
						<input type="checkbox" name="options[ty_enabled]" id="options[ty_enabled]"
							   value="1" <?php echo isset( $options['ty_enabled'] ) ? 'checked' : ''; ?> />
						<label for="options[ty_enabled]"><?php _e( 'Send a "Thank You" message sent to visitor on their first comment', 'comments-subscriber' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><label for="options[ty_subject]"><?php _e( 'Subject', 'comments-subscriber' ); ?></label></th>
					<td>
						<input name="options[ty_subject]" id="options[ty_subject]" type="text" size="70"
							   value="<?php echo $ty_subject; ?>"/>
						<p>
						<?php
						/* translators: 1: Post title, 2: Commenter name. */
						printf( __( 'Tags: %3$s %1$s - the post title %3$s %2$s - the commenter name', 'comments-subscriber' ), '{title}', '{author}', '<br />' );
						?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="options[ty_message]"><?php _e( 'Message Body', 'comments-subscriber' ); ?></label></th>
					<td>
						<textarea name="options[ty_message]" id="options[ty_message]" rows="10" cols="70"><?php echo esc_textarea($ty_message); ?></textarea>
						<p>
						<?php
						/* translators: 1: Post title, 2: Commenter name, 3: Post link, 4: Comment link, 5: Comment text. */
						printf( __( 'Tags: %5$s %1$s - the post title %5$s %2$s - the commenter name %5$s %3$s - link to the post/page %5$s %6$s - link to the comment %5$s %4$s - the comment text', 'comments-subscriber' ), '{title}', '{author}', '{link}', '{content}', '<br />', '{comment_link}' );
						?>
						</p>
					</td>
				</tr>
			</table>

			<hr/>

			<h2><?php _e( 'Theme Compatibility', 'comments-subscriber' ); ?></h2>
			<table class="form-table">
				<tr>
					<th>
						<?php _e( 'Show Checkbox After The Comment Form', 'comments-subscriber' ); ?>
					</th>
					<td>
						<input type="checkbox" name="options[theme_compat]" id="theme_compat"
							   value="1" <?php echo isset( $options['theme_compat'] ) ? 'checked' : ''; ?> />
						<label for="options[theme_compat]"><?php _e( 'If the checkbox is not appearing on your comment form, enable this option. <br/>Enabling this option will make the checkbox work on a larger variety of independent themes (themes that do not use standard WordPress comment form filters). <br/>This will add the checkbox <strong>below</strong> the comment form submit button.', 'comments-subscriber' ); ?></label>
					</td>
				</tr>
			</table>

			<hr/>

			<h2><?php _e( 'Advanced Settings', 'comments-subscriber' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="options[test]"><?php _e( 'Email address where to send test emails:', 'comments-subscriber' ); ?></label>
					</th>
					<td>
						<input id="options[test]" name="options[test]" type="text" size="50" value="<?php echo $test; ?>"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="options[copy]"><?php _e( 'Extra email address where to send a copy of EACH notification:', 'comments-subscriber' ); ?></label><br/><br/>
					</th>
					<td>
						<input id="options[copy]" name="options[copy]" type="text" size="50" value="<?php echo $copy; ?>"/>
						<p>
							<?php _e( 'Leave empty to disable.', 'comments-subscriber' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Delete Data on Uninstall', 'comments-subscriber' ); ?></th>
					<td>
						<input type="checkbox" id="options[delete_data]" name="options[delete_data]"
							   value="1" <?php echo isset( $options['delete_data'] ) ? 'checked' : ''; ?> />
						<label for="options[delete_data]"><?php _e( 'Check this box if you would like this plugin to <strong>delete all</strong> of its data when the plugin is deleted. This would delete the entire list of subscribers and their subscriptions. This does NOT delete the actual comments.', 'comments-subscriber' ); ?></label>
					</td>
				</tr>

			</table>
			<p class="submit">
				<?php wp_nonce_field( 'update-lstc-options' ); ?>
				<input class="button-primary" type="submit" name="save"
					   value="<?php _e( 'Save', 'comments-subscriber' ); ?>"/>
				<input class="button-secondary" type="submit" name="savethankyou"
					   value="<?php _e( 'Save and send a Thank You test email', 'comments-subscriber' ); ?>"/>
			</p>
		</form>

		<hr/>

		<form method="post">
			<?php wp_nonce_field( 'remove' ); ?>
			<h2><?php _e( 'Subscribers list', 'comments-subscriber' ); ?></h2>
			<ul style="list-style: square;padding-left:10px">
				<?php
				$list = $wpdb->get_results( "SELECT DISTINCT post_id, COUNT(post_id) AS total FROM {$wpdb->prefix}comment_subscriber WHERE post_id != 0 GROUP BY post_id ORDER BY total DESC" );
				if($list) {
					foreach ( $list as $r ) {
						$post_id = (int) $r->post_id;
						$total   = (int) $r->total;
						$post    = get_post( $post_id );
						echo '<li><a href="' . esc_url( get_permalink( $post_id ) ) . '" target="_blank">' .
							 esc_html( $post->post_title ) . '</a><br/>'.
							 __( 'Subscribers: ', 'comments-subscriber' ) . $total . '</li>';
						$list2 = $wpdb->get_results(
								$wpdb->prepare(
										"SELECT id,email,name FROM {$wpdb->prefix}comment_subscriber
                     WHERE post_id=%d",
										$post_id
								)
						);
						echo '<ul>';
						foreach ( $list2 as $r2 ) {
							echo '<li><input type="checkbox" name="s[]" value="' . esc_attr( $r2->id ) . '"/> ' . esc_html( $r2->email ) . '</li>';
						}
						echo '</ul>';
						echo '<input class="button-secondary" type="submit" name="remove" value="' . __( 'Remove', 'comments-subscriber' ) . '"/>';
					}
				}
				else {
					echo "<p>". __( 'There are no subscribers.', 'comments-subscriber' )."</p>";
				}
				?>
			</ul>
		</form>
	</div>
	<?php
}
