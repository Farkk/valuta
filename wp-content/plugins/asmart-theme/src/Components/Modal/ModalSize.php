<?php

declare(strict_types=1);

namespace Asmart\Components\Modal;

/**
 * Enum размеров модалки
 *
 * Определяет доступные размеры модалки.
 */
enum ModalSize: string
{
  case Small = 'small';
  case Medium = 'medium';
  case Large = 'large';
  case FullScreen = 'fullscreen';
}
