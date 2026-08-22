import axios from 'axios';
import { ref } from 'vue';

export const DEFAULT_INDIAN_STATES = [
  'Tamil Nadu',
  'Kerala',
  'Karnataka',
  'Andhra Pradesh',
  'Telangana',
  'Puducherry',
  'Arunachal Pradesh',
  'Assam',
  'Bihar',
  'Chhattisgarh',
  'Goa',
  'Gujarat',
  'Haryana',
  'Himachal Pradesh',
  'Jharkhand',
  'Madhya Pradesh',
  'Maharashtra',
  'Manipur',
  'Meghalaya',
  'Mizoram',
  'Nagaland',
  'Odisha',
  'Punjab',
  'Rajasthan',
  'Sikkim',
  'Tripura',
  'Uttar Pradesh',
  'Uttarakhand',
  'West Bengal',
  'Andaman and Nicobar Islands',
  'Chandigarh',
  'Dadra and Nagar Haveli and Daman and Diu',
  'Delhi (NCT)',
  'Jammu and Kashmir',
  'Ladakh',
  'Lakshadweep'
];

export function useIndianStates() {
  const indianStates = ref([...DEFAULT_INDIAN_STATES]);
  const loading = ref(false);

  const fetchIndianStates = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/api/storefront/indian-states');
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        indianStates.value = response.data.data;
      }
    } catch (err) {
      // Fallback already populated
    } finally {
      loading.value = false;
    }
  };

  const normalizeState = (val) => {
    if (!val) return '';
    const clean = val.trim().toLowerCase().replace(/\s+/g, '');
    const match = indianStates.value.find(s => s.toLowerCase().replace(/\s+/g, '') === clean);
    return match || val;
  };

  return {
    indianStates,
    loading,
    fetchIndianStates,
    normalizeState
  };
}
