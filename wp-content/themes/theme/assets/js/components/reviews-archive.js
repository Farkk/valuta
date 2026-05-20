export default function initReviewsArchive() {
  const button = document.querySelector('[data-reviews-load-more]');
  const grid = document.getElementById('reviews-archive-grid');

  if (!button || !grid || !window.themeSettings?.ajaxUrl) {
    return;
  }

  let pending = false;

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
      formData.append('action', 'load_more_reviews');
      formData.append('page', String(currentPage + 1));

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
      document.dispatchEvent(new CustomEvent('reviews:updated'));

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
}
