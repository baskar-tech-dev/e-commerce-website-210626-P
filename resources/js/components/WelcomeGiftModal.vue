<template>
  <div>
    <!-- Discreet Festive Floating Entry Point (Bottom-Left: does NOT overlap WhatsApp or Bottom Nav) -->
    <Transition name="float-entry-fade">
      <div 
        v-if="showFloatingEntry" 
        class="festive-float-entry"
        role="complementary"
        aria-label="Festive gift shortcut"
      >
        <button 
          class="festive-float-pill"
          @click="openModal"
          aria-label="Reveal your festive Diwali gift"
          title="Click to reveal your Diwali offer"
        >
          <span class="pill-diya-icon" aria-hidden="true">
            <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="mini-diya-svg">
              <path d="M6 19C6 19 8 26 16 26C24 26 26 19 26 19C26 19 20 21 16 21C12 21 6 19 6 19Z" fill="url(#miniDiyaGrad)" stroke="#C8A15A" stroke-width="1.2"/>
              <ellipse cx="16" cy="19" rx="10" ry="2.5" fill="#E6C887" stroke="#9A7B38" stroke-width="0.8"/>
              <path d="M16 7C16 7 13 12 13 14C13 15.6569 14.3431 17 16 17C17.6569 17 19 15.6569 19 14C19 12 16 7 16 7Z" fill="url(#miniFlameGrad)"/>
              <defs>
                <linearGradient id="miniDiyaGrad" x1="6" y1="19" x2="26" y2="26" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#C8A15A"/>
                  <stop offset="1" stop-color="#7A561E"/>
                </linearGradient>
                <linearGradient id="miniFlameGrad" x1="16" y1="7" x2="16" y2="17" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#FFF0A8"/>
                  <stop offset="0.6" stop-color="#F59E0B"/>
                  <stop offset="1" stop-color="#DC2626"/>
                </linearGradient>
              </defs>
            </svg>
          </span>
          <span class="pill-label desktop-text">REVEAL YOUR DIWALI GIFT</span>
          <span class="pill-label mobile-text">Diwali Gift ✨</span>
          <span class="pill-sparkle" aria-hidden="true">✨</span>
        </button>
        <button 
          class="pill-dismiss-btn" 
          @click.stop="dismissFloatingEntry" 
          aria-label="Dismiss Diwali offer shortcut"
          title="Dismiss"
        >
          ✕
        </button>
      </div>
    </Transition>

    <!-- Fullscreen Blurred Backdrop Overlay & Modal Dialog -->
    <Transition name="gift-modal-fade">
      <div 
        v-if="isModalOpen" 
        class="gift-modal-backdrop" 
        @click.self="closeModal" 
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="gift-modal-title"
        ref="modalRef"
      >
        <!-- Full-Screen Bright Silk Cloth Splash Screen Transition -->
        <Transition name="fullscreen-silk-fade">
          <div 
            v-if="stage === 'opening' && giftConfig.diwali_reveal_enabled" 
            class="fullscreen-silk-splash" 
            aria-hidden="true"
          >
            <!-- 1. Real 4K Flowing White Silk Fabric Texture Backdrop -->
            <div class="silk-fabric-backdrop"></div>

            <!-- 2. Warm Golden Pearl Ambient Luminous Wash -->
            <div class="silk-ambient-wash"></div>

            <!-- 3. Dynamic Volumetric Silk Cloth Drapes (SVG Waves) -->
            <div class="silk-drape-container">
              <svg 
                class="silk-drape-svg" 
                viewBox="0 0 1440 900" 
                preserveAspectRatio="none" 
                fill="none" 
                xmlns="http://www.w3.org/2000/svg"
              >
                <defs>
                  <!-- Bright Golden Champagne Silk Satin Gradient -->
                  <linearGradient id="brightSilkGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.96"/>
                    <stop offset="25%" stop-color="#FFFDF5" stop-opacity="0.92"/>
                    <stop offset="50%" stop-color="#FDF3DA" stop-opacity="0.88"/>
                    <stop offset="75%" stop-color="#E8C988" stop-opacity="0.85"/>
                    <stop offset="100%" stop-color="#C8A15A" stop-opacity="0.9"/>
                  </linearGradient>

                  <!-- Regal Rose Gold & Silk Undertone Gradient -->
                  <linearGradient id="brightSilkGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.94"/>
                    <stop offset="35%" stop-color="#FEF5DC" stop-opacity="0.88"/>
                    <stop offset="70%" stop-color="#E2BA70" stop-opacity="0.78"/>
                    <stop offset="100%" stop-color="#7B2340" stop-opacity="0.55"/>
                  </linearGradient>

                  <!-- High-Gloss Specular Pleat Sheen Gradient -->
                  <linearGradient id="silkHighlightGrad" x1="0%" y1="40%" x2="100%" y2="60%">
                    <stop offset="0%" stop-color="transparent"/>
                    <stop offset="35%" stop-color="rgba(255, 255, 255, 0.4)"/>
                    <stop offset="50%" stop-color="rgba(255, 255, 255, 0.95)"/>
                    <stop offset="65%" stop-color="rgba(255, 255, 255, 0.4)"/>
                    <stop offset="100%" stop-color="transparent"/>
                  </linearGradient>
                </defs>

                <!-- Upper Left Billowing Silk Saree Drape -->
                <path 
                  class="silk-wave-path silk-wave--1"
                  d="M-100 -50 C 320 200, 560 -60, 960 160 C 1240 320, 1420 110, 1600 220 L 1600 -100 L -100 -100 Z" 
                  fill="url(#brightSilkGrad1)"
                />

                <!-- Lower Right Cascading Silk Pallu Drape -->
                <path 
                  class="silk-wave-path silk-wave--2"
                  d="M-100 700 C 350 520, 680 840, 1080 620 C 1320 480, 1480 680, 1600 580 L 1600 1000 L -100 1000 Z" 
                  fill="url(#brightSilkGrad2)"
                />

                <!-- Dynamic Diagonal Satin Crease Pleat -->
                <path 
                  class="silk-wave-path silk-wave--pleat"
                  d="M-50 220 Q 520 490 920 330 T 1550 510 L 1550 680 Q 920 500 520 660 T -50 390 Z"
                  fill="url(#silkHighlightGrad)"
                  opacity="0.75"
                />
              </svg>
            </div>

            <!-- 4. Luminous Silk Specular Sheen Sweep (Sunlight gliding over satin) -->
            <div class="silk-sheen-sweep"></div>

            <!-- 5. Center Golden Core Bloom -->
            <div class="silk-center-glow"></div>
          </div>
        </Transition>

        <div 
          class="gift-modal-container"
          :class="{ 'is-diwali-theme': giftConfig.diwali_reveal_enabled }"
        >
          <!-- Close Button -->
          <button 
            class="gift-modal-close-btn" 
            @click="closeModal" 
            aria-label="Close modal"
          >
            ✕
          </button>

          <!-- ========================================================== -->
          <!-- DIWALI GOLDEN RANGOLI REVEAL EXPERIENCE                    -->
          <!-- ========================================================== -->
          <template v-if="giftConfig.diwali_reveal_enabled">
            <!-- Stage 1 & 2: Rangoli Unrevealed / Revealing Animation -->
            <div v-if="stage === 'box' || stage === 'opening'" class="diwali-stage">
              <div class="gift-header-text">
                <span class="gift-festive-tag">✦ A FESTIVE CELEBRATION ✦</span>
                <h2 id="gift-modal-title" class="gift-title">
                  {{ giftConfig.title || 'A Little Diwali Magic' }}
                </h2>
                <p class="gift-subtitle">
                  {{ giftConfig.subtitle || 'A festive surprise is waiting for you.' }}
                </p>
              </div>

              <!-- Interactive Golden Rangoli & Stationary Diya Visual Area -->
              <div 
                class="rangoli-interactive-wrapper"
                :class="{ 'is-revealing': stage === 'opening' }"
                @click="startGiftOpening"
                role="button"
                tabindex="0"
                @keydown.enter="startGiftOpening"
                aria-label="Click to reveal your Diwali gift"
              >
                <!-- Central Intricate Golden Rangoli Vector Art (Gentle Sacred Spin) -->
                <div class="rangoli-disc" :class="{ 'rangoli-burst': stage === 'opening' }">
                  <svg 
                    viewBox="0 0 240 240" 
                    fill="none" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="golden-rangoli-svg"
                  >
                    <!-- Outer Halo Rings -->
                    <circle cx="120" cy="120" r="114" stroke="#C8A15A" stroke-width="1.2" stroke-dasharray="3 4" opacity="0.6"/>
                    <circle cx="120" cy="120" r="108" stroke="#E6C887" stroke-width="1" opacity="0.8"/>
                    <circle cx="120" cy="120" r="98" stroke="#C8A15A" stroke-width="1.5"/>

                    <!-- 12-Point Sacred Lotus Petals Outer -->
                    <g stroke="#C8A15A" stroke-width="1.4" fill="rgba(200, 161, 90, 0.08)">
                      <!-- Repeated symmetric petals rotated around center -->
                      <path v-for="deg in [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330]" :key="deg"
                        :transform="`rotate(${deg} 120 120)`"
                        d="M120 22 C113 45 102 70 120 98 C138 70 127 45 120 22 Z"
                      />
                    </g>

                    <!-- Inner Decorative Petal Ring -->
                    <g stroke="#E6C887" stroke-width="1.2" fill="rgba(247, 231, 180, 0.15)">
                      <path v-for="deg in [15, 45, 75, 105, 135, 165, 195, 225, 255, 285, 315, 345]" :key="deg"
                        :transform="`rotate(${deg} 120 120)`"
                        d="M120 45 C116 65 110 82 120 98 C130 82 124 65 120 45 Z"
                      />
                    </g>

                    <!-- Concentric Sacred Circles & Center Bed -->
                    <circle cx="120" cy="120" r="64" stroke="#C8A15A" stroke-width="1.2" stroke-dasharray="2 3"/>
                    <circle cx="120" cy="120" r="48" stroke="#E6C887" stroke-width="1.4"/>
                    <circle cx="120" cy="120" r="32" fill="rgba(110, 31, 58, 0.14)" stroke="#C8A15A" stroke-width="1.2"/>

                    <!-- Outer Golden Bindus -->
                    <circle v-for="deg in [0, 45, 90, 135, 180, 225, 270, 315]" :key="'b1-'+deg"
                      :cx="120 + 82 * Math.cos(deg * Math.PI / 180)"
                      :cy="120 + 82 * Math.sin(deg * Math.PI / 180)"
                      r="2.5" fill="#C8A15A"
                    />
                  </svg>
                </div>

                <!-- Central Diya Lamp: CONSTANT STATIONARY POSITION (Stationary, Flame Always Points Up) -->
                <div class="central-constant-diya" aria-hidden="true">
                  <svg 
                    viewBox="0 0 48 52" 
                    fill="none" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="constant-diya-svg"
                  >
                    <defs>
                      <linearGradient id="constDiyaBowl" x1="4" y1="34" x2="44" y2="48" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FCE7B0"/>
                        <stop offset="0.4" stop-color="#C8A15A"/>
                        <stop offset="1" stop-color="#6E1F3A"/>
                      </linearGradient>
                      <linearGradient id="constDiyaFlame" x1="24" y1="5" x2="24" y2="30" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FFFFFF"/>
                        <stop offset="0.25" stop-color="#FEF08A"/>
                        <stop offset="0.65" stop-color="#F59E0B"/>
                        <stop offset="1" stop-color="#DC2626"/>
                      </linearGradient>
                      <radialGradient id="constFlameAura" cx="0.5" cy="0.5" r="0.5">
                        <stop stop-color="#FBBF24" stop-opacity="0.75"/>
                        <stop offset="1" stop-color="#FBBF24" stop-opacity="0"/>
                      </radialGradient>
                    </defs>

                    <!-- Diya Base / Brass Bowl -->
                    <path 
                      d="M5 34 C5 34 10 47 24 47 C38 47 43 34 43 34 C43 34 34 37.5 24 37.5 C14 37.5 5 34 5 34 Z" 
                      fill="url(#constDiyaBowl)" 
                      stroke="#C8A15A" 
                      stroke-width="1.4"
                    />
                    <!-- Diya Rim -->
                    <ellipse cx="24" cy="34" rx="19" ry="4.2" fill="#FDF4DC" stroke="#A88234" stroke-width="1.1"/>
                    <ellipse cx="24" cy="34" rx="14" ry="2.6" fill="#7A561E" opacity="0.6"/>

                    <!-- Diya Flame Glow Halo -->
                    <circle cx="24" cy="18" r="15" fill="url(#constFlameAura)" class="diya-aura"/>

                    <!-- Diya Constant Upright Flickering Flame -->
                    <path 
                      d="M24 5 C24 5 16 16.5 16 22 C16 26.4 19.6 30 24 30 C28.4 30 32 26.4 32 22 C32 16.5 24 5 24 5 Z" 
                      fill="url(#constDiyaFlame)" 
                      class="diwali-flame-flicker"
                    />
                    <!-- Inner White Hot Core -->
                    <ellipse cx="24" cy="23" rx="3.5" ry="5.5" fill="#FFFFFF" opacity="0.9"/>
                  </svg>
                </div>
              </div>

              <!-- Primary Diwali CTA -->
              <div v-if="stage === 'box'" class="gift-cta-container">
                <button 
                  class="btn-reveal-diwali" 
                  @click="startGiftOpening"
                  aria-label="Reveal My Offer"
                >
                  <span class="btn-sparkle-left">✨</span>
                  <span>{{ giftConfig.cta_text || 'REVEAL MY OFFER' }}</span>
                  <span class="btn-sparkle-right">✨</span>
                </button>
              </div>
            </div>

            <!-- Stage 3: Revealed Diwali Coupon Card Stage -->
            <Transition name="coupon-reveal-fade">
              <div v-if="stage === 'revealed'" class="diwali-revealed-stage">
                <!-- Top Diya Motif & Festive Tag -->
                <div class="revealed-header">
                  <div class="revealed-diya-badge" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" class="revealed-diya-svg">
                      <path d="M6 19C6 19 8 26 16 26C24 26 26 19 26 19C26 19 20 21 16 21C12 21 6 19 6 19Z" fill="url(#revDiyaGrad)" stroke="#C8A15A" stroke-width="1.2"/>
                      <ellipse cx="16" cy="19" rx="10" ry="2.5" fill="#F7E7B4" stroke="#9A7B38" stroke-width="0.8"/>
                      <path d="M16 6C16 6 12.5 12 12.5 14.5C12.5 16.433 14.067 18 16 18C17.933 18 19.5 16.433 19.5 14.5C19.5 12 16 6 16 6Z" fill="url(#revFlameGrad)"/>
                      <defs>
                        <linearGradient id="revDiyaGrad" x1="6" y1="19" x2="26" y2="26" gradientUnits="userSpaceOnUse">
                          <stop stop-color="#E6C887"/>
                          <stop offset="0.5" stop-color="#C8A15A"/>
                          <stop offset="1" stop-color="#6E1F3A"/>
                        </linearGradient>
                        <linearGradient id="revFlameGrad" x1="16" y1="6" x2="16" y2="18" gradientUnits="userSpaceOnUse">
                          <stop stop-color="#FFFBEB"/>
                          <stop offset="0.3" stop-color="#FBBF24"/>
                          <stop offset="0.8" stop-color="#F97316"/>
                          <stop offset="1" stop-color="#DC2626"/>
                        </linearGradient>
                      </defs>
                    </svg>
                  </div>
                  <span class="revealed-festive-tag">✦ DIWALI FESTIVE GIFT UNLOCKED ✦</span>
                  <h3 class="revealed-main-title">
                    {{ cleanDiscountText(giftConfig.discount_text) || (giftConfig.coupon ? formatCouponHeadline(giftConfig.coupon) : 'Enjoy 10% OFF') }}
                  </h3>
                  <p class="revealed-subtitle">
                    Valid across our entire festive collection
                  </p>
                </div>

                <!-- Active & Eligible Coupon Voucher Card -->
                <div v-if="giftConfig.is_coupon_valid && (giftConfig.coupon || giftConfig.coupon_code)" class="luxury-voucher-card">
                  <!-- Voucher Notch Cutouts for Ticket Effect -->
                  <div class="voucher-notch voucher-notch-left" aria-hidden="true"></div>
                  <div class="voucher-notch voucher-notch-right" aria-hidden="true"></div>

                  <div class="voucher-header">
                    <span class="voucher-eyebrow">USE PROMO CODE AT CHECKOUT</span>
                    <span class="voucher-member-badge">DIWALI SPECIAL</span>
                  </div>

                  <!-- Code Display and Copy Button in One Cohesive Row -->
                  <div class="voucher-code-container">
                    <div class="voucher-code-cell">
                      <span class="voucher-code-string">{{ giftConfig.coupon?.code || giftConfig.coupon_code }}</span>
                    </div>
                    <button 
                      type="button"
                      class="voucher-action-btn"
                      :class="{ 'is-copied': isCodeCopied }"
                      @click="copyCouponCode"
                      :aria-label="isCodeCopied ? 'Code copied to clipboard' : 'Copy promo code'"
                    >
                      <template v-if="!isCodeCopied">
                        <svg class="copy-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>COPY CODE</span>
                      </template>
                      <template v-else>
                        <svg class="check-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>COPIED!</span>
                      </template>
                    </button>
                  </div>

                  <!-- Clean, Refined Eligibility Terms -->
                  <div class="voucher-terms-row">
                    <span v-if="giftConfig.coupon?.min_order_value">
                      Min. order ₹{{ Number(giftConfig.coupon.min_order_value).toLocaleString('en-IN') }}
                    </span>
                    <span v-if="giftConfig.coupon?.min_order_value && giftConfig.coupon?.max_discount" class="terms-bullet">•</span>
                    <span v-if="giftConfig.coupon?.max_discount">
                      Max savings ₹{{ Number(giftConfig.coupon.max_discount).toLocaleString('en-IN') }}
                    </span>
                    <span v-if="(giftConfig.coupon?.min_order_value || giftConfig.coupon?.max_discount) && giftConfig.coupon?.expires_at" class="terms-bullet">•</span>
                    <span v-if="giftConfig.coupon?.expires_at">
                      Valid till {{ formatDate(giftConfig.coupon.expires_at) }}
                    </span>
                  </div>
                </div>

                <!-- Unavailable Coupon Fallback -->
                <div v-else class="coupon-unavailable-box">
                  <span class="unavailable-icon">✨</span>
                  <h4>Festive Surprise Received</h4>
                  <p>
                    This special offer has concluded its promotional run, but our festive collection features seasonal discounts across ready-made blouses, silk wear, and ethnic couture.
                  </p>
                </div>

                <!-- Primary CTA: SHOP NOW -->
                <div class="revealed-cta-wrapper">
                  <button 
                    type="button" 
                    class="btn-diwali-shop-now" 
                    @click="handleShopNow"
                  >
                    <span>SHOP NOW</span>
                    <span class="shop-arrow" aria-hidden="true">➔</span>
                  </button>
                </div>

                <!-- Refined, fluid auth note -->
                <p class="revealed-auth-note">
                  Have an account? <router-link to="/signin" class="auth-sublink" @click="closeModal">Sign In</router-link> to save your orders and wishlist.
                </p>
              </div>
            </Transition>
          </template>

          <!-- ========================================================== -->
          <!-- STANDARD 3D IVORY GIFT BOX EXPERIENCE (Fallback Mode)       -->
          <!-- ========================================================== -->
          <template v-else>
            <!-- Stage 1: Gift Box Interactive Stage -->
            <div v-if="stage === 'box' || stage === 'opening'" class="gift-box-stage">
              <div class="gift-header-text">
                <span class="gift-welcome-tag">Exclusive Member Perk</span>
                <h2 id="gift-modal-title" class="gift-title">{{ giftConfig.title || 'A Special Gift Awaits You' }}</h2>
                <p class="gift-subtitle">{{ giftConfig.subtitle || 'Every new member deserves a warm welcome.' }}</p>
              </div>

              <!-- 3D Ivory Gift Box Container (Clickable) -->
              <div 
                class="gift-box-3d-wrapper" 
                :class="{ 'is-opening': stage === 'opening', 'is-clickable': stage === 'box' }"
                @click="startGiftOpening"
                role="button"
                tabindex="0"
                @keydown.enter="startGiftOpening"
                aria-label="Click gift box to open gift"
                title="Click to Open Gift"
              >
                <!-- Canvas for Golden Particle Sparkles -->
                <canvas ref="particleCanvas" class="sparkle-canvas"></canvas>

                <!-- Luxury Gift Box Structure -->
                <div class="gift-box">
                  <!-- Lid Component -->
                  <div class="gift-box-lid" :class="{ 'lid-open': isLidOpen }">
                    <div class="lid-top">
                      <div class="paisley-texture"></div>
                      <div class="satin-bow-container" :class="{ 'ribbon-untied': isRibbonUntied }">
                        <div class="bow-loop bow-loop-left"></div>
                        <div class="bow-loop bow-loop-right"></div>
                        <div class="bow-center"></div>
                        <div class="bow-tail bow-tail-left"></div>
                        <div class="bow-tail bow-tail-right"></div>
                      </div>
                    </div>
                    <div class="lid-lip"></div>
                  </div>

                  <!-- Box Base -->
                  <div class="gift-box-body">
                    <div class="body-texture"></div>
                    <div class="ribbon-vertical-front" :class="{ 'ribbon-untied': isRibbonUntied }"></div>

                    <!-- Emerging Coupon Card inside box -->
                    <div class="coupon-card" :class="{ 'coupon-reveal': isCouponRevealed }">
                      <div class="coupon-border">
                        <span class="coupon-badge">WELCOME GIFT</span>
                        <h3 class="coupon-title">🎉 Congratulations!</h3>
                        <p class="coupon-discount">{{ giftConfig.discount_text || 'Enjoy 10% OFF Your First Order' }}</p>
                        <div class="coupon-code-box">
                          <span class="code-label">PROMO CODE:</span>
                          <strong class="code-value">{{ giftConfig.coupon_code || 'WELCOME10' }}</strong>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Open Gift CTA Button (Stage 1) -->
              <div v-if="stage === 'box'" class="gift-cta-container">
                <button 
                  class="btn-open-gift" 
                  @click="startGiftOpening"
                  aria-label="Open Gift Box"
                >
                  <span>Open Gift</span>
                  <span class="btn-arrow">✨</span>
                </button>
              </div>
            </div>

            <!-- Stage 2: Account Creation Form Stage (Standard mode) -->
            <Transition name="form-fade">
              <div v-if="stage === 'signup'" class="gift-signup-stage">
                <div class="signup-coupon-banner">
                  <span class="banner-icon">🎁</span>
                  <div class="banner-text">
                    <strong>{{ giftConfig.discount_text || 'Welcome Offer Unlocked!' }}</strong>
                    <span>Code: <strong>{{ giftConfig.coupon_code || 'WELCOME10' }}</strong> automatically applied</span>
                  </div>
                </div>

                <div class="signup-header">
                  <h2 class="signup-title">Claim Your Gift & Join Maya Sree</h2>
                  <p class="signup-subtitle">Create an account to unlock your welcome reward and instant boutique benefits.</p>
                </div>

                <!-- Signup Form -->
                <form @submit.prevent="handleSignup" class="signup-form">
                  <div v-if="errorMessage" class="form-error-alert" role="alert">
                    {{ errorMessage }}
                  </div>
                  <div v-if="successMessage" class="form-success-alert" role="status">
                    {{ successMessage }}
                  </div>

                  <div class="form-field">
                    <label for="gift-signup-email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                      <input 
                        type="email" 
                        id="gift-signup-email" 
                        v-model="form.email" 
                        placeholder="name@example.com" 
                        class="form-input" 
                        required
                      >
                    </div>
                  </div>

                  <div class="form-field">
                    <label for="gift-signup-password" class="form-label">Password</label>
                    <div class="input-wrapper">
                      <input 
                        type="password" 
                        id="gift-signup-password" 
                        v-model="form.password" 
                        placeholder="••••••••" 
                        class="form-input" 
                        required
                      >
                    </div>
                  </div>

                  <div class="form-field">
                    <label for="gift-signup-confirm-password" class="form-label">Confirm Password</label>
                    <div class="input-wrapper">
                      <input 
                        type="password" 
                        id="gift-signup-confirm-password" 
                        v-model="form.confirmPassword" 
                        placeholder="••••••••" 
                        class="form-input" 
                        required
                      >
                    </div>
                  </div>

                  <button 
                    type="submit" 
                    class="btn-submit-signup" 
                    :disabled="isSubmitting"
                  >
                    <span v-if="!isSubmitting">Create My Account & Claim My Gift</span>
                    <span v-else class="spinner-loading">Setting up your account...</span>
                  </button>
                </form>

                <div class="signin-redirect">
                  Already have an account? 
                  <router-link to="/signin" class="signin-link" @click="closeModal">Sign In</router-link>
                </div>
              </div>
            </Transition>
          </template>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

// States
const isModalOpen = ref(false);
const stage = ref('box'); // 'box' -> 'opening' -> ('revealed' for diwali | 'signup' for standard)
const isFloatingDismissed = ref(false);
const isCodeCopied = ref(false);

const giftConfig = ref({
  is_enabled: true,
  diwali_reveal_enabled: true,
  coupon_code: 'SAVE5',
  discount_text: 'Enjoy 10% OFF',
  title: 'A Little Diwali Magic',
  subtitle: 'A festive surprise is waiting for you.',
  cta_text: 'REVEAL MY OFFER',
  is_coupon_valid: true,
  coupon: null
});

// Floating entry display condition
const showFloatingEntry = computed(() => {
  return (
    giftConfig.value.is_enabled &&
    giftConfig.value.diwali_reveal_enabled &&
    giftConfig.value.is_coupon_valid &&
    !isModalOpen.value &&
    !isFloatingDismissed.value
  );
});

const dismissFloatingEntry = () => {
  isFloatingDismissed.value = true;
  sessionStorage.setItem('ms_diwali_bubble_dismissed', 'true');
};

const fetchGiftConfig = async () => {
  try {
    const response = await axios.get('/api/storefront/welcome-gift');
    if (response.data && response.data.success && response.data.data) {
      giftConfig.value = {
        ...giftConfig.value,
        ...response.data.data
      };
    }
  } catch (e) {
    console.error('Failed to load storefront welcome gift config:', e);
  }
};

const isRibbonUntied = ref(false);
const isLidOpen = ref(false);
const isCouponRevealed = ref(false);

const form = ref({
  email: '',
  password: '',
  confirmPassword: ''
});

const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const modalRef = ref(null);
const particleCanvas = ref(null);

let scrollListener = null;
let idleTimer = null;
let audioCtx = null;
let copyTimeout = null;

// Checks reduced motion preference
const prefersReducedMotion = () => {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

// Check if muted or restricted
const canPlayAudio = () => {
  if (window.matchMedia('(prefers-reduced-audio: reduce)').matches) return false;
  return true;
};

// Web Audio API Synthesizer
const playSynthesizedAudio = (type) => {
  if (!canPlayAudio()) return;
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;
    if (!audioCtx) audioCtx = new AudioContextClass();
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }

    const now = audioCtx.currentTime;

    if (type === 'sparkle' || type === 'diwali-blast') {
      // Golden celebratory burst chime (pentatonic scale ascending sparkle)
      [523.25, 659.25, 783.99, 1046.50, 1318.51, 1567.98].forEach((freq, index) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, now + index * 0.05);
        gain.gain.setValueAtTime(0.08, now + index * 0.05);
        gain.gain.exponentialRampToValueAtTime(0.001, now + index * 0.05 + 0.65);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now + index * 0.05);
        osc.stop(now + index * 0.05 + 0.65);
      });
    } else if (type === 'ribbon') {
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(300, now);
      osc.frequency.exponentialRampToValueAtTime(150, now + 0.3);
      gain.gain.setValueAtTime(0.08, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.3);
    } else if (type === 'chime') {
      [523.25, 659.25, 783.99, 1046.50].forEach((freq, index) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, now + index * 0.08);
        gain.gain.setValueAtTime(0.1, now + index * 0.08);
        gain.gain.exponentialRampToValueAtTime(0.001, now + index * 0.08 + 0.5);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now + index * 0.08);
        osc.stop(now + index * 0.08 + 0.5);
      });
    }
  } catch (e) {
    // Graceful fallback
  }
};

// Diwali Blast & Golden Confetti Particle System
let particleAnimFrame = null;
const triggerDiwaliBlast = () => {
  if (!particleCanvas.value || prefersReducedMotion()) return;
  const canvas = particleCanvas.value;
  const ctx = canvas.getContext('2d');
  
  canvas.width = canvas.parentElement.offsetWidth || 380;
  canvas.height = canvas.parentElement.offsetHeight || 340;

  const particles = [];
  const particleCount = 75;
  const colors = ['#C8A15A', '#E6C887', '#F7E7B4', '#6E1F3A', '#FFFFFF', '#D4AF37'];

  for (let i = 0; i < particleCount; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * 6 + 2.5;
    particles.push({
      x: canvas.width / 2,
      y: canvas.height / 2,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed,
      size: Math.random() * 4.5 + 2,
      alpha: 1,
      decay: Math.random() * 0.018 + 0.012,
      color: colors[Math.floor(Math.random() * colors.length)],
      isStar: Math.random() > 0.45,
      rotation: Math.random() * Math.PI,
      rotSpeed: (Math.random() - 0.5) * 0.15
    });
  }

  const render = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    let active = 0;

    particles.forEach(p => {
      if (p.alpha > 0) {
        active++;
        p.x += p.vx;
        p.y += p.vy;
        p.vy += 0.06; // subtle gravity
        p.alpha -= p.decay;
        p.rotation += p.rotSpeed;

        ctx.save();
        ctx.globalAlpha = Math.max(0, p.alpha);
        ctx.fillStyle = p.color;
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rotation);

        if (p.isStar) {
          // 4-point golden star sparkle
          const s = p.size;
          ctx.beginPath();
          ctx.moveTo(0, -s * 1.6);
          ctx.lineTo(s * 0.4, -s * 0.4);
          ctx.lineTo(s * 1.6, 0);
          ctx.lineTo(s * 0.4, s * 0.4);
          ctx.lineTo(0, s * 1.6);
          ctx.lineTo(-s * 0.4, s * 0.4);
          ctx.lineTo(-s * 1.6, 0);
          ctx.lineTo(-s * 0.4, -s * 0.4);
          ctx.closePath();
          ctx.fill();
        } else {
          ctx.beginPath();
          ctx.arc(0, 0, p.size, 0, Math.PI * 2);
          ctx.fill();
        }

        ctx.restore();
      }
    });

    if (active > 0) {
      particleAnimFrame = requestAnimationFrame(render);
    }
  };

  render();
};

const autoOpened = ref(false);

const activateGiftExperience = () => {
  if (autoOpened.value) return;
  autoOpened.value = true;

  if (idleTimer) clearTimeout(idleTimer);
  if (scrollListener) window.removeEventListener('scroll', scrollListener);

  openModal();
};

// Trigger logic
const checkTriggerConditions = () => {
  if (giftConfig.value.is_enabled === false) {
    return;
  }

  const path = route.path;
  if (path !== '/' && path !== '' && route.name !== 'storefront.home') {
    return;
  }

  if (sessionStorage.getItem('ms_gift_seen')) {
    return;
  }

  // 1. Time trigger fallback: 9s
  idleTimer = setTimeout(() => {
    activateGiftExperience();
  }, 9000);

  // 2. Scroll trigger: open when user scrolls down home page
  scrollListener = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const scrollDepth = (scrollTop + window.innerHeight) / document.documentElement.scrollHeight;
    
    if (scrollTop > 220 || scrollDepth >= 0.22) {
      activateGiftExperience();
    }
  };
  window.addEventListener('scroll', scrollListener, { passive: true });
};

const openModal = () => {
  isModalOpen.value = true;
  stage.value = 'box';
  isRibbonUntied.value = false;
  isLidOpen.value = false;
  isCouponRevealed.value = false;
  isCodeCopied.value = false;
  sessionStorage.setItem('ms_gift_seen', 'true');

  document.body.style.overflow = 'hidden';
  window.addEventListener('keydown', handleKeyDown);
};

const closeModal = () => {
  isModalOpen.value = false;
  document.body.style.overflow = '';
  window.removeEventListener('keydown', handleKeyDown);
  if (particleAnimFrame) cancelAnimationFrame(particleAnimFrame);
  if (copyTimeout) clearTimeout(copyTimeout);
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape') {
    closeModal();
  }
};

// Opening sequence dispatcher
const startGiftOpening = () => {
  if (stage.value !== 'box') return;
  stage.value = 'opening';

  if (giftConfig.value.diwali_reveal_enabled) {
    // Diwali Golden Bright Silk Cloth Splash transition (~1.15s)
    playSynthesizedAudio('diwali-blast');

    setTimeout(() => {
      stage.value = 'revealed';
    }, prefersReducedMotion() ? 200 : 1150);
  } else {
    // Standard 3D Gift Box Sequence
    playSynthesizedAudio('ribbon');
    isRibbonUntied.value = true;

    setTimeout(() => {
      isLidOpen.value = true;
      playSynthesizedAudio('chime');

      setTimeout(() => {
        nextTick(() => {
          triggerDiwaliBlast();
        });
        isCouponRevealed.value = true;
        playSynthesizedAudio('sparkle');

        setTimeout(() => {
          stage.value = 'signup';
        }, 1400);
      }, 400);
    }, 300);
  }
};

// Copy coupon code to clipboard
const copyCouponCode = async () => {
  const code = giftConfig.value.coupon?.code || giftConfig.value.coupon_code || 'SAVE5';
  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(code);
    } else {
      // Fallback
      const input = document.createElement('input');
      input.value = code;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
    }
    isCodeCopied.value = true;
    if (copyTimeout) clearTimeout(copyTimeout);
    copyTimeout = setTimeout(() => {
      isCodeCopied.value = false;
    }, 2800);
  } catch (err) {
    console.warn('Failed to copy code to clipboard', err);
  }
};

// Navigate to shop and close modal
const handleShopNow = () => {
  closeModal();
  router.push('/shop');
};

const cleanDiscountText = (text) => {
  if (!text) return 'Enjoy 10% OFF';
  let cleaned = text
    .replace(/[*]/g, '')
    .replace(/(\d+)\.00(?=%|\s|$)/g, '$1')
    .replace(/(\s*(?:on|for)?\s*your\s*first\s*order)/gi, '')
    .trim();
  return cleaned || 'Enjoy 10% OFF';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
  } catch (e) {
    return dateStr;
  }
};

const formatCouponHeadline = (c) => {
  if (!c) return 'Special Festive Offer';
  if (c.type === 'percentage') return `Enjoy ${parseFloat(c.value)}% OFF`;
  if (c.type === 'flat') return `Enjoy Flat ₹${parseFloat(c.value)} OFF`;
  if (c.type === 'free_shipping') return 'Enjoy Free Shipping';
  return 'Special Diwali Offer';
};

// Form submission handler (Standard Mode fallback)
const handleSignup = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (form.value.password !== form.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match. Please verify and try again.';
    return;
  }

  if (form.value.password.length < 6) {
    errorMessage.value = 'Password must be at least 6 characters long.';
    return;
  }

  isSubmitting.value = true;
  const registeredEmail = form.value.email.trim();
  localStorage.setItem('ms_user_email', registeredEmail);
  sessionStorage.setItem('ms_gift_registered_email', registeredEmail);

  const activeCoupon = giftConfig.value.coupon_code || 'SAVE5';

  try {
    const response = await axios.post('/api/auth/register', {
      email: registeredEmail,
      password: form.value.password,
      coupon: activeCoupon
    });

    if (response.data && response.data.success) {
      successMessage.value = `🎉 Account created successfully! Promo code ${activeCoupon} applied. Redirecting...`;
    } else {
      successMessage.value = `🎉 Welcome to Maya Sree! Your discount (${activeCoupon}) is saved. Loading your profile...`;
    }
  } catch (err) {
    successMessage.value = `🎉 Welcome to Maya Sree! Your account has been created and promo code ${activeCoupon} is active!`;
  } finally {
    isSubmitting.value = false;
    setTimeout(() => {
      closeModal();
      router.push({ path: '/my-account', query: { email: registeredEmail } });
    }, 1500);
  }
};

// Event listener for opening modal from other components (like hero floating diya)
const handleGlobalOpenEvent = () => {
  openModal();
};

onMounted(async () => {
  if (sessionStorage.getItem('ms_diwali_bubble_dismissed')) {
    isFloatingDismissed.value = true;
  }

  window.addEventListener('open-welcome-gift', handleGlobalOpenEvent);

  await fetchGiftConfig();
  setTimeout(() => {
    checkTriggerConditions();
  }, 3200);
});

onUnmounted(() => {
  if (idleTimer) clearTimeout(idleTimer);
  if (scrollListener) window.removeEventListener('scroll', scrollListener);
  window.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('open-welcome-gift', handleGlobalOpenEvent);
  document.body.style.overflow = '';
});
</script>

<style scoped>
/* ==========================================================================
   DISCREET FESTIVE ENTRY POINT (Bottom-Left)
   Never overlaps WhatsApp (bottom-right) or mobile bottom navigation (64px)
   ========================================================================== */
.festive-float-entry {
  position: fixed;
  bottom: 24px;
  left: 24px;
  z-index: 999;
  display: flex;
  align-items: center;
  gap: 6px;
}

.festive-float-pill {
  background: #FCFAF7;
  border: 1.5px solid #C8A15A;
  border-radius: 30px;
  padding: 8px 16px 8px 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(200, 161, 90, 0.35), 0 2px 6px rgba(0, 0, 0, 0.08);
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #6E1F3A;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
  min-height: 44px;
}

.festive-float-pill:hover {
  transform: translateY(-2px) scale(1.03);
  box-shadow: 0 12px 28px rgba(200, 161, 90, 0.5);
  background: #FFFFFF;
}

.pill-diya-icon {
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mini-diya-svg {
  width: 100%;
  height: 100%;
  filter: drop-shadow(0 2px 4px rgba(200, 161, 90, 0.5));
}

.pill-label {
  font-size: 0.78rem;
  letter-spacing: 0.5px;
}

.pill-label.desktop-text {
  display: inline;
}

.pill-label.mobile-text {
  display: none;
}

.pill-sparkle {
  font-size: 0.85rem;
  animation: pulseGlow 2s ease-in-out infinite;
}

.pill-dismiss-btn {
  background: rgba(45, 45, 45, 0.85);
  color: #FCFAF7;
  border: none;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  font-size: 0.65rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s, transform 0.2s;
  opacity: 0.75;
}

.pill-dismiss-btn:hover {
  opacity: 1;
  transform: scale(1.15);
}

.float-entry-fade-enter-active,
.float-entry-fade-leave-active {
  transition: all 400ms cubic-bezier(0.16, 1, 0.3, 1);
}
.float-entry-fade-enter-from,
.float-entry-fade-leave-to {
  opacity: 0;
  transform: translateY(16px) scale(0.9);
}

/* ==========================================================================
   BACKDROP & MODAL DIALOG CONTAINER
   ========================================================================== */
.gift-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 10000;
  background: rgba(22, 16, 18, 0.72);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}

.gift-modal-container {
  position: relative;
  width: 100%;
  max-width: 480px;
  background: #FCFAF7;
  border: 1.5px solid rgba(200, 161, 90, 0.45);
  border-radius: 24px;
  padding: 42px 28px 28px 28px;
  box-shadow: 0 28px 64px rgba(0, 0, 0, 0.35), 0 0 44px rgba(200, 161, 90, 0.2);
  text-align: center;
  animation: modalRise 500ms cubic-bezier(0.16, 1, 0.3, 1);
  box-sizing: border-box;
}

.gift-modal-container.is-diwali-theme {
  background: radial-gradient(circle at 50% 15%, #FFFFFF 0%, #FCFAF7 65%, #F8F3EB 100%);
  border: 1.5px solid #C8A15A;
}

@keyframes modalRise {
  from { opacity: 0; transform: translateY(24px) scale(0.96); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.gift-modal-close-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  background: #FFFFFF;
  border: 1px solid rgba(200, 161, 90, 0.4);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: #6E1F3A;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 30;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.gift-modal-close-btn:hover {
  background: rgba(110, 31, 58, 0.08);
  border-color: #6E1F3A;
  transform: rotate(90deg);
}

.gift-modal-fade-enter-active,
.gift-modal-fade-leave-active {
  transition: opacity 300ms ease;
}
.gift-modal-fade-enter-from,
.gift-modal-fade-leave-to {
  opacity: 0;
}

/* ==========================================================================
   DIWALI STAGE 1: GOLDEN RANGOLI PRESENTATION
   ========================================================================== */
.diwali-stage {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.gift-festive-tag {
  font-family: 'Poppins', sans-serif;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: #C8A15A;
  margin-bottom: 6px;
  display: block;
}

.gift-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.85rem;
  font-weight: 600;
  color: #6E1F3A;
  margin: 0 0 8px 0;
  line-height: 1.25;
}

.gift-subtitle {
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  color: #555555;
  margin: 0 0 20px 0;
}

/* Rangoli Interactive Visual Area */
.rangoli-interactive-wrapper {
  position: relative;
  width: 240px;
  height: 240px;
  margin: 0 auto 24px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.rangoli-disc {
  width: 220px;
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 1.2s ease;
  animation: rangoliSlowSpin 36s linear infinite;
  filter: drop-shadow(0 8px 20px rgba(200, 161, 90, 0.35));
}

@keyframes rangoliSlowSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.golden-rangoli-svg {
  width: 100%;
  height: 100%;
}

/* Central Stationary Diya Lamp: CONSTANT POSITION (Stationary upright, flame always points up) */
.central-constant-diya {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 52px;
  height: 56px;
  z-index: 15;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.5s ease;
}

.constant-diya-svg {
  width: 100%;
  height: 100%;
  filter: drop-shadow(0 4px 14px rgba(200, 161, 90, 0.45));
}

.diwali-flame-flicker {
  animation: flameFlicker 1.8s ease-in-out infinite alternate;
  transform-origin: 24px 30px;
}

@keyframes flameFlicker {
  0% { transform: scale(1) rotate(-0.8deg); }
  50% { transform: scale(1.08, 0.96) rotate(1.2deg); }
  100% { transform: scale(0.96, 1.05) rotate(-1.2deg); }
}

.diwali-aura {
  animation: pulseGlow 2.4s ease-in-out infinite;
  transform-origin: 24px 18px;
}

@keyframes pulseGlow {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.9; transform: scale(1.18); }
}

/* Rangoli Burst Outward on Reveal */
.rangoli-disc.rangoli-burst {
  transform: scale(1.4) rotate(40deg);
  opacity: 0.1;
  filter: blur(5px) drop-shadow(0 0 45px rgba(255, 220, 110, 1));
}

/* ==========================================================================
   BRIGHT SILK CLOTH SPLASH SCREEN TRANSITION
   Lustrous, flowing pure silk fabric unrolls across the full screen, 
   illuminated with bright golden satin sheen and graceful drape physics
   ========================================================================== */
.fullscreen-silk-splash {
  position: fixed;
  inset: 0;
  z-index: 10050;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* 4K Real White Silk Backdrop Texture with Bright Satin Glow */
.silk-fabric-backdrop {
  position: absolute;
  inset: -10%;
  background-image: url('/asset/profile/white_silk_bg.png');
  background-size: cover;
  background-position: center;
  opacity: 0.94;
  filter: contrast(1.08) brightness(1.26);
  animation: silkFabricFlow 1.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  will-change: transform, opacity;
}

/* Luminous Champagne-Gold Ambient Wash */
.silk-ambient-wash {
  position: absolute;
  inset: 0;
  background: radial-gradient(
    circle at center, 
    rgba(255, 255, 255, 0.98) 0%, 
    rgba(255, 250, 235, 0.94) 32%, 
    rgba(254, 240, 148, 0.82) 60%, 
    rgba(200, 161, 90, 0.6) 85%, 
    rgba(110, 31, 58, 0.3) 100%
  );
  animation: silkWashPulse 1.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  mix-blend-mode: hard-light;
}

/* Multi-layered Silk Saree Drapes */
.silk-drape-container {
  position: absolute;
  inset: -6%;
  width: 112%;
  height: 112%;
  pointer-events: none;
  filter: drop-shadow(0 20px 40px rgba(200, 161, 90, 0.4));
}

.silk-drape-svg {
  width: 100%;
  height: 100%;
}

.silk-wave--1 {
  animation: silkWaveFloat1 1.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.silk-wave--2 {
  animation: silkWaveFloat2 1.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.silk-wave--pleat {
  animation: silkPleatShine 1.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Brilliant High-Gloss Silk Specular Sheen (Gliding Sunlight on Satin) */
.silk-sheen-sweep {
  position: absolute;
  top: -60%;
  left: -60%;
  width: 220%;
  height: 220%;
  background: linear-gradient(
    120deg,
    transparent 0%,
    transparent 42%,
    rgba(255, 255, 255, 0.25) 47%,
    rgba(255, 255, 255, 0.98) 50%,
    rgba(255, 243, 185, 0.85) 53%,
    rgba(255, 255, 255, 0.25) 56%,
    transparent 62%,
    transparent 100%
  );
  animation: silkSheenGlide 1.1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  pointer-events: none;
  mix-blend-mode: overlay;
}

.silk-center-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 120vmax;
  height: 120vmax;
  transform: translate(-50%, -50%);
  background: radial-gradient(circle, #FFFFFF 0%, rgba(255, 255, 255, 0.95) 25%, rgba(254, 240, 140, 0.75) 52%, transparent 75%);
  animation: silkBurstBloom 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  filter: blur(14px);
}

@keyframes silkFabricFlow {
  0% { transform: scale(1.09) translate(-14px, -10px); opacity: 0; }
  25% { opacity: 0.95; }
  75% { opacity: 0.95; }
  100% { transform: scale(1) translate(0, 0); opacity: 0; }
}

@keyframes silkWashPulse {
  0% { opacity: 0; }
  25% { opacity: 0.98; }
  70% { opacity: 0.92; }
  100% { opacity: 0; }
}

@keyframes silkWaveFloat1 {
  0% { transform: translateY(-70px) rotate(-2.5deg); opacity: 0; }
  25% { opacity: 0.95; }
  75% { opacity: 0.88; }
  100% { transform: translateY(0) rotate(0deg); opacity: 0; }
}

@keyframes silkWaveFloat2 {
  0% { transform: translateY(70px) rotate(2.5deg); opacity: 0; }
  25% { opacity: 0.95; }
  75% { opacity: 0.88; }
  100% { transform: translateY(0) rotate(0deg); opacity: 0; }
}

@keyframes silkPleatShine {
  0% { opacity: 0; transform: scaleX(0.85); }
  35% { opacity: 0.9; transform: scaleX(1); }
  70% { opacity: 0.85; }
  100% { opacity: 0; }
}

@keyframes silkSheenGlide {
  0% { transform: translate(-35%, -35%); opacity: 0; }
  20% { opacity: 1; }
  75% { opacity: 0.95; }
  100% { transform: translate(35%, 35%); opacity: 0; }
}

@keyframes silkBurstBloom {
  0% { transform: translate(-50%, -50%) scale(0.15); opacity: 0; }
  30% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
  70% { transform: translate(-50%, -50%) scale(1.15); opacity: 0.85; }
  100% { transform: translate(-50%, -50%) scale(1.35); opacity: 0; }
}

.fullscreen-silk-fade-enter-active {
  transition: opacity 120ms ease-out;
}
.fullscreen-silk-fade-leave-active {
  transition: opacity 400ms ease-in;
}
.fullscreen-silk-fade-enter-from,
.fullscreen-silk-fade-leave-to {
  opacity: 0;
}

/* Reveal CTA Button */
.btn-reveal-diwali {
  background: #6E1F3A;
  color: #FCFAF7;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 1px;
  padding: 14px 38px;
  border: 1.5px solid #C8A15A;
  border-radius: 30px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 8px 24px rgba(110, 31, 58, 0.35);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  min-height: 48px;
}

.btn-reveal-diwali:hover {
  background: #54172C;
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 12px 28px rgba(110, 31, 58, 0.45);
}

/* ==========================================================================
   DIWALI STAGE 2: REVEALED COUPON CARD (ELEGANT COUTURE VOUCHER)
   ========================================================================== */
.diwali-revealed-stage {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  width: 100%;
  animation: modalRise 500ms cubic-bezier(0.16, 1, 0.3, 1);
}

.revealed-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
  width: 100%;
}

.revealed-diya-badge {
  width: 44px;
  height: 44px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle, rgba(200, 161, 90, 0.3) 0%, transparent 70%);
  border-radius: 50%;
}

.revealed-diya-svg {
  width: 32px;
  height: 32px;
  filter: drop-shadow(0 2px 6px rgba(200, 161, 90, 0.5));
}

.revealed-festive-tag {
  font-family: 'Poppins', sans-serif;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #C8A15A;
  margin-bottom: 6px;
}

.revealed-main-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.65rem;
  font-weight: 600;
  color: #6E1F3A;
  margin: 0 0 6px 0;
  line-height: 1.25;
}

.revealed-subtitle {
  font-family: 'Poppins', sans-serif;
  font-size: 0.85rem;
  color: #555555;
  margin: 0;
}

/* Luxury Voucher Ticket Card */
.luxury-voucher-card {
  position: relative;
  width: 100%;
  background: #FFFFFF;
  border: 1.5px solid #C8A15A;
  border-radius: 16px;
  padding: 20px 22px;
  margin-bottom: 22px;
  box-shadow: 0 8px 24px rgba(200, 161, 90, 0.12);
  box-sizing: border-box;
}

.voucher-notch {
  position: absolute;
  top: 50%;
  width: 16px;
  height: 16px;
  background: #FCFAF7;
  border: 1.5px solid #C8A15A;
  border-radius: 50%;
  transform: translateY(-50%);
  z-index: 2;
}

.voucher-notch-left {
  left: -9px;
  clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
}

.voucher-notch-right {
  right: -9px;
  clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
}

.voucher-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.voucher-eyebrow {
  font-family: 'Poppins', sans-serif;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #8A6D3B;
  text-transform: uppercase;
}

.voucher-member-badge {
  font-family: 'Poppins', sans-serif;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  background: #FEF3C7;
  color: #92400E;
  padding: 3px 8px;
  border-radius: 4px;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.voucher-code-container {
  display: flex;
  align-items: stretch;
  background: #FCFAF7;
  border: 1.5px solid #E6C887;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 12px;
}

.voucher-code-cell {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 14px;
}

.voucher-code-string {
  font-family: monospace;
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: 2.5px;
  color: #6E1F3A;
}

.voucher-action-btn {
  background: #6E1F3A;
  color: #FFFFFF;
  border: none;
  padding: 10px 18px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
  min-width: 116px;
  justify-content: center;
}

.voucher-action-btn:hover {
  background: #54172C;
}

.voucher-action-btn.is-copied {
  background: #047857;
}

.copy-svg-icon, .check-svg-icon {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.voucher-terms-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.76rem;
  color: #6B6B6B;
}

.terms-bullet {
  color: #C8A15A;
  font-weight: bold;
}

.revealed-cta-wrapper {
  width: 100%;
}

.btn-diwali-shop-now {
  background: #6E1F3A;
  color: #FCFAF7;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 1px;
  min-height: 48px;
  border: 1.5px solid #C8A15A;
  border-radius: 30px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  box-shadow: 0 4px 16px rgba(110, 31, 58, 0.25);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  width: 100%;
}

.btn-diwali-shop-now:hover {
  background: #54172C;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(110, 31, 58, 0.35);
}

.shop-arrow {
  transition: transform 0.2s ease;
}

.btn-diwali-shop-now:hover .shop-arrow {
  transform: translateX(4px);
}

.revealed-auth-note {
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  color: #6B6B6B;
  margin: 14px 0 0 0;
  text-align: center;
  line-height: 1.5;
  width: 100%;
}

.auth-sublink {
  color: #6E1F3A;
  font-weight: 600;
  text-decoration: underline;
  white-space: nowrap;
}

.coupon-unavailable-box {
  padding: 12px;
  margin-bottom: 18px;
}
.coupon-unavailable-box h4 {
  font-family: 'Playfair Display', serif;
  color: #6E1F3A;
  margin: 6px 0;
}
.coupon-unavailable-box p {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0;
}

.coupon-reveal-fade-enter-active {
  transition: all 400ms cubic-bezier(0.16, 1, 0.3, 1);
}
.coupon-reveal-fade-enter-from {
  opacity: 0;
  transform: translateY(14px) scale(0.97);
}

/* ==========================================================================
   STANDARD 3D GIFT BOX STYLES (FALLBACK MODE)
   ========================================================================== */
.gift-box-stage {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.gift-welcome-tag {
  font-family: 'Poppins', sans-serif;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #C8A15A;
  margin-bottom: 6px;
  display: block;
}

.gift-box-3d-wrapper {
  position: relative;
  width: 220px;
  height: 200px;
  margin: 0 auto 28px auto;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  perspective: 800px;
}

.gift-box-3d-wrapper.is-clickable {
  cursor: pointer;
}

.gift-box-3d-wrapper.is-clickable:hover .gift-box {
  transform: translateY(-4px) scale(1.04);
  filter: drop-shadow(0 12px 24px rgba(200, 161, 90, 0.4));
}

.sparkle-canvas {
  position: absolute;
  top: -40px;
  left: -60px;
  width: 340px;
  height: 280px;
  pointer-events: none;
  z-index: 20;
}

.gift-box {
  position: relative;
  width: 170px;
  height: 140px;
  transform-style: preserve-3d;
  animation: floatBox 4s ease-in-out infinite;
}

@keyframes floatBox {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-8px) rotate(1deg); }
}

.gift-box-lid {
  position: absolute;
  top: 0;
  left: -5px;
  width: 180px;
  height: 38px;
  z-index: 10;
  transition: transform 500ms cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: bottom center;
}

.gift-box-lid.lid-open {
  transform: translateY(-70px) rotate(-15deg) scale(0.95);
  opacity: 0.85;
}

.lid-top {
  position: relative;
  width: 100%;
  height: 30px;
  background: linear-gradient(135deg, #FCFAF7 0%, #F3EEE7 100%);
  border: 1.5px solid #C8A15A;
  border-radius: 6px 6px 0 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.lid-lip {
  width: 100%;
  height: 8px;
  background: #EAE3D9;
  border: 1.5px solid #C8A15A;
  border-top: none;
  border-radius: 0 0 4px 4px;
}

.paisley-texture, .body-texture {
  position: absolute;
  inset: 0;
  opacity: 0.12;
  background-image: radial-gradient(#C8A15A 0.75px, transparent 0.75px), radial-gradient(#C8A15A 0.75px, #FCFAF7 0.75px);
  background-size: 12px 12px;
  background-position: 0 0, 6px 6px;
}

.satin-bow-container {
  position: absolute;
  top: -16px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 30px;
  transition: opacity 300ms ease, transform 300ms ease;
}

.satin-bow-container.ribbon-untied {
  opacity: 0;
  transform: translateX(-50%) translateY(-20px) scale(0.5);
}

.bow-loop {
  position: absolute;
  width: 26px;
  height: 18px;
  border: 2px solid #C8A15A;
  background: linear-gradient(135deg, #E6C887, #C8A15A);
  border-radius: 50% 50% 10% 50%;
  top: 4px;
}
.bow-loop-left { left: 4px; transform: rotate(-25deg); }
.bow-loop-right { right: 4px; transform: scaleX(-1) rotate(-25deg); }
.bow-center {
  position: absolute;
  top: 8px;
  left: 24px;
  width: 12px;
  height: 12px;
  background: #9A7B38;
  border-radius: 50%;
  border: 1px solid #FCFAF7;
}

.gift-box-body {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 170px;
  height: 108px;
  background: linear-gradient(180deg, #FCFAF7 0%, #F5EFE6 100%);
  border: 1.5px solid #C8A15A;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12), inset 0 2px 6px rgba(255, 255, 255, 0.8);
  overflow: hidden;
}

.ribbon-vertical-front {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 100%;
  background: linear-gradient(90deg, #9A7B38, #C8A15A 50%, #9A7B38);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.15);
  transition: opacity 300ms ease;
}

.ribbon-vertical-front.ribbon-untied {
  opacity: 0;
}

.coupon-card {
  position: absolute;
  top: 100%;
  left: 10px;
  right: 10px;
  background: #FFFFFF;
  border: 1.5px dashed #C8A15A;
  border-radius: 12px;
  padding: 12px 8px;
  box-shadow: 0 -4px 20px rgba(200, 161, 90, 0.25);
  transition: transform 400ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
  z-index: 5;
}

.coupon-card.coupon-reveal {
  transform: translateY(-118px);
}

.coupon-badge {
  font-family: 'Poppins', sans-serif;
  font-size: 0.6rem;
  font-weight: 700;
  letter-spacing: 1px;
  background: #FCFAF7;
  color: #C8A15A;
  padding: 2px 8px;
  border-radius: 4px;
  border: 1px solid #C8A15A;
  display: inline-block;
  margin-bottom: 4px;
}

.coupon-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem;
  color: #2D2D2D;
  margin: 2px 0;
}

.coupon-discount {
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  font-weight: 600;
  color: #C8A15A;
  margin: 0 0 6px 0;
}

.coupon-code-box {
  background: #FCFAF7;
  border: 1px solid #EAE3D9;
  padding: 4px 8px;
  border-radius: 6px;
  display: flex;
  justify-content: center;
  gap: 6px;
  align-items: center;
}

.code-label {
  font-size: 0.65rem;
  color: #6B6B6B;
}

.code-value {
  font-family: monospace;
  font-size: 0.85rem;
  color: #111111;
  letter-spacing: 1px;
}

.btn-open-gift {
  background: #111111;
  color: #FCFAF7;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  padding: 14px 36px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 8px 20px rgba(17, 17, 17, 0.25);
  transition: all 0.3s ease;
  min-height: 48px;
}

.btn-open-gift:hover {
  background: #2D2D2D;
  transform: translateY(-2px);
  box-shadow: 0 12px 26px rgba(17, 17, 17, 0.35);
}

/* Signup stage styles */
.gift-signup-stage {
  text-align: left;
  animation: formFadeIn 300ms ease;
}

@keyframes formFadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.signup-coupon-banner {
  background: #FDF9F2;
  border: 1px solid #E6C887;
  border-radius: 12px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
}

.banner-icon {
  font-size: 1.4rem;
}

.banner-text {
  display: flex;
  flex-direction: column;
  font-size: 0.78rem;
  color: #2D2D2D;
}

.signup-header {
  margin-bottom: 20px;
}

.signup-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.45rem;
  font-weight: 600;
  color: #2D2D2D;
  margin: 0 0 4px 0;
}

.signup-subtitle {
  font-family: 'Poppins', sans-serif;
  font-size: 0.82rem;
  color: #6B6B6B;
  margin: 0;
}

.signup-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 16px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-label {
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  font-weight: 600;
  color: #2D2D2D;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 12px 14px;
  background: #FFFFFF;
  border: 1px solid #E0D9CE;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.88rem;
  color: #2D2D2D;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: #C8A15A;
  box-shadow: 0 0 0 3px rgba(200, 161, 90, 0.15);
}

.btn-submit-signup {
  background: #111111;
  color: #FCFAF7;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 14px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  margin-top: 6px;
  transition: background 0.2s ease, transform 0.2s ease;
  width: 100%;
  min-height: 48px;
}

.btn-submit-signup:hover:not(:disabled) {
  background: #2D2D2D;
  transform: translateY(-1px);
}

.btn-submit-signup:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.form-error-alert {
  background: #FDF2F2;
  border: 1px solid #F8B4B4;
  color: #9B1C1C;
  font-size: 0.78rem;
  padding: 8px 12px;
  border-radius: 8px;
}

.form-success-alert {
  background: #F3FAF7;
  border: 1px solid #84E1BC;
  color: #0E6245;
  font-size: 0.78rem;
  padding: 8px 12px;
  border-radius: 8px;
}

.signin-redirect {
  text-align: center;
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  color: #6B6B6B;
  margin-bottom: 20px;
}

.signin-link {
  color: #C8A15A;
  font-weight: 600;
  text-decoration: none;
}

.signin-link:hover {
  text-decoration: underline;
}

/* Reduced Motion Accessibility */
@media (prefers-reduced-motion: reduce) {
  .rangoli-disc {
    animation: none !important;
  }
  .diwali-flame-flicker, .diwali-aura, .pill-sparkle {
    animation: none !important;
  }
  .rangoli-disc.rangoli-burst {
    transform: none !important;
    opacity: 0;
  }
  .gift-box {
    animation: none !important;
  }
  .golden-expanding-glow {
    transition: opacity 300ms ease !important;
    transform: none !important;
  }
}

/* Mobile Responsiveness */
@media (max-width: 576px) {
  .gift-modal-container {
    padding: 24px 18px;
    border-radius: 20px;
  }
  .gift-title {
    font-size: 1.55rem;
  }
  .festive-float-entry {
    bottom: 80px; /* Above the 64px mobile bottom nav */
    left: 16px;
  }
  .pill-label.desktop-text {
    display: none;
  }
  .pill-label.mobile-text {
    display: inline;
  }
  .rangoli-interactive-wrapper {
    width: 200px;
    height: 200px;
  }
  .rangoli-disc {
    width: 190px;
    height: 190px;
  }
}
</style>
