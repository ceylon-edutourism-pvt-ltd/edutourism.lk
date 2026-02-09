<?php
// hero_section.php
include 'db.php';

$heroes = $con->query("SELECT * FROM heroes WHERE is_active = 1 ORDER BY display_order ASC, id DESC");
$hero_data = $heroes->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hero Section</title>
  <link rel="stylesheet" href="css/hero_section.css">
</head>
<body>

<div class="edutourism-hero-slider-main-wrapper" id="edutourismHeroSliderMainWrapper">
  <?php foreach($hero_data as $index => $hero): ?>
    <div class="edutourism-hero-slide-item edutourism-hero-slide-item-<?php echo $hero['id']; ?>" 
         data-hero-type="<?php echo $hero['type']; ?>" 
         data-hero-id="<?php echo $hero['id']; ?>"
         data-hero-index="<?php echo $index; ?>">
      
      <?php if($hero['type'] == 1): ?>
        <!-- Type 1: Left Text + Right Media -->
        <div class="edutourism-hero-container-type-1 edutourism-hero-container-<?php echo $hero['id']; ?>"
             <?php if($hero['background_image']): ?>
             style="background-image: url('<?php echo htmlspecialchars($hero['background_image']); ?>');"
             <?php endif; ?>>
          
          <?php if($hero['background_image']): ?>
          <div class="edutourism-hero-bg-overlay edutourism-hero-bg-overlay-<?php echo $hero['id']; ?>" 
               style="background: <?php echo htmlspecialchars($hero['background_overlay_color']); ?>;"></div>
          <?php endif; ?>
          
          <div class="edutourism-hero-content-wrapper edutourism-hero-content-<?php echo $hero['id']; ?> 
                      <?php echo $hero['text_bg_enabled'] ? 'edutourism-has-text-background' : ''; ?>" 
               <?php if($hero['text_bg_enabled'] && $hero['text_bg_image']): ?>
               style="background-image: url('<?php echo htmlspecialchars($hero['text_bg_image']); ?>');"
               <?php endif; ?>>
            
            <?php if($hero['text_bg_enabled']): ?>
            <div class="edutourism-hero-text-overlay edutourism-hero-text-overlay-<?php echo $hero['id']; ?>" 
                 style="background: <?php echo htmlspecialchars($hero['text_bg_color']); ?>;"></div>
            <?php endif; ?>
            
            <div class="edutourism-hero-content-inner edutourism-hero-content-inner-<?php echo $hero['id']; ?>">
              <h1 class="edutourism-hero-title edutourism-hero-title-<?php echo $hero['id']; ?>">
                <?php echo htmlspecialchars($hero['title']); ?>
              </h1>
              <p class="edutourism-hero-description edutourism-hero-description-<?php echo $hero['id']; ?>">
                <?php echo htmlspecialchars($hero['description']); ?>
              </p>
              <div class="edutourism-hero-buttons-wrapper edutourism-hero-buttons-<?php echo $hero['id']; ?>">
                <?php if($hero['button_text']): ?>
                  <a href="<?php echo htmlspecialchars($hero['button_url']); ?>" 
                     class="edutourism-hero-btn edutourism-hero-btn-primary edutourism-hero-btn-<?php echo $hero['id']; ?>-1">
                    <?php echo htmlspecialchars($hero['button_text']); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="edutourism-hero-media-wrapper edutourism-hero-media-wrapper-<?php echo $hero['id']; ?>">
            <div class="edutourism-hero-media-box edutourism-hero-media-box-<?php echo $hero['id']; ?>">
              <?php if($hero['media_type'] == 'video'): ?>
                <video autoplay muted loop playsinline 
                       class="edutourism-hero-video edutourism-hero-video-<?php echo $hero['id']; ?>" 
                       preload="auto"
                       id="edutourismHeroVideo<?php echo $hero['id']; ?>">
                  <source src="<?php echo htmlspecialchars($hero['media_url']); ?>" type="video/mp4">
                </video>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($hero['media_url']); ?>" 
                     alt="<?php echo htmlspecialchars($hero['title']); ?>" 
                     class="edutourism-hero-image edutourism-hero-image-<?php echo $hero['id']; ?>"
                     id="edutourismHeroImage<?php echo $hero['id']; ?>">
              <?php endif; ?>
            </div>
          </div>
        </div>

      <?php elseif($hero['type'] == 2): ?>
        <!-- Type 2: Center Image + Center Text -->
        <div class="edutourism-hero-container-type-2 edutourism-hero-container-<?php echo $hero['id']; ?>" 
             style="background-image: url('<?php echo htmlspecialchars($hero['media_url']); ?>');">
          <div class="edutourism-hero-center-overlay edutourism-hero-center-overlay-<?php echo $hero['id']; ?>"
               style="background: <?php echo htmlspecialchars($hero['background_overlay_color']); ?>;"></div>
          <div class="edutourism-hero-content-center edutourism-hero-content-center-<?php echo $hero['id']; ?>">
            <h1 class="edutourism-hero-title edutourism-hero-title-<?php echo $hero['id']; ?>">
              <?php echo htmlspecialchars($hero['title']); ?>
            </h1>
            <p class="edutourism-hero-description edutourism-hero-description-<?php echo $hero['id']; ?>">
              <?php echo htmlspecialchars($hero['description']); ?>
            </p>
            <div class="edutourism-hero-buttons-wrapper edutourism-hero-buttons-<?php echo $hero['id']; ?>">
              <?php if($hero['button_text']): ?>
                <a href="<?php echo htmlspecialchars($hero['button_url']); ?>" 
                   class="edutourism-hero-btn edutourism-hero-btn-primary edutourism-hero-btn-<?php echo $hero['id']; ?>-1">
                  <?php echo htmlspecialchars($hero['button_text']); ?>
                </a>
              <?php endif; ?>
              <?php if($hero['button_text_2']): ?>
                <a href="<?php echo htmlspecialchars($hero['button_url_2']); ?>" 
                   class="edutourism-hero-btn edutourism-hero-btn-secondary edutourism-hero-btn-<?php echo $hero['id']; ?>-2">
                  <?php echo htmlspecialchars($hero['button_text_2']); ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

      <?php elseif($hero['type'] == 3): ?>
        <!-- Type 3: Left Media + Right Text -->
        <div class="edutourism-hero-container-type-3 edutourism-hero-container-<?php echo $hero['id']; ?>"
             <?php if($hero['background_image']): ?>
             style="background-image: url('<?php echo htmlspecialchars($hero['background_image']); ?>');"
             <?php endif; ?>>
          
          <?php if($hero['background_image']): ?>
          <div class="edutourism-hero-bg-overlay edutourism-hero-bg-overlay-<?php echo $hero['id']; ?>" 
               style="background: <?php echo htmlspecialchars($hero['background_overlay_color']); ?>;"></div>
          <?php endif; ?>
          
          <div class="edutourism-hero-media-wrapper edutourism-hero-media-wrapper-<?php echo $hero['id']; ?>">
            <div class="edutourism-hero-media-box edutourism-hero-media-box-<?php echo $hero['id']; ?>">
              <?php if($hero['media_type'] == 'video'): ?>
                <video autoplay muted loop playsinline 
                       class="edutourism-hero-video edutourism-hero-video-<?php echo $hero['id']; ?>" 
                       preload="auto"
                       id="edutourismHeroVideo<?php echo $hero['id']; ?>">
                  <source src="<?php echo htmlspecialchars($hero['media_url']); ?>" type="video/mp4">
                </video>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($hero['media_url']); ?>" 
                     alt="<?php echo htmlspecialchars($hero['title']); ?>" 
                     class="edutourism-hero-image edutourism-hero-image-<?php echo $hero['id']; ?>"
                     id="edutourismHeroImage<?php echo $hero['id']; ?>">
              <?php endif; ?>
            </div>
          </div>
          
          <div class="edutourism-hero-content-wrapper edutourism-hero-content-<?php echo $hero['id']; ?>
                      <?php echo $hero['text_bg_enabled'] ? 'edutourism-has-text-background' : ''; ?>"
               <?php if($hero['text_bg_enabled'] && $hero['text_bg_image']): ?>
               style="background-image: url('<?php echo htmlspecialchars($hero['text_bg_image']); ?>');"
               <?php endif; ?>>
            
            <?php if($hero['text_bg_enabled']): ?>
            <div class="edutourism-hero-text-overlay edutourism-hero-text-overlay-<?php echo $hero['id']; ?>" 
                 style="background: <?php echo htmlspecialchars($hero['text_bg_color']); ?>;"></div>
            <?php endif; ?>
            
            <div class="edutourism-hero-content-inner edutourism-hero-content-inner-<?php echo $hero['id']; ?>">
              <h1 class="edutourism-hero-title edutourism-hero-title-<?php echo $hero['id']; ?>">
                <?php echo htmlspecialchars($hero['title']); ?>
              </h1>
              <p class="edutourism-hero-description edutourism-hero-description-<?php echo $hero['id']; ?>">
                <?php echo htmlspecialchars($hero['description']); ?>
              </p>
              <div class="edutourism-hero-buttons-wrapper edutourism-hero-buttons-<?php echo $hero['id']; ?>">
                <?php if($hero['button_text']): ?>
                  <a href="<?php echo htmlspecialchars($hero['button_url']); ?>" 
                     class="edutourism-hero-btn edutourism-hero-btn-primary edutourism-hero-btn-<?php echo $hero['id']; ?>-1">
                    <?php echo htmlspecialchars($hero['button_text']); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  <?php endforeach; ?>

  <!-- Slider Controls (Now inside wrapper) -->
  <button class="edutourism-slider-btn-prev edutourism-slider-control" 
          id="edutourismSliderBtnPrev" 
          aria-label="Previous slide">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="15 18 9 12 15 6"></polyline>
    </svg>
  </button>

  <button class="edutourism-slider-btn-next edutourism-slider-control" 
          id="edutourismSliderBtnNext" 
          aria-label="Next slide">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="9 18 15 12 9 6"></polyline>
    </svg>
  </button>

  <div class="edutourism-slider-dots-wrapper" id="edutourismSliderDotsWrapper"></div>
</div>

<script>
  class EdutourismHeroSlider {
    constructor() {
      this.sliderWrapper = document.getElementById('edutourismHeroSliderMainWrapper');
      this.slides = document.querySelectorAll('.edutourism-hero-slide-item');
      this.currentIndex = 0;
      this.slideCount = this.slides.length;
      this.autoplayInterval = null;
      this.autoplayDelay = 5000;
      this.videos = new Map();
      
      this.init();
    }

    init() {
      if (this.slideCount === 0) return;
      
      this.createDots();
      this.collectVideos();
      this.showSlide(0);
      this.attachEvents();
      this.startAutoplay();
    }

    collectVideos() {
      this.slides.forEach((slide, index) => {
        const video = slide.querySelector('.edutourism-hero-video');
        if (video) {
          this.videos.set(index, video);
          
          video.loop = true;
          video.muted = true;
          video.playsInline = true;
          
          video.addEventListener('loadeddata', () => {
            if (index === this.currentIndex) {
              video.play().catch(e => console.log('EdutourismHero: Video play failed:', e));
            }
          });
          
          video.addEventListener('ended', () => {
            video.currentTime = 0;
            video.play().catch(e => console.log('EdutourismHero: Video restart failed:', e));
          });
        }
      });
    }

    createDots() {
      const dotsContainer = document.getElementById('edutourismSliderDotsWrapper');
      dotsContainer.innerHTML = '';
      
      for (let i = 0; i < this.slideCount; i++) {
        const dot = document.createElement('button');
        dot.className = 'edutourism-slider-dot edutourism-slider-dot-' + i + (i === 0 ? ' edutourism-dot-active' : '');
        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
        dot.setAttribute('data-dot-index', i);
        dot.addEventListener('click', () => this.goToSlide(i));
        dotsContainer.appendChild(dot);
      }
      this.dots = document.querySelectorAll('.edutourism-slider-dot');
    }

    showSlide(index) {
      this.slides.forEach((slide, i) => {
        slide.classList.remove('edutourism-slide-active');
        
        const video = this.videos.get(i);
        if (video) {
          video.pause();
        }
      });
      
      this.dots.forEach(dot => dot.classList.remove('edutourism-dot-active'));
      
      this.slides[index].classList.add('edutourism-slide-active');
      this.dots[index].classList.add('edutourism-dot-active');
      this.currentIndex = index;
      
      const currentVideo = this.videos.get(index);
      if (currentVideo) {
        currentVideo.currentTime = 0;
        currentVideo.play().catch(e => console.log('EdutourismHero: Video play failed:', e));
      }
    }

    nextSlide() {
      this.goToSlide((this.currentIndex + 1) % this.slideCount);
    }

    prevSlide() {
      this.goToSlide((this.currentIndex - 1 + this.slideCount) % this.slideCount);
    }

    goToSlide(index) {
      this.showSlide(index);
      this.resetAutoplay();
    }

    startAutoplay() {
      this.autoplayInterval = setInterval(() => this.nextSlide(), this.autoplayDelay);
    }

    resetAutoplay() {
      clearInterval(this.autoplayInterval);
      this.startAutoplay();
    }

    attachEvents() {
      const prevBtn = document.getElementById('edutourismSliderBtnPrev');
      const nextBtn = document.getElementById('edutourismSliderBtnNext');
      
      if (prevBtn) prevBtn.addEventListener('click', () => this.prevSlide());
      if (nextBtn) nextBtn.addEventListener('click', () => this.nextSlide());
      
      if (this.sliderWrapper) {
        this.sliderWrapper.addEventListener('mouseenter', () => clearInterval(this.autoplayInterval));
        this.sliderWrapper.addEventListener('mouseleave', () => this.startAutoplay());
        
        let touchStartX = 0;
        let touchEndX = 0;
        
        this.sliderWrapper.addEventListener('touchstart', (e) => {
          touchStartX = e.changedTouches[0].screenX;
        });
        
        this.sliderWrapper.addEventListener('touchend', (e) => {
          touchEndX = e.changedTouches[0].screenX;
          this.handleSwipe(touchStartX, touchEndX);
        });
      }
    }

    handleSwipe(startX, endX) {
      if (startX - endX > 50) {
        this.nextSlide();
      }
      if (endX - startX > 50) {
        this.prevSlide();
      }
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    new EdutourismHeroSlider();
  });
</script>

</body>
</html>