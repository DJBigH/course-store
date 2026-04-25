<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\src\Http\Requests\TeacherAnnouncementRequest;
use Modules\Teacher\src\Models\TeacherAnnouncement;
use Modules\Packages\src\Models\Package;

class TeacherAnnouncementController extends Controller
{
    public function index()
    {
        $pageTitle = __('teacher::admin.titles.announcements');
        $announcements = TeacherAnnouncement::query()
            ->with('packages')
            ->orderByDesc('is_pinned')
            ->latest('id')
            ->get();

        return view('teacher::announcements.lists', compact('pageTitle', 'announcements'));
    }

    public function create()
    {
        $pageTitle = __('teacher::admin.titles.create_announcement');
        $announcement = new TeacherAnnouncement([
            'status' => true,
            'is_pinned' => false,
            'icon' => 'fas fa-bullhorn',
        ]);
        $packages = Package::query()->orderBy('sort_order')->get();
        $selectedPackageIds = [];

        return view('teacher::announcements.create', compact('pageTitle', 'announcement', 'packages', 'selectedPackageIds'));
    }

    public function store(TeacherAnnouncementRequest $request)
    {
        $announcement = DB::transaction(function () use ($request) {
            $announcement = TeacherAnnouncement::query()->create($this->payload($request));
            $announcement->packages()->sync($request->input('package_ids', []));

            return $announcement;
        });

        return redirect()->route('teacher-announcements.edit', $announcement->id)
            ->with('msg', __('teacher::admin.messages.announcement_create_success'));
    }

    public function edit(int $id)
    {
        $pageTitle = __('teacher::admin.titles.edit_announcement');
        $announcement = TeacherAnnouncement::query()->with('packages')->findOrFail($id);
        $packages = Package::query()->orderBy('sort_order')->get();
        $selectedPackageIds = $announcement->packages->pluck('id')->map(fn ($id) => (int) $id)->all();

        return view('teacher::announcements.edit', compact('pageTitle', 'announcement', 'packages', 'selectedPackageIds'));
    }

    public function update(TeacherAnnouncementRequest $request, int $id)
    {
        $announcement = TeacherAnnouncement::query()->findOrFail($id);

        DB::transaction(function () use ($request, $announcement) {
            $announcement->update($this->payload($request));
            $announcement->packages()->sync($request->input('package_ids', []));
        });

        return redirect()->route('teacher-announcements.edit', $announcement->id)
            ->with('msg', __('teacher::admin.messages.announcement_update_success'));
    }

    public function delete(int $id)
    {
        TeacherAnnouncement::query()->findOrFail($id)->delete();

        return redirect()->route('teacher-announcements.index')
            ->with('msg', __('teacher::admin.messages.announcement_delete_success'));
    }

    private function payload(TeacherAnnouncementRequest $request): array
    {
        return [
            'title' => $request->string('title')->toString(),
            'title_en' => $request->string('title_en')->toString() ?: null,
            'title_ko' => $request->string('title_ko')->toString() ?: null,
            'title_ja' => $request->string('title_ja')->toString() ?: null,
            'title_zh' => $request->string('title_zh')->toString() ?: null,
            'message' => $request->string('message')->toString(),
            'message_en' => $request->string('message_en')->toString() ?: null,
            'message_ko' => $request->string('message_ko')->toString() ?: null,
            'message_ja' => $request->string('message_ja')->toString() ?: null,
            'message_zh' => $request->string('message_zh')->toString() ?: null,
            'action_url' => $request->string('action_url')->toString() ?: null,
            'action_label' => $request->string('action_label')->toString() ?: null,
            'action_label_en' => $request->string('action_label_en')->toString() ?: null,
            'action_label_ko' => $request->string('action_label_ko')->toString() ?: null,
            'action_label_ja' => $request->string('action_label_ja')->toString() ?: null,
            'action_label_zh' => $request->string('action_label_zh')->toString() ?: null,
            'icon' => $request->string('icon')->toString() ?: 'fas fa-bullhorn',
            'status' => $request->boolean('status'),
            'is_pinned' => $request->boolean('is_pinned'),
            'starts_at' => $request->date('starts_at'),
            'ends_at' => $request->date('ends_at'),
            'created_by' => auth()->id(),
        ];
    }
}
