<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Asmart\Components\Modal\Modal;
use Asmart\Components\Modal\ModalTemplate;
use Theme\Cities\CityManager;

$citiesGrouped = CityManager::getCitiesGrouped();
$currentCity = CityManager::getCurrentCity();

ob_start();
?>
<div class="modal-city">
    <div class="modal-city__search">
        <input
            type="text"
            class="modal-city__search-input"
            placeholder="<?php esc_attr_e('Введите название города', 'theme'); ?>"
            id="city-search-input"
            data-city-search
        >
        <svg class="modal-city__search-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.78928 0C3.03966 0 0 3.05013 0 6.81267C0 10.5752 3.03966 13.6253 6.78928 13.6253C10.5389 13.6253 13.5786 10.5752 13.5786 6.81267C13.5786 3.05013 10.5389 0 6.78928 0ZM15.7202 15.7071C15.3424 16.0928 14.721 16.0983 14.3365 15.7192L11.6227 13.0434C12.139 12.6395 12.6036 12.1724 13.0054 11.6538L15.7081 14.3187C16.0925 14.6977 16.098 15.3213 15.7202 15.7071ZM6.78928 1.89682C9.49491 1.89682 11.6882 4.09772 11.6882 6.81264C11.6882 9.52755 9.49488 11.7284 6.78928 11.7284C4.08366 11.7284 1.89034 9.52755 1.89034 6.81264C1.89034 4.09772 4.08369 1.89682 6.78928 1.89682Z" fill="currentColor" />
        </svg>
    </div>

    <div class="modal-city__empty" data-city-empty>
        <?php esc_html_e('Город не найден', 'theme'); ?>
    </div>

    <div class="modal-city__list" data-city-list>
        <?php foreach ($citiesGrouped as $letter => $cityList) : ?>
            <div class="modal-city__group" data-city-group>
                <h3 class="modal-city__letter"><?php echo esc_html($letter); ?></h3>
                <div class="modal-city__items">
                    <?php foreach ($cityList as $city) : ?>
                        <?php $isActive = $city['slug'] === $currentCity['slug']; ?>
                        <a
                            class="modal-city__item<?php echo $isActive ? ' is-active' : ''; ?>"
                            href="<?php echo esc_url(CityManager::getCityUrl($city['slug'])); ?>"
                            data-city-item
                            data-city-name="<?php echo esc_attr($city['city_name']); ?>"
                            data-city-slug="<?php echo esc_attr($city['slug']); ?>"
                        >
                            <?php echo esc_html($city['city_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
$content = ob_get_clean();

Modal::render(
    title: __('Выберите <span>город</span>', 'theme'),
    content: $content,
    template: ModalTemplate::Default,
    id: 'city-modal'
);
