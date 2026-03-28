@section('scripts')
    <script>
        $(function() {
            const adminToggle = $('#is_admin');
            const adminPermissionSection = $('#admin-permission-section');
            const permissionCheckboxes = $('.permission-checkbox');
            const permissionMatrixBody = $('#permission-matrix-body');
            const permissionMatrixToggle = $('#permission-matrix-toggle');

            function normalizeText(value) {
                return String(value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function getModuleCheckboxes(moduleName) {
                return permissionCheckboxes.filter(function() {
                    return String($(this).data('module')) === String(moduleName);
                });
            }

            function getManageCheckbox(moduleName) {
                return getModuleCheckboxes(moduleName).filter(function() {
                    return String($(this).data('slug')).endsWith('.manage');
                });
            }

            function getViewCheckbox(moduleName) {
                return getModuleCheckboxes(moduleName).filter(function() {
                    return String($(this).data('slug')).endsWith('.view');
                });
            }

            function canAccessModuleActions(moduleName) {
                const manageCheckbox = getManageCheckbox(moduleName);
                const viewCheckbox = getViewCheckbox(moduleName);
                const moduleCheckboxes = getModuleCheckboxes(moduleName);
                const hasGate = manageCheckbox.length || viewCheckbox.length;

                if (!hasGate) {
                    return true;
                }

                return manageCheckbox.is(':checked') || viewCheckbox.is(':checked');
            }

            function toggleModulePermissions(moduleName, checked) {
                getModuleCheckboxes(moduleName).prop('checked', checked);
            }

            function syncManagePermissionState(moduleName) {
                const moduleCheckboxes = getModuleCheckboxes(moduleName);
                const manageCheckbox = getManageCheckbox(moduleName);

                if (!manageCheckbox.length) {
                    return;
                }

                const otherCheckboxes = moduleCheckboxes.not(manageCheckbox);
                if (!otherCheckboxes.length) {
                    return;
                }

                const checkedCount = otherCheckboxes.filter(':checked').length;
                const allChecked = otherCheckboxes.length === checkedCount;
                const partiallyChecked = checkedCount > 0 && checkedCount < otherCheckboxes.length;

                manageCheckbox
                    .prop('checked', allChecked)
                    .prop('indeterminate', partiallyChecked);
            }

            function syncModuleActionAvailability(moduleName) {
                const moduleCheckboxes = getModuleCheckboxes(moduleName);
                const manageCheckbox = getManageCheckbox(moduleName);
                const viewCheckbox = getViewCheckbox(moduleName);
                const allowActions = canAccessModuleActions(moduleName);

                moduleCheckboxes.each(function() {
                    const checkbox = $(this);
                    const slug = String(checkbox.data('slug') || '');
                    const isManage = slug.endsWith('.manage');
                    const isView = slug.endsWith('.view');

                    if (!isManage && !isView && !allowActions) {
                        checkbox.prop('checked', false);
                    }

                    checkbox.prop('disabled', !adminToggle.is(':checked') ? true : (!allowActions && !isManage && !isView));
                });

                $('.permission-matrix__toggle').each(function() {
                    const button = $(this);
                    const buttonModule = String(button.data('module') || '');
                    const action = String(button.data('action') || '');

                    if (buttonModule !== String(moduleName)) {
                        return;
                    }

                    const isManage = action === 'manage';
                    const isView = action === 'view';
                    button.prop('disabled', !adminToggle.is(':checked') ? true : (!allowActions && !isManage && !isView));
                });

                if (manageCheckbox.is(':checked')) {
                    moduleCheckboxes.prop('disabled', !adminToggle.is(':checked'));
                }
            }

            function syncSelectedCount() {
                $('#selected-permission-count').text($('.permission-checkbox:checked').length);

                $('.permission-matrix__toggle').each(function() {
                    const permissionId = String($(this).data('permission-id'));
                    const checked = $('.permission-checkbox[value="' + permissionId + '"]').is(':checked');
                    $(this).toggleClass('is-active', checked);
                });

                $('.permission-module').each(function() {
                    const moduleName = String($(this).data('module') || '');
                    const manageCheckbox = getManageCheckbox(moduleName);

                    if (!manageCheckbox.length) {
                        return;
                    }

                    const manageButton = $('.permission-matrix__toggle').filter(function() {
                        return String($(this).data('module')) === moduleName && String($(this).data('action')) === 'manage';
                    });

                    manageButton.toggleClass('is-partial', manageCheckbox.prop('indeterminate') === true);
                });
            }

            function filterPermissions(keyword) {
                const normalized = normalizeText(keyword);

                $('.permission-module').each(function() {
                    const module = $(this);
                    let visibleCount = 0;

                    module.find('.permission-item').each(function() {
                        const filterSource = normalizeText($(this).attr('data-filter'));
                        const matched = normalized === '' || filterSource.includes(normalized);
                        $(this).toggleClass('is-hidden', !matched);
                        if (matched) visibleCount++;
                    });

                    module.toggleClass('is-hidden', visibleCount === 0);
                });

                $('.permission-matrix__table tbody tr').each(function() {
                    const row = $(this);
                    const moduleName = normalizeText(row.find('td:first').text());
                    row.toggle(normalized === '' || moduleName.includes(normalized));
                });

                if (normalized !== '' && permissionMatrixBody.hasClass('is-collapsed')) {
                    permissionMatrixBody.removeClass('is-collapsed');
                    syncPermissionMatrixVisibility();
                }
            }

            function clearPermissions() {
                permissionCheckboxes.prop('checked', false);
                permissionCheckboxes.prop('indeterminate', false);
                $('.permission-matrix__toggle').prop('disabled', false);
                $('.permission-module').each(function() {
                    syncModuleActionAvailability(String($(this).data('module') || ''));
                });
                syncSelectedCount();
            }

            function syncPermissionMatrixVisibility() {
                const collapsed = permissionMatrixBody.hasClass('is-collapsed');
                permissionMatrixToggle.attr('aria-expanded', collapsed ? 'false' : 'true');
                permissionMatrixToggle.text(collapsed ? 'Mở rộng ma trận' : 'Thu gọn ma trận');
            }

            function syncAdminPermissionVisibility(initial = false) {
                const enabled = adminToggle.is(':checked');

                if (enabled) {
                    adminPermissionSection.removeClass('is-hidden').attr('aria-hidden', 'false');
                    $('.permission-checkbox, .permission-matrix__toggle, #select-all-permissions, #clear-all-permissions, .permission-select-module, .permission-clear-module, #permission-search')
                        .prop('disabled', false);
                    $('.permission-module').each(function() {
                        syncModuleActionAvailability(String($(this).data('module') || ''));
                    });
                    return;
                }

                clearPermissions();
                adminPermissionSection.addClass('is-hidden').attr('aria-hidden', 'true');
                $('.permission-checkbox, .permission-matrix__toggle, #select-all-permissions, #clear-all-permissions, .permission-select-module, .permission-clear-module, #permission-search')
                    .prop('disabled', true);
            }

            syncSelectedCount();
            syncAdminPermissionVisibility(true);
            syncPermissionMatrixVisibility();

            adminToggle.on('change', () => syncAdminPermissionVisibility());

            permissionMatrixToggle.on('click', function() {
                permissionMatrixBody.toggleClass('is-collapsed');
                syncPermissionMatrixVisibility();
            });

            $(document).on('click', '.permission-matrix__toggle', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                const permissionId = String($(this).data('permission-id'));
                const action = String($(this).data('action') || '');
                const moduleName = String($(this).data('module') || '');
                const checkbox = $('.permission-checkbox[value="' + permissionId + '"]');
                const nextState = !checkbox.is(':checked');

                if (action !== 'manage' && action !== 'view' && moduleName !== '' && !canAccessModuleActions(moduleName)) {
                    return;
                }

                if (action === 'manage' && moduleName !== '') {
                    toggleModulePermissions(moduleName, nextState);
                } else {
                    checkbox.prop('checked', nextState);
                    checkbox.prop('indeterminate', false);
                    syncManagePermissionState(moduleName);
                }

                if (moduleName !== '') {
                    syncModuleActionAvailability(moduleName);
                }
                syncSelectedCount();
            });

            $(document).on('change', '.permission-checkbox', function() {
                const checkbox = $(this);
                const moduleName = String(checkbox.data('module') || '');
                const slug = String(checkbox.data('slug') || '');
                const isManage = slug.endsWith('.manage');
                const isView = slug.endsWith('.view');

                if (!isManage && !isView && !canAccessModuleActions(moduleName)) {
                    checkbox.prop('checked', false);
                    syncModuleActionAvailability(moduleName);
                    syncSelectedCount();
                    return;
                }

                if (isManage) {
                    toggleModulePermissions(moduleName, checkbox.is(':checked'));
                } else {
                    syncManagePermissionState(moduleName);
                }

                syncModuleActionAvailability(moduleName);
                syncSelectedCount();
            });

            $(document).on('click', '.preset-role-trigger', function() {
                const preset = $(this).data('preset');

                if (!preset) {
                    return;
                }

                $('input[name="name"]').val(preset.name || '');
                $('input[name="slug"]').val(preset.slug || '');
                $('input[name="description"]').val(preset.description || '');
                $('#is_admin').prop('checked', !!preset.is_admin);

                permissionCheckboxes.prop('checked', false);

                (preset.permissions || []).forEach(function(permissionSlug) {
                    const checkbox = permissionCheckboxes.filter(function() {
                        return String($(this).data('slug') || '') === permissionSlug;
                    });

                    checkbox.prop('checked', true);
                });

                $('.permission-module').each(function() {
                    const moduleName = String($(this).data('module') || '');
                    syncManagePermissionState(moduleName);
                    syncModuleActionAvailability(moduleName);
                });

                syncAdminPermissionVisibility();
                syncSelectedCount();
            });

            $('#select-all-permissions').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                $('.permission-item:not(.is-hidden) .permission-checkbox').prop('checked', true);
                $('.permission-module').each(function() {
                    const moduleName = String($(this).data('module') || '');
                    syncManagePermissionState(moduleName);
                    syncModuleActionAvailability(moduleName);
                });
                syncSelectedCount();
            });

            $('#clear-all-permissions').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                clearPermissions();
            });

            $('.permission-select-module').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                const module = $(this).closest('.permission-module');
                const moduleName = String(module.data('module') || '');
                module.find('.permission-item:not(.is-hidden) .permission-checkbox').prop('checked', true);
                syncManagePermissionState(moduleName);
                syncModuleActionAvailability(moduleName);
                syncSelectedCount();
            });

            $('.permission-clear-module').on('click', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                const module = $(this).closest('.permission-module');
                const moduleName = String(module.data('module') || '');
                module.find('.permission-checkbox').prop('checked', false);
                syncManagePermissionState(moduleName);
                syncModuleActionAvailability(moduleName);
                syncSelectedCount();
            });

            $('#permission-search').on('input', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                filterPermissions($(this).val());
            });

            $('.permission-module').each(function() {
                syncModuleActionAvailability(String($(this).data('module') || ''));
            });
        });
    </script>
@endsection
