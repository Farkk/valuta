const showShareNotification = (button, message) => {
  button.querySelector('.news-single__share-notification')?.remove();

  const notification = document.createElement('span');
  notification.className = 'news-single__share-notification';
  notification.textContent = message;
  button.style.position = 'relative';
  button.appendChild(notification);

  window.setTimeout(() => {
    notification.remove();
  }, 2000);
};

const copyToClipboardFallback = (text, button) => {
  const textArea = document.createElement('textarea');
  textArea.value = text;
  textArea.style.position = 'fixed';
  textArea.style.top = '0';
  textArea.style.left = '0';
  textArea.style.width = '2em';
  textArea.style.height = '2em';
  textArea.style.padding = '0';
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';
  textArea.style.background = 'transparent';

  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();

  try {
    const successful = document.execCommand('copy');
    showShareNotification(
      button,
      successful ? 'Ссылка скопирована' : 'Не удалось скопировать',
    );
  } catch {
    showShareNotification(button, 'Не удалось скопировать');
  }

  document.body.removeChild(textArea);
};

const initNewsShare = () => {
  document.querySelectorAll('[data-news-share]').forEach((button) => {
    button.addEventListener('click', async (event) => {
      event.preventDefault();

      const url = button.dataset.url || window.location.href;
      const title = button.dataset.title || document.title;
      const text = button.dataset.text || '';

      if (navigator.share) {
        try {
          await navigator.share({ title, text, url });
        } catch {
          // Пользователь отменил шаринг.
        }

        return;
      }

      if (navigator.clipboard?.writeText) {
        try {
          await navigator.clipboard.writeText(url);
          showShareNotification(button, 'Ссылка скопирована');
        } catch {
          copyToClipboardFallback(url, button);
        }

        return;
      }

      copyToClipboardFallback(url, button);
    });
  });
};

export default initNewsShare;
