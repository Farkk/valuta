export default function initContentArchive() {
  const buttons = document.querySelectorAll('[data-content-load-more], [data-news-load-more]');

  buttons.forEach((button) => {
    const contentType = button.dataset.contentType || 'news';
    const gridId = `${contentType}-archive-grid`;
    const grid = document.getElementById(gridId);

    if (!grid || !window.themeSettings?.ajaxUrl) {
      return;
    }

    let pending = false;
    const config = window.themeSettings?.contentArchive?.[contentType] || {};
    const action = config.ajaxAction || (contentType === 'articles' ? 'load_more_articles' : 'load_more_news');

    button.addEventListener('click', async () => {
      if (pending) {
        return;
      }

      const currentPage = Number(button.dataset.currentPage || '1');
      const totalPages = Number(button.dataset.totalPages || '1');

      if (currentPage >= totalPages) {
        button.hidden = true;
        return;
      }

      pending = true;
      button.disabled = true;

      try {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('page', String(currentPage + 1));

        const tag = button.dataset.tag || '';
        if (tag !== '') {
          formData.append('tag', tag);
        }

        const response = await fetch(window.themeSettings.ajaxUrl, {
          method: 'POST',
          body: formData,
        });

        const payload = await response.json();

        if (!payload?.success || !payload.data?.html) {
          button.hidden = true;
          return;
        }

        grid.insertAdjacentHTML('beforeend', payload.data.html);
        button.dataset.currentPage = String(payload.data.page || currentPage + 1);

        if (!payload.data.has_more) {
          button.hidden = true;
        }
      } catch (error) {
        button.disabled = false;
        pending = false;
        return;
      }

      pending = false;
      button.disabled = false;
    });
  });
}
