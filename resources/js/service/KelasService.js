import { ApiService } from "../utils/ApiService";

export const KelasService = (() => {
  return {
    /**
     * Inisialisasi pertama kali (caching agar tidak fetch ulang)
     * @returns {Promise<Array>}
     */
    async initialize() {
      return this.fetchFromAPI();
    },
    
    /**
     * Ambil kelas dari server
     * @returns {Promise<Array>}
     */
    async fetchFromAPI() {
      try {
        const response = await fetch('/kelas');
        const result = await response.json();

        if (!Array.isArray(result.data)) {
          throw new Error('Invalid categories data received');
        }
        return result.data
      } catch (error) {
        console.error('Gagal mengambil kelas:', error);
        throw error;
      }
    },
    /**
     * Kirim perubahan (tambah/edit/hapus) ke server
     * @param {Object} changes
     */
    async saveChanges(changes) {
      try {
        if (!changes || typeof changes !== 'object') {
          throw new Error('Invalid changes object', 'warning');
        }
        
        const result = await ApiService.call('/kelas/save-changes', 'POST', changes)

        alert(result.message)

        return true
      } catch (error) {
          alert(error.message)
      }
    },
  };
})();
