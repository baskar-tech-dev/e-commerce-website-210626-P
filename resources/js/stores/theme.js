import { defineStore } from 'pinia';
import axios from 'axios';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    theme: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchActiveTheme() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/theme/active');
        this.theme = response.data;
        this.applyTheme(this.theme);
      } catch (err) {
        this.error = 'Failed to fetch active theme';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    applyTheme(theme) {
      if (!theme) return;
      
      const root = document.documentElement;
      
      if (theme.primary_color) {
        root.style.setProperty('--color-primary', theme.primary_color);
      }
      if (theme.primary_color_hover) {
        root.style.setProperty('--color-primary-hover', theme.primary_color_hover);
      }
      if (theme.secondary_color) {
        root.style.setProperty('--color-secondary', theme.secondary_color);
      }
      if (theme.secondary_color_hover) {
        root.style.setProperty('--color-secondary-hover', theme.secondary_color_hover);
      }
    }
  },
});
