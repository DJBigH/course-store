<div class="cp-notes-container">
    <div class="cp-notes-form mb-4">
        <textarea id="note-content" class="cp-textarea mb-3" rows="3" placeholder="{{ __('lessons::clients/common.note_placeholder') }}"></textarea>
        <div class="d-flex justify-content-between align-items-center">
            <div class="cp-note-current-time">
                <i class="fa-regular fa-clock me-1"></i>
                <span id="current-video-time">00:00</span>
            </div>
            <button id="save-note-btn" class="cp-btn cp-btn-primary cp-btn-sm">
                <i class="fa-solid fa-plus me-1"></i>
                {{ __('lessons::clients/common.add_note') }}
            </button>
        </div>
    </div>

    <div id="notes-list" class="cp-notes-list">
        <div class="text-center py-5 opacity-50">
            <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
            <div class="small">{{ __('lessons::clients/common.loading_notes') }}</div>
        </div>
    </div>
</div>

<style>
    .cp-notes-container {
        padding: 1.25rem;
    }

    .cp-textarea {
        width: 100%;
        padding: 0.875rem;
        border-radius: 0.75rem;
        border: 1px solid var(--cp-border);
        background-color: var(--cp-sidebar-bg);
        color: var(--cp-text-main);
        font-size: 0.875rem;
        resize: none;
        transition: all 0.2s;
    }

    .cp-textarea:focus {
        outline: none;
        border-color: var(--cp-tab-active);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .cp-note-current-time {
        font-size: 0.8125rem;
        color: var(--cp-text-muted);
        font-weight: 500;
    }

    .cp-btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
    }
</style>
