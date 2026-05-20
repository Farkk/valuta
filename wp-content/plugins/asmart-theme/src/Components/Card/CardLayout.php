<?php

declare(strict_types=1);

namespace Asmart\Components\Card;

/**
 * Типы отображения карточки
 *
 * Определяет различные варианты визуального представления карточки.
 */
enum CardLayout: string
{
  /**
   * Сеточное отображение (вертикальная карточка)
   */
  case Grid = 'grid';

  /**
   * Списковое отображение (горизонтальная карточка)
   */
  case List = 'list';
}
