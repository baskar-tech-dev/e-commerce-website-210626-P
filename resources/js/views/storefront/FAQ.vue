<template>
  <div class="static-page-wrapper" style="background: #fffcf7; min-height: 100vh; padding-bottom: 40px;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs" style="padding: var(--spacing-sm) 20px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">
      <router-link to="/" style="color: #64748b; text-decoration: none;">Home</router-link>
      <span style="margin: 0 8px;">/</span>
      <span style="color: #4a0e2e; font-weight: 500;">Frequently Asked Questions</span>
    </div>

    <!-- Hero Banner -->
    <section class="hero-section" style="text-align: center; padding: 48px 20px; background: #fffbf5; border-bottom: 1px solid #f3ece3; margin-bottom: 32px;">
      <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 2.5rem; color: #4a0e2e; margin-bottom: 12px; font-weight: 700;">FAQs</h1>
      <p style="font-family: 'Poppins', sans-serif; font-size: 1rem; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.6;">
        Find fast answers to common questions regarding orders, payments, size, shipping, and exchanges.
      </p>
    </section>

    <!-- Content Container -->
    <div class="container" style="max-width: 900px; margin: 0 auto; padding: 0 20px; font-family: 'Poppins', sans-serif; color: #2d2d2d; line-height: 1.7;">
      
      <!-- FAQ Category Selectors -->
      <div style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 32px;">
        <button 
          v-for="cat in categories" 
          :key="cat.id" 
          @click="activeCategory = cat.id"
          :style="{
            padding: '10px 20px',
            borderRadius: '20px',
            border: '1px solid ' + (activeCategory === cat.id ? '#4a0e2e' : '#e2d2ca'),
            background: activeCategory === cat.id ? '#4a0e2e' : '#ffffff',
            color: activeCategory === cat.id ? '#ffffff' : '#4a0e2e',
            fontWeight: '600',
            fontSize: '0.85rem',
            cursor: 'pointer',
            transition: 'all 0.2s'
          }"
        >
          {{ cat.name }}
        </button>
      </div>

      <!-- FAQ Accordion List -->
      <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 48px;">
        <div 
          v-for="(faq, idx) in filteredFaqs" 
          :key="idx" 
          style="background: #ffffff; border: 1px solid #e2d2ca; border-radius: 8px; overflow: hidden; transition: all 0.2s;"
        >
          <!-- Header -->
          <div 
            @click="toggleFaq(idx)" 
            style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none;"
          >
            <span style="font-weight: 600; font-size: 0.95rem; color: #4a0e2e; padding-right: 12px;">{{ faq.q }}</span>
            <span style="font-size: 1.2rem; color: #d4af37; font-weight: bold;">
              {{ activeFaqIndex === idx ? '−' : '+' }}
            </span>
          </div>

          <!-- Answer Body -->
          <div 
            v-if="activeFaqIndex === idx" 
            style="padding: 0 20px 20px; font-size: 0.9rem; color: #64748b; border-top: 1px solid #f8f1ed; padding-top: 16px; line-height: 1.6;"
          >
            {{ faq.a }}
          </div>
        </div>
      </div>

      <!-- Support CTA Banner -->
      <div style="text-align: center; padding: 24px; background: #fffbf5; border-radius: 12px; border: 1px solid #f3ece3;">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; color: #4a0e2e; margin-bottom: 8px; font-weight: 600;">Still Have Questions?</h3>
        <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 16px;">Our team is happy to assist you directly on WhatsApp or email.</p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
          <router-link to="/contact-us" class="btn" style="display: inline-block; background: #4a0e2e; color: #ffffff; font-weight: bold; text-decoration: none; padding: 10px 24px; border-radius: 20px; font-size: 0.85rem;">Contact Support</router-link>
          <a href="https://wa.me/919944285102" target="_blank" style="display: inline-block; background: #128c7e; color: #ffffff; font-weight: bold; text-decoration: none; padding: 10px 24px; border-radius: 20px; font-size: 0.85rem;">WhatsApp Chat</a>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const activeCategory = ref('orders');
const activeFaqIndex = ref(null);

const categories = [
  { id: 'orders', name: 'Orders' },
  { id: 'payments', name: 'Payments' },
  { id: 'shipping', name: 'Shipping' },
  { id: 'returns', name: 'Returns & Exchange' },
  { id: 'custom', name: 'Custom Stitching' }
];

const faqs = [
  // Orders
  { cat: 'orders', q: 'How do I place an order?', a: 'Browse our shop, select your size, and add items to your cart. Click Checkout, enter your shipping details, select your payment method (COD or Online), and confirm. You will receive an SMS and email order confirmation.' },
  { cat: 'orders', q: 'Can I modify or cancel my order after placing it?', a: 'You can cancel your order from your Customer Dashboard before it is dispatched. Once shipped, the order cannot be modified or cancelled.' },
  { cat: 'orders', q: 'How do I check my order status?', a: 'Log into your Customer Dashboard and select the My Orders tab. You will see real-time updates (Pending, Confirmed, Shipped, Delivered) and tracking numbers.' },

  // Payments
  { cat: 'payments', q: 'What payment options do you support?', a: 'We support all major payment options via Razorpay, including Credit/Debit Cards (Visa, MasterCard, RuPay), UPI (GPay, PhonePe, Paytm), Net Banking, and Wallets. Cash on Delivery (COD) is also available for select pin codes.' },
  { cat: 'payments', q: 'Is it safe to pay online here?', a: 'Absolutely. We do not store any card credentials on our servers. All transactions are handled securely by Razorpay using high-grade SSL encryption and PCI-DSS standards.' },
  { cat: 'payments', q: 'What happens if my transaction fails but my money is deducted?', a: 'This is usually held in the bank network and automatically returned to your original payment source within 24 to 48 hours. You can contact support if the amount is not returned.' },

  // Shipping
  { cat: 'shipping', q: 'How much are the shipping charges?', a: 'Standard shipping is ₹100 for orders under ₹999. Shipping is completely free for all orders of ₹999 and above.' },
  { cat: 'shipping', q: 'What is your delivery timeline?', a: 'We process and ship orders within 24 to 48 hours. Delivery takes 2 to 4 business days for South India, and 3 to 7 business days for other states.' },
  { cat: 'shipping', q: 'Do you ship internationally?', a: 'Currently, we only ship within India. We plan to introduce international shipping soon.' },

  // Returns
  { cat: 'returns', q: 'What is your return window?', a: 'We offer a 7-day exchange and return window starting from the delivery date. Custom stitched blouses or customized garments are not eligible for returns.' },
  { cat: 'returns', q: 'How do I initiate a return or exchange?', a: 'Go to your account dashboard, view the delivered order, select Return/Exchange, pick the item, and submit the reason. We will schedule a pickup within 2 business days.' },
  { cat: 'returns', q: 'When will I receive my refund?', a: 'Once the returned item is inspected at our Tirupur warehouse, refunds are approved. Prepaid refunds take 5 to 7 business days via Razorpay back to your original source. COD refunds are sent via direct bank transfer.' },

  // Custom
  { cat: 'custom', q: 'Do you offer custom blouse stitching?', a: 'Yes! We specialize in ready-made blouses with stretchable fabric and margins to adjust size. For bespoke blouse styling or specific sizing modifications, you can select the sizing variant or message our design team on WhatsApp.' },
  { cat: 'custom', q: 'How do I submit my custom measurements?', a: 'Select the Custom Size variant on the product details page and complete checkout. A customer care stylist will contact you via WhatsApp within 24 hours to collect detailed measurements.' }
];

const filteredFaqs = computed(() => {
  return faqs.filter(f => f.cat === activeCategory.value);
});

const toggleFaq = (index) => {
  activeFaqIndex.value = activeFaqIndex.value === index ? null : index;
};

// Reset open index when category changes
watch(activeCategory, () => {
  activeFaqIndex.value = null;
});

onMounted(() => {
  document.title = "FAQs - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Find answers to frequently asked questions about orders, payments, shipping, returns, and blouse customization at Maya Sree Fashion.");
  }
});
</script>
