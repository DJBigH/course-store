@section('stylesheets')
    <style>
        .permission-builder__header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
        }

        .permission-builder__actions {
            min-width: min(100%, 320px);
            display: none;
        }

        .preset-panel {
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .preset-panel__header {
            margin-bottom: 1rem;
        }

        .permission-sync-alert {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .permission-builder__admin-section {
            overflow: visible;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.28s ease, transform 0.28s ease;
        }

        .permission-builder__admin-section.is-hidden {
            display: none;
        }

        .permission-builder__admin-state {
            display: none;
            margin: 0 1.5rem 1.5rem;
            padding: 1rem 1.1rem;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: #475569;
        }

        .permission-builder__admin-state.is-visible {
            display: block;
            animation: fadeInUp 0.25s ease;
        }

        .permission-matrix {
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .permission-matrix__header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .permission-matrix__collapse-toggle {
            border-radius: 999px;
            white-space: nowrap;
        }

        .permission-matrix__body {
            overflow: visible;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .permission-matrix__body.is-collapsed {
            display: none;
        }

        .permission-matrix__table th,
        .permission-matrix__table td {
            vertical-align: middle;
        }

        .permission-matrix__toggle {
            min-width: 88px;
            border-radius: 999px;
            border: 1px solid #dbe4f0;
            background: #fff;
            color: #475569;
        }

        .permission-matrix__toggle.is-active {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #fff;
        }

        .permission-matrix__toggle.is-partial {
            background: #fff7ed;
            border-color: #fb923c;
            color: #c2410c;
        }

        .permission-module {
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            margin-bottom: 1rem;
        }

        .permission-module__header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .permission-card {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            background: #fff;
            min-height: 100%;
            cursor: pointer;
        }

        .permission-card__body {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .permission-card__body small {
            color: #64748b;
        }

        .permission-builder__aside {
            position: sticky;
            top: 96px;
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            padding: 1.25rem;
            background: #fff;
        }

        .permission-summary {
            padding: 1rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        }

        .permission-summary__number {
            font-size: 2rem;
            font-weight: 800;
            color: #1d4ed8;
            line-height: 1;
            margin-bottom: 0.35rem;
        }

        html[data-theme='dark'] .preset-panel,
        html[data-theme='dark'] .permission-matrix,
        html[data-theme='dark'] .permission-module,
        html[data-theme='dark'] .permission-builder__aside {
            border-color: rgba(148, 163, 184, 0.18);
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(17, 24, 39, 0.92) 100%);
            box-shadow: 0 18px 36px rgba(2, 6, 23, 0.24);
        }

        html[data-theme='dark'] .permission-builder__admin-state {
            border-color: rgba(148, 163, 184, 0.24);
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.92) 100%);
            color: #cbd5e1;
        }

        html[data-theme='dark'] .permission-card {
            border-color: rgba(148, 163, 184, 0.18);
            background: rgba(15, 23, 42, 0.88);
            color: #e2e8f0;
        }

        html[data-theme='dark'] .permission-card__body small,
        html[data-theme='dark'] .preset-panel .text-muted,
        html[data-theme='dark'] .permission-matrix .text-muted,
        html[data-theme='dark'] .permission-builder__aside .text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme='dark'] .permission-matrix__table {
            color: #e2e8f0;
        }

        html[data-theme='dark'] .permission-matrix__table thead th {
            color: #cbd5e1;
            border-bottom-color: rgba(148, 163, 184, 0.2);
            background: rgba(30, 41, 59, 0.72);
        }

        html[data-theme='dark'] .permission-matrix__table td {
            border-color: rgba(148, 163, 184, 0.14);
        }

        html[data-theme='dark'] .permission-matrix__toggle {
            border-color: #dbe4f0;
            background: #ffffff;
            color: #475569;
            box-shadow: none;
        }

        html[data-theme='dark'] .permission-matrix__toggle.is-active {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
            box-shadow: none;
        }

        .permission-item.is-hidden,
        .permission-module.is-hidden {
            display: none;
        }

        html[data-theme='dark'] .permission-matrix__toggle:hover,
        html[data-theme='dark'] .preset-role-trigger:hover,
        html[data-theme='dark'] #select-all-permissions:hover,
        html[data-theme='dark'] #clear-all-permissions:hover,
        html[data-theme='dark'] .permission-matrix__collapse-toggle:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #334155;
        }

        html[data-theme='dark'] .permission-matrix__toggle.is-partial {
            background: rgba(124, 45, 18, 0.28);
            border-color: rgba(251, 146, 60, 0.62);
            color: #fdba74;
        }

        html[data-theme='dark'] .permission-matrix__toggle:disabled,
        html[data-theme='dark'] .permission-matrix__toggle[disabled] {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(203, 213, 225, 0.18);
            color: rgba(226, 232, 240, 0.35);
            box-shadow: none;
            opacity: 1;
        }

        html[data-theme='dark'] .permission-summary {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid rgba(96, 165, 250, 0.14);
        }

        html[data-theme='dark'] .permission-summary__number {
            color: #60a5fa;
        }

        html[data-theme='dark'] .preset-role-trigger,
        html[data-theme='dark'] #select-all-permissions,
        html[data-theme='dark'] #clear-all-permissions,
        html[data-theme='dark'] .permission-matrix__collapse-toggle {
            border-color: rgba(148, 163, 184, 0.22) !important;
            background: rgba(15, 23, 42, 0.84) !important;
            color: #e2e8f0 !important;
        }

        html[data-theme='dark'] .preset-panel h6,
        html[data-theme='dark'] .permission-matrix h6,
        html[data-theme='dark'] .permission-builder__aside h6,
        html[data-theme='dark'] .permission-matrix__table .fw-semibold,
        html[data-theme='dark'] .permission-matrix__table td,
        html[data-theme='dark'] .permission-matrix__table th {
            color: #e2e8f0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1199.98px) {
            .permission-builder__aside {
                position: static;
                top: auto;
            }
        }

        @media (max-width: 767.98px) {
            .permission-builder .row.p-4,
            .permission-builder .row.px-4.pb-4 {
                --bs-gutter-x: 1rem;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .permission-builder__header {
                align-items: stretch;
            }

            .preset-panel,
            .permission-matrix,
            .permission-module,
            .permission-builder__aside {
                padding: 1rem;
                border-radius: 18px;
            }

            .permission-matrix__header {
                align-items: stretch;
            }

            .permission-matrix__collapse-toggle,
            .preset-role-trigger,
            #select-all-permissions,
            #clear-all-permissions,
            .admin-form__footer .btn {
                width: 100%;
            }

            .permission-matrix__toggle {
                min-width: 76px;
                padding-inline: 0.75rem;
            }
        }
        .permission-matrix__module-card .card {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        html[data-theme='dark'] .permission-matrix__module-card .card {
            border-color: rgba(255, 255, 255, 0.1);
            background: rgba(30, 41, 59, 0.5);
        }

        html[data-theme='dark'] .permission-matrix__module-card .card-header {
            background-color: rgba(15, 23, 42, 0.8) !important;
            border-bottom-color: rgba(255, 255, 255, 0.05) !important;
        }

        html[data-theme='dark'] .permission-matrix__module-card .card-header h6 {
            color: #f1f5f9 !important;
        }
    </style>
@endsection
