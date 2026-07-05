<?php
defined( 'ABSPATH' ) || exit;

/** @var \WP_Query $query */
?>
<div class="spa-posts">
	<?php if ( ! $query->have_posts() ) : ?>
		<p class="spa-posts-empty"><?php esc_html_e( 'No aggregated content yet.', 'smart-post-aggregator' ); ?></p>
	<?php else : ?>
		<ul class="spa-posts-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				?>
				<li class="spa-posts-item">
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="spa-posts-thumbnail">
							<?php the_post_thumbnail( 'thumbnail' ); ?>
						</a>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>" class="spa-posts-title"><?php the_title(); ?></a>
					<div class="spa-posts-excerpt">
						<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_content() ), 30 ) ); ?>
					</div>
				</li>
			<?php endwhile; ?>
		</ul>
	<?php endif; ?>
</div>
<?php wp_reset_postdata(); ?>
