<!-- Modal Edit-->
<div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input id="editable-input" class="form-control" type="text" placeholder="Edit kelas...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-profesional" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-profesional" id="edit-kelas-btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

  <!-- Modal Tambah-->
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input id="input-kelas" class="form-control" type="text" placeholder="Masukkan nama kelas...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-profesional" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-profesional" id="tambah-kelas-btn">Tambah</button>
            </div>
        </div>
    </div>
</div>

  <!-- Modal Delete-->
<div class="modal fade" id="modal-delete" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDeleteLabel">Delete kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus kelas ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-profesional" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger-profesional" id="confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>
</div>