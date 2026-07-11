<template>
  <div class="contact-page" style="background-color: var(--color-bg); min-height: 100vh; padding-bottom: 80px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
      <router-link to="/" class="breadcrumb-link">Home</router-link>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current">Contact Us</span>
    </div>

    <!-- Section 1: Hero -->
    <section class="contact-hero">
      <div class="contact-hero__grid">
        <div class="contact-hero__text reveal">
          <span class="section-tag">Let's Connect</span>
          <h1 class="contact-hero__title">We'd Love to Hear From You</h1>
          <p class="contact-hero__subtitle">
            We're here to help with product inquiries, orders, sizing, shipping, exchanges, wholesale, and custom stitching. Reach out to our dedicated support team.
          </p>
          <div class="contact-hero__actions">
            <a href="https://wa.me/919944285102" target="_blank" class="contact-btn contact-btn--whatsapp">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 16px; height: 16px; fill: currentColor; margin-right: 8px; vertical-align: middle;">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
              </svg>
              Chat on WhatsApp
            </a>
            <a href="tel:+919944285102" class="contact-btn contact-btn--outline">
              <span class="btn-icon">📞</span> Call Us
            </a>
            <a href="mailto:sales@mayasreefashion.com" class="contact-btn contact-btn--charcoal">
              <span class="btn-icon">✉️</span> Send Email
            </a>
          </div>
        </div>
        <div class="contact-hero__image-container reveal">
          <img :src="'/asset/contact_hero.png'" alt="Maya Sree Customer Support" class="contact-hero__image" loading="lazy">
        </div>
      </div>
    </section>

    <div class="contact-container">
      <!-- Section 2: Quick Contact Cards -->
      <section class="contact-section quick-cards reveal">
        <div class="quick-grid">
          <!-- Card 1: Call Us -->
          <div class="quick-card">
            <div class="quick-card__header">
              <span class="quick-card__icon">📞</span>
              <h3 class="quick-card__title">Call Us</h3>
            </div>
            <div class="quick-card__body">
              <a href="tel:+919944285102" class="quick-card__value">+91 99442 85102</a>
              <p class="quick-card__sub">Availability: Monday–Saturday</p>
              <p class="quick-card__sub">9:30 AM – 6:30 PM</p>
            </div>
            <a href="tel:+919944285102" class="contact-btn contact-btn--outline-gold btn-full">Call Now</a>
          </div>

          <!-- Card 2: WhatsApp -->
          <div class="quick-card">
            <div class="quick-card__header">
              <span class="quick-card__icon green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 24px; height: 24px; fill: #25D366; vertical-align: middle; display: inline-block;">
                  <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                </svg>
              </span>
              <h3 class="quick-card__title">WhatsApp Support</h3>
            </div>
            <div class="quick-card__body">
              <span class="quick-card__value">Quick Chat</span>
              <p class="quick-card__sub">Usually replies within 30 minutes.</p>
              <p class="quick-card__sub">Tap below to message directly.</p>
            </div>
            <a href="https://wa.me/919944285102" target="_blank" class="contact-btn contact-btn--whatsapp btn-full">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 16px; height: 16px; fill: currentColor; margin-right: 8px; vertical-align: middle;">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
              </svg>
              Chat on WhatsApp
            </a>
          </div>

          <!-- Card 3: Email -->
          <div class="quick-card">
            <div class="quick-card__header">
              <span class="quick-card__icon">✉️</span>
              <h3 class="quick-card__title">Email Us</h3>
            </div>
            <div class="quick-card__body">
              <a href="mailto:sales@mayasreefashion.com" class="quick-card__value text-email">sales@mayasreefashion.com</a>
              <p class="quick-card__sub">Response within 24 hours.</p>
              <p class="quick-card__sub">For orders & bulk wholesale queries.</p>
            </div>
            <a href="mailto:sales@mayasreefashion.com" class="contact-btn contact-btn--charcoal btn-full">Send Email</a>
          </div>
        </div>
      </section>

      <!-- Main Layout Split: Contact Form (Left) & Address/Hours (Right) -->
      <div class="main-split-grid">
        <!-- Section 3: Contact Form -->
        <section class="contact-section form-section reveal">
          <div class="form-card">
            <h2 class="form-card__title">Send Us A Message</h2>
            <p class="form-card__subtitle">Have a question or request? Fill out the form below and we will contact you shortly.</p>
            
            <form @submit.prevent="handleFormSubmit" class="contact-form">
              <div class="form-row">
                <!-- First Name -->
                <div class="form-group" :class="{ 'has-error': errors.firstName, 'is-valid': touched.firstName && !errors.firstName }">
                  <label for="firstName" class="form-label">First Name *</label>
                  <input 
                    id="firstName" 
                    v-model="form.firstName" 
                    type="text" 
                    class="form-input" 
                    placeholder="Enter your first name" 
                    @blur="validateField('firstName')"
                  />
                  <span v-if="errors.firstName" class="error-text">{{ errors.firstName }}</span>
                </div>

                <!-- Last Name -->
                <div class="form-group" :class="{ 'has-error': errors.lastName, 'is-valid': touched.lastName && !errors.lastName }">
                  <label for="lastName" class="form-label">Last Name *</label>
                  <input 
                    id="lastName" 
                    v-model="form.lastName" 
                    type="text" 
                    class="form-input" 
                    placeholder="Enter your last name" 
                    @blur="validateField('lastName')"
                  />
                  <span v-if="errors.lastName" class="error-text">{{ errors.lastName }}</span>
                </div>
              </div>

              <div class="form-row">
                <!-- Email Address -->
                <div class="form-group" :class="{ 'has-error': errors.email, 'is-valid': touched.email && !errors.email }">
                  <label for="email" class="form-label">Email Address *</label>
                  <input 
                    id="email" 
                    v-model="form.email" 
                    type="email" 
                    class="form-input" 
                    placeholder="name@example.com" 
                    @blur="validateField('email')"
                  />
                  <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                </div>

                <!-- Phone Number -->
                <div class="form-group" :class="{ 'has-error': errors.phone, 'is-valid': touched.phone && !errors.phone }">
                  <label for="phone" class="form-label">Phone Number *</label>
                  <input 
                    id="phone" 
                    v-model="form.phone" 
                    type="tel" 
                    class="form-input" 
                    placeholder="10-digit mobile number" 
                    @blur="validateField('phone')"
                  />
                  <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
                </div>
              </div>

              <!-- Subject -->
              <div class="form-group" :class="{ 'has-error': errors.subject, 'is-valid': touched.subject && !errors.subject }">
                <label for="subject" class="form-label">Subject *</label>
                <select 
                  id="subject" 
                  v-model="form.subject" 
                  class="form-input form-select"
                  @blur="validateField('subject')"
                >
                  <option value="" disabled>Select a subject</option>
                  <option value="Sizing & Fit">Sizing & Custom Blouse Fit</option>
                  <option value="Order Status">Order Status & Tracking</option>
                  <option value="Exchanges & Returns">Exchanges & Returns</option>
                  <option value="Wholesale Inquiry">Bulk / Wholesale Inquiry</option>
                  <option value="Other">Other Inquiry</option>
                </select>
                <span v-if="errors.subject" class="error-text">{{ errors.subject }}</span>
              </div>

              <!-- Message -->
              <div class="form-group" :class="{ 'has-error': errors.message, 'is-valid': touched.message && !errors.message }">
                <label for="message" class="form-label">Your Message *</label>
                <textarea 
                  id="message" 
                  v-model="form.message" 
                  rows="5" 
                  class="form-input form-textarea" 
                  placeholder="How can we help you?"
                  @blur="validateField('message')"
                ></textarea>
                <span v-if="errors.message" class="error-text">{{ errors.message }}</span>
              </div>

              <!-- Submit button -->
              <button type="submit" class="contact-btn contact-btn--primary form-submit-btn">
                Send Message
              </button>
            </form>
          </div>
        </section>

        <!-- Right Side: Address & Hours -->
        <div class="contact-sidebar">
          <!-- Section 4: Visit Our Office -->
          <section class="contact-section address-section reveal">
            <div class="info-card">
              <h2 class="info-card__title">Visit Our Store</h2>
              <div class="info-card__content">
                <div class="info-item">
                  <span class="info-item__icon">📍</span>
                  <div class="info-item__text">
                    <strong>MS Signature Blouses</strong><br>
                    No. 4, Seenivasa Nagar 2nd Street,<br>
                    Solipalayam, Tirupur – 641652<br>
                    Tamil Nadu, India
                  </div>
                </div>

                <div class="info-item">
                  <span class="info-item__icon">📞</span>
                  <div class="info-item__text">
                    <strong>Phone</strong><br>
                    +91 99442 85102
                  </div>
                </div>

                <div class="info-item">
                  <span class="info-item__icon">✉️</span>
                  <div class="info-item__text">
                    <strong>Email</strong><br>
                    sales@mayasreefashion.com
                  </div>
                </div>

                <div class="info-item">
                  <span class="info-item__icon">🕒</span>
                  <div class="info-item__text">
                    <strong>Working Hours</strong><br>
                    Monday – Saturday: 9:30 AM – 6:30 PM<br>
                    Sunday: Closed
                  </div>
                </div>
              </div>
              <a href="https://maps.google.com/?q=Solipalayam+Tiruppur+Tamil+Nadu" target="_blank" class="contact-btn contact-btn--primary btn-full info-card__btn">
                Open in Google Maps
              </a>
            </div>
          </section>

          <!-- Section 6: Google Map (Moved to sidebar) -->
          <section class="contact-section map-section reveal">
            <div class="map-container">
              <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3914.8872957134375!2d77.34005837583648!3d11.121966052731113!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba907abdf67dfc7%3A0xe54e604f323a1a1f!2sSolipalayam%2C%20Tiruppur%2C%20Tamil%20Nadu!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" 
                width="100%" 
                height="300" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                class="map-iframe"
              ></iframe>
              <div class="map-overlay">
                <a href="https://maps.google.com/?q=Solipalayam+Tiruppur+Tamil+Nadu" target="_blank" class="contact-btn contact-btn--secondary shadow-md">
                  Get Directions
                </a>
              </div>
            </div>
          </section>
        </div>
      </div>

      <!-- Section 7: FAQ Accordion -->
      <section class="contact-section faq-section reveal">
        <span class="section-tag section-tag--center">FAQ</span>
        <h2 class="section-title text-center">Frequently Asked Questions</h2>
        
        <div class="faq-accordion">
          <div 
            v-for="(faq, index) in faqs" 
            :key="index" 
            class="faq-item" 
            :class="{ 'is-active': activeFaqIndex === index }"
          >
            <button class="faq-item__header" @click="toggleFaq(index)">
              <span class="faq-item__question">{{ faq.question }}</span>
              <span class="faq-item__icon-indicator"></span>
            </button>
            <div class="faq-item__body">
              <p class="faq-item__answer">{{ faq.answer }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 8: Final CTA -->
      <section class="contact-section final-cta reveal">
        <div class="cta-banner">
          <h2 class="cta-banner__title">Need Immediate Assistance?</h2>
          <p class="cta-banner__subtitle">Our support team is always happy to help. Connect with us via text or voice call.</p>
          <div class="cta-banner__actions">
            <a href="https://wa.me/919944285102" target="_blank" class="contact-btn contact-btn--whatsapp">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 16px; height: 16px; fill: currentColor; margin-right: 8px; vertical-align: middle;">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
              </svg>
              WhatsApp Us
            </a>
            <a href="tel:+919944285102" class="contact-btn contact-btn--outline">
              Call Customer Support
            </a>
          </div>
        </div>
      </section>

      <!-- Footer Trust Indicators -->
      <div class="trust-indicators">
        <div class="trust-item">
          <span class="trust-icon">✔</span> Secure Shopping
        </div>
        <div class="trust-item">
          <span class="trust-icon">✔</span> Fast Customer Support
        </div>
        <div class="trust-item">
          <span class="trust-icon">✔</span> Easy Returns
        </div>
        <div class="trust-item">
          <span class="trust-icon">✔</span> Trusted Fashion Brand
        </div>
      </div>
    </div>

    <!-- Mobile Sticky Quick-Action Bar -->
    <div class="mobile-sticky-bar">
      <a href="tel:+919944285102" class="sticky-action-item sticky-action-item--call">
        <span class="sticky-icon">📞</span>
        <span class="sticky-text">Call Us</span>
      </a>
      <a href="https://wa.me/919944285102" target="_blank" class="sticky-action-item sticky-action-item--whatsapp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 16px; height: 16px; fill: currentColor; margin-bottom: 2px; vertical-align: middle;">
          <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
        </svg>
        <span class="sticky-text">WhatsApp</span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

// SEO Setup
onMounted(() => {
  document.title = "Contact Us - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Get in touch with Maya Sree Fashion. Send a message, find our address in Tirupur, or connect instantly via WhatsApp.");
  }

  // Scroll reveal logic
  const reveals = document.querySelectorAll('.reveal');
  revealOnScroll = () => {
    const windowHeight = window.innerHeight;
    reveals.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementVisible = 100;
      if (elementTop < windowHeight - elementVisible) {
        element.classList.add('active');
      }
    });
  };

  window.addEventListener('scroll', revealOnScroll);
  revealOnScroll();
});

let revealOnScroll;
onBeforeUnmount(() => {
  if (revealOnScroll) {
    window.removeEventListener('scroll', revealOnScroll);
  }
});

// FAQs Data
const faqs = ref([
  {
    question: "How long does delivery take?",
    answer: "Domestic orders typically arrive within 3-5 business days. Once shipped, you will receive a tracking link via SMS and email to monitor your package."
  },
  {
    question: "Can I exchange my order?",
    answer: "Yes! We offer size/design exchanges within 7 days of delivery for unworn garments with original tags intact. Reach out to sales@mayasreefashion.com to start an exchange."
  },
  {
    question: "Do you offer Cash on Delivery?",
    answer: "Yes, we support Cash on Delivery (COD) for most pin codes across India, in addition to secure online payment gateways like credit/debit card, UPI, and Net Banking."
  },
  {
    question: "How can I track my order?",
    answer: "After placing an order, tracking details will be sent directly to your registered phone number via WhatsApp and SMS as soon as the package is dispatched."
  },
  {
    question: "How do I contact customer support?",
    answer: "You can send us a message through the contact form, email sales@mayasreefashion.com directly, or chat with us instantly on WhatsApp at +91 99442 85102."
  }
]);

// Active FAQ Item Index
const activeFaqIndex = ref(null);

const toggleFaq = (index) => {
  if (activeFaqIndex.value === index) {
    activeFaqIndex.value = null;
  } else {
    activeFaqIndex.value = index;
  }
};

// Form State & Validation
const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
});

const errors = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
});

const touched = ref({
  firstName: false,
  lastName: false,
  email: false,
  phone: false,
  subject: false,
  message: false
});

const validateField = (field) => {
  touched.value[field] = true;
  
  if (field === 'firstName') {
    errors.value.firstName = form.value.firstName.trim() ? '' : 'First name is required';
  }
  
  if (field === 'lastName') {
    errors.value.lastName = form.value.lastName.trim() ? '' : 'Last name is required';
  }
  
  if (field === 'email') {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!form.value.email.trim()) {
      errors.value.email = 'Email address is required';
    } else if (!emailPattern.test(form.value.email)) {
      errors.value.email = 'Enter a valid email address';
    } else {
      errors.value.email = '';
    }
  }
  
  if (field === 'phone') {
    const phonePattern = /^\d{10}$/;
    if (!form.value.phone.trim()) {
      errors.value.phone = 'Phone number is required';
    } else if (!phonePattern.test(form.value.phone)) {
      errors.value.phone = 'Enter a valid 10-digit phone number';
    } else {
      errors.value.phone = '';
    }
  }
  
  if (field === 'subject') {
    errors.value.subject = form.value.subject ? '' : 'Please select a subject';
  }
  
  if (field === 'message') {
    errors.value.message = form.value.message.trim() ? '' : 'Message cannot be empty';
  }
};

const handleFormSubmit = () => {
  // Trigger validation for all fields
  Object.keys(form.value).forEach((field) => {
    validateField(field);
  });
  
  // Check if any errors exist
  const hasErrors = Object.values(errors.value).some((error) => error !== '');
  
  if (hasErrors) {
    return;
  }
  
  // Submit successful
  window.alert(`Thank you ${form.value.firstName}! Your inquiry about "${form.value.subject}" has been received. Our team will contact you shortly.`);
  
  // Reset form and states
  form.value = {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    subject: '',
    message: ''
  };
  
  Object.keys(touched.value).forEach((key) => {
    touched.value[key] = false;
    errors.value[key] = '';
  });
};
</script>

<style scoped>
/* Scoped Styling for Premium Contact Us Page */

.contact-page {
  font-family: var(--font-family-base);
  color: var(--color-text-primary);
}

/* Breadcrumbs */
.breadcrumbs {
  padding: 1.5rem 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-size: 0.85rem;
  color: var(--color-text-secondary);
}

.breadcrumb-link {
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: color var(--transition-fast);
}

.breadcrumb-link:hover {
  color: var(--color-primary);
}

.breadcrumb-separator {
  margin: 0 8px;
}

.breadcrumb-current {
  color: var(--color-primary);
  font-weight: 500;
}

/* Section Tag & Titles */
.section-tag {
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--color-secondary);
  margin-bottom: 0.5rem;
  display: block;
}

.section-tag--center {
  text-align: center;
}

.section-title {
  font-family: var(--font-family-heading);
  font-size: 2.25rem;
  color: var(--color-primary);
  margin-bottom: 1.5rem;
  line-height: 1.3;
}

.text-center {
  text-align: center;
}

/* Hero Section */
.contact-hero {
  max-width: 1200px;
  margin: 0 auto 4rem auto;
  padding: 0 24px;
}

.contact-hero__grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 3rem;
  align-items: center;
}

.contact-hero__title {
  font-family: var(--font-family-heading);
  font-size: 3rem;
  font-weight: 700;
  color: var(--color-primary);
  line-height: 1.2;
  margin-bottom: 1.25rem;
}

.contact-hero__subtitle {
  font-size: 1.05rem;
  line-height: 1.6;
  color: var(--color-text-secondary);
  margin-bottom: 2rem;
}

.contact-hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.contact-hero__image-container {
  display: flex;
  justify-content: center;
}

.contact-hero__image {
  max-width: 100%;
  height: auto;
  border-radius: 20px;
  box-shadow: var(--shadow-md);
  object-fit: cover;
  max-height: 380px;
}

/* Buttons */
.contact-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 1.5rem;
  height: 48px;
  border-radius: 24px;
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  transition: all var(--transition-normal);
  cursor: pointer;
  border: none;
  box-shadow: var(--shadow-sm);
  min-width: 140px;
}

.contact-btn--primary {
  background-color: var(--color-primary);
  color: #ffffff;
}

.contact-btn--primary:hover {
  background-color: var(--color-primary-hover);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.contact-btn--secondary {
  background-color: var(--color-secondary);
  color: #2d051c;
}

.contact-btn--secondary:hover {
  background-color: #ffffff;
  color: var(--color-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.contact-btn--whatsapp {
  background-color: #25d366;
  color: #ffffff;
}

.contact-btn--whatsapp:hover {
  background-color: #1ebd59;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.contact-btn--outline {
  border: 2px solid var(--color-secondary);
  background-color: transparent;
  color: var(--color-primary);
}

.contact-btn--outline:hover {
  background-color: var(--color-secondary);
  color: #2d051c;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.contact-btn--outline-gold {
  border: 1.5px solid var(--color-secondary);
  background-color: transparent;
  color: var(--color-secondary-hover);
}

.contact-btn--outline-gold:hover {
  background-color: var(--color-secondary);
  color: #2d051c;
  transform: translateY(-2px);
}

.contact-btn--charcoal {
  background-color: #111111;
  color: #ffffff;
}

.contact-btn--charcoal:hover {
  background-color: #2a2a2a;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.btn-full {
  width: 100%;
}

.btn-icon {
  margin-right: 8px;
}

/* Container */
.contact-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

.contact-section {
  margin-bottom: 5rem;
}

/* Section 2: Quick Cards */
.quick-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.quick-card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 280px;
  transition: transform var(--transition-normal), box-shadow var(--transition-normal);
}

.quick-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}

.quick-card__header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1.25rem;
}

.quick-card__icon {
  font-size: 1.75rem;
}

.quick-card__title {
  font-size: 1.2rem;
  font-weight: 600;
  font-family: var(--font-family-heading);
  color: var(--color-primary);
}

.quick-card__body {
  margin-bottom: 2rem;
}

.quick-card__value {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--color-text-primary);
  text-decoration: none;
  display: block;
  margin-bottom: 0.5rem;
  font-family: var(--font-family-heading);
}

.text-email {
  font-size: 1.1rem;
}

.quick-card__sub {
  font-size: 0.88rem;
  color: var(--color-text-secondary);
  line-height: 1.5;
  margin: 2px 0;
}

/* Main Split Grid (Form & Address) */
.main-split-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 2.5rem;
  margin-bottom: 5rem;
}

/* Section 3: Contact Form */
.form-card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2.5rem;
  box-shadow: var(--shadow-sm);
}

.form-card__title {
  font-family: var(--font-family-heading);
  font-size: 1.85rem;
  color: var(--color-primary);
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.form-card__subtitle {
  font-size: 0.95rem;
  color: var(--color-text-secondary);
  margin-bottom: 2rem;
  line-height: 1.5;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-primary);
}

.form-input {
  width: 100%;
  height: 48px;
  border: 1.5px solid var(--color-border);
  border-radius: 12px;
  padding: 0 16px;
  font-size: 0.92rem;
  font-family: var(--font-family-base);
  background-color: #ffffff;
  color: var(--color-text-primary);
  outline: none;
  transition: all var(--transition-fast);
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(74, 14, 46, 0.08);
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234a0e2e'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 16px;
  padding-right: 40px;
}

.form-textarea {
  height: auto;
  padding: 12px 16px;
  resize: vertical;
}

.form-submit-btn {
  height: 52px;
  font-size: 1rem;
  margin-top: 1rem;
}

/* Validation states styling */
.form-group.has-error .form-input {
  border-color: var(--color-danger);
}

.form-group.has-error .form-input:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
}

.form-group.is-valid .form-input {
  border-color: var(--color-success);
}

.error-text {
  font-size: 0.78rem;
  color: var(--color-danger);
  margin-top: 2px;
}

/* Sidebar Details */
.contact-sidebar {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Section 4: Visit Office */
.info-card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.info-card__title {
  font-family: var(--font-family-heading);
  font-size: 1.5rem;
  color: var(--color-primary);
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.info-card__content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.info-item {
  display: flex;
  gap: 12px;
}

.info-item__icon {
  font-size: 1.25rem;
  line-height: 1.4;
}

.info-item__text {
  font-size: 0.92rem;
  line-height: 1.6;
  color: var(--color-text-secondary);
}

.info-item__text strong {
  color: var(--color-text-primary);
}

.info-card__btn {
  height: 48px;
}

/* Section 5: Business Hours */
.hours-card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.hours-card__title {
  font-family: var(--font-family-heading);
  font-size: 1.35rem;
  color: var(--color-primary);
  margin-bottom: 1.25rem;
  font-weight: 600;
}

.hours-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.hours-list__item {
  display: flex;
  justify-content: space-between;
  font-size: 0.92rem;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--color-border);
  color: var(--color-text-primary);
}

.hours-list__item.closed {
  color: var(--color-text-muted);
}

.hours-list__item.closed .time {
  font-weight: 600;
  color: var(--color-danger);
}

/* Section 6: Google Map */
.map-section {
  position: relative;
}

.map-container {
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-md);
  position: relative;
}

.map-iframe {
  vertical-align: middle;
}

.map-overlay {
  position: absolute;
  bottom: 20px;
  right: 20px;
  z-index: 10;
}

/* Section 7: FAQ Accordion */
.faq-accordion {
  max-width: 800px;
  margin: 2.5rem auto 0 auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.faq-item {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-normal);
}

.faq-item__header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  outline: none;
}

.faq-item__question {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--color-primary);
  padding-right: 20px;
}

.faq-item__icon-indicator {
  width: 12px;
  height: 12px;
  position: relative;
  flex-shrink: 0;
}

.faq-item__icon-indicator::before,
.faq-item__icon-indicator::after {
  content: '';
  position: absolute;
  background-color: var(--color-primary);
  transition: transform var(--transition-normal);
}

/* Horizontal line */
.faq-item__icon-indicator::before {
  top: 5px;
  left: 0;
  width: 12px;
  height: 2px;
}

/* Vertical line */
.faq-item__icon-indicator::after {
  top: 0;
  left: 5px;
  width: 2px;
  height: 12px;
}

.faq-item.is-active {
  border-color: var(--color-secondary);
}

/* Rotate vertical line to make a minus sign */
.faq-item.is-active .faq-item__icon-indicator::after {
  transform: rotate(90deg);
  opacity: 0;
}

.faq-item__body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-out;
}

.faq-item.is-active .faq-item__body {
  max-height: 200px;
}

.faq-item__answer {
  padding: 0 1.5rem 1.5rem 1.5rem;
  font-size: 0.95rem;
  line-height: 1.6;
  color: var(--color-text-secondary);
}

/* Section 8: Final CTA */
.cta-banner {
  background: linear-gradient(135deg, var(--color-primary) 0%, #30081d 100%);
  border-radius: 16px;
  padding: 4rem 2rem;
  text-align: center;
  color: #ffffff;
  box-shadow: var(--shadow-lg);
}

.cta-banner__title {
  font-family: var(--font-family-heading);
  font-size: 2.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #fffcf7;
}

.cta-banner__subtitle {
  font-size: 1.05rem;
  max-width: 600px;
  margin: 0 auto 2rem auto;
  color: #ebd2dd;
  line-height: 1.6;
}

.cta-banner__actions {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1.25rem;
}

/* Trust Indicators */
.trust-indicators {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 1.5rem;
  padding: 2rem 1rem;
  border-top: 1px solid var(--color-border);
  margin-top: 4rem;
}

.trust-item {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}

.trust-icon {
  color: var(--color-secondary-hover);
  font-size: 1.1rem;
}

/* Mobile Sticky Bottom Bar */
.mobile-sticky-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background-color: #ffffff;
  box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.08);
  display: none;
  z-index: 999;
}

.sticky-action-item {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
}

.sticky-action-item--call {
  background-color: transparent;
  color: var(--color-primary);
  border-right: 1px solid var(--color-border);
}

.sticky-action-item--whatsapp {
  background-color: #25d366;
  color: #ffffff;
}

/* Scroll Reveal Classes */
.reveal {
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.6s ease-out;
}

.reveal.active {
  opacity: 1;
  transform: translateY(0);
}

/* Responsive Breakpoints */

@media (max-width: 992px) {
  .contact-hero__grid {
    grid-template-columns: 1fr;
    gap: 2.5rem;
    text-align: center;
  }

  .contact-hero__actions {
    justify-content: center;
  }

  .contact-hero__image-container {
    order: -1;
  }

  .quick-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .main-split-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .contact-hero__title {
    font-size: 2.25rem;
  }

  .section-title {
    font-size: 1.85rem;
  }

  .quick-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }

  .form-card {
    padding: 1.5rem;
  }

  .trust-indicators {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }

  /* Show sticky bottom bar on mobile */
  .mobile-sticky-bar {
    display: flex;
  }

  .contact-page {
    padding-bottom: 120px; /* offset for sticky bar */
  }
}
</style>
