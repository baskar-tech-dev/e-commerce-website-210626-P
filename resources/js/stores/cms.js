import { defineStore } from 'pinia';
import axios from 'axios';

export const useCmsStore = defineStore('cms', {
  state: () => ({
    settings: {},
    loading: false,
    loaded: false,
    error: null,
  }),

  getters: {
    announcement: (state) => state.settings.announcement || {},
    hero: (state) => state.settings.hero || {},
    homepageSections: (state) => {
      const sections = state.settings.homepage?.homepage_sections || [];
      if (!Array.isArray(sections)) return [];
      return [...sections].sort((a, b) => (a.order || 0) - (b.order || 0));
    },
    promotions: (state) => state.settings.promotions || {},
    testimonials: (state) => state.settings.testimonials?.testimonials || [],
    trustBadges: (state) => state.settings.trust?.trust_badges || [],
    newsletter: (state) => state.settings.newsletter || {},
    footer: (state) => state.settings.footer?.footer_links || {},
  },

  actions: {
    async fetchCmsSettings() {
      if (this.loaded && Object.keys(this.settings).length > 0) return;
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/storefront/cms');
        if (response.data.success) {
          this.settings = response.data.data;
          this.loaded = true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load CMS settings';
      } finally {
        this.loading = false;
      }
    },
  },
});
