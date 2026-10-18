import { defineStore } from 'pinia';
import axios from 'axios';

const loadCachedConfig = () => {
  try {
    const raw = localStorage.getItem('maya_sree_festival_config');
    if (raw) return JSON.parse(raw);
  } catch (e) {
    // ignore
  }
  return null;
};

export const useFestivalStore = defineStore('festival', {
  state: () => {
    const cached = loadCachedConfig() || {};
    return {
      isEnabled: cached.is_enabled !== undefined ? Boolean(cached.is_enabled) : false,
      festivalKey: cached.festival_key || 'diwali',
      festivalName: cached.festival_name || 'Diwali Grand Celebration',
      heroDesktopImage: cached.hero_desktop_image || '',
      heroMobileImage: cached.hero_mobile_image || '',
      heroOverlayOpacity: typeof cached.hero_overlay_opacity === 'number' ? cached.hero_overlay_opacity : 0.2,
      ornamentsEnabled: cached.ornaments_enabled !== undefined ? Boolean(cached.ornaments_enabled) : true,
      ornamentType: cached.ornament_type || 'diyas_toran',
      splashEnabled: cached.splash_enabled !== undefined ? Boolean(cached.splash_enabled) : true,
      splashGreeting: cached.splash_greeting || 'Wishing You a Joyous Festive Season',
      splashStyle: cached.splash_style || 'maroon_gold',
      headerBadgeText: cached.header_badge_text || 'FESTIVAL & WEDDING SPECIAL',
      sectionHeaderTag: cached.section_header_tag || 'FRESH OFF THE RUNWAY • FESTIVE EDIT',
      sectionHeaderMotif: cached.section_header_motif || 'diya',
      productBadgesEnabled: cached.product_badges_enabled !== undefined ? Boolean(cached.product_badges_enabled) : true,
      productBadgeIcon: cached.product_badge_icon !== undefined ? cached.product_badge_icon : '✨',
      productBadgeStyle: cached.product_badge_style || 'maroon_gold',
      productBadgePrefix: cached.product_badge_prefix || '',
      loading: false,
      initialized: false,
    };
  },

  getters: {
    isFestivalActive: (state) => Boolean(state.isEnabled),
    hasOrnaments: (state) => Boolean(state.isEnabled && state.ornamentsEnabled),
    isProductBadgeFestive: (state) => Boolean(state.isEnabled && state.productBadgesEnabled),
  },

  actions: {
    updateConfigLocally(d) {
      if (!d) return;
      if (d.is_enabled !== undefined) this.isEnabled = Boolean(d.is_enabled);
      if (d.festival_key) this.festivalKey = d.festival_key;
      if (d.festival_name) this.festivalName = d.festival_name;
      if (d.hero_desktop_image !== undefined) this.heroDesktopImage = d.hero_desktop_image;
      if (d.hero_mobile_image !== undefined) this.heroMobileImage = d.hero_mobile_image;
      if (d.hero_overlay_opacity !== undefined) this.heroOverlayOpacity = d.hero_overlay_opacity;
      if (d.ornaments_enabled !== undefined) this.ornamentsEnabled = Boolean(d.ornaments_enabled);
      if (d.ornament_type) this.ornamentType = d.ornament_type;
      if (d.splash_enabled !== undefined) this.splashEnabled = Boolean(d.splash_enabled);
      if (d.splash_greeting !== undefined) this.splashGreeting = d.splash_greeting;
      if (d.splash_style !== undefined) this.splashStyle = d.splash_style;
      if (d.header_badge_text !== undefined) this.headerBadgeText = d.header_badge_text;
      if (d.section_header_tag !== undefined) this.sectionHeaderTag = d.section_header_tag;
      if (d.section_header_motif !== undefined) this.sectionHeaderMotif = d.section_header_motif;
      if (d.product_badges_enabled !== undefined) this.productBadgesEnabled = Boolean(d.product_badges_enabled);
      if (d.product_badge_icon !== undefined) this.productBadgeIcon = d.product_badge_icon;
      if (d.product_badge_style !== undefined) this.productBadgeStyle = d.product_badge_style;
      if (d.product_badge_prefix !== undefined) this.productBadgePrefix = d.product_badge_prefix;

      try {
        localStorage.setItem('maya_sree_festival_config', JSON.stringify({
          is_enabled: this.isEnabled,
          festival_key: this.festivalKey,
          festival_name: this.festivalName,
          hero_desktop_image: this.heroDesktopImage,
          hero_mobile_image: this.heroMobileImage,
          hero_overlay_opacity: this.heroOverlayOpacity,
          ornaments_enabled: this.ornamentsEnabled,
          ornament_type: this.ornamentType,
          splash_enabled: this.splashEnabled,
          splash_greeting: this.splashGreeting,
          splash_style: this.splashStyle,
          header_badge_text: this.headerBadgeText,
          section_header_tag: this.sectionHeaderTag,
          section_header_motif: this.sectionHeaderMotif,
          product_badges_enabled: this.productBadgesEnabled,
          product_badge_icon: this.productBadgeIcon,
          product_badge_style: this.productBadgeStyle,
          product_badge_prefix: this.productBadgePrefix,
        }));
      } catch (e) {
        // ignore
      }
    },

    async fetchFestivalConfig() {
      if (this.initialized && !this.loading) return;
      this.loading = true;
      try {
        const response = await axios.get('/api/storefront/festival-mode');
        if (response.data && response.data.success && response.data.data) {
          const d = response.data.data;
          this.updateConfigLocally(d);
        }
      } catch (err) {
        console.warn('Could not load storefront festival config, defaulting to standard mode.', err);
      } finally {
        this.loading = false;
        this.initialized = true;
      }
    },
  },
});
