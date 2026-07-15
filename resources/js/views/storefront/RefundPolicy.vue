<template>
  <div class="static-page-wrapper" style="background: #fffcf7; min-height: 100vh; padding-bottom: 40px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs" style="padding: var(--spacing-sm) 20px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">
      <router-link to="/" style="color: #64748b; text-decoration: none;">Home</router-link>
      <span style="margin: 0 8px;">/</span>
      <span style="color: #4a0e2e; font-weight: 500;">Refund & Cancellation Policy</span>
    </div>

    <!-- Hero Banner -->
    <section class="hero-section" style="text-align: center; padding: 48px 20px; background: #fffbf5; border-bottom: 1px solid #f3ece3; margin-bottom: 32px;">
      <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.5rem; color: #4a0e2e; margin-bottom: 12px; font-weight: 700;">Refund & Cancellation Policy</h1>
      <p style="font-family: 'Poppins', sans-serif; font-size: 1rem; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.6;">
        Our goal is your complete satisfaction. Learn about return eligibility, exchange criteria, and refund processing timelines.
      </p>
    </section>

    <!-- Content Container -->
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; font-family: 'Poppins', sans-serif; color: #2d2d2d; line-height: 1.8;">
      <div class="policy-layout-grid">
        
        <!-- Sidebar Navigation (Desktop Only) -->
        <aside style="font-size: 0.88rem; position: sticky; top: 100px; align-self: start;" class="desktop-only">
          <div style="background: #ffffff; padding: 20px; border: 1px solid #e2d2ca; border-radius: 8px;">
            <h4 style="color: #4a0e2e; font-weight: 600; margin-bottom: 16px; border-bottom: 1px solid #f3ece3; padding-bottom: 8px;">Policy Sections</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
              <li><a href="#cancellation" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">1. Cancellations</a></li>
              <li><a href="#eligibility" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">2. Return Eligibility</a></li>
              <li><a href="#damaged" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">3. Damaged / Incorrect</a></li>
              <li><a href="#timeline" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">4. Processing Times</a></li>
              <li><a href="#methods" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">5. Refund Methods</a></li>
              <li><a href="#exchange" style="color: #64748b; text-decoration: none; font-weight: 500;" onmouseover="this.style.color='#4a0e2e'" onmouseout="this.style.color='#64748b'">6. Exchange Policy</a></li>
            </ul>
          </div>
        </aside>

        <!-- Policy Text Details -->
        <main class="glass-panel" style="padding: 32px; border: 1px solid #e2d2ca; border-radius: 12px; background: #ffffff; font-size: 0.95rem;">
          
          <!-- 1 -->
          <section id="cancellation" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">1. Order Cancellations</h2>
            <p style="margin-bottom: 12px;">
              You can cancel your order at any time before it enters the shipping / dispatch phase. To cancel:
            </p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li>Navigate to your **Customer Dashboard > My Orders** and click Cancel (available for pending orders).</li>
              <li>If the order has already been paid via Razorpay, our backend automatically initiates the cancellation event, decrements the active coupon's `times_used` counter, deletes the `coupon_usage` record, and returns the reserved stock back to the ledger.</li>
              <li>Once an order is shipped, cancellation requests cannot be accepted.</li>
            </ul>
          </section>

          <!-- 2 -->
          <section id="eligibility" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">2. Return Eligibility</h2>
            <p style="margin-bottom: 12px;">
              We offer a **7-day easy exchange/return window** starting from the date of delivery. To be eligible for a return:
            </p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li>The product must be unused, unwashed, and showing no perfume or wear marks.</li>
              <li>Original tags, invoice copies, and packaging bags must remain intact.</li>
              <li>Sarees must not have their fall or edging stitched.</li>
              <li>Custom stitched items or custom sizing blouses are non-returnable.</li>
            </ul>
          </section>

          <!-- 3 -->
          <section id="damaged" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">3. Damaged or Incorrect Products</h2>
            <p>
              In the rare event that you receive a damaged fabric, stitching defect, or incorrect product variant, please raise a return request via your dashboard within **48 hours** of delivery. We will schedule a free pickup and immediately ship a replacement or issue a full refund at no extra cost.
            </p>
          </section>

          <!-- 4 -->
          <section id="timeline" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">4. Refund Processing Timeline</h2>
            <p style="margin-bottom: 12px;">
              Once a return pickup is completed, the item is transported back to our Tirupur warehouse.
            </p>
            <ul style="padding-left: 20px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
              <li>The product undergoes a Quality Control (QC) inspection.</li>
              <li>If it passes, the admin approves the refund.</li>
              <li>Refund approvals trigger a callback processing system. Refunds are credited back to the original payment source within **5 to 7 business days** (standard bank processing times apply).</li>
            </ul>
          </section>

          <!-- 5 -->
          <section id="methods" style="margin-bottom: 32px;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">5. Refund Methods</h2>
            <ul style="padding-left: 20px; display: flex; flex-direction: column; gap: 4px;">
              <li><strong>Prepaid Transactions:</strong> Refunds are processed directly back to the original credit/debit card, UPI account, or net banking wallet via Razorpay.</li>
              <li><strong>Cash on Delivery (COD) Transactions:</strong> Our team will contact you to collect bank details or UPI IDs to execute a direct bank transfer, or provide store credit.</li>
            </ul>
          </section>

          <!-- 6 -->
          <section id="exchange">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.45rem; color: #4a0e2e; border-bottom: 1px solid #f3ece3; padding-bottom: 6px; margin-bottom: 12px; font-weight: 600;">6. Exchange Policy</h2>
            <p>
              If you require a different size or color group for an item, raise an exchange request. Once our courier picks up the original item, we will ship the replacement variant immediately. Exchanges are free of charge for the first occurrence.
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
  document.title = "Refund & Cancellation Policy - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Learn about the return window, refund eligibility, exchange policies, and cancellation timelines at Maya Sree Fashion.");
  }
});
</script>

<style scoped>
.policy-layout-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 32px;
}

.desktop-only {
  display: none;
}

@media (min-width: 768px) {
  .policy-layout-grid {
    grid-template-columns: 1fr 3fr;
  }
  .desktop-only {
    display: block;
  }
}
</style>
