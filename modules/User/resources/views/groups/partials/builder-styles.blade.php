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
            overflow: hidden;
            max-height: 6000px;
            opacity: 1;
            transform: translateY(0);
            transition: max-height 0.42s ease, opacity 0.28s ease, transform 0.28s ease;
        }

        .permission-builder__admin-section.is-hidden {
            max-height: 0;
            opacity: 0;
            transform: translateY(-12px);
            pointer-events: none;
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
            overflow: hidden;
            max-height: 1200px;
            opacity: 1;
            transform: translateY(0);
            transition: max-height 0.35s ease, opacity 0.22s ease, transform 0.22s ease;
        }

        .permission-matrix__body.is-collapsed {
            max-height: 0;
            opacity: 0;
            transform: translateY(-8px);
            pointer-events: none;
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

        .permission-item.is-hidden,
        .permission-module.is-hidden {
            display: none;
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
    </style>
@endsection
