<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Teacher Students Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-weight: 700;
            text-align: left;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Hoc vien</th>
                <th>Email</th>
                <th>So dien thoai</th>
                <th>Dia chi</th>
                <th>Tag noi bo</th>
                <th>Khoa hoc da mua</th>
                <th>So don thanh toan</th>
                <th>Tong chi tieu</th>
                <th>Lan mua gan nhat</th>
                <th>Hoc gan nhat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->phone }}</td>
                    <td>{{ $student->address }}</td>
                    <td>
                        {{ match ($student->teacher_tag) {
                            'potential' => 'Tiem nang',
                            'support_needed' => 'Can ho tro',
                            'vip' => 'VIP',
                            default => '',
                        } }}
                    </td>
                    <td>{{ $student->teacher_courses_all->map(fn ($course) => $course->name_locale ?: $course->name)->implode(' | ') }}</td>
                    <td>{{ $student->teacher_order_count }}</td>
                    <td>{{ $student->teacher_total_spent }}</td>
                    <td>{{ optional($student->teacher_last_purchase_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ optional($student->teacher_last_learning_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Khong co hoc vien nao de export.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
