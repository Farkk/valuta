# Asmart UI Components

> Высокопроизводительная библиотека UI-компонентов для WordPress тем

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Asmart UI Components** — современная библиотека UI-компонентов для WordPress, построенная по **component-driven** архитектуре. Предоставляет готовые к использованию компоненты для быстрой разработки самописных тем.

## Особенности

- ✨ **Component-driven архитектура** — чистое разделение логики и представления
- 🎯 **Типобезопасность** — PHP 8.1+ с строгой типизацией и Enums
- 🚀 **Высокая производительность** — in-memory кеширование, минимум WordPress hooks
- 🎨 **Современный дизайн** — готовые стили с поддержкой CSS Variables
- ♿ **Accessibility** — полная поддержка ARIA атрибутов и семантического HTML
- 📱 **Responsive** — адаптивный дизайн из коробки
- 🔧 **Гибкость** — переопределение шаблонов через тему
- 📦 **Нулевые зависимости** — Vanilla JS, без jQuery/React/Vue

## Требования

- PHP 8.1 или выше
- WordPress 6.0 или выше

## Установка

### Клонирование репозитория

```bash
cd wp-content/plugins/
git clone https://github.com/your-repo/asmart-ui-components.git asmart
```

### Активация

Перейдите в WordPress Admin → Plugins и активируйте **Asmart UI Components**.

Плагин готов к использованию сразу после клонирования — все необходимые файлы (включая скомпилированные assets) уже включены в репозиторий.

## Быстрый старт

### Button (Кнопка)

```php
use Asmart\Components\Button\Button;
use Asmart\Components\Button\ButtonVariant;

Button::render(
    label: 'Сохранить',
    variant: ButtonVariant::Primary,
);
```

### Modal (Модальное окно)

```php
use Asmart\Components\Modal\Modal;
use Asmart\Components\Modal\ModalTemplate;
use Asmart\Components\Modal\ModalSize;

Modal::render(
    id: 'success-modal',
    title: 'Успешно!',
    content: 'Данные сохранены.',
    template: ModalTemplate::Success,
    size: ModalSize::Medium,
);
```

### Card (Карточка)

```php
use Asmart\Components\Card\Card;
use Asmart\Components\Card\CardLayout;

Card::render(
    title: 'Заголовок статьи',
    excerpt: 'Краткое описание...',
    imageUrl: 'https://example.com/image.jpg',
    url: '/article-slug',
    layout: CardLayout::Grid,
    meta: [
        'date' => '29 января 2026',
        'author' => 'Иван Иванов',
    ],
);
```

### Pagination (Пагинация)

```php
use Asmart\Components\Pagination\Pagination;
use Asmart\Components\Pagination\PaginationType;

// Обычная пагинация
Pagination::render(
    currentPage: 2,
    totalPages: 10,
    baseUrl: '/blog/page/',
);

// AJAX пагинация
Pagination::render(
    currentPage: 1,
    totalPages: 10,
    baseUrl: '/blog/page/',
    type: PaginationType::Ajax,
    ajaxUrl: admin_url('admin-ajax.php'),
    ajaxAction: 'load_posts',
    containerId: 'posts-container',
);
```

## Доступные компоненты

| Компонент | Описание | Варианты |
|-----------|----------|----------|
| **Button** | Универсальная кнопка | Primary, Secondary, Danger, Ghost, Link |
| **Modal** | Модальное окно | Default, Success, Error, Form |
| **Card** | Карточка контента | Grid, List |
| **Pagination** | Навигация по страницам | Default, Ajax, LoadMore, Infinite |

## Архитектура

### Component-driven подход

Каждый компонент состоит из:

```
Component/
  ├── Component.php          # Основной класс с методом render()
  ├── ComponentProps.php     # Readonly DTO для параметров
  ├── ComponentEnum.php      # Enum для вариантов (опционально)
  └── templates/
      └── default.php        # PHP шаблон
```

### Типобезопасность

Все компоненты используют:
- **Named arguments** для читаемости
- **Enums** вместо magic strings
- **Readonly Props** для immutability
- **Строгую типизацию** PHP 8.1+

### Производительность

- In-memory кеширование путей к шаблонам
- Lazy loading компонентов через Composer autoloader
- Минимальное количество WordPress hooks
- Хешированные имена файлов для cache busting

## Переопределение шаблонов

Вы можете переопределить любой шаблон в своей теме:

```
your-theme/
└── asmart-components/
    ├── button/
    │   └── default.php
    ├── modal/
    │   └── success.php
    └── card/
        └── grid.php
```

Плагин автоматически использует шаблон из темы, если он существует.

## Кастомизация стилей

### Через CSS Variables

```css
:root {
    --asmart-color-primary-500: #your-color;
    --asmart-radius-md: 8px;
    --asmart-spacing-400: 20px;
}
```

### Через SCSS

Переопределите переменные в `_variables.scss` вашей темы и импортируйте перед использованием компонентов.

## JavaScript API

### Modal

```javascript
// Открыть модалку
AsmartModal.open('modal-id');

// Закрыть модалку
AsmartModal.close('modal-id');

// Callbacks
AsmartModal.on('modal-id', 'onOpen', (data) => {
    console.log('Modal opened', data);
});

// Ожидание закрытия (Promise)
const result = await AsmartModal.waitForClose('modal-id');
```

### Pagination

```javascript
// Получить инстанс пагинации
const pagination = AsmartPagination.getInstance('element-id');

// Загрузить страницу программно
AsmartPagination.loadPage('element-id', 3, false);

// Callbacks
AsmartPagination.on('element-id', 'onLoad', (data) => {
    console.log('Page loaded', data);
});
```

## Структура проекта

```
asmart/
├── src/
│   ├── Components/      # UI компоненты
│   ├── Core/            # Ядро плагина
│   ├── Contracts/       # Интерфейсы
│   └── Support/         # Вспомогательные классы
├── templates/           # PHP шаблоны
├── assets/
│   ├── src/            # Исходники (SCSS, JS)
│   └── dist/           # Скомпилированные файлы (готовые к использованию)
└── vendor/             # Composer зависимости (включены в репозиторий)
```

## Разработка

Если вы хотите вносить изменения в плагин:

### Требования для разработки

- Node.js 20+
- Composer

### Сборка assets

```bash
# Установка зависимостей (только для разработки)
npm install

# Режим разработки с watch
npm run dev

# Production сборка
npm run build
```

**Важно:** После внесения изменений в SCSS/JS запустите `npm run build` и закоммитьте обновлённые файлы в `assets/dist/`.

## Примеры использования

### WordPress Post Card

```php
$post = get_post();

ob_start();
Button::render(
    label: 'Читать полностью',
    url: get_permalink($post),
);
$buttonHtml = ob_get_clean();

Card::render(
    title: get_the_title($post),
    excerpt: get_the_excerpt($post),
    imageUrl: get_the_post_thumbnail_url($post, 'medium'),
    url: get_permalink($post),
    meta: [
        'date' => get_the_date('j F Y', $post),
        'author' => get_the_author_meta('display_name', $post->post_author),
    ],
    buttonHtml: $buttonHtml,
);
```

### Infinite Scroll для постов

```php
// В шаблоне темы
<div id="posts-container">
    <?php
    while (have_posts()) {
        the_post();
        Card::render(
            title: get_the_title(),
            excerpt: get_the_excerpt(),
            imageUrl: get_the_post_thumbnail_url(get_the_ID(), 'medium'),
            url: get_permalink(),
        );
    }
    ?>
</div>

<?php
Pagination::render(
    currentPage: max(1, get_query_var('paged')),
    totalPages: $wp_query->max_num_pages,
    baseUrl: get_pagenum_link(1),
    type: PaginationType::Infinite,
    ajaxUrl: admin_url('admin-ajax.php'),
    ajaxAction: 'load_more_posts',
    containerId: 'posts-container',
);
?>
```

## Лицензия

MIT License. См. [LICENSE](LICENSE) для деталей.

## Поддержка

- 📖 [Документация](docs/architecture.md)
- 🐛 [Сообщить о баге](https://github.com/your-repo/asmart-ui-components/issues)
- 💡 [Предложить улучшение](https://github.com/your-repo/asmart-ui-components/issues)

## Changelog

### v1.0.0 (2026-01-29)

#### Добавлено
- ✨ Button компонент с 5 вариантами
- ✨ Modal компонент с 4 шаблонами
- ✨ Card компонент с 2 layout вариантами
- ✨ Pagination компонент с 4 типами (Default, Ajax, LoadMore, Infinite)
- 🎨 SCSS стили с CSS Variables
- 📱 Responsive дизайн
- ♿ Accessibility поддержка
- 🚀 Vite 6 для сборки assets
- 📦 Vanilla JS API для Modal и Pagination

---

Made with ❤️ for WordPress developers
