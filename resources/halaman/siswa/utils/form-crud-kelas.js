import { KelasService } from "../../../js/service/KelasService";
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

(function(){

  const modalEdit = new bootstrap.Modal(document.getElementById('modal-edit'));
  const modalTambah = new bootstrap.Modal(document.getElementById('modal-tambah'));
  const modalDelete = new bootstrap.Modal(document.getElementById('modal-delete'));

  let selectedButton = null;
  let initialkelas = [];
  let deleteIds = [];
  let isDeleteMode = false;

  const saveChangesBtn = document.getElementById('save-changes-btn');
  const saveCancelBtn = document.getElementById('save-cancel-btn');
  const deletekelasBtn = document.getElementById('delete-kelas-btn');
  const editkelasBtn = document.getElementById('edit-kelas-btn');
  const tambahkelasBtn = document.getElementById('tambah-kelas-btn');
  const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
  const datakelasContainer = document.querySelector('.data-kelas');
  const tambahkelasPlaceholder = document.getElementById('tambah-kelas-placeholder');
  const loadingIndicator = document.getElementById('loading-profesional');


  async function initializekelas() {
    try {
      const kelasList = await KelasService.initialize();

      initialkelas = kelasList.map(k => ({
        id: k.id.toString(),
        text: k.nama_kelas
      }));

      document.querySelectorAll('.kelas-btn').forEach(button => {
        if (button.id !== 'tambah-kelas-placeholder') {
          button.remove();
        }
      });

      // Render tombol baru
      initialkelas.forEach(category => {
        const newButton = createCategoryButton(category.id, category.text);
        datakelasContainer.insertBefore(newButton, tambahkelasPlaceholder);
      });
    } catch (error) {
      console.error('Gagal inisialisasi kelas:', error);
      alert('Gagal memuat data kelas dari server.');
    }finally {
      tambahkelasPlaceholder.classList.remove('d-none')
      loadingIndicator.classList.add('d-none'); // 🔹 SEMBUNYIKAN LOADING
    }
  }


  // Show delete confirmation
  function showDeleteConfirmation(id, text) {
    const modalBody = document.querySelector('#modal-delete .modal-body');
    modalBody.textContent = `Apakah Anda yakin ingin menghapus kelas "${text}"?`;
    
    confirmDeleteBtn.onclick = function() {
      deleteCategory(id);
      modalDelete.hide();
    };
    
    modalDelete.show();
  }

  // Delete category
  function deleteCategory(id) {
    const button = document.querySelector(`.kelas-btn[data-id="${id}"]`);
    if (button) {
      if (initialkelas.some(cat => cat.id === id)) {
        deleteIds.push(id);
      }
      button.remove();
      showSaveChangesButtons();
    }
  }

  // Get the last ID from existing kelas
  function getLastId() {
    let maxId = 0;
    document.querySelectorAll('.kelas-btn').forEach(button => {
      if (button.id !== 'tambah-kelas-placeholder') {
        const id = parseInt(button.getAttribute('data-id'), 10);
        if (id > maxId) {
          maxId = id;
        }
      }
    });
    return maxId;
  }

  // Show save changes buttons
  function showSaveChangesButtons() {
    saveChangesBtn.classList.remove('d-none');
    saveCancelBtn.classList.remove('d-none');
  }

  // Hide save changes buttons
  function hideSaveChangesButtons() {
    saveChangesBtn.classList.add('d-none');
    saveCancelBtn.classList.add('d-none');
  }

  // Cancel all changes
  function cancelAllChanges() {
    // Remove all existing category buttons
    document.querySelectorAll('.kelas-btn').forEach(button => {
      if (button.id !== 'tambah-kelas-placeholder') {
        button.remove();
      }
    });
    
    // Recreate initial kelas
    initialkelas.forEach(category => {
      const newButton = createCategoryButton(category.id, category.text);
      datakelasContainer.insertBefore(newButton, tambahkelasPlaceholder);
    });
    
    hideSaveChangesButtons();
    deleteIds = [];
    exitDeleteMode();
  }

  // Create a new category button
  function createCategoryButton(id, text) {
    const newButton = document.createElement('button');
    newButton.type = 'button';
    newButton.className = 'btn btn-primary-profesional kelas-btn';
    newButton.setAttribute('data-id', id);
    newButton.textContent = text;

    newButton.addEventListener('click', function() {
      if (!isDeleteMode) {
        selectedButton = this;
        document.getElementById('editable-input').value = this.textContent.trim();
        modalEdit.show();
      } else {
        const id = this.getAttribute('data-id');
        const text = this.textContent.trim();
        showDeleteConfirmation(id, text);
      }
    });

    return newButton;
  }

  // Toggle delete mode
  function toggleDeleteMode() {
    isDeleteMode = !isDeleteMode;
    // Dapatkan semua tombol kelas (kecuali tombol tambah)
    const kelasButtons = document.querySelectorAll('.kelas-btn:not(#tambah-kelas-placeholder)');
    
    if (isDeleteMode) {
      // Ubah semua tombol ke mode danger
      kelasButtons.forEach(button => {
        button.classList.remove('btn-primary-profesional');
        button.classList.add('btn-danger-profesional');
      });
      
      deletekelasBtn.textContent = 'Cancel';
      deletekelasBtn.classList.remove('btn-danger-profesional');
      deletekelasBtn.classList.add('btn-warning-profesional');
    } else {
      // Kembalikan semua tombol ke mode normal
      kelasButtons.forEach(button => {
        button.classList.remove('btn-danger-profesional');
        button.classList.add('btn-primary-profesional');
      });
      
      exitDeleteMode();
    }
  }

  // Exit delete mode
  function exitDeleteMode() {
    isDeleteMode = false;
    deletekelasBtn.textContent = 'Delete';
    deletekelasBtn.classList.remove('btn-warning-profesional');
    deletekelasBtn.classList.add('btn-danger-profesional');
  }

  // Event listeners
  deletekelasBtn.addEventListener('click', toggleDeleteMode);

  editkelasBtn.addEventListener('click', function() {
    if (selectedButton) {
      const newText = document.getElementById('editable-input').value.trim();
      if (newText) {
        selectedButton.textContent = newText;
        modalEdit.hide();
        showSaveChangesButtons();
      }
    }
  });

  tambahkelasBtn.addEventListener('click', function() {
    const input = document.getElementById('input-kelas');
    const text = input.value.trim();
    
    if (text) {
      const newId = getLastId() + 1;
      const newButton = createCategoryButton(newId, text);
      datakelasContainer.insertBefore(newButton, tambahkelasPlaceholder);
      input.value = '';
      modalTambah.hide();
      showSaveChangesButtons();
    }
  });

  saveChangesBtn.addEventListener('click', function() {
    // Kumpulkan semua kelas sekarang
    const currentkelas = [];
    document.querySelectorAll('.kelas-btn').forEach(button => {
      if (button.id !== 'tambah-kelas-placeholder') {
        currentkelas.push({
          id: button.getAttribute('data-id'),
          text: button.textContent.trim()
        });
      }
    });

    // Buat diff antara initial dan current
    const added = currentkelas.filter(cat =>
      !initialkelas.some(init => init.id === cat.id)
    ).map(cat => ({ nama_kelas: cat.text }));

    const updated = currentkelas.filter(cat => {
      const init = initialkelas.find(init => init.id === cat.id);
      return init && init.text !== cat.text;
    }).map(cat => ({ id: cat.id, nama_kelas: cat.text }));

    const deleted = deleteIds;

    const changes = { added, updated, deleted };

    KelasService.saveChanges(changes).then((ok) => {
      if(ok){
        initializekelas();
        hideSaveChangesButtons();
        deleteIds = [];
        exitDeleteMode();
      }
    });
  });


  saveCancelBtn.addEventListener('click', cancelAllChanges);

  // Inisialisasi pertama kali
  initializekelas();
})();