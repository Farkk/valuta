<?php

declare(strict_types=1);

namespace Theme\SEO;

use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

final class SeoImportAdmin
{
    private const PAGE_SLUG = 'theme-seo-import';
    private const IMPORT_NONCE_ACTION = 'theme_seo_import';
    private const IMPORT_NONCE_NAME = 'theme_seo_import_nonce';
    private const SITEMAP_NONCE_ACTION = 'theme_seo_sitemap';
    private const SITEMAP_NONCE_NAME = 'theme_seo_sitemap_nonce';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'registerPage']);
    }

    public static function registerPage(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . SeoPageManager::POST_TYPE,
            'Excel и Sitemap',
            'Excel / Sitemap',
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die('Недостаточно прав.');
        }

        $activeTab = self::getActiveTab();
        $notice = null;
        $sitemapText = null;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            if ($activeTab === 'sitemap') {
                $handled = self::handleSitemapRequest();
                $notice = $handled['notice'] ?? null;
                $sitemapText = $handled['sitemap_text'] ?? null;
            } else {
                $notice = self::handleUpload();
            }
        }

        echo '<div class="wrap">';
        echo '<h1>Excel и Sitemap</h1>';
        self::renderTabs($activeTab);

        if ($notice !== null) {
            if ($activeTab === 'sitemap') {
                self::renderAdminNotice($notice);
            } else {
                self::renderImportResult($notice);
            }
        }

        if ($activeTab === 'sitemap') {
            self::renderSitemapTab($sitemapText);
        } else {
            self::renderImportTab();
        }

        echo '</div>';
    }

    private static function getActiveTab(): string
    {
        $tab = sanitize_key((string) ($_GET['tab'] ?? 'import'));

        return in_array($tab, ['import', 'sitemap'], true) ? $tab : 'import';
    }

    private static function renderTabs(string $activeTab): void
    {
        $baseUrl = admin_url('edit.php?post_type=' . SeoPageManager::POST_TYPE . '&page=' . self::PAGE_SLUG);
        $tabs = [
            'import' => 'Импорт Excel',
            'sitemap' => 'Sitemap',
        ];

        echo '<nav class="nav-tab-wrapper">';

        foreach ($tabs as $tabKey => $tabLabel) {
            $class = $activeTab === $tabKey ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url = $baseUrl . '&tab=' . $tabKey;
            echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">' . esc_html($tabLabel) . '</a>';
        }

        echo '</nav>';
    }

    private static function renderImportTab(): void
    {
        echo '<p>Файл должен содержать колонки: A тип, B запрос, C URL, D title, E description, F H1, G H2, H SEO text. Опционально можно добавить колонку I для H2 Bottom.</p>';
        echo '<form method="post" enctype="multipart/form-data">';
        wp_nonce_field(self::IMPORT_NONCE_ACTION, self::IMPORT_NONCE_NAME);
        echo '<table class="form-table" role="presentation"><tbody><tr>';
        echo '<th scope="row"><label for="theme_seo_file">Excel/CSV файл</label></th>';
        echo '<td><input id="theme_seo_file" name="theme_seo_file" type="file" accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required></td>';
        echo '</tr></tbody></table>';
        submit_button('Загрузить и импортировать');
        echo '</form>';
    }

    private static function renderSitemapTab(?string $sitemapText = null): void
    {
        $settings = SeoSitemapManager::getSettings();

        if ($sitemapText === null) {
            $sitemapText = SeoSitemapManager::getSavedText();
        }

        $urlCount = SeoSitemapManager::countUrlsInText($sitemapText);
        $updatedAt = SeoSitemapManager::getSavedUpdatedAt();

        echo '<p>Sitemap собирается на основе всех доступных URL сайта, включая виртуальные страницы темы. После генерации список можно вручную редактировать.</p>';
        echo '<p><strong>Публичный адрес:</strong> <a href="' . esc_url(SeoSitemapManager::getPublicUrl()) . '" target="_blank" rel="noopener">' . esc_html(SeoSitemapManager::getPublicUrl()) . '</a></p>';

        if ($updatedAt > 0) {
            echo '<p><strong>Последнее сохранение:</strong> ' . esc_html(wp_date('d.m.Y H:i:s', $updatedAt)) . ' · <strong>URL:</strong> ' . (int) $urlCount . '</p>';
        } elseif ($urlCount > 0) {
            echo '<p><strong>Текущий список URL:</strong> ' . (int) $urlCount . '</p>';
        } else {
            echo '<p>Список ещё не сохранён. Нажмите «Сгенерировать из текущих страниц», чтобы создать sitemap.</p>';
        }

        echo '<form method="post">';
        wp_nonce_field(self::SITEMAP_NONCE_ACTION, self::SITEMAP_NONCE_NAME);
        echo '<table class="form-table" role="presentation"><tbody>';
        echo '<tr>';
        echo '<th scope="row">Исключения</th>';
        echo '<td>';
        self::renderCheckboxList(
            'Исключить single-страницы типов записей',
            'theme_seo_sitemap_settings[exclude_post_types]',
            SeoSitemapManager::getPostTypeChoices(),
            $settings['exclude_post_types']
        );
        self::renderCheckboxList(
            'Исключить архивы типов записей',
            'theme_seo_sitemap_settings[exclude_post_type_archives]',
            SeoSitemapManager::getPostTypeArchiveChoices(),
            $settings['exclude_post_type_archives']
        );
        self::renderCheckboxList(
            'Исключить таксономии',
            'theme_seo_sitemap_settings[exclude_taxonomies]',
            SeoSitemapManager::getTaxonomyChoices(),
            $settings['exclude_taxonomies']
        );
        self::renderCheckboxList(
            'Исключить виртуальные страницы',
            'theme_seo_sitemap_settings[exclude_virtual_sources]',
            SeoSitemapManager::getVirtualSourceChoices(),
            $settings['exclude_virtual_sources']
        );

        echo '<fieldset style="margin:16px 0 0;">';
        echo '<label style="display:block;margin-bottom:8px;"><input type="checkbox" name="theme_seo_sitemap_settings[exclude_homepage]" value="1"' . checked(! empty($settings['exclude_homepage']), true, false) . '> Исключить главную страницу</label>';
        echo '<label style="display:block;"><input type="checkbox" name="theme_seo_sitemap_settings[exclude_seo_pages]" value="1"' . checked(! empty($settings['exclude_seo_pages']), true, false) . '> Исключить SEO-страницы из `seo_page`</label>';
        echo '</fieldset>';

        echo '<p style="margin:16px 0 6px;"><strong>Правила исключения по URL</strong></p>';
        echo '<textarea name="theme_seo_sitemap_settings[excluded_url_rules]" rows="6" class="large-text code" spellcheck="false">' . esc_textarea((string) ($settings['excluded_url_rules'] ?? '')) . '</textarea>';
        echo '<p class="description">Одна строка = одно правило. Для точного URL: <code>/reviews/</code>. Для префикса: <code>/reviews/*</code> или <code>/city/*</code>.</p>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="theme_seo_sitemap_text">Содержимое sitemap</label></th>';
        echo '<td>';
        echo '<textarea id="theme_seo_sitemap_text" name="theme_seo_sitemap_text" rows="24" class="large-text code" spellcheck="false">' . esc_textarea($sitemapText) . '</textarea>';
        echo '<p class="description">Одна строка = один URL. Строки, начинающиеся с <code>#</code>, считаются комментариями и не попадают в XML.</p>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody></table>';

        submit_button('Сгенерировать из текущих страниц', 'secondary', 'theme_seo_generate_sitemap', false);
        echo '&nbsp;';
        submit_button('Сохранить sitemap', 'primary', 'theme_seo_save_sitemap', false);
        echo '<p class="description" style="margin-top:8px;">Чтобы применить новые исключения к автоматически собранному sitemap, нажмите «Сгенерировать из текущих страниц».</p>';
        echo '</form>';
    }

    /**
     * @param array{type?: string, message?: string} $notice
     */
    private static function renderAdminNotice(array $notice): void
    {
        $type = sanitize_key((string) ($notice['type'] ?? 'success'));
        $allowedTypes = ['success', 'warning', 'error', 'info'];
        $type = in_array($type, $allowedTypes, true) ? $type : 'success';

        echo '<div class="notice notice-' . esc_attr($type) . '"><p>' . esc_html((string) ($notice['message'] ?? '')) . '</p></div>';
    }

    /**
     * @param array<string, string> $choices
     * @param list<string> $selected
     */
    private static function renderCheckboxList(string $title, string $name, array $choices, array $selected): void
    {
        if ($choices === []) {
            return;
        }

        echo '<fieldset style="margin:0 0 16px;">';
        echo '<p style="margin:0 0 8px;"><strong>' . esc_html($title) . '</strong></p>';

        foreach ($choices as $value => $label) {
            echo '<label style="display:block;margin-bottom:6px;"><input type="checkbox" name="' . esc_attr($name) . '[]" value="' . esc_attr($value) . '"' . checked(in_array($value, $selected, true), true, false) . '> ' . esc_html($label) . '</label>';
        }

        echo '</fieldset>';
    }

    /**
     * @param array{created: int, updated: int, skipped: int, errors: list<string>} $result
     */
    private static function renderImportResult(array $result): void
    {
        $class = $result['errors'] === [] ? 'notice notice-success' : 'notice notice-warning';

        echo '<div class="' . esc_attr($class) . '"><p>';
        echo 'Создано: ' . (int) $result['created'] . '. ';
        echo 'Обновлено: ' . (int) $result['updated'] . '. ';
        echo 'Пропущено: ' . (int) $result['skipped'] . '.';
        echo '</p></div>';

        if ($result['errors'] !== []) {
            echo '<div class="notice notice-error"><p><strong>Ошибки:</strong></p><ul>';

            foreach ($result['errors'] as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }

            echo '</ul></div>';
        }
    }

    /**
     * @return array{notice?: array{type: string, message: string}, sitemap_text?: string}
     */
    private static function handleSitemapRequest(): array
    {
        if (
            empty($_POST[self::SITEMAP_NONCE_NAME])
            || ! wp_verify_nonce((string) $_POST[self::SITEMAP_NONCE_NAME], self::SITEMAP_NONCE_ACTION)
        ) {
            return [
                'notice' => [
                    'type' => 'error',
                    'message' => 'Не удалось проверить nonce. Повторите действие.',
                ],
                'sitemap_text' => SeoSitemapManager::getSavedText(),
            ];
        }

        SeoSitemapManager::saveSettings($_POST['theme_seo_sitemap_settings'] ?? []);

        if (isset($_POST['theme_seo_generate_sitemap'])) {
            $saved = SeoSitemapManager::generateAndSave();

            return [
                'notice' => [
                    'type' => 'success',
                    'message' => 'Sitemap сгенерирован по текущим настройкам и сохранён. URL: ' . (int) $saved['count'] . '.',
                ],
                'sitemap_text' => $saved['text'],
            ];
        }

        if (isset($_POST['theme_seo_save_sitemap'])) {
            $rawText = (string) ($_POST['theme_seo_sitemap_text'] ?? '');
            $saved = SeoSitemapManager::saveText($rawText);

            return [
                'notice' => [
                    'type' => 'success',
                    'message' => 'Sitemap и настройки сохранены. URL: ' . (int) $saved['count'] . '.',
                ],
                'sitemap_text' => $saved['text'],
            ];
        }

        return [
            'notice' => [
                'type' => 'warning',
                'message' => 'Действие не распознано.',
            ],
            'sitemap_text' => SeoSitemapManager::getSavedText(),
        ];
    }

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    private static function handleUpload(): array
    {
        if (
            empty($_POST[self::IMPORT_NONCE_NAME])
            || ! wp_verify_nonce((string) $_POST[self::IMPORT_NONCE_NAME], self::IMPORT_NONCE_ACTION)
        ) {
            return self::emptyResult(['Не удалось проверить nonce. Повторите импорт.']);
        }

        if (empty($_FILES['theme_seo_file']) || ! is_uploaded_file($_FILES['theme_seo_file']['tmp_name'])) {
            return self::emptyResult(['Файл не загружен.']);
        }

        $file = $_FILES['theme_seo_file'];
        $filename = sanitize_file_name((string) ($file['name'] ?? ''));
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        try {
            if ($extension === 'xlsx') {
                $rows = self::readXlsx((string) $file['tmp_name']);
            } elseif ($extension === 'csv') {
                $rows = self::readCsv((string) $file['tmp_name']);
            } else {
                return self::emptyResult(['Поддерживаются только .xlsx и .csv файлы.']);
            }
        } catch (Throwable $e) {
            return self::emptyResult([$e->getMessage()]);
        }

        $result = self::emptyResult();
        $batchId = current_time('Ymd-His') . '-' . wp_generate_password(6, false, false);

        foreach ($rows as $index => $rawRow) {
            $rowNumber = $index + 1;

            if (self::isHeaderRow($rawRow) || self::isEmptyRow($rawRow)) {
                continue;
            }

            $row = self::mapRow($rawRow);

            if ($row['url'] === '') {
                $result['skipped']++;
                $result['errors'][] = 'Строка ' . $rowNumber . ': пустой URL.';
                continue;
            }

            if ($row['type'] === '') {
                $row['type'] = SeoTemplateRenderer::detectTypeFromUrl($row['url']);
            }

            $upsert = SeoPageManager::upsertFromImportRow($row, $batchId);

            if ($upsert['status'] === 'created') {
                $result['created']++;
            } elseif ($upsert['status'] === 'updated') {
                $result['updated']++;
            } else {
                $result['skipped']++;
                $result['errors'][] = 'Строка ' . $rowNumber . ': ' . ($upsert['message'] ?? 'ошибка импорта.');
            }
        }

        return $result;
    }

    /**
     * @param list<string> $errors
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    private static function emptyResult(array $errors = []): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => $errors,
        ];
    }

    /**
     * @param array<string, string> $row
     * @return array{type: string, query: string, url: string, title: string, description: string, h1: string, h2: string, seo_text: string, h2_bottom: string}
     */
    private static function mapRow(array $row): array
    {
        return [
            'type' => trim((string) ($row['A'] ?? '')),
            'query' => trim((string) ($row['B'] ?? '')),
            'url' => trim((string) ($row['C'] ?? '')),
            'title' => trim((string) ($row['D'] ?? '')),
            'description' => trim((string) ($row['E'] ?? '')),
            'h1' => trim((string) ($row['F'] ?? '')),
            'h2' => trim((string) ($row['G'] ?? '')),
            'seo_text' => trim((string) ($row['H'] ?? '')),
            'h2_bottom' => trim((string) ($row['I'] ?? '')),
        ];
    }

    /**
     * @param array<string, string> $row
     */
    private static function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, string> $row
     */
    private static function isHeaderRow(array $row): bool
    {
        $a = mb_strtolower(trim((string) ($row['A'] ?? '')), 'UTF-8');
        $b = mb_strtolower(trim((string) ($row['B'] ?? '')), 'UTF-8');
        $c = mb_strtolower(trim((string) ($row['C'] ?? '')), 'UTF-8');

        return in_array($a, ['тип', 'type'], true)
            && in_array($b, ['запрос', 'query'], true)
            && in_array($c, ['url', 'урл'], true);
    }

    /**
     * @return list<array<string, string>>
     */
    private static function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException('Не удалось открыть CSV файл.');
        }

        $firstLine = fgets($handle);

        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = [];

            foreach ($data as $index => $value) {
                $row[self::columnLetter($index + 1)] = self::cleanupCell((string) $value);
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('На сервере недоступен ZipArchive, поэтому .xlsx нельзя прочитать. Загрузите CSV или включите PHP zip extension.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Не удалось открыть XLSX файл.');
        }

        $sharedStrings = self::readSharedStrings($zip);
        $sheetPath = self::getFirstSheetPath($zip);
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException('Не удалось найти первый лист XLSX.');
        }

        $sheet = simplexml_load_string($sheetXml);

        if (! $sheet instanceof SimpleXMLElement) {
            $zip->close();
            throw new RuntimeException('Не удалось прочитать XML первого листа XLSX.');
        }

        $rows = [];

        foreach ($sheet->sheetData->row as $xmlRow) {
            $row = [];

            foreach ($xmlRow->c as $cell) {
                $attrs = $cell->attributes();
                $cellRef = (string) ($attrs['r'] ?? '');
                $column = preg_replace('/\d+/', '', $cellRef);

                if (! $column) {
                    $column = self::columnLetter(count($row) + 1);
                }

                $type = (string) ($attrs['t'] ?? '');
                $value = self::readXlsxCell($cell, $type, $sharedStrings);
                $row[$column] = self::cleanupCell($value);
            }

            $rows[] = $row;
        }

        $zip->close();

        return $rows;
    }

    /**
     * @return list<string>
     */
    private static function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);

        if (! $shared instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $parts = [];

            foreach ($item->r as $run) {
                $parts[] = (string) $run->t;
            }

            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private static function getFirstSheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if (! $workbook instanceof SimpleXMLElement || ! $rels instanceof SimpleXMLElement || empty($workbook->sheets->sheet[0])) {
            return 'xl/worksheets/sheet1.xml';
        }

        $sheet = $workbook->sheets->sheet[0];
        $namespaces = $sheet->getNamespaces(true);
        $attrs = $sheet->attributes($namespaces['r'] ?? '');
        $relationshipId = (string) ($attrs['id'] ?? '');

        if ($relationshipId === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        foreach ($rels->Relationship as $relationship) {
            $relAttrs = $relationship->attributes();

            if ((string) ($relAttrs['Id'] ?? '') !== $relationshipId) {
                continue;
            }

            $target = (string) ($relAttrs['Target'] ?? '');

            if ($target === '') {
                break;
            }

            return str_starts_with($target, '/')
                ? ltrim($target, '/')
                : 'xl/' . ltrim($target, '/');
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /**
     * @param list<string> $sharedStrings
     */
    private static function readXlsxCell(SimpleXMLElement $cell, string $type, array $sharedStrings): string
    {
        if ($type === 's') {
            $index = (int) $cell->v;

            return (string) ($sharedStrings[$index] ?? '');
        }

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return (string) $cell->is->t;
            }

            $parts = [];

            foreach ($cell->is->r as $run) {
                $parts[] = (string) $run->t;
            }

            return implode('', $parts);
        }

        return isset($cell->v) ? (string) $cell->v : '';
    }

    private static function cleanupCell(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        return trim($value);
    }

    private static function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int) (($index - $mod) / 26);
        }

        return $letter;
    }
}
