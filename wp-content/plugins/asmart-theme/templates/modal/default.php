<?php
/**
 * Modal Template - Default
 *
 * @var \Asmart\Components\Modal\ModalProps $props
 */

declare(strict_types=1);

use Asmart\Components\Modal\ModalProps;

if (!isset($props) || !($props instanceof ModalProps)) {
    return;
}

$classString = esc_attr(implode(' ', $props->getClasses()));
?>

<div id="<?php echo esc_attr($props->id); ?>" class="<?php echo $classString; ?>" data-modal="true" <?php foreach ($props->attributes as $attr => $value): ?><?php echo esc_attr($attr); ?>="<?php echo esc_attr($value); ?>" <?php endforeach; ?>>
    <div class="asmart-modal__overlay" data-modal-close="true"></div>
    <div class="asmart-modal__container">
        <div class="asmart-modal__content">
            <div class="asmart-modal__header">
                <h2 class="asmart-modal__title"><?php echo esc_html($props->title); ?></h2>
                <?php if ($props->closeable): ?>
                    <button type="button" class="asmart-modal__close" data-modal-close="true" aria-label="<?php esc_attr_e('Close', 'asmart'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                <?php endif; ?>
            </div>
            <div class="asmart-modal__body">
                <?php echo wp_kses_post($props->content); ?>
            </div>
            <?php if ($props->footer !== null): ?>
                <div class="asmart-modal__footer">
                    <?php echo wp_kses_post($props->footer); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
