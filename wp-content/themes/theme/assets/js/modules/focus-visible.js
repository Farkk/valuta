const initFocusVisible = () => {
  const root = document.documentElement;

  document.addEventListener('keydown', () => {
    root.classList.add('using-keyboard');
  });

  document.addEventListener('pointerdown', () => {
    root.classList.remove('using-keyboard');
  });
};

export default initFocusVisible;

