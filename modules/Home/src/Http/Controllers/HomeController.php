<?php

namespace Modules\Home\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;

class HomeController extends Controller
{
    protected $courseRepository;
    protected $studentRepository;

    public function __construct(CoursesRepositoryInterface $coursesRepository, StudentsRepositoryInterface $studentsRepository)
    {
        $this->courseRepository = $coursesRepository;
        $this->studentRepository = $studentsRepository;
    }

    public function index(Request $request)
    {
        $pageTitle = __('home::common.pageTile');
        $courseFree = $this->courseRepository->getCourseFree();
        $courseView = $this->courseRepository->getCourseView();
        $courseNew = $this->courseRepository->getCourseCreateUpdate();

        $studentId = Auth::guard('students')->id();
        $myCourse = collect();

        if ($studentId) {
            $student = Auth::guard('students')->user();
            $ownTeacherId = $student->teacher ? $student->teacher->id : null;
            
            $myCourse = \Modules\Courses\src\Models\Courses::query()
                ->with('teacher')
                ->where(function($query) use ($studentId, $ownTeacherId) {
                    $query->whereHas('students', function($q) use ($studentId) {
                        $q->where('students.id', $studentId)->where('students_courses.status', 1);
                    });
                    
                    if ($ownTeacherId) {
                        $query->orWhere('teacher_id', $ownTeacherId);
                    }
                })
                ->latest('created_at')
                ->paginate(config('paginate.home_mycourse_limit'))
                ->withQueryString();
        }

        if ($request->ajax()) {
            return view('home::my_course_home', compact('myCourse'))->render();
        }

        $courseAll = $this->courseRepository->getCourseForYou($studentId);

        return view('home::index', compact(
            'pageTitle',
            'courseFree',
            'courseView',
            'courseNew',
            'myCourse',
            'courseAll'
        ));
    }

    public function studentSupport()
    {
        $pageTitle = trans('home::clients/static_pages.support.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.support.hero_badge');
        $heroHeading = trans('home::clients/static_pages.support.hero_heading');
        $heroDescription = trans('home::clients/static_pages.support.hero_description');
        $contactPanelTitle = trans('home::clients/static_pages.support.contact_panel_title');
        $flowPanelTitle = trans('home::clients/static_pages.support.flow_panel_title');
        $supportChannels = trans('home::clients/static_pages.support.channels');
        $contactCards = trans('home::clients/static_pages.support.contacts');
        $supportFlow = trans('home::clients/static_pages.support.flow');

        return view('home::student_support', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'contactPanelTitle',
            'flowPanelTitle',
            'supportChannels',
            'contactCards',
            'supportFlow'
        ));
    }

    public function about()
    {
        $pageTitle = trans('home::clients/static_pages.about.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.about.hero_badge');
        $heroHeading = trans('home::clients/static_pages.about.hero_heading');
        $heroDescription = trans('home::clients/static_pages.about.hero_description');
        $highlights = trans('home::clients/static_pages.about.highlights');
        $commitmentsTitle = trans('home::clients/static_pages.about.commitments_title');
        $commitments = trans('home::clients/static_pages.about.commitments');
        $contactNoteTitle = trans('home::clients/static_pages.about.contact_note_title');
        $contactNoteDescription = trans('home::clients/static_pages.about.contact_note_description');
        $contactNoteMeta = trans('home::clients/static_pages.about.contact_note_meta');

        return view('home::about_page', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'highlights',
            'commitmentsTitle',
            'commitments',
            'contactNoteTitle',
            'contactNoteDescription',
            'contactNoteMeta'
        ));
    }

    public function faq()
    {
        $pageTitle = trans('home::clients/static_pages.faq.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.faq.hero_badge');
        $heroHeading = trans('home::clients/static_pages.faq.hero_heading');
        $heroDescription = trans('home::clients/static_pages.faq.hero_description');
        $faqs = trans('home::clients/static_pages.faq.items');

        return view('home::faq', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'faqs'
        ));
    }

    public function testimonials()
    {
        $pageTitle = trans('home::clients/static_pages.testimonials.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.testimonials.hero_badge');
        $heroDescription = trans('home::clients/static_pages.testimonials.hero_description');
        $highlightStats = trans('home::clients/static_pages.testimonials.highlight_stats');
        $testimonials = trans('home::clients/static_pages.testimonials.items');

        return view('home::testimonials', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroDescription',
            'highlightStats',
            'testimonials'
        ));
    }

    public function paymentPolicy()
    {
        $pageTitle = trans('home::clients/static_pages.payment_policy.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.payment_policy.hero_badge');
        $heroHeading = trans('home::clients/static_pages.payment_policy.hero_heading');
        $heroDescription = trans('home::clients/static_pages.payment_policy.hero_description');
        $sections = trans('home::clients/static_pages.payment_policy.sections');

        return view('home::payment_policy', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'sections'
        ));
    }

    public function refundPolicy()
    {
        $pageTitle = trans('home::clients/static_pages.refund_policy.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.refund_policy.hero_badge');
        $heroHeading = trans('home::clients/static_pages.refund_policy.hero_heading');
        $heroDescription = trans('home::clients/static_pages.refund_policy.hero_description');
        $sections = trans('home::clients/static_pages.refund_policy.sections');

        return view('home::refund_policy', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'sections'
        ));
    }

    public function termsOfService()
    {
        $pageTitle = trans('home::clients/static_pages.terms_of_service.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.terms_of_service.hero_badge');
        $heroHeading = trans('home::clients/static_pages.terms_of_service.hero_heading');
        $heroDescription = trans('home::clients/static_pages.terms_of_service.hero_description');
        $sections = trans('home::clients/static_pages.terms_of_service.sections');

        return view('home::terms_of_service', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'sections'
        ));
    }

    public function privacyPolicy()
    {
        $pageTitle = trans('home::clients/static_pages.privacy_policy.page_title');
        $pageName = $pageTitle;
        $heroBadge = trans('home::clients/static_pages.privacy_policy.hero_badge');
        $heroHeading = trans('home::clients/static_pages.privacy_policy.hero_heading');
        $heroDescription = trans('home::clients/static_pages.privacy_policy.hero_description');
        $sections = trans('home::clients/static_pages.privacy_policy.sections');

        return view('home::privacy_policy', compact(
            'pageTitle',
            'pageName',
            'heroBadge',
            'heroHeading',
            'heroDescription',
            'sections'
        ));
    }
}
