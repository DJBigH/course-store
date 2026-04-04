<style>
    .teacher-auth-shell {
        max-width: 560px;
        margin: 0 auto;
    }

    .teacher-auth-panel {
        padding: 2rem;
        border-radius: 28px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(244, 248, 255, 0.98) 100%);
        border: 1px solid rgba(37, 99, 235, 0.12);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.08);
    }

    html[data-theme="dark"] .teacher-auth-panel {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(17, 28, 51, 0.98) 100%);
        border-color: rgba(96, 165, 250, 0.2);
        box-shadow: 0 28px 60px rgba(2, 6, 23, 0.44);
    }

    .teacher-auth-badge {
        display: inline-flex;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.76rem;
        letter-spacing: 0.05em;
    }

    .teacher-auth-panel h1 {
        margin: 1rem 0 0.75rem;
        font-size: 2rem;
        font-weight: 900;
    }

    .teacher-auth-desc,
    .teacher-auth-footnote {
        color: #5f6f89;
    }

    html[data-theme="dark"] .teacher-auth-panel h1 {
        color: #e8f0ff;
    }

    html[data-theme="dark"] .teacher-auth-desc,
    html[data-theme="dark"] .teacher-auth-footnote {
        color: #a9bbd5;
    }

    .teacher-auth-form {
        display: grid;
        gap: 0.85rem;
        margin: 1.5rem 0;
    }

    .teacher-auth-alert {
        border-radius: 16px;
    }

    .teacher-auth-form label {
        font-weight: 700;
    }

    .teacher-auth-form input[type="email"],
    .teacher-auth-form input[type="password"] {
        min-height: 52px;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.28);
        background: #fff;
    }

    html[data-theme="dark"] .teacher-auth-form input[type="email"],
    html[data-theme="dark"] .teacher-auth-form input[type="password"] {
        color: #e8f0ff;
        border-color: rgba(96, 165, 250, 0.18);
        background: #0b1220;
    }

    .teacher-auth-check {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
    }

    .teacher-auth-links {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .teacher-auth-switcher {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .teacher-auth-switcher__card {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(37, 99, 235, 0.12);
        background: rgba(244, 248, 255, 0.92);
    }

    .teacher-auth-switcher__card h2 {
        margin: 0 0 0.45rem;
        font-size: 1rem;
        font-weight: 800;
    }

    .teacher-auth-switcher__card p {
        margin: 0 0 0.85rem;
        color: #5f6f89;
        font-size: 0.93rem;
        line-height: 1.6;
    }

    html[data-theme="dark"] .teacher-auth-switcher__card {
        border-color: rgba(96, 165, 250, 0.18);
        background: rgba(11, 18, 32, 0.92);
    }

    html[data-theme="dark"] .teacher-auth-switcher__card h2 {
        color: #e8f0ff;
    }

    html[data-theme="dark"] .teacher-auth-switcher__card p {
        color: #a9bbd5;
    }

    .teacher-auth-panel a {
        color: #2563eb;
    }

    @media (max-width: 767.98px) {
        .teacher-auth-switcher {
            grid-template-columns: 1fr;
        }
    }
</style>
