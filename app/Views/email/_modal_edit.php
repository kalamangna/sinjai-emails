<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('email/update_details/' . $email['user']) ?>" method="post">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" value="<?= esc(strtoupper($email['name'])) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="<?= esc($email['email']) ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="gelar_depan" class="form-label">Gelar Depan</label>
                            <input type="text" name="gelar_depan" id="gelar_depan" value="<?= esc($email['gelar_depan'] ?? '') ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="gelar_belakang" class="form-label">Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" id="gelar_belakang" value="<?= esc($email['gelar_belakang'] ?? '') ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="text" name="password" id="password" value="<?= esc($email['password']) ?>" class="form-control">
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" name="nik" id="nik" value="<?= esc($email['nik']) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" name="nip" id="nip" value="<?= esc($email['nip']) ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" value="<?= esc($email['tempat_lahir']) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?= esc($email['tanggal_lahir']) ?>" class="form-control">
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pendidikan" class="form-label">Pendidikan</label>
                            <input type="text" name="pendidikan" id="pendidikan" value="<?= esc($email['pendidikan']) ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" value="<?= esc($email['jabatan']) ?>" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="status_asn" class="form-label">Status ASN</label>
                            <select name="status_asn" id="status_asn" class="form-select">
                                <option value="" <?= empty($email['status_asn_id']) ? 'selected' : '' ?>>Select...</option>
                                <?php foreach ($status_asn_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>" <?= ($email['status_asn_id'] == $option['id']) ? 'selected' : '' ?>>
                                        <?= esc($option['nama_status_asn']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eselon" class="form-label">Eselon</label>
                            <select name="eselon" id="eselon" class="form-select">
                                <option value="" <?= empty($email['eselon_id']) ? 'selected' : '' ?>>Select...</option>
                                <?php foreach ($eselon_options as $option): ?>
                                    <option value="<?= esc($option['id']) ?>" <?= ($email['eselon_id'] == $option['id']) ? 'selected' : '' ?>>
                                        <?= esc($option['nama_eselon']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="unit_kerja_id" class="form-label">Unit Kerja</label>
                            <select class="form-select" id="unit_kerja_id" name="unit_kerja_id">
                                <option value="">--Pilih Unit Kerja--</option>
                                <?php foreach ($unit_kerja_options as $unit): ?>
                                    <option value="<?= esc($unit['id']) ?>" <?= ($unit['id'] == $email['unit_kerja_id']) ? 'selected' : '' ?>>
                                        <?= esc(strtoupper($unit['nama_unit_kerja'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pimpinan" class="form-label">Pimpinan</label>
                            <select name="pimpinan" id="pimpinan" class="form-select">
                                <option value="0" <?= ($email['pimpinan'] ?? 0) == 0 ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= ($email['pimpinan'] ?? 0) == 1 ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="pimpinan_desa" class="form-label">Pimpinan Desa</label>
                            <select name="pimpinan_desa" id="pimpinan_desa" class="form-select">
                                <option value="0" <?= ($email['pimpinan_desa'] ?? 0) == 0 ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= ($email['pimpinan_desa'] ?? 0) == 1 ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>