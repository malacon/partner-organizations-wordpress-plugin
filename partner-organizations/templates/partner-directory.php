<?php
/**
 * Partner Directory shortcode template.
 *
 * @package PartnerOrganizations
 *
 * @var WP_Query $partner_query Partner Organization query.
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="po-partner-directory" aria-label="<?php echo esc_attr__('Partner Directory', 'partner-organizations'); ?>">
    <?php if (! $partner_query->have_posts()) : ?>
        <p class="po-partner-directory__empty"><?php echo esc_html__('No partner organizations found.', 'partner-organizations'); ?></p>
    <?php else : ?>
        <div class="po-partner-directory__grid">
            <?php while ($partner_query->have_posts()) : ?>
                <?php
                $partner_query->the_post();
                $website_url = esc_url((string) get_post_meta(get_the_ID(), PartnerOrganizations\MetaBoxes::WEBSITE_META_KEY, true));
                $has_website_url = '' !== $website_url;
                ?>
                <article class="po-partner-directory__card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="po-partner-directory__logo-wrap">
                            <?php echo wp_kses_post(get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'po-partner-directory__logo'])); ?>
                        </div>
                    <?php endif; ?>

                    <h3 class="po-partner-directory__title">
                        <?php if ($has_website_url) : ?>
                            <a class="po-partner-directory__link" href="<?php echo esc_url($website_url); ?>">
                                <?php echo esc_html(get_the_title()); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html(get_the_title()); ?>
                        <?php endif; ?>
                    </h3>
                </article>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
