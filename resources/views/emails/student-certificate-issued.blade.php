<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chung chi hoan thanh</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;">
        <div style="padding:24px 24px 8px;background:linear-gradient(135deg,#eff6ff,#f8fafc);">
            <div style="display:inline-block;padding:6px 12px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">
                Completion Certificate
            </div>
            <h1 style="margin:16px 0 8px;font-size:28px;line-height:1.2;">Ban vua duoc cap chung chi</h1>
            <p style="margin:0 0 12px;color:#475569;line-height:1.6;">
                Chung chi hoan thanh khoa hoc cua ban da san sang. Ban co the mo ngay de xem, in hoac luu PDF.
            </p>
        </div>

        <div style="padding:24px;">
            <div style="padding:18px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc;">
                <p style="margin:0 0 8px;color:#64748b;font-size:13px;">Hoc vien</p>
                <p style="margin:0 0 16px;font-size:20px;font-weight:700;">{{ $certificate->student_name_snapshot }}</p>

                <p style="margin:0 0 8px;color:#64748b;font-size:13px;">Khoa hoc</p>
                <p style="margin:0 0 16px;font-size:18px;font-weight:700;">{{ $certificate->course_name_snapshot }}</p>

                <p style="margin:0 0 8px;color:#64748b;font-size:13px;">Ma chung chi</p>
                <p style="margin:0 0 16px;font-size:16px;font-weight:700;">{{ $certificate->code }}</p>

                <p style="margin:0;color:#64748b;font-size:13px;">Giang vien cap</p>
                <p style="margin:8px 0 0;font-size:16px;font-weight:700;">{{ $certificate->teacher_name_snapshot }}</p>
            </div>

            <div style="margin-top:24px;">
                <a href="{{ $certificateUrl }}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">
                    Xem chung chi
                </a>
            </div>
        </div>
    </div>
</body>
</html>
