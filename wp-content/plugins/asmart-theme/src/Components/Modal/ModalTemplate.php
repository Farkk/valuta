<?php

declare(strict_types=1);

namespace Asmart\Components\Modal;

/**
 * Enum шаблонов модалки
 *
 * Определяет доступные шаблоны модалки.
 * Каждый шаблон представляет различный сценарий использования и структуру.
 */
enum ModalTemplate: string
{
  case Default = 'default';
  case Success = 'success';
  case Error = 'error';
  case Form = 'form';
}
