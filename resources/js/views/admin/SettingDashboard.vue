<template>
    <div class="admin-page__header">
        <div class="admin-page__title-section">
            <h1 class="admin-page__title">Store & System Settings</h1>
            <span class="admin-page__subtitle"
                >Configure general business variables, active payment gateway
                toggles, and SMTP email parameters.</span
            >
        </div>
    </div>

    <div
        class="responsive-grid-1-3"
        style="gap: var(--spacing-lg); margin-top: var(--spacing-md)"
    >
        <!-- Left Column: Tab list -->
        <div
            class="glass-panel"
            style="
                padding: var(--spacing-md);
                height: fit-content;
                display: flex;
                flex-direction: column;
                gap: var(--spacing-xs);
            "
        >
            <button
                v-for="tab in ['general', 'shipping', 'payment', 'email', 'announcement', 'welcome_gift', 'reviews']"
                :key="tab"
                type="button"
                @click="activeTab = tab"
                :class="[
                    'btn',
                    activeTab === tab ? 'btn--primary' : 'btn--secondary',
                ]"
                style="
                    text-align: left;
                    text-transform: capitalize;
                    justify-content: flex-start;
                "
            >
                <span v-if="tab === 'general'">🏪 General Store</span>
                <span v-else-if="tab === 'shipping'">🚚 Shipping & Delivery</span>
                <span v-else-if="tab === 'payment'">💳 Payment Gateways</span>
                <span v-else-if="tab === 'email'">✉️ Notifications (SMTP)</span>
                <span v-else-if="tab === 'announcement'"
                    >📢 Announcement Ticker</span
                >
                <span v-else-if="tab === 'welcome_gift'"
                    >🎁 Welcome Gift Modal</span
                >
                <span v-else-if="tab === 'reviews'"
                    >⭐ Product Reviews</span
                >
            </button>
        </div>

        <!-- Right Column: Settings Tab Pane Form -->
        <div class="glass-panel" style="padding: var(--spacing-lg)">
            <div v-if="loading" style="text-align: center; padding: 4rem">
                <div class="stat-card__value" style="font-size: 1.2rem">
                    Loading store settings...
                </div>
            </div>

            <form v-else @submit.prevent="saveSettings">
                <!-- Tab 1: General Details -->
                <div
                    v-if="activeTab === 'general'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs)"
                    >
                        General Details
                    </div>

                    <div class="form-group">
                        <label class="form-label">Store Profile Name *</label>
                        <input
                            type="text"
                            v-model="settings.general.store_name"
                            class="form-input"
                            required
                        />
                    </div>

                    <div
                        class="responsive-grid-1-1"
                        style="gap: var(--spacing-md)"
                    >
                        <div class="form-group">
                            <label class="form-label"
                                >Customer Support Email *</label
                            >
                            <input
                                type="email"
                                v-model="settings.general.contact_email"
                                class="form-input"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label"
                                >Contact Helpline Phone *</label
                            >
                            <input
                                type="text"
                                v-model="settings.general.contact_phone"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"
                            >Store Currency Setting *</label
                        >
                        <select
                            v-model="settings.general.currency"
                            class="form-input"
                            required
                        >
                            <option value="INR">INR (₹) - Indian Rupee</option>
                            <option value="USD">USD ($) - US Dollar</option>
                            <option value="EUR">EUR (€) - Euro</option>
                            <option value="GBP">GBP (£) - British Pound</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"
                            >Primary Warehouse Address</label
                        >
                        <textarea
                            v-model="settings.general.store_address"
                            class="form-input"
                            style="height: 80px; resize: vertical"
                        ></textarea>
                    </div>
                </div>

                <!-- Tab: Shipping & Delivery Rates -->
                <div
                    v-if="activeTab === 'shipping'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;"
                    >
                        <span>🚚 State-Based Shipping & Delivery Rates</span>
                        <span style="font-size: 0.8rem; font-weight: normal; color: var(--color-text-muted);">
                            Configure state delivery charges and threshold for Free Delivery
                        </span>
                    </div>

                    <!-- Highlights row: Free Shipping Threshold & Default Base Rate -->
                    <div class="responsive-grid-1-1" style="gap: var(--spacing-md)">
                        <div style="background: #FAF8F5; border: 1px solid #E8DDD3; border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <label class="form-label" style="font-weight: 700; color: #5B163A; margin: 0; font-size: 0.95rem;">
                                    🎁 Free Delivery Minimum Order Amount (₹) *
                                </label>
                                <span class="badge badge--success" style="font-weight: bold; font-size: 0.75rem;">Active</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: bold; font-size: 1.2rem; color: #5B163A;">₹</span>
                                <input
                                    type="number"
                                    v-model.number="settings.shipping.free_shipping_threshold"
                                    class="form-input"
                                    style="font-size: 1.1rem; font-weight: 700; color: #5B163A;"
                                    min="0"
                                    required
                                />
                            </div>
                            <span style="font-size: 0.78rem; color: #64748b;">
                                Orders equal to or above ₹{{ settings.shipping.free_shipping_threshold }} will automatically get <strong>100% FREE delivery</strong> across all Indian states.
                            </span>
                        </div>

                        <div style="background: #FAF8F5; border: 1px solid #E8DDD3; border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <label class="form-label" style="font-weight: 700; color: #1e293b; margin: 0; font-size: 0.95rem;">
                                    📦 Default Base Shipping Fee (₹) *
                                </label>
                                <span class="badge badge--secondary" style="font-size: 0.75rem;">Fallback Rate</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: bold; font-size: 1.2rem; color: #1e293b;">₹</span>
                                <input
                                    type="number"
                                    v-model.number="settings.shipping.default_shipping_fee"
                                    class="form-input"
                                    style="font-size: 1.1rem; font-weight: 700;"
                                    min="0"
                                    required
                                />
                            </div>
                            <span style="font-size: 0.78rem; color: #64748b;">
                                Applied as the delivery charge for orders under ₹{{ settings.shipping.free_shipping_threshold }} if no custom state rate is specified.
                            </span>
                        </div>
                    </div>

                    <!-- Dispatch Time & Banner Text -->
                    <div class="responsive-grid-1-1" style="gap: var(--spacing-md)">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">⏱️ Estimated Dispatch Time Text</label>
                            <input
                                type="text"
                                v-model="settings.shipping.dispatch_time_text"
                                class="form-input"
                                placeholder="e.g. 3-5 working days"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">📢 Storefront Shipping Promo Text</label>
                            <input
                                type="text"
                                v-model="settings.shipping.shipping_banner_text"
                                class="form-input"
                                placeholder="e.g. Free Shipping on orders above ₹1,999"
                            />
                        </div>
                    </div>

                    <!-- State-wise Shipping Rates Configuration Table -->
                    <div style="margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <h3 style="margin: 0; font-size: 1.05rem; color: #5B163A; font-weight: 700;">
                                    State-Specific Delivery Rates (for orders under ₹{{ settings.shipping.free_shipping_threshold }})
                                </h3>
                                <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #64748b;">
                                    Set customized shipping costs per Indian State / Union Territory.
                                </p>
                            </div>

                            <!-- Quick Preset Actions -->
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button
                                    type="button"
                                    class="btn btn--secondary btn--sm"
                                    @click="applyZonePreset('south', 50)"
                                    title="Set TN, KL, KA, AP, TS, PY to ₹50"
                                >
                                    🌴 Set South India to ₹50
                                </button>
                                <button
                                    type="button"
                                    class="btn btn--secondary btn--sm"
                                    @click="applyZonePreset('rest', 100)"
                                    title="Set other states to ₹100"
                                >
                                    🇮🇳 Set Rest of India to ₹100
                                </button>
                            </div>
                        </div>

                        <!-- Search Filter for State Table -->
                        <div style="margin-bottom: 0.75rem;">
                            <input
                                type="text"
                                v-model="stateSearchQuery"
                                placeholder="🔍 Search state name (e.g. Tamil Nadu, Kerala, Maharashtra)..."
                                class="form-input"
                                style="max-width: 380px; font-size: 0.85rem;"
                            />
                        </div>

                        <div style="border: 1px solid #E8DDD3; border-radius: 10px; overflow: hidden; background: #ffffff;">
                            <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                                <thead>
                                    <tr style="background: #FAF8F5; border-bottom: 2px solid #E8DDD3; color: #64748b; font-size: 0.78rem; text-transform: uppercase;">
                                        <th style="padding: 10px 14px; width: 50px;">#</th>
                                        <th style="padding: 10px 14px;">State / Union Territory</th>
                                        <th style="padding: 10px 14px; width: 140px;">Region</th>
                                        <th style="padding: 10px 14px; width: 180px;">Shipping Charge (₹)</th>
                                        <th style="padding: 10px 14px; width: 120px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="(st, idx) in filteredStatesList" 
                                        :key="st.name"
                                        style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;"
                                        :style="{ background: st.isSouth ? 'rgba(212, 175, 55, 0.04)' : 'transparent' }"
                                    >
                                        <td style="padding: 10px 14px; color: #94a3b8; font-size: 0.8rem;">{{ idx + 1 }}</td>
                                        <td style="padding: 10px 14px; font-weight: 600; color: #1e293b;">
                                            {{ st.name }}
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <span 
                                                class="badge" 
                                                :style="st.isSouth ? 'background: #5B163A; color: #fff;' : 'background: #f1f5f9; color: #475569;'"
                                                style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px;"
                                            >
                                                {{ st.region }}
                                            </span>
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-weight: bold; color: #5B163A;">₹</span>
                                                <input
                                                    type="number"
                                                    v-model.number="settings.shipping.state_rates[st.name]"
                                                    class="form-input"
                                                    style="width: 100px; padding: 4px 8px; height: 34px; font-weight: 600;"
                                                    min="0"
                                                    :placeholder="String(settings.shipping.default_shipping_fee || 100)"
                                                />
                                            </div>
                                        </td>
                                        <td style="padding: 10px 14px; text-align: right;">
                                            <button
                                                type="button"
                                                class="btn btn--secondary btn--sm"
                                                style="font-size: 0.75rem; padding: 3px 8px;"
                                                @click="settings.shipping.state_rates[st.name] = settings.shipping.default_shipping_fee"
                                                title="Reset to default fee"
                                            >
                                                Reset
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Payment Gateways -->
                <div
                    v-if="activeTab === 'payment'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs)"
                    >
                        Active Gateways Toggles
                    </div>

                    <!-- COD Active -->
                    <div
                        style="
                            background: rgba(255, 255, 255, 0.02);
                            border: 1px solid var(--color-border);
                            border-radius: 8px;
                            padding: var(--spacing-md);
                            display: flex;
                            flex-direction: column;
                            gap: 0.25rem;
                        "
                    >
                        <label
                            style="
                                display: flex;
                                align-items: center;
                                gap: 0.5rem;
                                color: #1e293b;
                                font-weight: bold;
                                cursor: pointer;
                            "
                        >
                            <input
                                type="checkbox"
                                v-model="settings.payment.cod_active"
                            />
                            Cash on Delivery (COD)
                        </label>
                        <span
                            style="
                                font-size: 0.75rem;
                                color: var(--color-text-muted);
                                margin-left: 1.5rem;
                            "
                        >
                            Enables buyers to pay at their doorstep in cash upon
                            receiving package shipments.
                        </span>
                    </div>

                    <!-- Cashfree Payments Active -->
                    <div
                        style="
                            background: rgba(255, 255, 255, 0.02);
                            border: 1px solid var(--color-border);
                            border-radius: 8px;
                            padding: var(--spacing-md);
                            display: flex;
                            flex-direction: column;
                            gap: var(--spacing-md);
                        "
                    >
                        <div
                            style="
                                display: flex;
                                flex-direction: column;
                                gap: 0.25rem;
                            "
                        >
                            <label
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    color: #1e293b;
                                    font-weight: bold;
                                    cursor: pointer;
                                "
                            >
                                <input
                                    type="checkbox"
                                    v-model="settings.payment.cashfree_active"
                                />
                                Cashfree Payments Gateway API
                            </label>
                            <span
                                style="
                                    font-size: 0.75rem;
                                    color: var(--color-text-muted);
                                    margin-left: 1.5rem;
                                "
                            >
                                Enables online Credit/Debit Card, NetBanking,
                                and UPI checkout options via Cashfree.
                            </span>
                        </div>

                        <!-- Cashfree Config Inputs -->
                        <div
                            v-if="settings.payment.cashfree_active"
                            class="responsive-grid-1-1"
                            style="gap: var(--spacing-md); margin-left: 1.5rem"
                        >
                            <div class="form-group">
                                <label class="form-label"
                                    >Environment *</label
                                >
                                <select
                                    v-model="settings.payment.cashfree_environment"
                                    class="form-input"
                                >
                                    <option value="sandbox">Sandbox (Testing)</option>
                                    <option value="production">Production (Live)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"
                                    >Cashfree App ID *</label
                                >
                                <input
                                    type="text"
                                    v-model="settings.payment.cashfree_app_id"
                                    class="form-input"
                                    placeholder="e.g. TEST123456..."
                                    required
                                />
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label"
                                    >Cashfree Secret Key *</label
                                >
                                <input
                                    type="password"
                                    v-model="settings.payment.cashfree_secret_key"
                                    class="form-input"
                                    placeholder="Enter Cashfree Secret Key"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Notification (SMTP) -->
                <div
                    v-if="activeTab === 'email'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs)"
                    >
                        Notifications (SMTP Host)
                    </div>

                    <div
                        class="responsive-grid-3-1"
                        style="gap: var(--spacing-md)"
                    >
                        <div class="form-group">
                            <label class="form-label"
                                >SMTP Mailer Hostserver *</label
                            >
                            <input
                                type="text"
                                v-model="settings.email.smtp_host"
                                placeholder="smtp.mailgun.org"
                                class="form-input"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Port *</label>
                            <input
                                type="number"
                                v-model="settings.email.smtp_port"
                                placeholder="587"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>

                    <div
                        class="responsive-grid-1-1"
                        style="gap: var(--spacing-md)"
                    >
                        <div class="form-group">
                            <label class="form-label">SMTP Username *</label>
                            <input
                                type="text"
                                v-model="settings.email.smtp_username"
                                class="form-input"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Password *</label>
                            <input
                                type="password"
                                v-model="settings.email.smtp_password"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>

                    <div
                        class="responsive-grid-1-1"
                        style="gap: var(--spacing-md)"
                    >
                        <div class="form-group">
                            <label class="form-label"
                                >Sender Name (display) *</label
                            >
                            <input
                                type="text"
                                v-model="settings.email.sender_name"
                                class="form-input"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label"
                                >Sender Email Address *</label
                            >
                            <input
                                type="email"
                                v-model="settings.email.sender_email"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Announcement Ticker Master -->
                <div
                    v-if="activeTab === 'announcement'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs)"
                    >
                        Storefront Announcement Ticker
                    </div>

                    <!-- Live Preview Banner Block -->
                    <div style="margin-bottom: var(--spacing-md)">
                        <label
                            class="form-label"
                            style="
                                font-weight: bold;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            "
                        >
                            ✨ Live Interactive Preview (Unsaved Changes)
                        </label>
                        <div
                            style="
                                border: 1px solid var(--color-border);
                                border-radius: 8px;
                                overflow: hidden;
                                background: #f8fafc;
                                padding: 10px;
                            "
                        >
                            <StorefrontAnnouncementBar
                                :preview-items="announcementsList"
                                :preview-config="settings.announcement.config"
                            />
                        </div>
                    </div>

                    <!-- Enable & Sticky Toggle -->
                    <div
                        class="responsive-grid-1-1"
                        style="
                            gap: var(--spacing-md);
                            grid-template-columns: 1fr 1fr;
                        "
                    >
                        <div
                            style="
                                background: rgba(255, 255, 255, 0.02);
                                border: 1px solid var(--color-border);
                                border-radius: 8px;
                                padding: var(--spacing-md);
                                display: flex;
                                flex-direction: column;
                                gap: 0.25rem;
                            "
                        >
                            <label
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    color: #1e293b;
                                    font-weight: bold;
                                    cursor: pointer;
                                    user-select: none;
                                "
                            >
                                <input
                                    type="checkbox"
                                    v-model="
                                        settings.announcement.config.is_enabled
                                    "
                                />
                                Enable Announcement Bar
                            </label>
                            <span
                                style="
                                    font-size: 0.75rem;
                                    color: var(--color-text-muted);
                                "
                            >
                                Toggles the visibility of the announcement bar
                                on the storefront header.
                            </span>
                        </div>

                        <div
                            style="
                                background: rgba(255, 255, 255, 0.02);
                                border: 1px solid var(--color-border);
                                border-radius: 8px;
                                padding: var(--spacing-md);
                                display: flex;
                                flex-direction: column;
                                gap: 0.25rem;
                            "
                        >
                            <label
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    color: #1e293b;
                                    font-weight: bold;
                                    cursor: pointer;
                                    user-select: none;
                                "
                            >
                                <input
                                    type="checkbox"
                                    v-model="
                                        settings.announcement.config.is_sticky
                                    "
                                />
                                Sticky Position
                            </label>
                            <span
                                style="
                                    font-size: 0.75rem;
                                    color: var(--color-text-muted);
                                "
                            >
                                Keeps the announcement bar fixed at the top of
                                the viewport when scrolling.
                            </span>
                        </div>
                    </div>

                    <!-- Style Configuration (Mode, Speed, Colors) -->
                    <div
                        class="responsive-grid-1-1"
                        style="
                            gap: var(--spacing-md);
                            grid-template-columns: 1fr 1fr;
                        "
                    >
                        <!-- Animation Mode & Speed -->
                        <div
                            style="
                                display: flex;
                                flex-direction: column;
                                gap: var(--spacing-md);
                            "
                        >
                            <div class="form-group">
                                <label class="form-label"
                                    >Ticker Scrolling Mode</label
                                >
                                <select
                                    v-model="settings.announcement.config.mode"
                                    class="form-input"
                                >
                                    <option value="marquee">
                                        Seamless Continuous Ticker (Marquee)
                                    </option>
                                    <option value="slide">
                                        One-by-One Slide Carousel
                                    </option>
                                    <option value="fade">
                                        One-by-One Fade Carousel
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Animation Speed / Slide Interval:
                                    <strong
                                        >{{
                                            settings.announcement.config.speed
                                        }}s</strong
                                    >
                                </label>
                                <input
                                    type="range"
                                    v-model.number="
                                        settings.announcement.config.speed
                                    "
                                    min="2"
                                    max="30"
                                    step="1"
                                    class="form-input"
                                    style="
                                        cursor: pointer;
                                        height: 10px;
                                        padding: 0;
                                    "
                                />
                                <span
                                    style="
                                        font-size: 0.75rem;
                                        color: var(--color-text-muted);
                                    "
                                >
                                    Sets transition timer for slider/fade mode,
                                    or duration factor for marquee.
                                </span>
                            </div>
                        </div>

                        <!-- Custom Colors -->
                        <div
                            style="
                                display: flex;
                                flex-direction: column;
                                gap: var(--spacing-md);
                            "
                        >
                            <div class="form-group">
                                <label class="form-label"
                                    >Background Color</label
                                >
                                <div
                                    style="
                                        display: flex;
                                        gap: 0.5rem;
                                        align-items: center;
                                    "
                                >
                                    <input
                                        type="color"
                                        v-model="
                                            settings.announcement.config
                                                .background_color
                                        "
                                        style="
                                            width: 44px;
                                            height: 44px;
                                            border: 1px solid
                                                var(--color-border);
                                            border-radius: 4px;
                                            padding: 2px;
                                            cursor: pointer;
                                        "
                                    />
                                    <input
                                        type="text"
                                        v-model="
                                            settings.announcement.config
                                                .background_color
                                        "
                                        class="form-input"
                                        style="flex: 1"
                                        placeholder="#6E1F3A"
                                    />
                                </div>
                                <!-- Color Presets -->
                                <div
                                    style="
                                        display: flex;
                                        gap: var(--spacing-xs);
                                        margin-top: 6px;
                                    "
                                >
                                    <button
                                        v-for="color in [
                                            '#6E1F3A',
                                            '#493b54',
                                            '#D4AF37',
                                            '#1C1C1C',
                                            '#E53E3E',
                                        ]"
                                        :key="color"
                                        type="button"
                                        @click="
                                            settings.announcement.config.background_color =
                                                color
                                        "
                                        style="
                                            width: 20px;
                                            height: 20px;
                                            border-radius: 50%;
                                            border: 1px solid #cbd5e1;
                                            cursor: pointer;
                                        "
                                        :style="{ backgroundColor: color }"
                                        :title="color"
                                    ></button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label"
                                    >Text & Icon Color</label
                                >
                                <div
                                    style="
                                        display: flex;
                                        gap: 0.5rem;
                                        align-items: center;
                                    "
                                >
                                    <input
                                        type="color"
                                        v-model="
                                            settings.announcement.config
                                                .text_color
                                        "
                                        style="
                                            width: 44px;
                                            height: 44px;
                                            border: 1px solid
                                                var(--color-border);
                                            border-radius: 4px;
                                            padding: 2px;
                                            cursor: pointer;
                                        "
                                    />
                                    <input
                                        type="text"
                                        v-model="
                                            settings.announcement.config
                                                .text_color
                                        "
                                        class="form-input"
                                        style="flex: 1"
                                        placeholder="#FFFFFF"
                                    />
                                </div>
                                <!-- Color Presets -->
                                <div
                                    style="
                                        display: flex;
                                        gap: var(--spacing-xs);
                                        margin-top: 6px;
                                    "
                                >
                                    <button
                                        v-for="color in [
                                            '#FFFFFF',
                                            '#FFFDF9',
                                            '#D4AF37',
                                            '#1C1C1C',
                                            '#493b54',
                                        ]"
                                        :key="color"
                                        type="button"
                                        @click="
                                            settings.announcement.config.text_color =
                                                color
                                        "
                                        style="
                                            width: 20px;
                                            height: 20px;
                                            border-radius: 50%;
                                            border: 1px solid #cbd5e1;
                                            cursor: pointer;
                                        "
                                        :style="{ backgroundColor: color }"
                                        :title="color"
                                    ></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Announcements Table CRUD & Active/Inactive Filter -->
                    <div
                        style="
                            border-top: 1px solid var(--color-border);
                            padding-top: var(--spacing-md);
                            display: flex;
                            flex-direction: column;
                            gap: var(--spacing-md);
                        "
                    >
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                flex-wrap: wrap;
                                gap: 1rem;
                            "
                        >
                            <div>
                                <span
                                    class="card-header-title"
                                    style="font-size: 1.1rem; margin: 0; display: block;"
                                    >Announcement Database Table</span
                                >
                                <span style="font-size: 0.8rem; color: var(--color-text-muted);">
                                    Manage live announcement carousel slides with instant active/inactive views
                                </span>
                            </div>

                            <button
                                type="button"
                                @click="openAddModal"
                                class="btn btn--primary"
                                style="padding: 0.5rem 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;"
                            >
                                <span>➕</span> Add New Announcement
                            </button>
                        </div>

                        <!-- Active / Inactive View Filter Tabs -->
                        <div style="display: flex; gap: 8px; border-bottom: 2px solid var(--color-border); padding-bottom: 8px;">
                            <button
                                type="button"
                                @click="setAnnouncementFilter('all')"
                                :style="{
                                    padding: '6px 14px',
                                    borderRadius: '20px',
                                    border: 'none',
                                    fontSize: '0.85rem',
                                    fontWeight: '600',
                                    cursor: 'pointer',
                                    backgroundColor: announcementFilter === 'all' ? '#6E1F3A' : '#f1f5f9',
                                    color: announcementFilter === 'all' ? '#ffffff' : '#64748b',
                                    transition: 'all 0.2s ease'
                                }"
                            >
                                All ({{ announcementMeta.total }})
                            </button>

                            <button
                                type="button"
                                @click="setAnnouncementFilter('active')"
                                :style="{
                                    padding: '6px 14px',
                                    borderRadius: '20px',
                                    border: 'none',
                                    fontSize: '0.85rem',
                                    fontWeight: '600',
                                    cursor: 'pointer',
                                    backgroundColor: announcementFilter === 'active' ? '#10b981' : '#f1f5f9',
                                    color: announcementFilter === 'active' ? '#ffffff' : '#64748b',
                                    transition: 'all 0.2s ease'
                                }"
                            >
                                Active View ({{ announcementMeta.active_count }})
                            </button>

                            <button
                                type="button"
                                @click="setAnnouncementFilter('inactive')"
                                :style="{
                                    padding: '6px 14px',
                                    borderRadius: '20px',
                                    border: 'none',
                                    fontSize: '0.85rem',
                                    fontWeight: '600',
                                    cursor: 'pointer',
                                    backgroundColor: announcementFilter === 'inactive' ? '#ef4444' : '#f1f5f9',
                                    color: announcementFilter === 'inactive' ? '#ffffff' : '#64748b',
                                    transition: 'all 0.2s ease'
                                }"
                            >
                                Inactive View ({{ announcementMeta.inactive_count }})
                            </button>
                        </div>

                        <!-- List of Announcement Items -->
                        <div
                            style="
                                display: flex;
                                flex-direction: column;
                                gap: var(--spacing-sm);
                            "
                        >
                            <div
                                v-for="(item, idx) in announcementsList"
                                :key="item.id"
                                style="
                                    border: 1px solid var(--color-border);
                                    border-radius: 8px;
                                    padding: 12px 16px;
                                    background: #ffffff;
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                                    display: flex;
                                    flex-direction: column;
                                    gap: 8px;
                                    transition: border-color 0.2s ease;
                                "
                                :style="{ borderColor: item.is_active ? 'var(--color-border)' : '#fca5a5' }"
                            >
                                <div
                                    style="
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: center;
                                        gap: var(--spacing-md);
                                        flex-wrap: wrap;
                                    "
                                >
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <!-- Active/Inactive Status Badge -->
                                        <span
                                            :style="{
                                                padding: '3px 10px',
                                                borderRadius: '12px',
                                                fontSize: '0.75rem',
                                                fontWeight: 'bold',
                                                backgroundColor: item.is_active ? '#ecfdf5' : '#fef2f2',
                                                color: item.is_active ? '#047857' : '#b91c1c',
                                                border: item.is_active ? '1px solid #a7f3d0' : '1px solid #fecaca'
                                            }"
                                        >
                                            {{ item.is_active ? '🟢 Active' : '🔴 Inactive' }}
                                        </span>

                                        <span
                                            style="
                                                font-weight: 600;
                                                color: #1e293b;
                                                font-size: 0.95rem;
                                            "
                                        >
                                            #{{ item.sort_order || (idx + 1) }} — {{ item.text }}
                                        </span>
                                    </div>

                                    <div
                                        style="
                                            display: flex;
                                            align-items: center;
                                            gap: 0.5rem;
                                        "
                                    >
                                        <!-- Quick Active Toggle Button -->
                                        <button
                                            type="button"
                                            @click="toggleAnnouncementActive(item)"
                                            :style="{
                                                padding: '4px 10px',
                                                border: '1px solid',
                                                borderColor: item.is_active ? '#fca5a5' : '#6ee7b7',
                                                background: item.is_active ? '#fff1f2' : '#ecfdf5',
                                                color: item.is_active ? '#e11d48' : '#047857',
                                                borderRadius: '6px',
                                                fontSize: '0.75rem',
                                                fontWeight: '600',
                                                cursor: 'pointer'
                                            }"
                                        >
                                            {{ item.is_active ? 'Mark Inactive' : 'Mark Active' }}
                                        </button>

                                        <!-- Edit Button -->
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            style="
                                                padding: 4px 10px;
                                                border: 1px solid #cbd5e1;
                                                background: #f8fafc;
                                                color: #334155;
                                                border-radius: 6px;
                                                font-size: 0.75rem;
                                                font-weight: 600;
                                                cursor: pointer;
                                            "
                                        >
                                            ✏️ Edit
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            type="button"
                                            @click="deleteAnnouncement(item)"
                                            style="
                                                padding: 4px 10px;
                                                border: 1px solid #fca5a5;
                                                background: #fee2e2;
                                                color: #b91c1c;
                                                border-radius: 6px;
                                                font-size: 0.75rem;
                                                font-weight: 600;
                                                cursor: pointer;
                                            "
                                        >
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </div>

                                <div v-if="item.link" style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 4px;">
                                    <span>🔗 Redirect Link:</span>
                                    <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">{{ item.link }}</code>
                                </div>
                            </div>

                            <div
                                v-if="announcementsList.length === 0"
                                style="
                                    text-align: center;
                                    color: var(--color-text-muted);
                                    padding: var(--spacing-lg);
                                    border: 1px dashed var(--color-border);
                                    border-radius: 8px;
                                    background: #fafafa;
                                "
                            >
                                No {{ announcementFilter !== 'all' ? announcementFilter : '' }} announcement messages found in database table. Click "Add New Announcement" to create one.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Welcome Gift Modal Settings -->
                <div
                    v-if="activeTab === 'welcome_gift'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs)"
                    >
                        🎁 Welcome Gift Modal & Offer Configuration
                    </div>

                    <!-- Enable/Disable Toggle Box -->
                    <div
                        style="
                            background: rgba(255, 255, 255, 0.02);
                            border: 1px solid var(--color-border);
                            border-radius: 8px;
                            padding: var(--spacing-md);
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: var(--spacing-md);
                            flex-wrap: wrap;
                        "
                    >
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <label
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    color: #1e293b;
                                    font-weight: bold;
                                    cursor: pointer;
                                    user-select: none;
                                    font-size: 1.05rem;
                                "
                            >
                                <input
                                    type="checkbox"
                                    v-model="settings.welcome_gift.is_enabled"
                                    style="width: 18px; height: 18px; cursor: pointer;"
                                />
                                Enable Welcome Gift Popup
                            </label>
                            <span style="font-size: 0.8rem; color: var(--color-text-muted); margin-left: 1.6rem;">
                                Toggles the interactive 3D gift box modal on the storefront homepage when visitors scroll.
                            </span>
                        </div>

                        <span
                            :style="{
                                padding: '6px 14px',
                                borderRadius: '20px',
                                fontSize: '0.82rem',
                                fontWeight: 'bold',
                                backgroundColor: settings.welcome_gift.is_enabled ? '#ecfdf5' : '#fef2f2',
                                color: settings.welcome_gift.is_enabled ? '#047857' : '#b91c1c',
                                border: settings.welcome_gift.is_enabled ? '1px solid #a7f3d0' : '1px solid #fecaca'
                            }"
                        >
                            {{ settings.welcome_gift.is_enabled ? '🟢 Popup Active' : '🔴 Popup Disabled' }}
                        </span>
                    </div>

                    <!-- Offer Code Choice Selection -->
                    <div class="responsive-grid-1-1" style="gap: var(--spacing-md)">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: bold;">
                                Select Offer / Coupon Code *
                            </label>
                            <select
                                v-model="settings.welcome_gift.coupon_code"
                                class="form-input"
                                @change="onCouponSelect"
                            >
                                <option v-for="c in availableCoupons" :key="c.id" :value="c.code">
                                    {{ c.code }} — {{ c.name }} ({{ c.type === 'percentage' ? c.value + '% OFF' : (c.type === 'flat' ? '₹' + c.value + ' OFF' : 'Free Shipping') }})
                                </option>
                                <option value="WELCOME10">WELCOME10 — Welcome 10% OFF</option>
                                <option value="FIRST20">FIRST20 — 20% OFF First Order</option>
                            </select>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                                Choose which discount code is revealed when customers open their welcome gift.
                            </span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: bold;">
                                Active Offer Code (Value) *
                            </label>
                            <input
                                type="text"
                                v-model="settings.welcome_gift.coupon_code"
                                class="form-input"
                                style="text-transform: uppercase; font-weight: bold; letter-spacing: 1px;"
                                placeholder="e.g. WELCOME10"
                                required
                            />
                        </div>
                    </div>

                    <!-- Headline & Subtitle Text Config -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: bold;">
                            Discount Headline Text *
                        </label>
                        <input
                            type="text"
                            v-model="settings.welcome_gift.discount_text"
                            class="form-input"
                            placeholder="e.g. Enjoy 10% OFF Your First Order"
                            required
                        />
                    </div>

                    <div class="responsive-grid-1-1" style="gap: var(--spacing-md)">
                        <div class="form-group">
                            <label class="form-label">Modal Main Title</label>
                            <input
                                type="text"
                                v-model="settings.welcome_gift.title"
                                class="form-input"
                                placeholder="A Special Gift Awaits You"
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Modal Subtitle</label>
                            <input
                                type="text"
                                v-model="settings.welcome_gift.subtitle"
                                class="form-input"
                                placeholder="Every new member deserves a warm welcome."
                            />
                        </div>
                    </div>

                    <!-- Live Interactive Card Preview -->
                    <div style="margin-top: var(--spacing-sm);">
                        <label class="form-label" style="font-weight: bold;">
                            ✨ Live Storefront Preview (Unsaved Changes)
                        </label>
                        <div
                            style="
                                border: 1px solid var(--color-border);
                                border-radius: 16px;
                                padding: 24px;
                                background: #FCFAF7;
                                max-width: 420px;
                                margin: 0 auto;
                                text-align: center;
                                box-shadow: 0 8px 24px rgba(0,0,0,0.06);
                                position: relative;
                            "
                        >
                            <div style="font-size: 0.7rem; font-weight: 700; color: #C8A15A; letter-spacing: 1.5px; text-transform: uppercase;">
                                EXCLUSIVE MEMBER PERK
                            </div>
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; color: #2D2D2D; margin: 4px 0 6px 0;">
                                {{ settings.welcome_gift.title || 'A Special Gift Awaits You' }}
                            </h3>
                            <p style="font-size: 0.8rem; color: #6B6B6B; margin: 0 0 16px 0;">
                                {{ settings.welcome_gift.subtitle || 'Every new member deserves a warm welcome.' }}
                            </p>

                            <!-- Coupon Reveal Mock Box -->
                            <div style="background: #ffffff; border: 1.5px dashed #C8A15A; border-radius: 12px; padding: 14px;">
                                <div style="font-size: 0.65rem; font-weight: bold; color: #C8A15A; letter-spacing: 1px;">WELCOME GIFT</div>
                                <h4 style="font-size: 1.05rem; margin: 4px 0; color: #2D2D2D;">🎉 {{ settings.welcome_gift.discount_text || 'Enjoy 10% OFF Your First Order' }}</h4>
                                <div style="background: #111111; color: #C8A15A; display: inline-block; padding: 6px 14px; border-radius: 8px; font-weight: bold; font-size: 0.9rem; margin-top: 6px; letter-spacing: 1px;">
                                    CODE: {{ settings.welcome_gift.coupon_code || 'WELCOME10' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add / Edit Modal -->
                <div
                    v-if="modal.isOpen"
                    style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(15, 23, 42, 0.6);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                        padding: 16px;
                    "
                >
                    <div
                        style="
                            background: #ffffff;
                            border-radius: 12px;
                            width: 100%;
                            max-width: 500px;
                            padding: 24px;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                            display: flex;
                            flex-direction: column;
                            gap: 16px;
                        "
                    >
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                            <h3 style="margin: 0; font-family: 'Playfair Display', serif; color: #6E1F3A; font-size: 1.25rem;">
                                {{ modal.isEditing ? '✏️ Edit Announcement' : '➕ New Announcement' }}
                            </h3>
                            <button
                                type="button"
                                @click="modal.isOpen = false"
                                style="border: none; background: transparent; font-size: 1.2rem; cursor: pointer; color: #64748b;"
                            >
                                ✕
                            </button>
                        </div>

                        <form @submit.prevent="submitModal" style="display: flex; flex-direction: column; gap: 14px;">
                            <div class="form-group" style="margin: 0">
                                <label class="form-label" style="font-weight: bold;">Announcement Text *</label>
                                <textarea
                                    v-model="modal.form.text"
                                    class="form-input"
                                    rows="3"
                                    placeholder="e.g. 🚚 Free Shipping Above ₹1999 across South India"
                                    required
                                    style="width: 100%; font-family: 'Poppins', sans-serif;"
                                ></textarea>
                            </div>

                            <div class="form-group" style="margin: 0">
                                <label class="form-label" style="font-weight: bold;">Redirect Link (Optional)</label>
                                <input
                                    type="text"
                                    v-model="modal.form.link"
                                    class="form-input"
                                    placeholder="e.g. /shop or /categories/sarees"
                                />
                            </div>

                            <div style="display: flex; gap: 16px; align-items: center;">
                                <div class="form-group" style="margin: 0; flex: 1;">
                                    <label class="form-label" style="font-weight: bold;">Sort Order</label>
                                    <input
                                        type="number"
                                        v-model.number="modal.form.sort_order"
                                        class="form-input"
                                        placeholder="0"
                                    />
                                </div>

                                <div class="form-group" style="margin: 0; flex: 1; display: flex; flex-direction: column; justify-content: flex-end;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 0;">
                                        <input type="checkbox" v-model="modal.form.is_active" />
                                        <span style="font-weight: bold; color: #1e293b;">Active Status</span>
                                    </label>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                                <button
                                    type="button"
                                    @click="modal.isOpen = false"
                                    class="btn btn--secondary"
                                    style="padding: 0.5rem 1rem;"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="btn btn--primary"
                                    :disabled="modal.submitting"
                                    style="padding: 0.5rem 1.25rem; background: #6E1F3A; color: #ffffff;"
                                >
                                    {{ modal.submitting ? 'Saving...' : (modal.isEditing ? 'Update Announcement' : 'Create Announcement') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tab 6: Product Reviews Settings -->
                <div
                    v-if="activeTab === 'reviews'"
                    style="
                        display: flex;
                        flex-direction: column;
                        gap: var(--spacing-md);
                    "
                >
                    <div
                        class="card-header-title"
                        style="margin-bottom: var(--spacing-xs)"
                    >
                        Product Review & Rating Controls
                    </div>

                    <!-- Eligibility Section -->
                    <div style="font-weight: bold; color: #6E1F3A; font-size: 1.05rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-top: 0.5rem;">
                        Eligibility & Purchase Enforcement
                    </div>

                    <div class="responsive-grid-1-1" style="gap: var(--spacing-md)">
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                                <input type="checkbox" v-model="settings.reviews.login_required" style="width: 18px; height: 18px;" />
                                Require Customer Login
                            </label>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">Guest users cannot submit reviews.</span>
                        </div>

                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                                <input type="checkbox" v-model="settings.reviews.verified_purchase_required" style="width: 18px; height: 18px;" />
                                Require Verified Purchase
                            </label>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">Customer must have purchased the specific product in an order.</span>
                        </div>

                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                                <input type="checkbox" v-model="settings.reviews.delivered_order_required" style="width: 18px; height: 18px;" />
                                Require Delivered Order
                            </label>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">Order must be marked as delivered before review submission.</span>
                        </div>

                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                                <input type="checkbox" v-model="settings.reviews.one_review_per_product" style="width: 18px; height: 18px;" />
                                One Review Per Customer
                            </label>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">Restrict each customer to one review per product.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: bold;">Review Window (Days)</label>
                        <select v-model.number="settings.reviews.review_window_days" class="form-input">
                            <option :value="0">No Restriction (Unlimited)</option>
                            <option :value="7">7 Days after delivery</option>
                            <option :value="15">15 Days after delivery</option>
                            <option :value="30">30 Days after delivery</option>
                            <option :value="60">60 Days after delivery</option>
                        </select>
                    </div>

                    <!-- Moderation & Photo Limits -->
                    <div style="font-weight: bold; color: #6E1F3A; font-size: 1.05rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-top: 1rem;">
                        Moderation & Photos
                    </div>

                    <div class="responsive-grid-1-1" style="gap: var(--spacing-md)">
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                                <input type="checkbox" v-model="settings.reviews.admin_approval_required" style="width: 18px; height: 18px;" />
                                Require Admin Approval (Moderation)
                            </label>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">New reviews enter pending queue until approved by admin.</span>
                        </div>

                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                                <input type="checkbox" v-model="settings.reviews.customer_images_allowed" style="width: 18px; height: 18px;" />
                                Allow Customer Photos
                            </label>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">Customers can attach compressed photos to reviews.</span>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div
                    style="
                        margin-top: var(--spacing-lg);
                        padding-top: var(--spacing-md);
                        border-top: 1px solid var(--color-border);
                        display: flex;
                        justify-content: flex-end;
                        gap: var(--spacing-md);
                        align-items: center;
                    "
                >
                    <span
                        v-if="successMsg"
                        style="
                            color: var(--color-success);
                            font-size: 0.9rem;
                            font-weight: bold;
                        "
                    >
                        {{ successMsg }}
                    </span>
                    <button
                        type="submit"
                        class="btn btn--primary"
                        :disabled="saving"
                    >
                        {{
                            saving
                                ? "Saving configurations..."
                                : "💾 Save Settings"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import StorefrontAnnouncementBar from "../../components/StorefrontAnnouncementBar.vue";

const activeTab = ref("general");
const loading = ref(true);
const saving = ref(false);
const successMsg = ref("");

// State Search & Indian States Regions
const stateSearchQuery = ref("");

const INDIAN_STATES_REGIONS = [
    { name: "Tamil Nadu", region: "South India", isSouth: true },
    { name: "Puducherry", region: "South India", isSouth: true },
    { name: "Kerala", region: "South India", isSouth: true },
    { name: "Karnataka", region: "South India", isSouth: true },
    { name: "Andhra Pradesh", region: "South India", isSouth: true },
    { name: "Telangana", region: "South India", isSouth: true },
    { name: "Maharashtra", region: "Western", isSouth: false },
    { name: "Goa", region: "Western", isSouth: false },
    { name: "Gujarat", region: "Western", isSouth: false },
    { name: "Madhya Pradesh", region: "Central", isSouth: false },
    { name: "Chhattisgarh", region: "Central", isSouth: false },
    { name: "Delhi (NCT)", region: "Northern", isSouth: false },
    { name: "Haryana", region: "Northern", isSouth: false },
    { name: "Punjab", region: "Northern", isSouth: false },
    { name: "Rajasthan", region: "Northern", isSouth: false },
    { name: "Uttar Pradesh", region: "Northern", isSouth: false },
    { name: "Uttarakhand", region: "Northern", isSouth: false },
    { name: "Himachal Pradesh", region: "Northern", isSouth: false },
    { name: "Jammu and Kashmir", region: "Northern", isSouth: false },
    { name: "Ladakh", region: "Northern", isSouth: false },
    { name: "Chandigarh", region: "Northern", isSouth: false },
    { name: "West Bengal", region: "Eastern", isSouth: false },
    { name: "Odisha", region: "Eastern", isSouth: false },
    { name: "Bihar", region: "Eastern", isSouth: false },
    { name: "Jharkhand", region: "Eastern", isSouth: false },
    { name: "Assam", region: "North-East", isSouth: false },
    { name: "Sikkim", region: "North-East", isSouth: false },
    { name: "Meghalaya", region: "North-East", isSouth: false },
    { name: "Tripura", region: "North-East", isSouth: false },
    { name: "Manipur", region: "North-East", isSouth: false },
    { name: "Nagaland", region: "North-East", isSouth: false },
    { name: "Mizoram", region: "North-East", isSouth: false },
    { name: "Arunachal Pradesh", region: "North-East", isSouth: false },
    { name: "Andaman and Nicobar Islands", region: "Union Territory", isSouth: false },
    { name: "Dadra and Nagar Haveli and Daman and Diu", region: "Union Territory", isSouth: false },
    { name: "Lakshadweep", region: "Union Territory", isSouth: false },
];

const filteredStatesList = computed(() => {
    if (!stateSearchQuery.value.trim()) return INDIAN_STATES_REGIONS;
    const q = stateSearchQuery.value.toLowerCase().trim();
    return INDIAN_STATES_REGIONS.filter(
        (st) =>
            st.name.toLowerCase().includes(q) ||
            st.region.toLowerCase().includes(q)
    );
});

const applyZonePreset = (zone, rate) => {
    INDIAN_STATES_REGIONS.forEach((st) => {
        if (zone === "south" && st.isSouth) {
            settings.value.shipping.state_rates[st.name] = Number(rate);
        } else if (zone === "rest" && !st.isSouth) {
            settings.value.shipping.state_rates[st.name] = Number(rate);
        } else if (zone === "all") {
            settings.value.shipping.state_rates[st.name] = Number(rate);
        }
    });
};

// Database Announcements State
const announcementFilter = ref("all");
const announcementsList = ref([]);
const announcementMeta = ref({ total: 0, active_count: 0, inactive_count: 0 });

const modal = ref({
    isOpen: false,
    isEditing: false,
    submitting: false,
    form: {
        id: null,
        text: "",
        link: "",
        is_active: true,
        sort_order: 0,
    },
});

const settings = ref({
    general: {
        store_name: "",
        contact_email: "",
        contact_phone: "",
        currency: "INR",
        store_address: "",
    },
    shipping: {
        free_shipping_threshold: 1999,
        default_shipping_fee: 100,
        shipping_banner_text: "Free Shipping on orders above ₹1,999",
        dispatch_time_text: "3-5 working days",
        state_rates: {},
    },
    payment: {
        cod_active: true,
        cashfree_active: false,
        cashfree_app_id: "",
        cashfree_secret_key: "",
        cashfree_environment: "sandbox",
    },
    email: {
        smtp_host: "",
        smtp_port: 587,
        smtp_username: "",
        smtp_password: "",
        sender_name: "",
        sender_email: "",
    },
    announcement: {
        config: {
            is_enabled: true,
            mode: "slide",
            speed: 10,
            background_color: "#6E1F3A",
            text_color: "#FFFFFF",
            is_sticky: true,
        },
    },
    welcome_gift: {
        is_enabled: true,
        coupon_code: "WELCOME10",
        discount_text: "Enjoy 10% OFF Your First Order",
        title: "A Special Gift Awaits You",
        subtitle: "Every new member deserves a warm welcome.",
    },
    reviews: {
        login_required: true,
        verified_purchase_required: true,
        delivered_order_required: true,
        one_review_per_product: true,
        review_window_days: 0,
        admin_approval_required: true,
        customer_editing_allowed: true,
        customer_deletion_allowed: true,
        customer_images_allowed: true,
        max_images_per_review: 4,
        max_image_size_kb: 200,
    },
});

const availableCoupons = ref([]);
const fetchCoupons = async () => {
    try {
        const response = await axios.get("/api/admin/coupons");
        if (response.data && response.data.data) {
            availableCoupons.value = response.data.data;
        }
    } catch (err) {
        console.error("Failed to load coupons list:", err);
    }
};

const onCouponSelect = () => {
    const selected = availableCoupons.value.find(
        (c) => c.code === settings.value.welcome_gift.coupon_code
    );
    if (selected) {
        if (selected.type === "percentage") {
            settings.value.welcome_gift.discount_text = `Enjoy ${selected.value}% OFF Your First Order`;
        } else if (selected.type === "flat") {
            settings.value.welcome_gift.discount_text = `Enjoy Flat ₹${selected.value} OFF Your First Order`;
        } else if (selected.type === "free_shipping") {
            settings.value.welcome_gift.discount_text = "Enjoy Free Shipping On Your First Order";
        }
    }
};

const fetchAnnouncementsList = async () => {
    try {
        const response = await axios.get("/api/admin/announcements", {
            params: { status: announcementFilter.value }
        });
        if (response.data && response.data.success) {
            announcementsList.value = response.data.data || [];
            if (response.data.meta) {
                announcementMeta.value = response.data.meta;
            }
        }
    } catch (err) {
        console.error("Failed to load announcements database table:", err);
    }
};

const setAnnouncementFilter = (filter) => {
    announcementFilter.value = filter;
    fetchAnnouncementsList();
};

const toggleAnnouncementActive = async (item) => {
    try {
        const response = await axios.patch(`/api/admin/announcements/${item.id}/toggle`);
        if (response.data && response.data.success) {
            await fetchAnnouncementsList();
        }
    } catch (err) {
        console.error("Failed to toggle announcement status:", err);
        alert("Failed to update status");
    }
};

const openAddModal = () => {
    modal.value = {
        isOpen: true,
        isEditing: false,
        submitting: false,
        form: {
            id: null,
            text: "",
            link: "",
            is_active: true,
            sort_order: announcementsList.value.length + 1,
        },
    };
};

const openEditModal = (item) => {
    modal.value = {
        isOpen: true,
        isEditing: true,
        submitting: false,
        form: {
            id: item.id,
            text: item.text,
            link: item.link || "",
            is_active: item.is_active,
            sort_order: item.sort_order || 0,
        },
    };
};

const submitModal = async () => {
    if (!modal.value.form.text.trim()) return;
    modal.value.submitting = true;
    try {
        if (modal.value.isEditing) {
            await axios.put(`/api/admin/announcements/${modal.value.form.id}`, modal.value.form);
        } else {
            await axios.post("/api/admin/announcements", modal.value.form);
        }
        modal.value.isOpen = false;
        await fetchAnnouncementsList();
    } catch (err) {
        console.error("Failed to save announcement:", err);
        alert("Failed to save announcement");
    } finally {
        modal.value.submitting = false;
    }
};

const deleteAnnouncement = async (item) => {
    if (!confirm(`Are you sure you want to delete this announcement:\n"${item.text}"?`)) {
        return;
    }
    try {
        await axios.delete(`/api/admin/announcements/${item.id}`);
        await fetchAnnouncementsList();
    } catch (err) {
        console.error("Failed to delete announcement:", err);
        alert("Failed to delete announcement");
    }
};

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await axios.get("/api/admin/settings");
        if (response.data && response.data.success) {
            const data = response.data.data;

            if (data.general)
                settings.value.general = {
                    ...settings.value.general,
                    ...data.general,
                };
            if (data.shipping) {
                settings.value.shipping = {
                    free_shipping_threshold:
                        data.shipping.free_shipping_threshold !== undefined
                            ? Number(data.shipping.free_shipping_threshold)
                            : 1999,
                    default_shipping_fee:
                        data.shipping.default_shipping_fee !== undefined
                            ? Number(data.shipping.default_shipping_fee)
                            : 100,
                    shipping_banner_text:
                        data.shipping.shipping_banner_text ||
                        "Free Shipping on orders above ₹1,999",
                    dispatch_time_text:
                        data.shipping.dispatch_time_text ||
                        "3-5 working days",
                    state_rates:
                        typeof data.shipping.state_rates === "object" &&
                        data.shipping.state_rates !== null
                            ? { ...data.shipping.state_rates }
                            : {},
                };
            }
            if (data.payment) {
                settings.value.payment = {
                    ...settings.value.payment,
                    ...data.payment,
                    cod_active: filterBool(data.payment.cod_active),
                    cashfree_active: filterBool(data.payment.cashfree_active),
                    cashfree_environment: data.payment.cashfree_environment || "sandbox",
                };
            }
            if (data.email)
                settings.value.email = {
                    ...settings.value.email,
                    ...data.email,
                };

            if (data.announcement && data.announcement.config) {
                settings.value.announcement.config = {
                    is_enabled: data.announcement.config.is_enabled !== false,
                    mode: data.announcement.config.mode || "slide",
                    speed: data.announcement.config.speed !== undefined
                        ? parseInt(data.announcement.config.speed)
                        : 10,
                    background_color: data.announcement.config.background_color || "#6E1F3A",
                    text_color: data.announcement.config.text_color || "#FFFFFF",
                    is_sticky: data.announcement.config.is_sticky !== false,
                };
            }

            if (data.welcome_gift) {
                settings.value.welcome_gift = {
                    ...settings.value.welcome_gift,
                    ...data.welcome_gift,
                    is_enabled: filterBool(data.welcome_gift.is_enabled),
                    coupon_code: data.welcome_gift.coupon_code || "WELCOME10",
                    discount_text: data.welcome_gift.discount_text || "Enjoy 10% OFF Your First Order",
                    title: data.welcome_gift.title || "A Special Gift Awaits You",
                    subtitle: data.welcome_gift.subtitle || "Every new member deserves a warm welcome.",
                };
            }

            if (data.reviews) {
                settings.value.reviews = {
                    ...settings.value.reviews,
                    ...data.reviews,
                    login_required: filterBool(data.reviews.login_required),
                    verified_purchase_required: filterBool(data.reviews.verified_purchase_required),
                    delivered_order_required: filterBool(data.reviews.delivered_order_required),
                    one_review_per_product: filterBool(data.reviews.one_review_per_product),
                    admin_approval_required: filterBool(data.reviews.admin_approval_required),
                    customer_editing_allowed: filterBool(data.reviews.customer_editing_allowed),
                    customer_deletion_allowed: filterBool(data.reviews.customer_deletion_allowed),
                    customer_images_allowed: filterBool(data.reviews.customer_images_allowed),
                    review_window_days: data.reviews.review_window_days !== undefined
                        ? Number(data.reviews.review_window_days)
                        : 0,
                    max_images_per_review: data.reviews.max_images_per_review !== undefined
                        ? Number(data.reviews.max_images_per_review)
                        : 4,
                    max_image_size_kb: data.reviews.max_image_size_kb !== undefined
                        ? Number(data.reviews.max_image_size_kb)
                        : 200,
                };
            }
        }
    } catch (err) {
        console.error("Failed to load store settings:", err);
    } finally {
        loading.value = false;
    }
};

const filterBool = (val) => {
    if (typeof val === "boolean") return val;
    return val === "1" || val === 1 || val === "true";
};

const saveSettings = async () => {
    saving.value = true;
    successMsg.value = "";
    try {
        const payload = {
            settings: {
                general: { ...settings.value.general },
                shipping: {
                    free_shipping_threshold: Number(settings.value.shipping.free_shipping_threshold || 1999),
                    default_shipping_fee: Number(settings.value.shipping.default_shipping_fee || 100),
                    shipping_banner_text: settings.value.shipping.shipping_banner_text,
                    dispatch_time_text: settings.value.shipping.dispatch_time_text,
                    state_rates: { ...settings.value.shipping.state_rates },
                },
                payment: {
                    ...settings.value.payment,
                    cod_active: settings.value.payment.cod_active ? "1" : "0",
                    cashfree_active: settings.value.payment.cashfree_active
                        ? "1"
                        : "0",
                },
                email: { ...settings.value.email },
                announcement: {
                    config: { ...settings.value.announcement.config },
                },
                welcome_gift: {
                    ...settings.value.welcome_gift,
                    is_enabled: settings.value.welcome_gift.is_enabled ? "1" : "0",
                },
                reviews: {
                    ...settings.value.reviews,
                },
            },
        };

        const response = await axios.post("/api/admin/settings/batch", payload);
        if (response.data && response.data.success) {
            successMsg.value = "✓ Store settings updated successfully!";
            setTimeout(() => {
                successMsg.value = "";
            }, 4000);
            await fetchSettings();
        }
    } catch (err) {
        console.error("Failed to save settings:", err);
        alert(
            err.response?.data?.message ||
                "Failed to save store configurations",
        );
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchSettings();
    fetchAnnouncementsList();
    fetchCoupons();
});
</script>
