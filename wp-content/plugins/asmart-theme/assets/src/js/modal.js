/**
 * Modal Component JavaScript
 * Version: 2.0.0
 *
 */

(function () {
  'use strict';

  /**
   * Класс для управления модальным окном
   */
  class AsmartModal {
    constructor(element) {
      this.element = element;
      this.isAnimating = false;
      this.callbacks = {
        onOpen: [],
        onClose: [],
        onBeforeOpen: [],
        onBeforeClose: [],
      };
      this.init();
    }

    /**
     * Инициализация модалки
     */
    init() {
      // Обработчики закрытия
      const closeTriggers = this.element.querySelectorAll('[data-modal-close]');
      closeTriggers.forEach(trigger => {
        trigger.addEventListener('click', e => {
          e.preventDefault();
          this.close();
        });
      });

      // Закрытие по клику на overlay
      const overlay = this.element.querySelector('.asmart-modal__overlay');
      if (overlay) {
        overlay.addEventListener('click', e => {
          if (e.target === overlay) {
            this.close();
          }
        });
      }

      // Закрытие по Escape
      this.escapeHandler = e => {
        if (e.key === 'Escape' && this.isOpen()) {
          this.close();
        }
      };
    }

    /**
     * Открыть модалку
     * @param {Object} options - Опции открытия
     * @returns {Promise} - Промис, который резолвится после открытия
     */
    async open(options = {}) {
      if (this.isOpen() || this.isAnimating) {
        return;
      }

      this.isAnimating = true;

      // Callback перед открытием
      await this.trigger('onBeforeOpen', options);

      // Открытие
      this.element.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', this.escapeHandler);

      // Фокус на первый элемент
      setTimeout(() => {
        const firstFocusable = this.element.querySelector(
          'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (firstFocusable) {
          firstFocusable.focus();
        }
      }, 100);

      // Событие открытия
      this.element.dispatchEvent(
        new CustomEvent('asmart:modal:open', {
          detail: { modal: this, options },
        })
      );

      // Callback после открытия
      await this.trigger('onOpen', options);

      this.isAnimating = false;
    }

    /**
     * Закрыть модалку
     * @param {Object} result - Результат для передачи в callbacks
     * @returns {Promise} - Промис, который резолвится после закрытия
     */
    async close(result = null) {
      if (!this.isOpen() || this.isAnimating) {
        return;
      }

      this.isAnimating = true;

      // Callback перед закрытием
      await this.trigger('onBeforeClose', result);

      // Закрытие
      this.element.classList.remove('is-open');
      document.body.style.overflow = '';
      document.removeEventListener('keydown', this.escapeHandler);

      // Событие закрытия
      this.element.dispatchEvent(
        new CustomEvent('asmart:modal:close', {
          detail: { modal: this, result },
        })
      );

      // Callback после закрытия
      await this.trigger('onClose', result);

      this.isAnimating = false;
    }

    /**
     * Переключить состояние модалки
     */
    toggle() {
      if (this.isOpen()) {
        this.close();
      } else {
        this.open();
      }
    }

    /**
     * Проверить, открыта ли модалка
     * @returns {boolean}
     */
    isOpen() {
      return this.element.classList.contains('is-open');
    }

    /**
     * Добавить callback
     * @param {string} event - Имя события (onOpen, onClose, onBeforeOpen, onBeforeClose)
     * @param {Function} callback - Функция callback
     */
    on(event, callback) {
      if (this.callbacks[event]) {
        this.callbacks[event].push(callback);
      }
    }

    /**
     * Удалить callback
     * @param {string} event - Имя события
     * @param {Function} callback - Функция callback
     */
    off(event, callback) {
      if (this.callbacks[event]) {
        this.callbacks[event] = this.callbacks[event].filter(
          cb => cb !== callback
        );
      }
    }

    /**
     * Вызвать все callbacks для события
     * @param {string} event - Имя события
     * @param {*} data - Данные для передачи в callback
     */
    async trigger(event, data) {
      if (this.callbacks[event]) {
        for (const callback of this.callbacks[event]) {
          await callback.call(this, data);
        }
      }
    }

    /**
     * Ожидать закрытия модалки
     * @returns {Promise} - Промис с результатом закрытия
     */
    waitForClose() {
      return new Promise(resolve => {
        const handler = e => {
          this.element.removeEventListener('asmart:modal:close', handler);
          resolve(e.detail.result);
        };
        this.element.addEventListener('asmart:modal:close', handler);
      });
    }
  }

  /**
   * Инициализация всех модалок на странице
   */
  function initModals() {
    const modals = document.querySelectorAll('[data-modal="true"]');
    modals.forEach(modal => {
      if (!modal.asmartModal) {
        modal.asmartModal = new AsmartModal(modal);
      }
    });
  }

  /**
   * Инициализация кнопок для открытия модалок
   */
  function initModalTriggers() {
    const triggers = document.querySelectorAll('[data-modal-target]');
    triggers.forEach(trigger => {
      if (!trigger.hasAttribute('data-modal-initialized')) {
        trigger.setAttribute('data-modal-initialized', 'true');
        trigger.addEventListener('click', e => {
          e.preventDefault();
          const modalId = trigger.getAttribute('data-modal-target');
          if (modalId) {
            window.AsmartModal.open(modalId);
          }
        });
      }
    });
  }

  /**
   * Инициализация при загрузке DOM
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initModals();
      initModalTriggers();
    });
  } else {
    initModals();
    initModalTriggers();
  }

  /**
   * Переинициализация при изменении DOM (для динамического контента)
   */
  const observer = new MutationObserver(() => {
    initModals();
    initModalTriggers();
  });
  observer.observe(document.body, { childList: true, subtree: true });

  /**
   * Публичный API для работы с модалками из JS темы
   */
  window.AsmartModal = {
    /**
     * Открыть модалку по ID
     * @param {string} modalId - ID модалки
     * @param {Object} options - Опции открытия
     * @returns {Promise}
     */
    open(modalId, options = {}) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        return modal.asmartModal.open(options);
      }
      console.warn(`Modal with ID "${modalId}" not found`);
      return Promise.resolve();
    },

    /**
     * Закрыть модалку по ID
     * @param {string} modalId - ID модалки
     * @param {*} result - Результат для передачи в callbacks
     * @returns {Promise}
     */
    close(modalId, result = null) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        return modal.asmartModal.close(result);
      }
      console.warn(`Modal with ID "${modalId}" not found`);
      return Promise.resolve();
    },

    /**
     * Переключить модалку по ID
     * @param {string} modalId - ID модалки
     */
    toggle(modalId) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        modal.asmartModal.toggle();
      } else {
        console.warn(`Modal with ID "${modalId}" not found`);
      }
    },

    /**
     * Проверить, открыта ли модалка
     * @param {string} modalId - ID модалки
     * @returns {boolean}
     */
    isOpen(modalId) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        return modal.asmartModal.isOpen();
      }
      return false;
    },

    /**
     * Получить инстанс модалки
     * @param {string} modalId - ID модалки
     * @returns {AsmartModal|null}
     */
    getInstance(modalId) {
      const modal = document.getElementById(modalId);
      return modal && modal.asmartModal ? modal.asmartModal : null;
    },

    /**
     * Добавить callback на событие модалки
     * @param {string} modalId - ID модалки
     * @param {string} event - Имя события
     * @param {Function} callback - Функция callback
     */
    on(modalId, event, callback) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        modal.asmartModal.on(event, callback);
      } else {
        console.warn(`Modal with ID "${modalId}" not found`);
      }
    },

    /**
     * Удалить callback с события модалки
     * @param {string} modalId - ID модалки
     * @param {string} event - Имя события
     * @param {Function} callback - Функция callback
     */
    off(modalId, event, callback) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        modal.asmartModal.off(event, callback);
      } else {
        console.warn(`Modal with ID "${modalId}" not found`);
      }
    },

    /**
     * Ожидать закрытия модалки (возвращает Promise)
     * @param {string} modalId - ID модалки
     * @returns {Promise}
     */
    waitForClose(modalId) {
      const modal = document.getElementById(modalId);
      if (modal && modal.asmartModal) {
        return modal.asmartModal.waitForClose();
      }
      console.warn(`Modal with ID "${modalId}" not found`);
      return Promise.resolve(null);
    },
  };
})();
