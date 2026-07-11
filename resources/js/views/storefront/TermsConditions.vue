<template>
  <div class="static-page-wrapper" style="background: #fffcf7; min-height: 100vh; padding-bottom: 40px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs" style="padding: var(--spacing-sm) 20px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">
      <router-link to="/" style="color: #64748b; text-decoration: none;">Home</router-link>
      <span style="margin: 0 8px;">/</span>
      <span style="color: #4a0e2e; font-weight: 500;">Terms & Conditions</span>
    </div>

    <!-- Hero Banner -->
    <section class="hero-section" style="text-align: center; padding: 48px 20px; background: #fffbf5; border-bottom: 1px solid #f3ece3; margin-bottom: 32px;">
      <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.5rem; color: #4a0e2e; margin-bottom: 12px; font-weight: 700;">Terms & Conditions</h1>
      <p style="font-family: 'Poppins', sans-serif; font-size: 1rem; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.6;">
        Welcome to Maya Sree South Indian Fashion. Please review the terms, conditions, and regulations governing your usage of our store.
      </p>
    </section>

    <!-- Content Container -->
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; font-family: 'Poppins', sans-serif; color: #2d2d2d; line-height: 1.8;">
      <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 32px;">
        
        <!-- Sidebar Navigation (Desktop Only) -->
        <aside style="font-size: 0.88rem; position: sticky; top: 100px; align-self: start;" class="desktop-only">
          <div style="background: #ffffff; padding: 20px; border: 1px solid #e2d2ca; border-radius: 8px;">
            <h4 style="color: #4a0e2e; font-weight: 600; margin-bottom: 16px; border-bottom: 1px solid #f3ece3; padding-bottom: 8px;">Terms Sections</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
              <li><a href="#intro" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">1. Agreement Terms</a></li>
              <li><a href="#pricing" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">2. Products & Pricing</a></li>
              <li><a href="#orders" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">3. Orders & Payments</a></li>
              <li><a href="#shipping" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">4. Shipping & Returns</a></li>
              <li><a href="#intellectual" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">5. Intellectual Property</a></li>
              <li><a href="#liability" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">6. Limit of Liability</a></li>
              <li><a href="#governing" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">7. Governing Law</a></li>
            </ul>
          </div>
        </aside>

        <!-- Terms Text Details -->
        <main class="glass-panel" style="padding: 32px; border: 1px solid #e2d2ca; border-radius: 12px; background: #ffffff; font-size: 0.95rem;">
          
          <p style="margin-bottom: 24px;">
            These Terms & Conditions constitute a legally binding agreement made between you ("the customer") and **Maya Sree Fashion**, concerning your access to and use of our e-commerce platform.
          </p>

          <!-- 1 -->
          <section id="intro" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">1. Agreement to Terms</h2>
            <p>
              By accessing this website, creating an account, or placing an order, you agree that you have read, understood, and agreed to be bound by all of these Terms and Conditions. If you do not agree, you are prohibited from using the site.
            </p>
          </section>

          <!-- 2 -->
          <section id="pricing" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">2. Products and Pricing</h2>
            <p style="margin-bottom: 12px;">
              We make every effort to display the colors, sizing, and details of our sarees, blouses, and traditional wear as accurately as possible. However, actual colors depend on your monitor.
            </p>
            <p>
              All prices are listed in Indian Rupees (INR) and include GST unless specified otherwise. We reserve the right to modify prices or discontinue items without prior notice.
            </p>
          </section>

          <!-- 3 -->
          <section id="orders" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">3. Orders and Payments</h2>
            <p style="margin-bottom: 12px;">
              All orders are subject to stock availability. When you check out, order totals and coupon discount eligibility are automatically recalculated and verified on our server side to ensure transactional security.
            </p>
            <p>
              We integrate with **Razorpay** to process credit cards, debit cards, UPI, net banking, and wallets securely. Payments are processed in real-time. We reserve the right to cancel any orders placed with fraudulent intent or showing double payment codes.
            </p>
          </section>

          <!-- 4 -->
          <section id="shipping" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">4. Shipping and Returns</h2>
            <p>
              Product delivery timelines are estimates and depend on local logistics networks. All orders carry stock reservations that are automatically released back to our inventory ledger if a payment fails or is aborted. Returns and cancellations are subject to our refund policy.
            </p>
          </section>

          <!-- 5 -->
          <section id="intellectual" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">5. Intellectual Property Rights</h2>
            <p>
              All content on this site, including design code, logos, icons, styling rules, typography, templates, images, and text, is owned or licensed by Maya Sree Fashion and protected by copyright, trademark, and trade dress regulations.
            </p>
            <p style="margin-top: 12px;">
              For the protection of our weavers and designs, copy prevention mechanisms (disabling right-click and DevTools inspect deterrence) are active across product details. You are prohibited from scraping, reproducing, or hotlinking our photography.
            </p>
          </section>

          <!-- 6 -->
          <section id="liability" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">6. Limitation of Liability</h2>
            <p>
              To the maximum extent permitted by applicable Indian laws, Maya Sree Fashion shall not be liable for any indirect, incidental, special, or consequential damages resulting from your usage or inability to access our storefront, checkout, or payment gateways.
            </p>
          </section>

          <!-- 7 -->
          <section id="governing">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">7. Governing Law and Dispute Resolution</h2>
            <p>
              These Terms and Conditions shall be governed by and defined in accordance with the laws of India. Any disputes arising out of these terms shall be subject to the exclusive jurisdiction of the courts located in Tirupur, Tamil Nadu, India.
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
  document.title = "Terms & Conditions - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Review the Terms & Conditions of Maya Sree South Indian Fashion. Learn about orders, secure payments, shipping, and user responsibilities.");
  }
});
</script>
