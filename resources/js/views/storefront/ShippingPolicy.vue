<template>
  <div class="static-page-wrapper" style="background: #fffcf7; min-height: 100vh; padding-bottom: 40px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs" style="padding: var(--spacing-sm) 20px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">
      <router-link to="/" style="color: #64748b; text-decoration: none;">Home</router-link>
      <span style="margin: 0 8px;">/</span>
      <span style="color: #4a0e2e; font-weight: 500;">Shipping Policy</span>
    </div>

    <!-- Hero Banner -->
    <section class="hero-section" style="text-align: center; padding: 48px 20px; background: #fffbf5; border-bottom: 1px solid #f3ece3; margin-bottom: 32px;">
      <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.5rem; color: #4a0e2e; margin-bottom: 12px; font-weight: 700;">Shipping Policy</h1>
      <p style="font-family: 'Poppins', sans-serif; font-size: 1rem; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.6;">
        Providing nationwide courier delivery across India. Learn about our dispatch timelines, shipping rates, and delivery estimates.
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
              <li><a href="#locations" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">1. Shipping Locations</a></li>
              <li><a href="#processing" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">2. Processing Times</a></li>
              <li><a href="#delivery" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">3. Delivery Timelines</a></li>
              <li><a href="#charges" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">4. Shipping Charges</a></li>
              <li><a href="#tracking" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">5. Order Tracking</a></li>
              <li><a href="#failed" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">6. Delivery Issues</a></li>
            </ul>
          </div>
        </aside>

        <!-- Policy Text Details -->
        <main class="glass-panel" style="padding: 32px; border: 1px solid #e2d2ca; border-radius: 12px; background: #ffffff; font-size: 0.95rem;">
          
          <!-- 1 -->
          <section id="locations" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">1. Shipping Locations</h2>
            <p>
              We deliver to over 19,000 pin codes across India, covering all major metropolitan cities, Tier-2/Tier-3 towns, and rural districts. We partner with India's leading logistics providers, including **Delhivery, Blue Dart, Xpressbees, and DTDC**, to ensure safe and timely handovers.
            </p>
          </section>

          <!-- 2 -->
          <section id="processing" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">2. Order Processing & Dispatch</h2>
            <ul style="padding-left: 20px; display: flex; flex-direction: column; gap: 4px;">
              <li><strong>Ready-to-Ship Products:</strong> Dispatched from our Tirupur warehouse within **24 to 48 hours** of successful payment confirmation.</li>
              <li><strong>Custom Stitched / Blouses:</strong> Require **3 to 5 additional business days** for tailoring and custom styling before shipment.</li>
              <li>Dispatches are executed daily from Monday to Saturday, excluding national and state holidays.</li>
            </ul>
          </section>

          <!-- 3 -->
          <section id="delivery" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">3. Estimated Delivery Timelines</h2>
            <p style="margin-bottom: 12px;">Standard transit timelines following dispatch:</p>
            <ul style="padding-left: 20px; display: flex; flex-direction: column; gap: 4px;">
              <li><strong>South India (Tamil Nadu, Kerala, Karnataka, Andhra/Telangana):</strong> 2 to 4 business days.</li>
              <li><strong>Metro Cities (Mumbai, Delhi, Kolkata, Bangalore):</strong> 3 to 5 business days.</li>
              <li><strong>Rest of India (North, West & East regions):</strong> 4 to 7 business days.</li>
              <li><strong>North-East & Remote areas:</strong> 7 to 10 business days.</li>
            </ul>
          </section>

          <!-- 4 -->
          <section id="charges" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">4. Shipping Charges</h2>
            <ul style="padding-left: 20px; display: flex; flex-direction: column; gap: 4px;">
              <li><strong>Free Shipping:</strong> Applied automatically on all orders with cart totals of **₹999 and above**.</li>
              <li><strong>Standard Shipping Fee:</strong> A flat fee of **₹100** is applied on orders below ₹999.</li>
              <li>Shipping charges are clearly calculated and displayed on the checkout page before payment.</li>
            </ul>
          </section>

          <!-- 5 -->
          <section id="tracking" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">5. Order Tracking</h2>
            <p>
              Once your package is handpicked and dispatched, we will send an SMS and email notification containing the assigned courier name and tracking number. You can also view live tracking updates by logging into your **Customer Dashboard > My Account > Orders** section.
            </p>
          </section>

          <!-- 6 -->
          <section id="failed">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">6. Failed Delivery Attempts & Reshipment</h2>
            <p>
              Our courier partners make up to **three delivery attempts** before returning the package to our warehouse. Please ensure your contact number is reachable and shipping address details (including landmark) are accurate. Address updates can be requested by contacting support before the first delivery attempt.
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
  document.title = "Shipping Policy - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Check shipping costs, delivery times, dispatch windows, and tracking details for orders at Maya Sree Fashion.");
  }
});
</script>
