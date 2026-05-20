import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

export default function initReviewsSlider() {
  const slider = document.querySelector('[data-reviews-slider]');
  if (!slider) {
    return;
  }

  const prev = document.querySelector('[data-reviews-prev]');
  const next = document.querySelector('[data-reviews-next]');
  const pagination = document.querySelector('[data-reviews-pagination]');

  const navigation =
    prev && next
      ? {
          prevEl: prev,
          nextEl: next,
        }
      : undefined;

  const paginationConfig = pagination
    ? {
        el: pagination,
        clickable: true,
      }
    : undefined;

  new Swiper(slider, {
    modules: [Navigation, Pagination],
    slidesPerView: 1,
    spaceBetween: 26,
    speed: 350,
    watchOverflow: true,
    navigation,
    pagination: paginationConfig,
    breakpoints: {
      0: {
        slidesPerView: 1,
        centeredSlides: true,
        spaceBetween: 26,
      },
      568: {
        slidesPerView: 2,
        centeredSlides: false,
        spaceBetween: 26,
      },
    },
  });
}
