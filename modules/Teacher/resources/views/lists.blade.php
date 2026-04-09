@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sÃƒÂ¡ch giÃ¡ÂºÂ£ng viÃƒÂªn</h5>
                    <p class="text-muted mb-0">QuÃ¡ÂºÂ£n lÃƒÂ½ hÃ¡Â»â€œ sÃ†Â¡ giÃ¡ÂºÂ£ng viÃƒÂªn, kinh nghiÃ¡Â»â€¡m vÃƒÂ  Ã¡ÂºÂ£nh Ã„â€˜Ã¡ÂºÂ¡i diÃ¡Â»â€¡n theo layout admin mÃ¡Â»â€ºi.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()
                            ?->canAnyPermission(['teachers.soft_delete', 'teachers.delete', 'teachers.force_delete']))
                        <a href="{{ route('teacher.trash') }}" class="btn btn-light border">
                            <i class="fa-solid fa-trash-can me-2"></i>
                            ThÃƒÂ¹ng rÃƒÂ¡c
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('teachers.create'))
                        <a href="{{ route('teacher.add') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-2"></i>
                            ThÃƒÂªm giÃ¡ÂºÂ£ng viÃƒÂªn
                        </a>
                    @endif
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger border-0 rounded-4">{{ session('msg_danger') }}</div>
            @endif
            @if ($errors->has('bulk_action'))
                <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('bulk_action') }}</div>
            @endif

            <form id="teacher-filter-form" class="admin-filter-panel mb-4">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label">TÃ¡Â»Â« khÃƒÂ³a</label>
                        <input type="text" class="form-control" name="q" id="filter-q"
                            placeholder="TÃƒÂªn, slug, kinh nghiÃ¡Â»â€¡m...">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">TrÃ¡ÂºÂ¡ng thÃƒÂ¡i hÃ¡Â»â€œ sÃ†Â¡</label>
                        <select class="form-select" name="profile_status" id="filter-profile-status">
                            <option value="">TÃ¡ÂºÂ¥t cÃ¡ÂºÂ£</option>
                            <option value="has_image">Ã„ÂÃƒÂ£ cÃƒÂ³ Ã¡ÂºÂ£nh Ã„â€˜Ã¡ÂºÂ¡i diÃ¡Â»â€¡n</option>
                            <option value="missing_image">ChÃ†Â°a cÃƒÂ³ Ã¡ÂºÂ£nh Ã„â€˜Ã¡ÂºÂ¡i diÃ¡Â»â€¡n</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Hoáº¡t Ä‘á»™ng gáº§n nháº¥t</label>
                        <select class="form-select" name="activity_status" id="filter-activity-status">
                            <option value="">Táº¥t cáº£</option>
                            <option value="active_30">CÃ³ hoáº¡t Ä‘á»™ng trong 30 ngÃ y</option>
                            <option value="inactive_30">KhÃ´ng hoáº¡t Ä‘á»™ng tá»« 30 ngÃ y</option>
                            <option value="inactive_60">KhÃ´ng hoáº¡t Ä‘á»™ng tá»« 60 ngÃ y</option>
                            <option value="never_active">ChÆ°a cÃ³ hoáº¡t Ä‘á»™ng nÃ o</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">TÃ¡Â»Â« ngÃƒÂ y</label>
                        <input type="date" class="form-control" name="from_date" id="filter-from-date">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Ã„ÂÃ¡ÂºÂ¿n ngÃƒÂ y</label>
                        <input type="date" class="form-control" name="to_date" id="filter-to-date">
                    </div>
                    <div class="col-lg-1 col-md-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">LÃ¡Â»Âc</button>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-light border" id="reset-filters">XÃƒÂ³a lÃ¡Â»Âc</button>
                </div>
            </form>

            @if (auth()->user()
                    ?->canAnyPermission(['teachers.soft_delete', 'teachers.delete']))
                <form id="bulk-action-form" action="{{ route('teacher.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> giÃ¡ÂºÂ£ng viÃƒÂªn Ã„â€˜Ã†Â°Ã¡Â»Â£c chÃ¡Â»Ân
                        </div>
                        <button type="button" class="btn btn-outline-danger bulk-action-trigger"
                            data-action="delete">XÃƒÂ³a</button>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-records" class="form-check-input">
                            </th>
                            <th>Ã¡ÂºÂ¢nh</th>
                            <th>TÃƒÂªn</th>
                            <th>Kinh nghiÃ¡Â»â€¡m</th>
                            <th>NgÃƒÂ y tÃ¡ÂºÂ¡o</th>
                            <th>Hoáº¡t Ä‘á»™ng gáº§n nháº¥t</th>
                            <th>KhÃ´ng hoáº¡t Ä‘á»™ng</th>
                            <th>LÃ¡Â»â€¹ch sÃ¡Â»Â­</th>
                            <th>SÃ¡Â»Â­a</th>
                            <th>XÃƒÂ³a</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('part.backend.delete')
@endsection

@section('stylesheets')
    <style>
        .admin-filter-panel {
            padding: 1.1rem;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .bulk-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .bulk-toolbar__summary {
            font-weight: 600;
            color: #334155;
        }

        html[data-theme="dark"] .admin-filter-panel {
            background: linear-gradient(180deg, #162033 0%, #111827 100%);
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar {
            background: #162033;
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar__summary {
            color: #cbd5e1;
        }

        html[data-theme="dark"] #datatable tbody td,
        html[data-theme="dark"] #datatable tbody a {
            color: #e2e8f0;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedIds = new Set();

            const table = $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: "{{ route('teacher.data') }}",
                    data: function(d) {
                        d.q = $('#filter-q').val();
                        d.profile_status = $('#filter-profile-status').val();
                        d.activity_status = $('#filter-activity-status').val();
                        d.from_date = $('#filter-from-date').val();
                        d.to_date = $('#filter-to-date').val();
                    }
                },
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'exp'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'last_active_at',
                        searchable: false
                    },
                    {
                        data: 'inactive_days',
                        searchable: false
                    },
                    {
                        data: 'logs'
                    },
                    {
                        data: 'edit'
                    },
                    {
                        data: 'delete'
                    }
                ],
                language: {
                    processing: 'Äang xá»­ lÃ½...',
                    search: 'TÃ¬m kiáº¿m:',
                    lengthMenu: 'Hiá»ƒn thá»‹ _MENU_ báº£n ghi',
                    info: 'Hiá»ƒn thá»‹ tá»« _START_ Ä‘áº¿n _END_ cá»§a _TOTAL_ báº£n ghi',
                    infoEmpty: 'Hiá»ƒn thá»‹ 0 Ä‘áº¿n 0 cá»§a 0 báº£n ghi',
                    infoFiltered: '(lá»c tá»« _MAX_ báº£n ghi)',
                    loadingRecords: 'Äang táº£i...',
                    zeroRecords: 'KhÃ´ng tÃ¬m tháº¥y báº£n ghi nÃ o',
                    emptyTable: 'KhÃ´ng cÃ³ dá»¯ liá»‡u trong báº£ng',
                    paginate: {
                        previous: 'TrÆ°á»›c',
                        next: 'Tiáº¿p'
                    },
                    aria: {
                        sortAscending: ': sáº¯p xáº¿p tÄƒng dáº§n',
                        sortDescending: ': sáº¯p xáº¿p giáº£m dáº§n'
                    }
                },
                drawCallback: function() {
                    syncCheckboxState();
                }
            });

            function syncCheckboxState() {
                $('.bulk-row-checkbox').each(function() {
                    $(this).prop('checked', selectedIds.has($(this).val()));
                });

                $('#selected-count').text(selectedIds.size);

                const visibleCheckboxes = $('.bulk-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes
                    .length === checkedVisible);
            }

            $('#teacher-filter-form').on('submit', function(event) {
                event.preventDefault();
                table.ajax.reload();
            });

            $('#reset-filters').on('click', function() {
                $('#teacher-filter-form')[0].reset();
                table.ajax.reload();
            });

            $('#datatable').on('change', '.bulk-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-records').on('change', function() {
                $('.bulk-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-records').is(':checked')) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('.bulk-action-trigger').on('click', function() {
                if (selectedIds.size === 0) {
                    alert('Vui lÃ²ng chá»n Ã­t nháº¥t má»™t giáº£ng viÃªn.');
                    return;
                }

                if ($(this).data('action') === 'delete' && !confirm('XÃ³a cÃ¡c giáº£ng viÃªn Ä‘Ã£ chá»n?')) {
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
