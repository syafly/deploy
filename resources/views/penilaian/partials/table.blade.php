<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-container">
            <table class="table table-hover mb-0">
                <thead class="table-light sticky-header">
                    <tr>
                        <th class="text-center align-middle">#</th>
                        <th class="text-center align-middle">
                            <span class="d-block fw-semibold">Masuk</span>
                            <small class="text-muted">(M)</small>
                        </th>
                        <th class="text-center align-middle">
                            <span class="d-block fw-semibold">Istirahat</span>
                            <small class="text-muted">(I)</small>
                        </th>
                        <th class="text-center align-middle">
                            <span class="d-block fw-semibold">K. Istirahat</span>
                            <small class="text-muted">(K)</small>
                        </th>
                        <th class="text-center align-middle">
                            <span class="d-block fw-semibold">Pulang</span>
                            <small class="text-muted">(P)</small>
                        </th>
                        <th class="text-center align-middle">Status Klasifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rules as $rule)
                        <tr class="align-middle" id="rule-{{ $rule->id }}">
                            <td class="text-center fw-semibold text-muted">{{ $rule->id }}</td>
                            
                            <!-- Status Indicators -->
                            <td class="text-center">
                                <div class="status-indicator {{ $rule->masuk ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $rule->masuk ? 'fa-check' : 'fa-times' }}"></i>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="status-indicator {{ $rule->istirahat ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $rule->istirahat ? 'fa-check' : 'fa-times' }}"></i>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="status-indicator {{ $rule->kembali_istirahat ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $rule->kembali_istirahat ? 'fa-check' : 'fa-times' }}"></i>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="status-indicator {{ $rule->pulang ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $rule->pulang ? 'fa-check' : 'fa-times' }}"></i>
                                </div>
                            </td>

                            <!-- Status Control -->
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="flex-grow-1">
                                        <select name="status_output" class="form-select status-select" 
                                                data-rule-id="{{ $rule->id }}" required>
                                            <option value="masuk" {{ $rule->status_output == 'masuk' ? 'selected' : '' }} data-color="success">Masuk</option>
                                            <option value="alpa" {{ $rule->status_output == 'alpa' ? 'selected' : '' }} data-color="danger">Alpa</option>
                                        </select>
                                    </div>
                                    
                                    <button type="button" class="btn btn-primary-profesional btn-sm update-btn" 
                                            data-rule-id="{{ $rule->id }}">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted align-middle">
                                <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                Belum ada aturan yang didefinisikan. Silakan jalankan seeder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>