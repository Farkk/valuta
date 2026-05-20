import Swiper from 'swiper';
import { Pagination } from 'swiper/modules';

export default function initNewsPreviewSlider() {
  const slider = document.querySelector('[data-news-preview-slider]');
  if (!slider) {
    return;
  }

  const pagination = document.querySelector('[data-news-preview-pagination]');

  new Swiper(slider, {
    modules: [Pagination],
    slidesPerView: 1,
    spaceBetween: 0,
    speed: 350,
    watchOverflow: true,
    pagination: pagination
      ? {
          el: pagination,
          clickable: true,
        }
      : undefined,
    breakpoints: {
      568: {
        enabled: false,
      },
    },
  });
}
