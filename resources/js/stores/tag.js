import { defineStore } from 'pinia';
import axios from 'axios';

export const useTagStore = defineStore('tag', {
  state: () => ({
    tags: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchTags() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/tags');
        if (response.data.success) {
          this.tags = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch tags';
      } finally {
        this.loading = false;
      }
    },

    async createTag(data) {
      this.loading = true;
      try {
        const response = await axios.post('/api/admin/tags', data);
        if (response.data.success) {
          await this.fetchTags();
          return response.data.data;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to create tag' };
      } finally {
        this.loading = false;
      }
    },

    async updateTag(id, data) {
      this.loading = true;
      try {
        const response = await axios.put(`/api/admin/tags/${id}`, data);
        if (response.data.success) {
          await this.fetchTags();
          return response.data.data;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to update tag' };
      } finally {
        this.loading = false;
      }
    },

    async deleteTag(id) {
      this.loading = true;
      try {
        const response = await axios.delete(`/api/admin/tags/${id}`);
        if (response.data.success) {
          await this.fetchTags();
          return true;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to delete tag' };
      } finally {
        this.loading = false;
      }
    },
  },
});
