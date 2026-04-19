@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">{{ __('courses::teacher/messages.trash.title') }}</h5>
                    <p class="text-muted mb-0">{{ __('courses::teacher/messages.trash.description') }}</p>
                </div>
                <a href="{{ route('teacher.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    {{ __('courses::teacher/messages.trash.return') }}
                </a>
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

            @if (auth()->user()?->canAnyPermission(['teachers.soft_delete', 'teachers.delete', 'teachers.force_delete']))
                <form id="bulk-trash-action-form" action="{{ route('teacher.trash.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-trash-ids">
                    <input type="hidden" name="bulk_action" id="bulk-trash-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-trash-count">0</span> {{ __('courses::teacher/messages.trash.selected_count', ['count' => '']) }}
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->canAnyPermission(['teachers.soft_delete', 'teachers.delete']))
                                <button type="button" class="btn btn-success bulk-trash-action-trigger" data-action="restore">{{ __('courses::teacher/messages.trash.restore') }}</button>
                            @endif
                            @if (auth()->user()?->hasPermission('teachers.force_delete'))
                                <button type="button" class="btn btn-outline-danger bulk-trash-action-trigger" data-action="force_delete">{{ __('courses::teacher/messages.trash.force_delete') }}</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="trash-datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-trashed-records" class="form-check-input">
                            </th>
                            <th>{{ __('courses::teacher/messages.trash.table.image') }}</th>
                            <th>{{ __('courses::teacher/messages.trash.table.name') }}</th>
                            <th>{{ __('courses::teacher/messages.trash.table.deleted_at') }}</th>
                            <th>{{ __('courses::teacher/messages.trash.table.restore') }}</th>
                            <th>{{ __('courses::teacher/messages.trash.table.force_delete') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
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

        html[data-theme="dark"] .bulk-toolbar {
            background: #162033;
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar__summary {
            color: #cbd5e1;
        }

        html[data-theme="dark"] #trash-datatable tbody td,
        html[data-theme="dark"] #trash-datatable tbody a {
            color: #e2e8f0;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedIds = new Set();

            $('#trash-datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [3, 'desc']
                ],
                ajax: "{{ route('teacher.trash.data') }}",
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
                        data: 'deleted_at'
                    },
                    {
                        data: 'restore',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'force_delete',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    processing: "{{ __('courses::teacher/messages.trash.datatable.processing') }}",
                    search: "{{ __('courses::teacher/messages.trash.datatable.search') }}",
                    lengthMenu: "{{ __('courses::teacher/messages.trash.datatable.lengthMenu') }}",
                    info: "{{ __('courses::teacher/messages.trash.datatable.info') }}",
                    infoEmpty: "{{ __('courses::teacher/messages.trash.datatable.infoEmpty') }}",
                    infoFiltered: "{{ __('courses::teacher/messages.trash.datatable.infoFiltered') }}",
                    loadingRecords: "{{ __('courses::teacher/messages.trash.datatable.loadingRecords') }}",
                    zeroRecords: "{{ __('courses::teacher/messages.trash.datatable.zeroRecords') }}",
                    emptyTable: "{{ __('courses::teacher/messages.trash.datatable.emptyTable') }}",
                    paginate: {
                        previous: "{{ __('courses::teacher/messages.trash.datatable.paginate.previous') }}",
                        next: "{{ __('courses::teacher/messages.trash.datatable.paginate.next') }}"
                    },
                    aria: {
                        sortAscending: "{{ __('courses::teacher/messages.trash.datatable.aria.sortAscending') }}",
                        sortDescending: "{{ __('courses::teacher/messages.trash.datatable.aria.sortDescending') }}"
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

                $('#selected-trash-count').text(selectedIds.size);

                const visibleCheckboxes = $('.bulk-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-trashed-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible);
            }

            $('#trash-datatable').on('change', '.bulk-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-trashed-records').on('change', function() {
                $('.bulk-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-trashed-records').is(':checked')) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('.bulk-trash-action-trigger').on('click', function() {
                if (selectedIds.size === 0) {
                    alert("{{ __('courses::teacher/messages.trash.alert.none_selected') }}");
                    return;
                }

                if ($(this).data('action') === 'force_delete' && !confirm("{{ __('courses::teacher/messages.trash.confirm.force_delete') }}")) {
                    return;
                }

                $('#selected-trash-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-trash-action-input').val($(this).data('action'));
                $('#bulk-trash-action-form').trigger('submit');
            });
        });
    </script>
@endsection
