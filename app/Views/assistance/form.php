<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><?= esc($title) ?></h5>
            </div>
            <div class="card-body">
                <form action="<?= isset($activity) ? site_url('assistance/update/' . $activity['id']) : site_url('assistance/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="tanggal_kegiatan" class="form-label">Activity Date</label>
                        <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal_kegiatan"
                            value="<?= isset($activity) ? $activity['tanggal_kegiatan'] : date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="agency_info" class="form-label">Agency (OPD / Desa / Kelurahan)</label>
                        <select class="form-select" id="agency_info" name="agency_info" required>
                            <option value="">Select Agency...</option>
                            <?php
                            $groups = [];
                            foreach ($agencies as $agency) {
                                $groups[$agency->group][] = $agency;
                            }
                            ?>

                            <?php foreach ($groups as $groupName => $items): ?>
                                <optgroup label="<?= $groupName ?>">
                                    <?php foreach ($items as $item): ?>
                                        <?php
                                        $selected = '';
                                        if (isset($activity)) {
                                            $currentVal = $activity['agency_type'] . '|' . $activity['agency_id'] . '|' . $activity['agency_name'];
                                            // Ideally match simpler unique ID, but reconstructing value here works
                                            // Or check breakdown
                                            if ($activity['agency_id'] == explode('|', $item->value)[1] && $activity['agency_type'] == explode('|', $item->value)[0]) {
                                                $selected = 'selected';
                                            }
                                        }
                                        ?>
                                        <option value="<?= $item->value ?>" <?= $selected ?>><?= esc($item->label) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Method</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" id="methodOnline" value="Online"
                                    <?= (isset($activity) && $activity['method'] == 'Online') ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="methodOnline">Online</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" id="methodOffline" value="Offline"
                                    <?= (isset($activity) && $activity['method'] == 'Offline') ? 'checked' : ((!isset($activity)) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="methodOffline">Offline</label>
                            </div>
                        </div>
                    </div>

                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-select" id="category" name="category" onchange="updateServices()" required>
                                                <option value="">Select Category...</option>
                                                <?php foreach ($categoryMap as $id => $label): ?>
                                                    <option value="<?= $id ?>" <?= (isset($activity) && $activity['category'] == $id) ? 'selected' : '' ?>>
                                                        <?= esc($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                    
                                        <div class="mb-3">
                                            <label class="form-label">Services Provided</label>
                                            <div id="servicesContainer">
                                                <?php 
                                                $serviceOptions = [
                                                    1 => [ // Aplikasi SPBE
                                                        'Website OPD', 
                                                        'Email Resmi', 
                                                        'Tanda Tangan Elektronik'
                                                    ],
                                                    2 => [ // Website Desa & Kelurahan
                                                        'Bimtek Website', 
                                                        'Domain Hosting Website'
                                                    ]
                                                ];
                                                $selectedServices = isset($activity['services']) ? $activity['services'] : [];
                                                ?>
                                                
                                                <?php foreach ($serviceOptions as $catId => $svcs): ?>
                                                    <div class="service-group" id="group_<?= $catId ?>" style="display: none;">
                                                        <div class="row">
                                                            <?php foreach ($svcs as $svc): ?>
                                                                <div class="col-md-6">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input service-checkbox" type="checkbox" name="services[]" id="svc_<?= md5($svc) ?>" value="<?= $svc ?>"
                                                                               <?= in_array($svc, $selectedServices) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label" for="svc_<?= md5($svc) ?>"><?= $svc ?></label>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                    
                                        <div class="mb-3">
                                            <label for="keterangan" class="form-label">Notes</label>
                                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= isset($activity) ? esc($activity['keterangan']) : '' ?></textarea>
                                        </div>
                    
                                        <div class="d-flex justify-content-between">
                                            <a href="<?= site_url('assistance') ?>" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">Save Activity</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    function updateServices() {
                        const category = document.getElementById('category').value;
                        const groups = document.querySelectorAll('.service-group');
                        
                        // Hide all groups
                        groups.forEach(g => g.style.display = 'none');
                    
                        if (category) {
                            // Show selected group
                            const targetId = 'group_' + category;
                            const targetGroup = document.getElementById(targetId);
                            if (targetGroup) {
                                targetGroup.style.display = 'block';
                            }
                        }
                    }
                    
                    // Initial update on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        updateServices();
                    });
                    </script><?= $this->endSection() ?>