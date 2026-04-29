<div class="card-alternatif-with-footer mb-5">
  <div class="card-header d-flex **flex-column flex-sm-row** justify-content-between">
    <div class="**mb-2 mb-sm-0**"> <h3>Manage kelas</h3>
        <div class="accent-bar"></div>
    </div>
    <div class="feature">
        <button type="button" id="save-changes-btn" class="btn btn-primary-profesional d-none">
          Save Changes
        </button>

        <button type="button" id="save-cancel-btn" class="btn btn-outline-secondary d-none">
          Cancel ALL
        </button>
        <button id="delete-kelas-btn" class="btn btn-danger-profesional" type="button">Delete</button>
    </div>
  </div>
  <div class="card-body">
    <div class="form-crud-kelas">
        <div id="loading-profesional" class="text-center my-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Memuat Data...</p>
        </div>
        <div class="data-kelas gap-3 **flex-wrap**">
            <button id="tambah-kelas-placeholder" type="button" class="btn rounded-circle glowing-pulse d-none" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                <i class="fas fa-plus" style="font-weight: bold !important"></i>
            </button>
        </div>
   </div>
  </div>
</div>