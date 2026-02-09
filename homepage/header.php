<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="EduTourism - Educational Tourism in Sri Lanka">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <title>Edutourism.lk</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,500,600,700,800,900&display=swap" rel="stylesheet">

    <!-- Tab Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">

    <!-- CSS Styles -->
    <link rel='stylesheet' href='css/bootstrap.min.css' type='text/css'>
    <link rel='stylesheet' href='css/font-awesome.min.css' type='text/css'>
    <link rel='stylesheet' href='css/themify-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/elegant-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/owl.carousel.min.css' type='text/css'>
    <link rel='stylesheet' href='css/slicknav.min.css' type='text/css'>
    <link rel='stylesheet' href='css/header.css' type='text/css'>

    <style>
        /* Space for Google Translate bar */
        body {
            margin-top: 0;
            transition: margin-top 0.3s ease;
        }

        body.translated {
            margin-top: 0px;
        }

        /* Hide the default Google Translate element */
        #google_translate_element {
            display: none;
        }

        /* Optional: Style the Google Translate bar */
        .goog-te-banner-frame {
            top: 0 !important;
        }

        .skiptranslate {
            top: 0 !important;
        }
    </style>
</head>

<body>
    <!-- Mobile Overlay -->
    <div class="edutourism-mobile-overlay" id="edutourismMobileOverlay"></div>

    <!-- Header Section Begin -->
    <header class="edutourism-header-section" id="edutourismHeader">
        <div class="edutourism-header-wrapper">
            <div class="container-fluid">
                <div class="edutourism-header-inner" id="edutourismHeaderInner">
                    <!-- Logo Section -->
                    <div class="edutourism-logo-section" id="edutourismLogoSection">
                        <div class="edutourism-logo">
                            <a href="index.php" id="edutourismLogoLink">
                                <img src="img/logo.png" alt="EduTourism Logo" id="edutourismLogoImg">
                            </a>
                        </div>
                    </div>

                    <!-- Navigation Menu -->
                    <div class="edutourism-nav-menu-section" id="edutourismNavMenuSection">
                        <nav class="edutourism-nav-menu" id="edutourismNavMenu">
                            <ul class="edutourism-nav-list" id="edutourismNavList">
                                <?php
                                // Navigation menu items
                                $menu_items = [
                                    'Home' => ['url' => 'index.php', 'key' => 'home'],
                                    'Past Tours' => ['url' => 'pasttours.php', 'key' => 'pasttours'],
                                    'Testimonials' => ['url' => 'testimonials.php', 'key' => 'testimonials'],
                                    'About Us' => ['url' => 'aboutus.php', 'key' => 'aboutus'],
                                    'Contact Us' => ['url' => 'contact.php', 'key' => 'contact'],
                                    'FAQs' => ['url' => 'faqs.php', 'key' => 'faqs']
                                ];

                                // Generate menu items
                                foreach ($menu_items as $label => $item) {
                                    // Check for active page
                                    $active_class = '';
                                    if (isset($active) && strtolower($active) == strtolower($item['key'])) {
                                        $active_class = 'edutourism-nav-item-active';
                                    }

                                    $item_id = 'edutourismNavItem' . ucfirst($item['key']);
                                    $link_id = 'edutourismNavLink' . ucfirst($item['key']);

                                    echo '<li class="edutourism-nav-item ' . $active_class . '" id="' . $item_id . '">';
                                    echo '<a href="' . $item['url'] . '" class="edutourism-nav-link" id="' . $link_id . '">';
                                    echo '<span class="edutourism-nav-text">' . $label . '</span>';
                                    echo '<span class="edutourism-nav-liquid"></span>';
                                    echo '</a>';
                                    echo '</li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>

                    <!-- Language Switcher Section -->
                    <div class="edutourism-lang-section" id="edutourismLangSection">
                        <div class="edutourism-lang-switcher" id="edutourismLangSwitcher">
                            <div id="google_translate_element"></div>
                            <select id="edutourismLanguageSelect" class="edutourism-language-select" onchange="changeLang(this.value)">
                                <option value="">Select Language</option>
                                <option value="en">English</option>
                                <option value="si">සිංහල</option>
                                <option value="ta">தமிழ்</option>
                            </select>
                        </div>

                        <!-- Mobile Menu Button -->
                        <div class="edutourism-mobile-menu-btn" id="edutourismMobileMenuBtn">
                            <span class="edutourism-burger-line edutourism-burger-line-1"></span>
                            <span class="edutourism-burger-line edutourism-burger-line-2"></span>
                            <span class="edutourism-burger-line edutourism-burger-line-3"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Header End -->

    <script>
        // Initialize Google Translate
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,si,ta',
                autoDisplay: false
            }, 'google_translate_element');
        }

        // Change language function
        function changeLang(lang) {
            if (!lang) return;
            
            const select = document.querySelector('.goog-te-combo');
            if (select) {
                select.value = lang;
                select.dispatchEvent(new Event('change'));
                
                // Add top margin when language is changed (not English)
                if (lang !== 'en') {
                    setTimeout(() => {
                        document.body.classList.add('translated');
                    }, 500);
                } else {
                    document.body.classList.remove('translated');
                }
            }
        }

        // Sticky Header Scroll Effect
        let lastScrollTop = 0;
        const header = document.getElementById('edutourismHeader');
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 50) {
                header.classList.add('edutourism-header-scrolled');
            } else {
                header.classList.remove('edutourism-header-scrolled');
            }
            
            lastScrollTop = scrollTop;
        });

        // Check if page is already translated on load
        document.addEventListener('DOMContentLoaded', function() {
            // Monitor for Google Translate bar
            const observer = new MutationObserver(function(mutations) {
                const translateBar = document.querySelector('.goog-te-banner-frame');
                if (translateBar && translateBar.style.display !== 'none') {
                    document.body.classList.add('translated');
                } else {
                    // Check if body has translate class
                    if (!document.body.classList.contains('translated') && 
                        document.documentElement.classList.contains('translated-ltr')) {
                        document.body.classList.add('translated');
                    }
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style']
            });

            // Mobile menu functionality
            const mobileMenuBtn = document.getElementById('edutourismMobileMenuBtn');
            const navMenuSection = document.getElementById('edutourismNavMenuSection');
            const mobileOverlay = document.getElementById('edutourismMobileOverlay');
            const body = document.body;

            // Function to open mobile menu
            function openMobileMenu() {
                if (mobileMenuBtn && navMenuSection && mobileOverlay) {
                    mobileMenuBtn.classList.add('edutourism-mobile-menu-active');
                    navMenuSection.classList.add('edutourism-nav-active');
                    mobileOverlay.classList.add('edutourism-overlay-active');
                    body.classList.add('edutourism-menu-open');
                }
            }

            // Function to close mobile menu
            function closeMobileMenu() {
                if (mobileMenuBtn && navMenuSection && mobileOverlay) {
                    mobileMenuBtn.classList.remove('edutourism-mobile-menu-active');
                    navMenuSection.classList.remove('edutourism-nav-active');
                    mobileOverlay.classList.remove('edutourism-overlay-active');
                    body.classList.remove('edutourism-menu-open');
                }
            }

            // Open/close menu when burger is clicked
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (this.classList.contains('edutourism-mobile-menu-active')) {
                        closeMobileMenu();
                    } else {
                        openMobileMenu();
                    }
                });
            }

            // Close menu when overlay is clicked
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    closeMobileMenu();
                });
            }

            // Close menu when navigation link is clicked (mobile only)
            const navLinks = document.querySelectorAll('.edutourism-nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        closeMobileMenu();
                    }
                });
            });

            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileMenu();
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991) {
                    closeMobileMenu();
                }
            });
        });
    </script>

    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>

</html>