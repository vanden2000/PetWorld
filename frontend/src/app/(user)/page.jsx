"use client";

import { useState, useEffect, useCallback } from 'react';

export default function Homepage() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [categories, setCategories] = useState([]);
  const [banners, setBanners] = useState([]);
  const [loading, setLoading] = useState(true);

  const nextSlide = useCallback(() => {
    if (banners.length === 0) return;
    setCurrentIndex((prevIndex) => (prevIndex + 1) % banners.length);
  }, [banners.length]);

  const prevSlide = () => {
    if (banners.length === 0) return;
    setCurrentIndex((prevIndex) => (prevIndex - 1 + banners.length) % banners.length);
  };

  useEffect(() => {
    if (banners.length === 0) return;
    const timer = setInterval(nextSlide, 5000);
    return () => clearInterval(timer);
  }, [nextSlide, banners.length]);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/home')
      .then((res) => {
        if (!res.ok) {
          throw new Error('API connection failed');
        }
        return res.json();
      })
      .then((resJson) => {
        if (resJson && resJson.data) {
          if (resJson.data.categories) {
            setCategories(resJson.data.categories);
          }
          if (resJson.data.banners && resJson.data.banners.length > 0) {
            setBanners(resJson.data.banners);
          }
        }
        setLoading(false);
      })
      .catch((err) => {
        console.error('Error fetching data from API:', err);
        setLoading(false);
      });
  }, []);

  return (
    <main className="main-content">
      <div className="homepage-container">

        {/* Hero Banner Section */}
        {banners.length > 0 && (
          <section className="homepage-section">
            <div className="hero-slider">
              <div className="slider-wrapper">
                {banners.map((slide, index) => (
                  <div
                    key={slide.id || index}
                    className={`hero-slide ${index === currentIndex ? 'active' : ''}`}
                    style={{
                      opacity: index === currentIndex ? 1 : 0,
                      visibility: index === currentIndex ? 'visible' : 'hidden',
                      transition: 'opacity 0.6s ease-in-out, visibility 0.6s ease-in-out'
                    }}
                  >
                    <a href={slide.link || '#'} className="slide-link">
                      <img 
                        src={slide.image.startsWith('http') ? slide.image : `/image/${slide.image}`} 
                        alt={slide.description || 'PetWorld Banner'} 
                        className="slide-img" 
                      />
                    </a>
                  </div>
                ))}
              </div>

              {/* Navigation Arrows */}
              <button className="slider-arrow prev" onClick={prevSlide} aria-label="Previous slide">
                &#10094;
              </button>
              <button className="slider-arrow next" onClick={nextSlide} aria-label="Next slide">
                &#10095;
              </button>

              {/* Navigation Dots */}
              <div className="slider-dots">
                {banners.map((_, index) => (
                  <span
                    key={index}
                    className={`slider-dot ${index === currentIndex ? 'active' : ''}`}
                    onClick={() => setCurrentIndex(index)}
                  />
                ))}
              </div>
            </div>
          </section>
        )}

        {/* Product Categories Section */}
        <section className="homepage-section">
          <div className="section-header">
            <h2 className="section-title category-section-title">Danh mục Sản Phẩm</h2>
            <a href="#" className="view-all-link category-view-all">xem tất cả ➔</a>
          </div>

          {loading ? (
            <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
              Đang tải danh mục...
            </div>
          ) : categories.length > 0 ? (
            <div className="categories-grid-6">
              {categories.map((category) => (
                <a href={`#`} className="category-card-figma" key={category.id}>
                  <div className="category-img-box">
                    <img src={`/image/${category.image}`} alt={category.name} className="category-figma-img" />
                  </div>
                  <span className="category-figma-name">{category.name}</span>
                </a>
              ))}
            </div>
          ) : (
            <div style={{ textAlign: 'center', padding: '40px', color: '#666', fontStyle: 'italic' }}>
              Không có danh mục nào được tìm thấy. Vui lòng bật API Laravel.
            </div>
          )}

          {/* Users Counter Banner */}
          <div className="users-counter-banner">
            Hơn 12.000 người dùng hoạt động mỗi ngày
          </div>
        </section>
      </div>
    </main>
  );
}