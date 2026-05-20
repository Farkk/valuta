const initExampleComponent = () => {
  const modal = document.querySelector('[data-modal]');

  if (!modal) {
    return;
  }

  const dialog = modal.tagName === 'DIALOG' ? modal : null;
  const openButtons = document.querySelectorAll('[data-open-modal]');
  const closeButtons = modal.querySelectorAll('[data-close-modal]');

  openButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (dialog?.showModal) {
        dialog.showModal();
        return;
      }

      modal.removeAttribute('hidden');
    });
  });

  closeButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (dialog?.close) {
        dialog.close();
        return;
      }

      modal.setAttribute('hidden', '');
    });
  });
};

export default initExampleComponent;

