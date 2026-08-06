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
                v-for="tab in ['general', 'payment', 'email', 'announcement']"
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
                <span v-else-if="tab === 'payment'">💳 Payment Gateways</span>
                <span v-else-if="tab === 'email'">✉️ Notifications (SMTP)</span>
                <span v-else-if="tab === 'announcement'"
                    >📢 Announcement Ticker</span
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

                <!-- Tab 2: Payment Gateways -->
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

                    <!-- Razorpay Active -->
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
                                    v-model="settings.payment.razorpay_active"
                                />
                                Razorpay Payment Gateway API
                            </label>
                            <span
                                style="
                                    font-size: 0.75rem;
                                    color: var(--color-text-muted);
                                    margin-left: 1.5rem;
                                "
                            >
                                Enables online credit/debit card, netbanking,
                                and UPI checkout options.
                            </span>
                        </div>

                        <!-- Razorpay Config Inputs -->
                        <div
                            v-if="settings.payment.razorpay_active"
                            class="responsive-grid-1-1"
                            style="gap: var(--spacing-md); margin-left: 1.5rem"
                        >
                            <div class="form-group">
                                <label class="form-label"
                                    >Razorpay Key ID *</label
                                >
                                <input
                                    type="text"
                                    v-model="settings.payment.razorpay_key"
                                    class="form-input"
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label class="form-label"
                                    >Razorpay Key Secret *</label
                                >
                                <input
                                    type="password"
                                    v-model="settings.payment.razorpay_secret"
                                    class="form-input"
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
import { ref, onMounted } from "vue";
import axios from "axios";
import StorefrontAnnouncementBar from "../../components/StorefrontAnnouncementBar.vue";

const activeTab = ref("general");
const loading = ref(true);
const saving = ref(false);
const successMsg = ref("");

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
    payment: {
        cod_active: true,
        razorpay_active: false,
        razorpay_key: "",
        razorpay_secret: "",
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
});

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
            if (data.payment) {
                settings.value.payment = {
                    ...settings.value.payment,
                    ...data.payment,
                    cod_active: filterBool(data.payment.cod_active),
                    razorpay_active: filterBool(data.payment.razorpay_active),
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
                payment: {
                    ...settings.value.payment,
                    cod_active: settings.value.payment.cod_active ? "1" : "0",
                    razorpay_active: settings.value.payment.razorpay_active
                        ? "1"
                        : "0",
                },
                email: { ...settings.value.email },
                announcement: {
                    config: { ...settings.value.announcement.config },
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
});
</script>
