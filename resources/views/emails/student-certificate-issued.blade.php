@extends('emails.layouts.client', [
    'mailEyebrow' => __('courses::teacher/messages.certificates.mail.eyebrow'),
    'mailHeading' => __('courses::teacher/messages.certificates.mail.heading'),
    'mailSubtitle' => __('courses::teacher/messages.certificates.mail.subheading'),
])

@section('mail_content')
    <div style="margin-bottom: 24px;">
        <div style="padding: 24px; border: 1px solid #eef2f7; border-radius: 16px; background: #fbfdff;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding-bottom: 16px;">
                        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                            {{ __('courses::teacher/messages.certificates.mail.student_label') }}
                        </div>
                        <div style="font-size: 18px; font-weight: 800; color: #111827;">
                            {{ $certificate->student_name_snapshot }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 16px;">
                        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                            {{ __('courses::teacher/messages.certificates.mail.course_label') }}
                        </div>
                        <div style="font-size: 16px; font-weight: 700; color: #2563eb;">
                            {{ $certificate->course_name_snapshot }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 16px;">
                        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                            {{ __('courses::teacher/messages.certificates.mail.code_label') }}
                        </div>
                        <div style="font-size: 14px; font-family: monospace; font-weight: 700; color: #111827; background: #f3f5f9; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                            {{ $certificate->code }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">
                            {{ __('courses::teacher/messages.certificates.mail.teacher_label') }}
                        </div>
                        <div style="font-size: 15px; font-weight: 700; color: #111827;">
                            {{ $certificate->teacher_name_snapshot }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ $certificateUrl }}" style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 800; padding: 14px 32px; border-radius: 999px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">
            {{ __('courses::teacher/messages.certificates.mail.button_view') }}
        </a>
    </div>

    <div style="border-top: 1px solid #eef2f7; padding-top: 20px; font-size: 14px; color: #6b7280; line-height: 1.6; font-style: italic;">
        {{ __('courses::teacher/messages.certificates.mail.footer_note') }}
    </div>
@endsection
