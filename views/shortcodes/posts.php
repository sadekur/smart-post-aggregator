<?php
defined( 'ABSPATH' ) || exit;

/** @var \WP_Query $query */
?>
<div class="spa-posts">
	<?php if ( ! $query->have_posts() ) : ?>
		<div class="flex flex-col items-center justify-center text-center py-16 px-6 rounded-2xl bg-slate-50 border border-slate-100">
			<span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white text-slate-300 shadow-sm mb-4">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
					<rect x="3" y="10" width="18" height="10" rx="2" />
					<polyline points="3 10 8 10 9.5 13 14.5 13 16 10 21 10" />
				</svg>
			</span>
			<p class="font-medium text-slate-600 spa-posts-empty"><?php esc_html_e( 'No aggregated content yet.', 'smart-post-aggregator' ); ?></p>
		</div>
	<?php else : ?>
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 spa-posts-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				?>
				<article class="spa-posts-item group flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
					<?php $spa_thumbnail_url = \SmartPostAggregator\Helpers\Utility::get_thumbnail_url( get_the_ID() ); ?>
					<a href="<?php the_permalink(); ?>" class="spa-posts-thumbnail block aspect-[16/9] overflow-hidden bg-slate-100" tabindex="-1">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php
							the_post_thumbnail(
								'medium_large',
								array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300' )
							);
							?>
						<?php elseif ( $spa_thumbnail_url ) : ?>
							<img
								src="<?php echo esc_url( $spa_thumbnail_url ); ?>"
								alt=""
								loading="lazy"
								class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
							/>
						<?php else : ?>
							<div class="w-full h-full flex items-center justify-center text-slate-300">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10">
									<rect x="3" y="4" width="18" height="16" rx="2" />
									<circle cx="9" cy="10" r="1.75" />
									<polyline points="4 18 9.5 12.5 13 16 16.5 12.5 20 16" />
								</svg>
							</div>
						<?php endif; ?>
					</a>

					<div class="flex flex-col flex-1 p-5">
						<time class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-2" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>

						<h3 class="spa-posts-title text-base font-bold text-slate-900 leading-snug mb-2 line-clamp-2">
							<a href="<?php the_permalink(); ?>" class="hover:text-indigo-600 transition-colors">
								<?php the_title(); ?>
							</a>
						</h3>

						<div class="spa-posts-excerpt text-sm text-slate-500 leading-relaxed line-clamp-3 mb-4 flex-1">
							<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_content() ), 30 ) ); ?>
						</div>

						<a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-700 mt-auto">
							<?php esc_html_e( 'Read more', 'smart-post-aggregator' ); ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform group-hover:translate-x-0.5">
								<line x1="5" y1="12" x2="19" y2="12" />
								<polyline points="12 5 19 12 12 19" />
							</svg>
						</a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
</div>
<?php wp_reset_postdata(); ?>
