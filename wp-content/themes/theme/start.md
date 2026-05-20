Скопируй этот промпт целиком:

```txt
Создай production-ready starter theme для WordPress с современной архитектурой и Vite-сборкой.

Требования:
- Использовать современную архитектуру проекта
- PHP OOP
- Namespace для всех классов
- PSR-4 autoload
- Разделение логики по папкам и классам
- Минимум монолитных файлов
- Масштабируемая структура
- Чистая архитектура
- Подготовка под дальнейшую разработку

==================================================
VITE СБОРКА
==================================================

Использовать Vite вместо Gulp/Webpack.

Нужно настроить:

- SCSS compilation
- JS modules
- Hot reload
- Full reload для PHP файлов
- Sourcemaps
- Production build
- Минификацию CSS/JS
- Alias paths
- Watch mode

Использовать:
- vite
- sass
- vite-plugin-full-reload

Настроить:
- npm run dev
- npm run build

Vite должен работать с WordPress/PHP:
- JS/SCSS через HMR
- PHP через full reload

Сгенерировать:
- package.json
- vite.config.js

==================================================
СТРУКТУРА ПРОЕКТА
==================================================

Создать структуру:

theme/
├── assets/
│   ├── scss/
│   │   ├── abstracts/
│   │   │   ├── _variables.scss
│   │   │   ├── _mixins.scss
│   │   │   ├── _functions.scss
│   │   │   └── _reset.scss
│   │   ├── base/
│   │   ├── layouts/
│   │   ├── components/
│   │   ├── pages/
│   │   └── style.scss
│   │
│   ├── js/
│   │   ├── modules/
│   │   ├── components/
│   │   └── app.js
│   │
│   ├── images/
│   └── fonts/
│
├── dist/
│   ├── css/
│   ├── js/
│   └── assets/
│
├── inc/
│   ├── Core/
│   ├── Setup/
│   ├── Assets/
│   ├── Helpers/
│   ├── SEO/
│   ├── ACF/
│   ├── Ajax/
│   ├── API/
│   ├── Admin/
│   ├── Gutenberg/
│   ├── PostTypes/
│   ├── Taxonomies/
│   ├── VirtualPages/
│   └── Templates/
│
├── template-parts/
│   ├── components/
│   ├── sections/
│   ├── cards/
│   ├── modals/
│   └── forms/
│
├── page-templates/
├── virtual-pages/
├── languages/
├── vendor/
│
├── front-page.php
├── index.php
├── header.php
├── footer.php
├── functions.php
├── style.css
├── composer.json
├── package.json
└── vite.config.js

==================================================
FRONT PAGE
==================================================

Главная страница должна работать через:

front-page.php

Использовать компонентный подход.

Все секции подключать через:
get_template_part()

==================================================
КОМПОНЕНТЫ
==================================================

Создать систему компонентов:

template-parts/components/

Каждый компонент должен быть отдельным файлом - но сейчас сами компоненты не создаём.

Примеры:
- hero.php
- button.php
- modal.php
- form-contact.php
- card-service.php

==================================================
SCSS АРХИТЕКТУРА
==================================================

Использовать SCSS architecture.

Создать полноценный:

1. _reset.scss

Современный reset:
- box-sizing
- normalize/reset
- typography reset
- img/video/svg defaults
- form reset
- button reset
- list reset
- table reset
- prefers-reduced-motion
- smooth font rendering
- accessibility defaults
- hidden attributes
- dialog reset
- autofill reset
- mobile tap highlight reset

2. _variables.scss

Подготовить - только сам файл не нужно писать переменные:
- colors
- breakpoints
- typography
- spacing
- z-index
- transitions
- containers

3. _mixins.scss

Создать mixins:
- media queries
- hover
- container

Добавить функцию - должна быть возможность использовать в каждом файле scss без лишнего подключения (если это возможно):

@function rem($px) {
  @return calc($px / 16) * 1rem;
}

==================================================
JS
==================================================

Использовать модульную архитектуру JS.

Пример:

assets/js/
├── modules/
├── components/
└── app.js

В app.js подключать модули через import.

==================================================
PHP АРХИТЕКТУРА
==================================================

Создать bootstrap/init систему.

Примеры классов:

Theme\Core\Application
Theme\Setup\ThemeSetup
Theme\Assets\AssetsManager
Theme\SEO\SeoManager
Theme\ACF\AcfManager

Все классы должны подключаться автоматически через PSR-4 autoload.

==================================================
SEO
==================================================

Подготовить архитектуру для SEO - только архитектуру никакие файлы пока не создаём:

inc/SEO/

Добавить основу:
- meta title
- meta description
- Open Graph
- canonical
- robots
- schema.org foundation

==================================================
ACF
==================================================

Подготовить архитектуру для ACF - сами поля и тд пока не создавай, только функции для этого:

inc/ACF/

Добавить:
- регистрацию полей
- helper functions
- flexible content support
- options pages
- blocks support

==================================================
ВИРТУАЛЬНЫЕ СТРАНИЦЫ
==================================================

Подготовить:
- rewrite rules
- virtual pages architecture

Папки:
- virtual-pages/
- inc/VirtualPages/

==================================================
WORDPRESS SETUP
==================================================

Добавить:
- menus
- thumbnails
- theme supports
- cleanup wp head
- disable unnecessary wp features
- ajax architecture
- helper functions
- debug mode

==================================================
CODE STYLE
==================================================

Использовать:
- strict types
- typed properties
- PSR-12
- semantic naming
- escaping output
- WordPress security best practices

==================================================
СГЕНЕРИРОВАТЬ
==================================================

Сгенерируй:
1. Полную структуру проекта
2. Все базовые файлы
3. composer.json
4. package.json
5. vite.config.js
6. bootstrap/init систему
7. Примеры классов
8. SCSS starter architecture
9. reset.scss
10. variables.scss
11. mixins.scss
12. Базовые компоненты
13. Подключение assets в WordPress
14. Front-page architecture
15. Пример JS modules
16. Пример ACF architecture
17. Пример SEO architecture
18. Пример virtual pages architecture

Проект должен выглядеть как современная профессиональная WordPress starter theme для production-разработки.
```
