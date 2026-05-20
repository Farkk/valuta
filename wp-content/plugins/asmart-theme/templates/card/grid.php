<?php
/**
 * Card Template - Grid Layout
 *
 * Вертикальная карточка для сеточного отображения.
 *
 * @var \Asmart\Components\Card\CardProps $props
 */

declare(strict_types=1);

use Asmart\Components\Card\CardProps;

if (!isset($props) || !($props instanceof CardProps)) {
    return;
}

// Формирование атрибутов
$attributes = '';
foreach ($props->attributes as $key => $value) {
    $attributes .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
}
?>

<article class="<?php echo esc_attr($props->getCssClasses()); ?>"<?php echo $attributes; ?>>
    <?php if ($props->hasImage()): ?>
        <div class="asmart-card__image-wrapper">
            <?php if ($props->hasUrl()): ?>
                <a href="<?php echo esc_url($props->url); ?>" class="asmart-card__image-link" aria-label="<?php echo esc_attr($props->title); ?>">
            <?php endif; ?>

            <img
                src="<?php echo esc_url($props->imageUrl); ?>"
                alt="<?php echo esc_attr($props->imageAlt ?? $props->title); ?>"
                class="asmart-card__image"
                loading="<?php echo esc_attr($props->getImageLoadingAttr()); ?>"
            />

            <?php if ($props->hasUrl()): ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="asmart-card__content">
        <header class="asmart-card__header">
            <?php if ($props->hasUrl()): ?>
                <h3 class="asmart-card__title">
                    <a href="<?php echo esc_url($props->url); ?>" class="asmart-card__title-link">
                        <?php echo esc_html($props->title); ?>
                    </a>
                </h3>
            <?php else: ?>
                <h3 class="asmart-card__title">
                    <?php echo esc_html($props->title); ?>
                </h3>
            <?php endif; ?>
        </header>

        <?php if ($props->hasMeta()): ?>
            <div class="asmart-card__meta">
                <?php foreach ($props->meta as $key => $value): ?>
                    <span class="asmart-card__meta-item asmart-card__meta-item--<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($value); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($props->hasExcerpt()): ?>
            <div class="asmart-card__excerpt">
                <?php echo wp_kses_post($props->excerpt); ?>
            </div>
        <?php endif; ?>

        <?php if ($props->hasButton()): ?>
            <footer class="asmart-card__footer">
                <?php echo $props->buttonHtml; // Уже должен быть escaped при создании ?>
            </footer>
        <?php endif; ?>
    </div>
</article>
