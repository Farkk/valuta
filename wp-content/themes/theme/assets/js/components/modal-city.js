const initModalCity = () => {
  const searchInput = document.querySelector('[data-city-search]');
  const cityList = document.querySelector('[data-city-list]');
  const emptyMessage = document.querySelector('[data-city-empty]');

  if (!searchInput || !cityList) {
    return;
  }

  searchInput.addEventListener('input', () => {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const groups = cityList.querySelectorAll('[data-city-group]');
    let hasVisibleItems = false;

    groups.forEach((group) => {
      const items = group.querySelectorAll('[data-city-item]');
      let hasVisibleGroupItems = false;

      items.forEach((item) => {
        const cityName = item.textContent.toLowerCase();
        const isMatch = cityName.includes(searchTerm);

        item.classList.toggle('is-hidden', !isMatch);

        if (isMatch) {
          hasVisibleItems = true;
          hasVisibleGroupItems = true;
        }
      });

      group.classList.toggle('is-hidden', !hasVisibleGroupItems);
    });

    const isEmpty = !hasVisibleItems && searchTerm !== '';

    emptyMessage?.classList.toggle('is-visible', isEmpty);
    cityList.classList.toggle('is-hidden', isEmpty);
  });

  cityList.querySelectorAll('[data-city-item]').forEach((item) => {
    item.addEventListener('click', () => {
      cityList.querySelectorAll('[data-city-item]').forEach((cityItem) => {
        cityItem.classList.remove('is-active');
      });

      item.classList.add('is-active');
    });
  });
};

export default initModalCity;
