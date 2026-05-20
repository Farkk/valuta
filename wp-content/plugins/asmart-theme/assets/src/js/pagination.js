/**
 * Pagination Component JavaScript
 * Version: 1.0.0
 */

(function () {
  'use strict';

  /**
   * Базовый класс для управления пагинацией
   */
  class AsmartPagination {
    constructor(element) {
      this.element = element;
      this.type = element.getAttribute('data-pagination');
      this.ajaxUrl = element.getAttribute('data-ajax-url');
      this.ajaxAction = element.getAttribute('data-ajax-action');
      this.containerId = element.getAttribute('data-container-id');
      this.currentPage = parseInt(
        element.getAttribute('data-current-page'),
        10
      );
      this.totalPages = parseInt(element.getAttribute('data-total-pages'), 10);
      this.isLoading = false;
      this.callbacks = {
        onBeforeLoad: [],
        onLoad: [],
        onError: [],
      };
    }

    /**
     * Добавить callback
     */
    on(event, callback) {
      if (this.callbacks[event]) {
        this.callbacks[event].push(callback);
      }
    }

    /**
     * Удалить callback
     */
    off(event, callback) {
      if (this.callbacks[event]) {
        this.callbacks[event] = this.callbacks[event].filter(
          cb => cb !== callback
        );
      }
    }

    /**
     * Вызвать callbacks
     */
    async trigger(event, data) {
      if (this.callbacks[event]) {
        for (const callback of this.callbacks[event]) {
          await callback.call(this, data);
        }
      }
    }

    /**
     * Показать индикатор загрузки
     */
    showLoader() {
      const loader = this.element.querySelector(
        '.asmart-pagination__loader, .asmart-pagination__spinner, .asmart-pagination__infinite-loader'
      );
      if (loader) {
        loader.style.display = '';
      }
    }

    /**
     * Скрыть индикатор загрузки
     */
    hideLoader() {
      const loader = this.element.querySelector(
        '.asmart-pagination__loader, .asmart-pagination__spinner, .asmart-pagination__infinite-loader'
      );
      if (loader) {
        loader.style.display = 'none';
      }
    }

    /**
     * Загрузить страницу через AJAX
     */
    async loadPage(page, append = false) {
      if (this.isLoading || page < 1 || page > this.totalPages) {
        return false;
      }

      this.isLoading = true;
      this.showLoader();

      await this.trigger('onBeforeLoad', { page, append });

      try {
        const formData = new FormData();
        formData.append('action', this.ajaxAction);
        formData.append('page', page);
        formData.append('nonce', window.asmartPaginationData?.nonce || '');

        const response = await fetch(this.ajaxUrl, {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
          throw new Error(data.data?.message || 'Unknown error');
        }

        const container = document.getElementById(this.containerId);
        if (!container) {
          throw new Error(`Container #${this.containerId} not found`);
        }

        // Вставка контента
        if (append) {
          container.insertAdjacentHTML('beforeend', data.data.html);
        } else {
          container.innerHTML = data.data.html;
        }

        this.currentPage = page;
        this.element.setAttribute('data-current-page', page);

        await this.trigger('onLoad', { page, append, data });

        return true;
      } catch (error) {
        console.error('Pagination error:', error);
        await this.trigger('onError', { page, error });
        return false;
      } finally {
        this.isLoading = false;
        this.hideLoader();
      }
    }
  }

  /**
   * AJAX пагинация
   */
  class AjaxPagination extends AsmartPagination {
    constructor(element) {
      super(element);
      this.init();
    }

    init() {
      const links = this.element.querySelectorAll(
        '.asmart-pagination__link--ajax'
      );

      links.forEach(link => {
        link.addEventListener('click', async e => {
          e.preventDefault();

          if (this.isLoading) {
            return;
          }

          const page = parseInt(link.getAttribute('data-page'), 10);
          const success = await this.loadPage(page, false);

          if (success) {
            // Обновить активные классы
            this.updateActiveState(page);
          }
        });
      });
    }

    updateActiveState(page) {
      const links = this.element.querySelectorAll('.asmart-pagination__link');
      links.forEach(link => {
        link.classList.remove('asmart-pagination__link--active');
        if (parseInt(link.getAttribute('data-page'), 10) === page) {
          link.classList.add('asmart-pagination__link--active');
        }
      });
    }
  }

  /**
   * Load More пагинация
   */
  class LoadMorePagination extends AsmartPagination {
    constructor(element) {
      super(element);
      this.button = element.querySelector('.asmart-pagination__load-more-btn');
      this.init();
    }

    init() {
      if (!this.button) {
        return;
      }

      this.button.addEventListener('click', async e => {
        e.preventDefault();

        if (this.isLoading) {
          return;
        }

        const nextPage = this.currentPage + 1;
        const success = await this.loadPage(nextPage, true);

        if (success) {
          // Проверить, есть ли ещё страницы
          if (nextPage >= this.totalPages) {
            this.button.style.display = 'none';

            // Показать сообщение об окончании
            const endMessage = document.createElement('div');
            endMessage.className = 'asmart-pagination__end-message';
            endMessage.textContent = 'All items loaded';
            this.element.appendChild(endMessage);
          }

          // Обновить счётчик
          const counter = this.element.querySelector(
            '.asmart-pagination__counter'
          );
          if (counter) {
            counter.textContent = `Page ${nextPage} of ${this.totalPages}`;
          }
        }
      });
    }
  }

  /**
   * Infinite Scroll пагинация
   */
  class InfinitePagination extends AsmartPagination {
    constructor(element) {
      super(element);
      this.trigger = element.querySelector(
        '.asmart-pagination__infinite-trigger'
      );
      this.endElement = element.querySelector(
        '.asmart-pagination__infinite-end'
      );
      this.offset =
        parseInt(element.getAttribute('data-infinite-offset'), 10) || 300;
      this.observer = null;
      this.init();
    }

    init() {
      if (!this.trigger) {
        return;
      }

      // Использовать Intersection Observer для определения видимости триггера
      this.observer = new IntersectionObserver(
        entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting && !this.isLoading) {
              this.loadNextPage();
            }
          });
        },
        {
          rootMargin: `${this.offset}px`,
        }
      );

      this.observer.observe(this.trigger);
    }

    async loadNextPage() {
      if (this.currentPage >= this.totalPages) {
        this.destroy();
        return;
      }

      const nextPage = this.currentPage + 1;
      const success = await this.loadPage(nextPage, true);

      if (success && nextPage >= this.totalPages) {
        this.destroy();
      }
    }

    destroy() {
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }

      if (this.trigger) {
        this.trigger.style.display = 'none';
      }

      if (this.endElement) {
        this.endElement.style.display = '';
      }
    }
  }

  /**
   * Фабрика для создания экземпляров пагинации
   */
  function createPagination(element) {
    const type = element.getAttribute('data-pagination');

    switch (type) {
      case 'ajax':
        return new AjaxPagination(element);
      case 'load-more':
        return new LoadMorePagination(element);
      case 'infinite':
        return new InfinitePagination(element);
      default:
        return null;
    }
  }

  /**
   * Инициализация всех пагинаций на странице
   */
  function initPaginations() {
    const paginations = document.querySelectorAll('[data-pagination]');
    paginations.forEach(element => {
      if (!element.asmartPagination) {
        const pagination = createPagination(element);
        if (pagination) {
          element.asmartPagination = pagination;
        }
      }
    });
  }

  /**
   * Инициализация при загрузке DOM
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPaginations);
  } else {
    initPaginations();
  }

  /**
   * Переинициализация при изменении DOM (для динамического контента)
   */
  const observer = new MutationObserver(() => {
    initPaginations();
  });
  observer.observe(document.body, { childList: true, subtree: true });

  /**
   * Публичный API
   */
  window.AsmartPagination = {
    /**
     * Получить инстанс пагинации по ID элемента
     */
    getInstance(elementId) {
      const element = document.getElementById(elementId);
      return element && element.asmartPagination
        ? element.asmartPagination
        : null;
    },

    /**
     * Загрузить страницу программно
     */
    async loadPage(elementId, page, append = false) {
      const instance = this.getInstance(elementId);
      if (instance) {
        return instance.loadPage(page, append);
      }
      return false;
    },

    /**
     * Добавить callback на событие
     */
    on(elementId, event, callback) {
      const instance = this.getInstance(elementId);
      if (instance) {
        instance.on(event, callback);
      }
    },

    /**
     * Удалить callback
     */
    off(elementId, event, callback) {
      const instance = this.getInstance(elementId);
      if (instance) {
        instance.off(event, callback);
      }
    },
  };
})();
