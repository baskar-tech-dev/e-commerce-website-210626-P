import { defineStore } from 'pinia';
import axios from 'axios';

export const useMenuStore = defineStore('menu', {
  state: () => ({
    menus: {},
    loading: false,
    error: null,
  }),

  actions: {
    async fetchMenus() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/menus');
        this.menus = response.data;
      } catch (err) {
        this.error = 'Failed to fetch menus';
        console.error(err);
      } finally {
        this.loading = false;
      }
    }
  },
});
