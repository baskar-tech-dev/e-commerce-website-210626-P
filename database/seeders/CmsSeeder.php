<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // General Brand Information
        Setting::set('store_name', 'Maya Sree South Indian Fashion', 'general', 'text');
        Setting::set('contact_email', 'sales@mayasreefashion.com', 'general', 'text');
        Setting::set('contact_phone', '+91 99442 85102', 'general', 'text');
        Setting::set('currency', 'INR', 'general', 'text');
        Setting::set('store_address', 'Main Studio, Tirupur & Erode, Tamil Nadu, India', 'general', 'text');

        // Announcement Bar
        Setting::set('announcement_bar_enabled', true, 'announcement', 'boolean');
        Setting::set('announcement_bar_text', '🚚 Free Shipping Above ₹999 | 🔄 Easy 7-Day Exchange & Returns | 🌟 South Indian Festive Collection Live', 'announcement', 'text');

        // Hero Section
        Setting::set('hero_title', 'A Legacy of Love,<br>Woven into Every Thread', 'hero', 'text');
        Setting::set('hero_subtitle', 'Discover handcrafted South Indian sarees, designer kurtis, and stretchable ready-made blouses.', 'hero', 'text');
        Setting::set('hero_banner', '/asset/profile/banner.jpg', 'hero', 'text');
        Setting::set('hero_bg', 'var(--color-bg)', 'hero', 'text');
        Setting::set('hero_cta_text', 'SHOP THE COLLECTION', 'hero', 'text');
        Setting::set('hero_cta_link', '/shop', 'hero', 'text');

        Setting::set('hero_slides', [
            [
                'tag' => 'Exclusive South Indian Heritage',
                'script' => 'Handcrafted Perfection',
                'title' => 'Timeless Elegance,<br><span class="gradient-text">South Indian Craftsmanship</span>',
                'desc' => 'Graceful silk sarees, breathable everyday kurtis, and stretchable ready-made blouses tailored for supreme comfort.',
                'leftImage' => '/asset/banner-1-left.png',
                'rightImage' => '/asset/banner-1-right.png',
                'ctaText' => 'EXPLORE SHOP',
                'ctaLink' => '/shop'
            ],
            [
                'tag' => 'Festival & Wedding Special',
                'script' => 'Royal Heritage',
                'title' => 'Grand Festive Weaves<br><span class="gradient-text">& Bridal Blouses</span>',
                'desc' => 'Radiate timeless allure during weddings and celebrations with our rich zari borders and flawless fits.',
                'leftImage' => '/asset/banner-2-left.png',
                'rightImage' => '/asset/banner-2-right.png',
                'ctaText' => 'SHOP OCCASIONS',
                'ctaLink' => '/shop?category=wedding'
            ]
        ], 'hero', 'json');

        // Homepage Section Controls (Toggles & Ordering)
        Setting::set('homepage_sections', [
            ['id' => 'announcement_bar', 'name' => 'Announcement Bar', 'enabled' => true, 'order' => 1],
            ['id' => 'hero_carousel', 'name' => 'Hero Carousel', 'enabled' => true, 'order' => 2],
            ['id' => 'trust_ticker', 'name' => 'Trust Ticker', 'enabled' => true, 'order' => 3],
            ['id' => 'categories', 'name' => 'Shop By Categories', 'enabled' => true, 'order' => 4],
            ['id' => 'featured_collections', 'name' => 'Featured Collections', 'enabled' => true, 'order' => 5],
            ['id' => 'shop_by_occasion', 'name' => 'Shop By Occasion', 'enabled' => true, 'order' => 6],
            ['id' => 'new_arrivals', 'name' => 'New Arrivals', 'enabled' => true, 'order' => 7],
            ['id' => 'trending_collections', 'name' => 'Trending Collections', 'enabled' => true, 'order' => 8],
            ['id' => 'best_sellers', 'name' => 'Best Sellers', 'enabled' => true, 'order' => 9],
            ['id' => 'festival_banner', 'name' => 'Festival Banner', 'enabled' => true, 'order' => 10],
            ['id' => 'customer_reviews', 'name' => 'Customer Reviews', 'enabled' => true, 'order' => 11],
            ['id' => 'instagram_gallery', 'name' => 'Instagram Gallery', 'enabled' => true, 'order' => 12],
            ['id' => 'newsletter', 'name' => 'Newsletter Join', 'enabled' => true, 'order' => 13]
        ], 'homepage', 'json');

        // Banners & Promotional Banners
        Setting::set('festival_banner', [
            'title' => 'South Indian Festive Season Special',
            'subtitle' => 'Get up to 25% Off on Selected Silk Sarees & Designer Blouse Ensembles',
            'coupon_code' => 'MAYASREE25',
            'cta_text' => 'CLAIM FESTIVE OFFER',
            'cta_link' => '/shop',
            'banner_image' => '/asset/festive-collection.jpg',
            'is_active' => true
        ], 'promotions', 'json');

        Setting::set('homepage_banner', [
            'title' => 'Comfort-First Ready Made Stretchable Blouses',
            'subtitle' => 'Engineered with double margins & premium stretch lycra for the perfect fit.',
            'cta_text' => 'SHOP BLOUSES',
            'cta_link' => '/shop?category=blouses',
            'image' => '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg'
        ], 'promotions', 'json');

        // Offers
        Setting::set('offers', [
            [
                'code' => 'FESTIVE100',
                'title' => 'Flat ₹100 OFF',
                'desc' => 'Applicable on first order above ₹999',
                'bg_color' => '#4a0e2e'
            ],
            [
                'code' => 'BUNDLE200',
                'title' => 'Buy Any 2 Save ₹200',
                'desc' => 'Automatic discount applied at checkout',
                'bg_color' => '#d4af37'
            ]
        ], 'promotions', 'json');

        // Testimonials / Reviews
        Setting::set('testimonials', [
            [
                'id' => 1,
                'name' => 'Priya Ramachandran',
                'city' => 'Chennai',
                'rating' => 5,
                'comment' => 'The stretchable blouse quality is outstanding! No stitching hassle at all, and it fits like a glove.',
                'product' => 'Deep Maroon Cotton Lycra Blouse',
                'avatar' => '/asset/testimonials/user1.png'
            ],
            [
                'id' => 2,
                'name' => 'Kavitha Sundaram',
                'city' => 'Coimbatore',
                'rating' => 5,
                'comment' => 'Bought Kora Silk Sarees for a family wedding. The zari border look and soft drape exceeded my expectations.',
                'product' => 'Kora Silk Zari Border Saree',
                'avatar' => '/asset/testimonials/user2.png'
            ],
            [
                'id' => 3,
                'name' => 'Anitha Venkatesh',
                'city' => 'Bengaluru',
                'rating' => 5,
                'comment' => 'Fast 2-day delivery to Bangalore! Soft breathable fabric perfect for everyday office wear.',
                'product' => 'Traditional Soft Cotton Kurti',
                'avatar' => '/asset/testimonials/user3.png'
            ]
        ], 'testimonials', 'json');

        // Trust Badges
        Setting::set('trust_badges', [
            ['icon' => 'Gem', 'title' => 'PREMIUM QUALITY', 'desc' => 'Artisanal South Indian craftsmanship'],
            ['icon' => 'RotateCcw', 'title' => 'EASY 7-DAY RETURNS', 'desc' => 'Hassle-free size exchange policy'],
            ['icon' => 'Lock', 'title' => '100% SECURE PAYMENT', 'desc' => 'Razorpay & Cash on Delivery supported'],
            ['icon' => 'Truck', 'title' => 'EXPRESS DELIVERY', 'desc' => 'Dispatched within 24-48 hours']
        ], 'trust', 'json');

        // Newsletter CMS
        Setting::set('newsletter_title', 'Join The Maya Sree Family', 'newsletter', 'text');
        Setting::set('newsletter_subtitle', 'Subscribe for early access to new collection drops, festive offer codes, and blouse styling guides.', 'newsletter', 'text');

        // Footer CMS
        Setting::set('footer_links', [
            'collections' => [
                ['label' => 'Sarees', 'url' => '/shop?category=sarees'],
                ['label' => 'Readymade Blouses', 'url' => '/shop?category=blouses'],
                ['label' => 'Kurtis & Tunics', 'url' => '/shop?category=kurtis'],
                ['label' => 'Dupattas & Accessories', 'url' => '/shop?category=dupatta']
            ],
            'customer_care' => [
                ['label' => 'Track Orders', 'url' => '/my-account?tab=orders'],
                ['label' => 'About Us', 'url' => '/about-us'],
                ['label' => 'Contact Us', 'url' => '/contact-us'],
                ['label' => 'FAQs', 'url' => '/faq']
            ],
            'policies' => [
                ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                ['label' => 'Terms & Conditions', 'url' => '/terms-conditions'],
                ['label' => 'Refund Policy', 'url' => '/refund-policy'],
                ['label' => 'Shipping Policy', 'url' => '/shipping-policy']
            ]
        ], 'footer', 'json');
    }
}
