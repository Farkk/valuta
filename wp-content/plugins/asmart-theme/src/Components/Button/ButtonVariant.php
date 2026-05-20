<?php

declare(strict_types=1);

namespace Asmart\Components\Button;

/**
 * Enum вариантов кнопки
 *
 * Определяет доступные варианты кнопки.
 */
enum ButtonVariant: string
{
  case Primary = 'primary';
  case Secondary = 'secondary';
  case Danger = 'danger';
  case Ghost = 'ghost';
  case Link = 'link';
}
