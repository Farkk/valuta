<?php
/**
 * Button Template - Default
 *
 * @var \Asmart\Components\Button\ButtonProps $props
 */

declare(strict_types=1);

use Asmart\Components\Button\ButtonProps;

if (!isset($props) || !($props instanceof ButtonProps)) {
  return;
}

$tag = $props->getTagName();
$classes = ['asmart-button', 'asmart-button--' . $props->variant->value];

if ($props->disabled) {
  $classes[] = 'asmart-button--disabled';
}

if ($props->icon !== null) {
  $classes[] = 'asmart-button--with-icon';
}


if ($props->customClass !== null) {
  $classes[] = $props->customClass;
}

$classString = esc_attr(implode(' ', $classes));
?>

<<?php echo $tag; ?>
    class="<?php echo $classString; ?>"
    <?php if ($tag === 'button'): ?>
        type="<?php echo esc_attr($props->type ?? 'button'); ?>"
        <?php if ($props->disabled): ?>disabled<?php endif; ?>
    <?php endif; ?>
    <?php if ($tag === 'a' && $props->url !== null): ?>
        href="<?php echo esc_url($props->url); ?>"
        <?php if ($props->disabled): ?>aria-disabled="true"<?php endif; ?>
    <?php endif; ?>
    <?php if ($props->ariaLabel !== null): ?>
        aria-label="<?php echo esc_attr($props->ariaLabel); ?>"
    <?php endif; ?>
    <?php if ($props->modalTarget !== null): ?>
        data-modal-target="<?php echo esc_attr($props->modalTarget); ?>"
    <?php endif; ?>
    <?php foreach ($props->attributes as $attr => $value): ?>
        <?php echo esc_attr($attr); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if ($props->icon !== null): ?>
        <span class="asmart-button__icon"><?php echo wp_kses_post(
          $props->icon,
        ); ?></span>
    <?php endif; ?>
    <span class="asmart-button__label"><?php echo esc_html(
      $props->label,
    ); ?></span>
</<?php echo $tag; ?>>
