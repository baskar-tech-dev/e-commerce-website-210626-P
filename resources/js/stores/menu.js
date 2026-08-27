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
        const data = response.data || {};

        // Find which group contains Reports
        let reportGroupKey = null;
        let hasReportMenu = false;
        let hasPayments = false;
        let hasSettlements = false;

        for (const [groupName, items] of Object.entries(data)) {
          if (Array.isArray(items)) {
            if (items.some(m => m.path === '/admin/reports' || m.name?.toLowerCase() === 'reports')) {
              reportGroupKey = groupName;
              hasReportMenu = true;
            }
            if (items.some(m => m.path === '/admin/reports/payments')) {
              hasPayments = true;
            }
            if (items.some(m => m.path === '/admin/reports/settlements')) {
              hasSettlements = true;
            }
          }
        }

        // If user can see Reports, ensure Payments and Settlements are also listed in sidebar
        if (hasReportMenu && reportGroupKey && data[reportGroupKey]) {
          if (!hasPayments) {
            data[reportGroupKey].push({
              id: 'cf-payments-runtime',
              name: 'Payments',
              path: '/admin/reports/payments',
              icon: 'CreditCard',
              group: reportGroupKey,
            });
          }
          if (!hasSettlements) {
            data[reportGroupKey].push({
              id: 'cf-settlements-runtime',
              name: 'Settlements',
              path: '/admin/reports/settlements',
              icon: 'Landmark',
              group: reportGroupKey,
            });
          }
        }

        this.menus = data;
      } catch (err) {
        this.error = 'Failed to fetch menus';
        console.error(err);
      } finally {
        this.loading = false;
      }
    }
  },
});
