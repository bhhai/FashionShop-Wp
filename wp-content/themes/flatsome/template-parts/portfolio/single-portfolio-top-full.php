<?php get_template_part('template-parts/portfolio/portfolio-title', flatsome_option('portfolio_title')); ?>

<div class="portfolio-top">
	<div id="portfolio-content" role="main" class="page-wrapper">
		<div class="portfolio-inner">
			<?php get_template_part('template-parts/portfolio/portfolio-content'); ?>
		</div>
	</div>

	<div class="row">
	<div class="large-12 col">
		<div class="portfolio-summary entry-summary">
			<?php get_template_part('template-parts/portfolio/portfolio-summary','full'); ?>
		</div>
	</div>
	</div>
</div>

<div class="portfolio-bottom">
	<?php get_template_part('template-parts/portfolio/portfolio-next-prev'); ?>
	<?php get_template_part('template-parts/portfolio/portfolio-related'); ?>
</div>