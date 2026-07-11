<template>
  <div class="static-page-wrapper" style="background: #fffcf7; min-height: 100vh; padding-bottom: 40px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs" style="padding: var(--spacing-sm) 20px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">
      <router-link to="/" style="color: #64748b; text-decoration: none;">Home</router-link>
      <span style="margin: 0 8px;">/</span>
      <span style="color: #4a0e2e; font-weight: 500;">Privacy Policy</span>
    </div>

    <!-- Hero Banner -->
    <section class="hero-section" style="text-align: center; padding: 48px 20px; background: #fffbf5; border-bottom: 1px solid #f3ece3; margin-bottom: 32px;">
      <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.5rem; color: #4a0e2e; margin-bottom: 12px; font-weight: 700;">Privacy Policy</h1>
      <p style="font-family: 'Poppins', sans-serif; font-size: 1rem; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.6;">
        Last Updated: July 6, 2026. Your privacy is important to us. Learn how we handle and protect your customer information.
      </p>
    </section>

    <!-- Content Container -->
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; font-family: 'Poppins', sans-serif; color: #2d2d2d; line-height: 1.8;">
      <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 32px;">
        
        <!-- Sidebar Navigation (Desktop Only) -->
        <aside style="font-size: 0.88rem; position: sticky; top: 100px; align-self: start;" class="desktop-only">
          <div style="background: #ffffff; padding: 20px; border: 1px solid #e2d2ca; border-radius: 8px;">
            <h4 style="color: #4a0e2e; font-weight: 600; margin-bottom: 16px; border-bottom: 1px solid #f3ece3; padding-bottom: 8px;">Policy Sections</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
              <li><a href="#collect" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">1. Information Collect</a></li>
              <li><a href="#usage" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">2. How We Use It</a></li>
              <li><a href="#cookies" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">3. Cookies & Tracking</a></li>
              <li><a href="#security" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">4. Data Security</a></li>
              <li><a href="#thirdparty" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">5. Third-Party Access</a></li>
              <li><a href="#rights" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">6. Your Rights</a></li>
              <li><a href="#contact" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">7. Support details</a></li>
            </ul>
          </div>
        </aside>

        <!-- Policy Text Details -->
        <main class="glass-panel" style="padding: 32px; border: 1px solid #e2d2ca; border-radius: 12px; background: #ffffff; font-size: 0.95rem;">
          
          <p style="margin-bottom: 24px;">
            Maya Sree Fashion ("we", "our", "us") values your trust. This Privacy Policy details how we collect, use, share, and protect your personal information when you visit or shop on our website.
          </p>

          <!-- 1 -->
          <section id="collect" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">1. Information We Collect</h2>
            <p style="margin-bottom: 12px;">When you buy items or open an account, we collect:</p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li><strong>Personal Details:</strong> Your name, billing/shipping address, telephone number, and email.</li>
              <li><strong>Credentials:</strong> Account login details (hashed passwords).</li>
              <li><strong>Transactions:</strong> Ordered items, subtotal, discounts, and payment methods.</li>
              <li><strong>Device Info:</strong> IP address, browser type, operating system, and activity logs.</li>
            </ul>
          </section>

          <!-- 2 -->
          <section id="usage" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">2. How We Use Your Information</h2>
            <p style="margin-bottom: 12px;">We process your data to fulfill business obligations, including:</p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li>Registering your orders, calculating exact totals, and shipping products.</li>
              <li>Processing payments through secure integrated gateways (e.g., Razorpay).</li>
              <li>Verifying webhook callback payloads to mark orders as paid.</li>
              <li>Sending transaction alerts, order status logs, and newsletter updates (if subscribed).</li>
              <li>Preventing checkout abuse, duplicate payments, and security threats.</li>
            </ul>
          </section>

          <!-- 3 -->
          <section id="cookies" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">3. Cookies & Tracking Technologies</h2>
            <p style="margin-bottom: 12px;">
              We use secure session cookies to remember items in your shopping cart, authenticate your dashboard sessions, and maintain active storefront login sessions.
            </p>
            <p>
              In compliance with production security guidelines, all session cookies carry the HttpOnly, Secure, and SameSite=Lax flags in production environments. You can manage or disable cookies via your browser settings, though it may disable cart/checkout functions.
            </p>
          </section>

          <!-- 4 -->
          <section id="security" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">4. Data Security</h2>
            <p style="margin-bottom: 12px;">
              We implement industry-grade technical safeguards to prevent unauthorized data access, leakage, or loss:
            </p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li>Enforcing secure HTTPS transport protocols throughout the entire portal.</li>
              <li>Sanitizing user inputs on checkout to block database injections and cross-site scripting (XSS).</li>
              <li>Excluding raw passwords or credentials from audit logs and webhook payloads.</li>
              <li>Storing card details or credentials only on PCI-compliant payment gateways (Razorpay).</li>
            </ul>
          </section>

          <!-- 5 -->
          <section id="thirdparty" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">5. Third-Party Services</h2>
            <p style="margin-bottom: 12px;">
              We share minimal data with trusted partners to operate our store:
            </p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li><strong>Razorpay:</strong> To process secure payments. Transaction details, order totals, and customer emails are passed to facilitate payments.</li>
              <li><strong>Shipping Partners (e.g., Delhivery):</strong> To deliver physical orders to your shipping address.</li>
            </ul>
          </section>

          <!-- 6 -->
          <section id="rights" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">6. Your Customer Rights</h2>
            <p style="margin-bottom: 12px;">Under Indian data privacy regulations, you have the right to:</p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li>Access, correct, or update your name, email, addresses, and profiles.</li>
              <li>Delete your registered storefront account.</li>
              <li>Opt-out of marketing newsletters or tracking.</li>
            </ul>
          </section>

          <!-- 7 -->
          <section id="contact">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">7. Privacy Support Contact</h2>
            <p>
              If you have any questions about this Privacy Policy or wish to request data deletion, contact us at:<br />
              ✉️ Email: <a href="mailto:support@mayasree.com" style="color: #4a0e2e; font-weight: 600; text-decoration: none;">support@mayasree.com</a><br />
              📞 Phone: <span style="font-weight: 600;">+91 99442 85102</span>
            </p>
          </section>

        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  document.title = "Privacy Policy - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Review the Privacy Policy of Maya Sree South Indian Fashion. Learn how we secure, process, and respect your customer information.");
  }
});
</script>
