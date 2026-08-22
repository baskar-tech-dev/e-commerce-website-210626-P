import { defineStore } from 'pinia';
import axios from 'axios';

export const useSizeStore = defineStore('size', {
  state: () => ({
    sizeGroups: [],
    activeSizeGroups: [],
    sizes: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchSizeGroups(search = '') {
      this.loading = true;
      this.error = null;
      try {
        const url = search ? `/api/admin/size-groups?search=${encodeURIComponent(search)}` : '/api/admin/size-groups';
        const response = await axios.get(url);
        if (response.data.success) {
          this.sizeGroups = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch size groups';
      } finally {
        this.loading = false;
      }
    },

    async fetchActiveSizeGroups() {
      try {
        const response = await axios.get('/api/admin/size-groups/active');
        if (response.data.success) {
          this.activeSizeGroups = response.data.data;
          return this.activeSizeGroups;
        }
      } catch (err) {
        console.error('Failed to fetch active size groups', err);
      }
      return [];
    },

    async createSizeGroup(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/size-groups', data);
        if (response.data.success) {
          this.sizeGroups.push(response.data.data);
          this.activeSizeGroups.push(response.data.data);
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create size group';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateSizeGroup(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/size-groups/${id}`, data);
        if (response.data.success) {
          const index = this.sizeGroups.findIndex(g => g.id === id);
          if (index !== -1) {
            this.sizeGroups[index] = response.data.data;
          }
          await this.fetchActiveSizeGroups();
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update size group';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async deleteSizeGroup(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.delete(`/api/admin/size-groups/${id}`);
        if (response.data.success) {
          this.sizeGroups = this.sizeGroups.filter(g => g.id !== id);
          this.activeSizeGroups = this.activeSizeGroups.filter(g => g.id !== id);
          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete size group';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // Individual Size CRUD
    async fetchSizes(sizeGroupId = null) {
      this.loading = true;
      this.error = null;
      try {
        const url = sizeGroupId ? `/api/admin/sizes?size_group_id=${sizeGroupId}` : '/api/admin/sizes';
        const response = await axios.get(url);
        if (response.data.success) {
          this.sizes = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch sizes';
      } finally {
        this.loading = false;
      }
    },

    async createSize(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/sizes', data);
        if (response.data.success) {
          this.sizes.push(response.data.data);
          await this.fetchActiveSizeGroups();
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create size';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateSize(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/sizes/${id}`, data);
        if (response.data.success) {
          const index = this.sizes.findIndex(s => s.id === id);
          if (index !== -1) {
            this.sizes[index] = response.data.data;
          }
          await this.fetchActiveSizeGroups();
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update size';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async deleteSize(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.delete(`/api/admin/sizes/${id}`);
        if (response.data.success) {
          this.sizes = this.sizes.filter(s => s.id !== id);
          await this.fetchActiveSizeGroups();
          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete size';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
