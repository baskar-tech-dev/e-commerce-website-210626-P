import { defineStore } from 'pinia';
import axios from 'axios';

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [],
    categoryTree: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchCategories() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/categories');
        if (response.data.success) {
          this.categories = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch categories';
      } finally {
        this.loading = false;
      }
    },

    async fetchPublicCategories() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/storefront/categories');
        if (response.data.success) {
          this.categories = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch public categories';
      } finally {
        this.loading = false;
      }
    },

    async fetchCategoryTree() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/categories?tree=1');
        if (response.data.success) {
          this.categoryTree = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch category tree';
      } finally {
        this.loading = false;
      }
    },

    async createCategory(data) {
      this.loading = true;
      try {
        const response = await axios.post('/api/admin/categories', data);
        if (response.data.success) {
          await this.fetchCategories();
          await this.fetchCategoryTree();
          return response.data.data;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to create category' };
      } finally {
        this.loading = false;
      }
    },

    async updateCategory(id, data) {
      this.loading = true;
      try {
        const response = await axios.put(`/api/admin/categories/${id}`, data);
        if (response.data.success) {
          await this.fetchCategories();
          await this.fetchCategoryTree();
          return response.data.data;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to update category' };
      } finally {
        this.loading = false;
      }
    },

    async deleteCategory(id) {
      this.loading = true;
      try {
        const response = await axios.delete(`/api/admin/categories/${id}`);
        if (response.data.success) {
          await this.fetchCategories();
          await this.fetchCategoryTree();
          return true;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to delete category' };
      } finally {
        this.loading = false;
      }
    },
  },
});
