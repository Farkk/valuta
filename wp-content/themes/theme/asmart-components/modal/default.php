<?php

/**
 * Modal Template - Default
 *
 * @var \Asmart\Components\Modal\ModalProps $props
 */

declare(strict_types=1);

use Asmart\Components\Modal\ModalProps;

if (! isset($props) || ! ($props instanceof ModalProps)) {
    return;
}

$classString = esc_attr(implode(' ', $props->getClasses()));
?>

<div id="<?php echo esc_attr($props->id); ?>" class="<?php echo $classString; ?>" data-modal="true" <?php foreach ($props->attributes as $attr => $value) : ?><?php echo esc_attr($attr); ?>="<?php echo esc_attr($value); ?>" <?php endforeach; ?>>
    <div class="asmart-modal__overlay" data-modal-close="true"></div>
    <div class="asmart-modal__container">
        <div class="asmart-modal__content">
            <div class="asmart-modal__header">
                <h2 class="asmart-modal__title"><?php echo wp_kses_post($props->title); ?></h2>
                <?php if ($props->closeable) : ?>
                    <button type="button" class="asmart-modal__close" data-modal-close="true" aria-label="<?php esc_attr_e('Закрыть', 'theme'); ?>">
                        <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M1.43848 14.9921C1.06644 15.0137 0.700553 14.8901 0.417975 14.6471C-0.139325 14.0865 -0.139325 13.1811 0.417975 12.6205L12.621 0.417396C13.2007 -0.124994 14.1102 -0.0948424 14.6526 0.484799C15.1431 1.00897 15.1717 1.81462 14.7195 2.37221L2.44459 14.6471C2.16565 14.8866 1.80564 15.01 1.43848 14.9921Z" fill="currentColor" />
                            <path d="M13.6271 14.9922C13.2501 14.9906 12.8887 14.841 12.621 14.5754L0.417882 2.37229C-0.0984271 1.76936 -0.0282321 0.861983 0.574697 0.345626C1.11283 -0.115209 1.90646 -0.115209 2.44454 0.345626L14.7195 12.5487C15.299 13.0912 15.3289 14.0008 14.7864 14.5803C14.7648 14.6034 14.7425 14.6257 14.7195 14.6473C14.4189 14.9086 14.0234 15.0336 13.6271 14.9922Z" fill="currentColor" />
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
            <div class="asmart-modal__body">
                <?php echo $props->content; ?>
            </div>
            <?php if ($props->footer !== null) : ?>
                <div class="asmart-modal__footer">
                    <?php echo wp_kses_post($props->footer); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
