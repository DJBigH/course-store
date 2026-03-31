<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Modules\Teacher\src\Models\TeacherPackage;

class TeacherLandingController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $content = $this->contentForLocale($locale);
        $pageTitle = $content['page_title'];
        $pageName = $content['page_title'];
        $packages = TeacherPackage::query()->where('status', true)->orderBy('sort_order')->get();

        return view('teacher::clients.landing', compact('pageTitle', 'pageName', 'packages', 'content'));
    }

    private function contentForLocale(string $locale): array
    {
        $content = [
            'vi' => [
                'page_title' => 'Trở thành giảng viên',
                'hero' => [
                    'eyebrow' => 'Teacher Program',
                    'title' => 'Biến kiến thức của bạn thành một kênh giảng dạy có doanh thu, có màu sắc và có người nhớ tên.',
                    'description' => 'Nếu bạn đã từng nghĩ “mình dạy cũng ổn đấy”, thì đây là lúc thôi nghĩ và bắt đầu làm thật. BigK Udemy giúp bạn mở kênh giảng viên bài bản: có hồ sơ, có gói phù hợp, có admin duyệt và có không gian để phát triển nghiêm túc chứ không phải đăng cho vui rồi để đó.',
                    'primary' => 'Bắt đầu đăng ký',
                    'secondary' => 'Xem các gói',
                    'chips' => [
                        'Đăng ký gọn, duyệt rõ ràng',
                        'Free vẫn được đăng 2 khóa',
                        'Có thể nâng cấp khi kênh bắt đầu nóng máy',
                    ],
                    'panel_title' => 'Một flow đủ nghiêm túc để đi đường dài',
                    'panel_items' => [
                        'Tạo hồ sơ giảng viên chỉn chu, không cần màu mè quá tay nhưng phải đủ thuyết phục.',
                        'Chọn gói phù hợp với giai đoạn của bạn: thử sức nhẹ nhàng hoặc vào việc nghiêm túc ngay.',
                        'Admin xem xét, phản hồi, duyệt hồ sơ và mở quyền kênh giảng viên khi mọi thứ đã ổn.',
                    ],
                ],
                'stats' => [
                    ['value' => '2 khóa', 'label' => 'Miễn phí vẫn được bắt đầu, không bị “vào cho vui” rồi đứng nhìn.'],
                    ['value' => '70%', 'label' => 'Mức chia doanh thu tối đa ở gói Pro cho người muốn làm bài bản.'],
                    ['value' => '1 kênh riêng', 'label' => 'Một không gian tách biệt để bạn quản lý hồ sơ, doanh thu và rút tiền.'],
                ],
                'highlights' => [
                    [
                        'title' => 'Bắt đầu nhỏ, không bắt bạn “all-in” ngay',
                        'description' => 'Gói Free cho đăng 2 khóa để bạn thử sức thật sự. Không cần đầu tư ầm ầm ngày đầu, chỉ cần nội dung đủ tốt để khiến học viên muốn quay lại.',
                    ],
                    [
                        'title' => 'Có lộ trình nâng cấp khi bạn bắt đầu có đà',
                        'description' => 'Khi khóa học bắt đầu có học viên, bạn có thể lên gói cao hơn để mở rộng số lượng khóa, tăng commission và được ưu tiên hơn trong quá trình duyệt.',
                    ],
                    [
                        'title' => 'Không phải “đăng đại rồi cầu may”',
                        'description' => 'Hệ thống có bước duyệt hồ sơ để bảo đảm chất lượng giảng viên. Nghe nghiêm thì đúng là nghiêm, nhưng mục tiêu là giúp kênh của bạn đi bền chứ không làm khó cho vui.',
                    ],
                ],
                'journey' => [
                    'title' => 'Hành trình lên kênh giảng viên',
                    'description' => 'Rõ ràng từng bước để bạn biết mình đang ở đâu, cần làm gì tiếp theo và khi nào có thể bắt đầu đăng khóa.',
                    'steps' => [
                        ['title' => 'Điền hồ sơ', 'description' => 'Giới thiệu bạn là ai, dạy gì, có gì đáng tin. Ngắn gọn thôi, nhưng đừng ngắn tới mức ai đọc cũng thấy “ủa rồi sao nữa?”.'],
                        ['title' => 'Chọn gói', 'description' => 'Free để thử lực, Starter để tăng tốc, Pro cho người đã xác định nghiêm túc với việc xây kênh.'],
                        ['title' => 'Chờ duyệt', 'description' => 'Admin xem hồ sơ, gói đã chọn và tính phù hợp. Nếu cần chỉnh, bạn vẫn có thể cập nhật để hồ sơ tốt hơn.'],
                        ['title' => 'Vào kênh riêng', 'description' => 'Khi được duyệt, bạn có portal riêng để theo dõi khóa học, doanh thu và các yêu cầu rút tiền.'],
                    ],
                ],
                'earnings' => [
                    'title' => 'Kiếm tiền kiểu tử tế, không mơ hồ',
                    'description' => 'Mỗi gói có mức commission khác nhau, nhưng logic luôn rõ: doanh thu được ghi nhận, đối soát và hiển thị để bạn biết kênh của mình đang vận hành ra sao.',
                    'points' => [
                        'Bạn nhìn thấy doanh thu gộp, phần giảm giá phân bổ, phần thực nhận tạm tính và số dư khả dụng.',
                        'Gói càng cao, quyền lợi càng mở và mức chia doanh thu càng tốt hơn.',
                        'Không hứa “ngồi chơi cũng ra đơn”, nhưng hứa là dữ liệu đủ rõ để bạn biết nên tối ưu chỗ nào.',
                    ],
                ],
                'packages' => [
                    'title' => 'Chọn gói đúng nhịp, đừng chọn theo cảm xúc lúc 2 giờ sáng',
                    'description' => 'Mỗi gói phù hợp với một giai đoạn khác nhau. Chọn cái đủ dùng trước, rồi nâng cấp khi kênh bắt đầu chạy ngon hơn.',
                    'cta' => 'Đăng ký với gói này',
                    'most_popular' => 'Được chọn nhiều',
                    'meta_free' => 'Phù hợp để bắt đầu gọn nhẹ',
                    'meta_paid' => 'Dành cho người muốn tăng tốc nghiêm túc',
                    'features' => [
                        'commission' => 'Chia doanh thu :rate%',
                        'course_limit' => 'Đăng tối đa :count khóa',
                        'unlimited' => 'Đăng khóa học không giới hạn',
                        'priority_yes' => 'Ưu tiên duyệt hồ sơ',
                        'priority_no' => 'Duyệt theo hàng chờ tiêu chuẩn',
                        'support' => 'Hỗ trợ :level',
                    ],
                    'descriptions' => [
                        'free' => 'Bước vào sân chơi bằng một gói miễn phí nhưng vẫn đủ đất diễn: đăng 2 khóa, test nội dung và xem học viên phản ứng ra sao.',
                        'starter' => 'Khi bạn đã sẵn sàng làm nghiêm túc hơn, Starter mở thêm số khóa, nâng mức chia doanh thu và giúp kênh trông có tương lai hơn hẳn.',
                        'pro' => 'Dành cho người không muốn chơi nửa vời: mở rộng gần như toàn bộ quyền, ưu tiên duyệt và mức chia doanh thu tốt nhất hiện tại.',
                    ],
                ],
                'faq' => [
                    'title' => 'Câu hỏi thường gặp',
                    'items' => [
                        [
                            'question' => 'Tôi chưa từng dạy online, có bắt đầu được không?',
                            'answer' => 'Có. Gói Free sinh ra để bạn thử sức thật sự. Điều quan trọng không phải bạn nói thật “mượt”, mà là nội dung của bạn có giúp học viên tiến bộ hay không.',
                        ],
                        [
                            'question' => 'Vì sao cần admin duyệt?',
                            'answer' => 'Để chất lượng giảng viên trên nền tảng không bị loãng. Nói vui là để học viên không gặp kiểu khóa học “mở đầu rất hùng hồn, kết thúc rất mông lung”.',
                        ],
                        [
                            'question' => 'Sau này tôi có thể nâng cấp gói không?',
                            'answer' => 'Có. Bạn có thể bắt đầu bằng Free hoặc Starter rồi nâng cấp khi thấy kênh bắt đầu có lực kéo tốt hơn.',
                        ],
                        [
                            'question' => 'Gói Free có bị bó quá không?',
                            'answer' => 'Không quá chật để nản, cũng không quá rộng để lạc hướng. 2 khóa là đủ để bạn test niche, cách dạy và phản hồi từ học viên trước khi đầu tư mạnh hơn.',
                        ],
                    ],
                ],
                'final' => [
                    'title' => 'Bạn có kiến thức, nền tảng này lo phần sân khấu.',
                    'description' => 'Nếu đã sẵn sàng biến kỹ năng thành một kênh giảng dạy nghiêm túc, đây là lúc bắt đầu. Làm tử tế từ đầu sẽ đỡ phải chữa cháy về sau.',
                    'primary' => 'Tạo hồ sơ giảng viên',
                    'secondary' => 'Khám phá các gói',
                ],
            ],
            'en' => [
                'page_title' => 'Become an Instructor',
                'hero' => [
                    'eyebrow' => 'Teacher Program',
                    'title' => 'Turn what you know into a teaching channel with real structure, real momentum, and real earning potential.',
                    'description' => 'If you have ever thought “I could probably teach this well,” this is your sign to stop probably and start properly. BigK Udemy gives you a clear instructor path: profile, package, review flow, and a dedicated workspace once you are approved.',
                    'primary' => 'Start your application',
                    'secondary' => 'See packages',
                    'chips' => [
                        'Clear application flow',
                        'Free package includes 2 courses',
                        'Upgrade when your channel gets traction',
                    ],
                    'panel_title' => 'Serious enough to scale, simple enough to start',
                    'panel_items' => [
                        'Build a solid instructor profile that feels confident, not over-polished.',
                        'Choose the package that matches your current stage and ambition.',
                        'Get reviewed by admin and unlock your instructor channel once everything is ready.',
                    ],
                ],
                'stats' => [
                    ['value' => '2 courses', 'label' => 'The free package gives you enough room to actually test your teaching.'],
                    ['value' => '70%', 'label' => 'Top commission level available on Pro for serious channel builders.'],
                    ['value' => '1 portal', 'label' => 'A dedicated space for your profile, earnings, and payout requests.'],
                ],
                'highlights' => [
                    ['title' => 'Start lean, not reckless', 'description' => 'The free package lets you publish 2 courses, so you can validate your content before going all in.'],
                    ['title' => 'Scale when your momentum is real', 'description' => 'Upgrade when your courses start moving, your audience grows, and your channel deserves more room.'],
                    ['title' => 'Quality beats chaos', 'description' => 'Instructor review exists to protect learner trust and help your channel start on stronger ground.'],
                ],
                'journey' => [
                    'title' => 'How the journey works',
                    'description' => 'A clear path from “I want to teach” to “my instructor channel is live.”',
                    'steps' => [
                        ['title' => 'Create your profile', 'description' => 'Show what you teach, why learners should trust you, and what makes your approach worth their time.'],
                        ['title' => 'Pick a package', 'description' => 'Free for testing, Starter for acceleration, Pro for creators who mean business.'],
                        ['title' => 'Review and approval', 'description' => 'Admin reviews your application and can ask for improvements before activation.'],
                        ['title' => 'Launch your portal', 'description' => 'Once approved, you get a dedicated teacher portal for courses, earnings, and payouts.'],
                    ],
                ],
                'earnings' => [
                    'title' => 'Revenue that feels transparent',
                    'description' => 'Each package has its own commission level, but the logic stays clear so you can see what your channel is doing.',
                    'points' => [
                        'Track gross revenue, allocated discounts, estimated take-home income, and available balance.',
                        'Higher packages unlock stronger commission and more room to grow.',
                        'No magical promises, just cleaner numbers and better decisions.',
                    ],
                ],
                'packages' => [
                    'title' => 'Choose the package that fits your stage',
                    'description' => 'Pick what you truly need now, then upgrade when your channel has earned it.',
                    'cta' => 'Choose this package',
                    'most_popular' => 'Popular choice',
                    'meta_free' => 'Great for getting started',
                    'meta_paid' => 'Built for serious growth',
                    'features' => [
                        'commission' => ':rate% revenue share',
                        'course_limit' => 'Up to :count published courses',
                        'unlimited' => 'Unlimited course publishing',
                        'priority_yes' => 'Priority application review',
                        'priority_no' => 'Standard review queue',
                        'support' => ':level support',
                    ],
                    'descriptions' => [
                        'free' => 'A free entry package that still gives you room to test your niche, your teaching style, and your first learner reactions.',
                        'starter' => 'For creators who are ready to move faster with more course slots and a better revenue split.',
                        'pro' => 'For instructors who are done playing small and want the most open setup available right now.',
                    ],
                ],
                'faq' => [
                    'title' => 'Frequently asked questions',
                    'items' => [
                        ['question' => 'Can I apply if I have never taught online before?', 'answer' => 'Yes. The free package is specifically there so you can test your teaching in the real world, not just in your head.'],
                        ['question' => 'Why is there an approval step?', 'answer' => 'To keep quality high and learner trust intact. In short: less chaos, better channels.'],
                        ['question' => 'Can I upgrade later?', 'answer' => 'Absolutely. Start where you are, then upgrade when your channel starts gaining traction.'],
                        ['question' => 'Is the free package too limiting?', 'answer' => 'Not really. Two courses is enough to validate your direction without spreading yourself too thin.'],
                    ],
                ],
                'final' => [
                    'title' => 'You bring the knowledge. We help shape the stage.',
                    'description' => 'If you are ready to build a serious teaching channel, this is a strong place to start.',
                    'primary' => 'Create instructor profile',
                    'secondary' => 'Explore packages',
                ],
            ],
            'ko' => [
                'page_title' => '강사 등록',
                'hero' => [
                    'eyebrow' => 'Teacher Program',
                    'title' => '당신의 지식을 구조와 성장 가능성을 갖춘 강의 채널로 바꿔보세요.',
                    'description' => '“이건 내가 꽤 잘 가르칠 수 있는데?”라고 생각한 적이 있다면, 이제는 진짜 시작할 차례입니다. BigK Udemy에서는 강사 프로필, 패키지 선택, 심사 과정, 승인 후 전용 포털까지 한 흐름으로 준비되어 있습니다.',
                    'primary' => '지금 시작하기',
                    'secondary' => '패키지 보기',
                    'chips' => [
                        '깔끔한 신청 흐름',
                        '무료 패키지도 2개 강좌 등록 가능',
                        '채널이 성장하면 업그레이드 가능',
                    ],
                    'panel_title' => '가볍게 시작하고, 제대로 확장하세요',
                    'panel_items' => [
                        '과하지 않지만 신뢰감 있는 강사 프로필을 만드세요.',
                        '지금 단계와 목표에 맞는 패키지를 선택하세요.',
                        '관리자 검토 후 강사 채널이 활성화됩니다.',
                    ],
                ],
                'stats' => [
                    ['value' => '2개 강좌', 'label' => '무료 패키지로도 실제 반응을 테스트할 수 있습니다.'],
                    ['value' => '70%', 'label' => 'Pro 패키지에서 제공되는 최대 수익 배분율입니다.'],
                    ['value' => '전용 포털', 'label' => '프로필, 수익, 출금 요청을 한곳에서 관리합니다.'],
                ],
                'highlights' => [
                    ['title' => '무리하지 말고 똑똑하게 시작', 'description' => '무료 패키지로 2개 강좌를 등록해 보고, 당신의 주제와 강의 스타일이 통하는지 확인할 수 있습니다.'],
                    ['title' => '성장할 때 자연스럽게 확장', 'description' => '수강생 반응이 좋아지고 채널이 커지면 더 높은 패키지로 부드럽게 올라갈 수 있습니다.'],
                    ['title' => '무작정 올리고 기다리는 방식이 아닙니다', 'description' => '심사 과정은 학습자 신뢰를 지키고, 강사 채널이 더 탄탄하게 출발하도록 돕기 위한 장치입니다.'],
                ],
                'journey' => [
                    'title' => '강사 채널 오픈 흐름',
                    'description' => '어디까지 진행됐는지, 다음에 무엇을 해야 하는지 명확하게 보이도록 설계했습니다.',
                    'steps' => [
                        ['title' => '프로필 작성', 'description' => '무엇을 가르치고 왜 당신을 믿어도 되는지 분명하게 보여주세요.'],
                        ['title' => '패키지 선택', 'description' => 'Free로 가볍게 시작하거나 Starter, Pro로 빠르게 확장할 수 있습니다.'],
                        ['title' => '심사 대기', 'description' => '관리자가 프로필과 패키지를 검토하고 필요 시 수정 방향을 안내합니다.'],
                        ['title' => '전용 채널 오픈', 'description' => '승인 후 강좌, 수익, 출금 요청을 관리할 수 있는 포털이 열립니다.'],
                    ],
                ],
                'earnings' => [
                    'title' => '수익 구조도 명확하게',
                    'description' => '패키지마다 커미션은 다르지만, 계산 흐름은 투명하게 보여서 채널 상태를 쉽게 파악할 수 있습니다.',
                    'points' => [
                        '총매출, 할인 배분, 예상 실수령액, 출금 가능 잔액을 확인할 수 있습니다.',
                        '상위 패키지일수록 더 좋은 수익 배분과 확장성을 제공합니다.',
                        '과장된 약속 대신, 더 명확한 데이터와 더 나은 판단을 드립니다.',
                    ],
                ],
                'packages' => [
                    'title' => '지금 단계에 맞는 패키지를 고르세요',
                    'description' => '당장 필요한 만큼만 선택하고, 채널이 성장하면 업그레이드하세요.',
                    'cta' => '이 패키지 선택',
                    'most_popular' => '인기 선택',
                    'meta_free' => '가볍게 시작하기 좋음',
                    'meta_paid' => '본격적인 성장을 위한 선택',
                    'features' => [
                        'commission' => '수익 배분 :rate%',
                        'course_limit' => '최대 :count개 강좌 등록',
                        'unlimited' => '강좌 등록 무제한',
                        'priority_yes' => '우선 심사',
                        'priority_no' => '일반 심사 대기열',
                        'support' => ':level 지원',
                    ],
                    'descriptions' => [
                        'free' => '무료지만 시작하기에 충분한 패키지입니다. 2개 강좌를 올리며 주제와 수강생 반응을 테스트할 수 있습니다.',
                        'starter' => '강사 채널을 조금 더 빠르게 키우고 싶은 분을 위한 패키지입니다.',
                        'pro' => '제대로 키우고 싶은 강사를 위한 가장 개방적인 선택입니다.',
                    ],
                ],
                'faq' => [
                    'title' => '자주 묻는 질문',
                    'items' => [
                        ['question' => '온라인 강의 경험이 없어도 시작할 수 있나요?', 'answer' => '네. Free 패키지는 실제로 테스트해 볼 수 있도록 설계되어 있습니다.'],
                        ['question' => '왜 관리자 승인이 필요한가요?', 'answer' => '학습자 신뢰와 채널 품질을 지키기 위해서입니다.'],
                        ['question' => '나중에 업그레이드할 수 있나요?', 'answer' => '물론입니다. 채널이 성장하면 더 높은 패키지로 이동할 수 있습니다.'],
                        ['question' => '무료 패키지가 너무 제한적이지 않나요?', 'answer' => '오히려 시작하기엔 균형이 좋습니다. 2개 강좌면 방향성과 반응을 충분히 검증할 수 있습니다.'],
                    ],
                ],
                'final' => [
                    'title' => '지식은 당신이 준비하고, 무대는 우리가 함께 만듭니다.',
                    'description' => '진지한 강의 채널을 만들고 싶다면, 여기서 깔끔하게 시작해 보세요.',
                    'primary' => '강사 프로필 만들기',
                    'secondary' => '패키지 살펴보기',
                ],
            ],
            'ja' => [
                'page_title' => '講師になる',
                'hero' => [
                    'eyebrow' => 'Teacher Program',
                    'title' => 'あなたの知識を、成長できる講師チャンネルへ変えてみませんか。',
                    'description' => '「これなら自分でも教えられそう」と思ったことがあるなら、今がその一歩です。BigK Udemy では、講師プロフィール作成、プラン選択、審査、承認後の専用ポータルまで、流れがしっかり整っています。',
                    'primary' => '今すぐ始める',
                    'secondary' => 'プランを見る',
                    'chips' => [
                        'わかりやすい申請フロー',
                        '無料プランでも2講座まで公開可能',
                        'チャンネル成長後にアップグレード可能',
                    ],
                    'panel_title' => '軽く始めて、しっかり育てる',
                    'panel_items' => [
                        '信頼感のある講師プロフィールを作成します。',
                        '今の段階に合ったプランを選びます。',
                        '管理者の審査後、講師チャンネルが有効になります。',
                    ],
                ],
                'stats' => [
                    ['value' => '2講座', 'label' => '無料プランでも、しっかりテストできる余白があります。'],
                    ['value' => '70%', 'label' => 'Pro プランで利用できる最大収益配分です。'],
                    ['value' => '専用ポータル', 'label' => 'プロフィール、収益、出金申請を一か所で管理します。'],
                ],
                'highlights' => [
                    ['title' => '無理せず、でも前に進める', 'description' => '無料プランで2講座まで公開し、自分のテーマや教え方の反応を確かめられます。'],
                    ['title' => '伸び始めたら自然に拡張', 'description' => '受講者の反応が良くなってきたら、より上位のプランへ移行できます。'],
                    ['title' => 'とりあえず公開、では終わらせない', 'description' => '審査フローは学習者の信頼を守り、講師チャンネルの立ち上がりを整えるためにあります。'],
                ],
                'journey' => [
                    'title' => '講師チャンネル開始までの流れ',
                    'description' => '今どこにいるのか、次に何をすればいいのかが見えやすい設計です。',
                    'steps' => [
                        ['title' => 'プロフィール作成', 'description' => '何を教えるのか、なぜあなたから学ぶ価値があるのかを伝えます。'],
                        ['title' => 'プラン選択', 'description' => 'Free で試し、Starter や Pro で本格的に広げることができます。'],
                        ['title' => '審査待ち', 'description' => '管理者が内容を確認し、必要なら改善ポイントを案内します。'],
                        ['title' => '専用ポータル開始', 'description' => '承認後は講座、収益、出金申請を管理できるポータルが使えます。'],
                    ],
                ],
                'earnings' => [
                    'title' => '収益の見え方もクリアに',
                    'description' => 'プランごとに配分率は異なりますが、計算の流れはわかりやすく表示されます。',
                    'points' => [
                        '総売上、割引配分、見込み受取額、出金可能残高を確認できます。',
                        '上位プランほど、収益配分や拡張性が良くなります。',
                        '過剰な約束ではなく、判断しやすい数字を提供します。',
                    ],
                ],
                'packages' => [
                    'title' => '今の段階に合うプランを選ぶ',
                    'description' => '今必要な分だけ選び、チャンネルの成長に合わせてアップグレードしましょう。',
                    'cta' => 'このプランを選ぶ',
                    'most_popular' => '人気プラン',
                    'meta_free' => '気軽に始めやすい',
                    'meta_paid' => '本格成長向け',
                    'features' => [
                        'commission' => '収益配分 :rate%',
                        'course_limit' => ':count講座まで公開可能',
                        'unlimited' => '講座公開数は無制限',
                        'priority_yes' => '優先審査',
                        'priority_no' => '通常審査',
                        'support' => ':level サポート',
                    ],
                    'descriptions' => [
                        'free' => '無料でも、2講座を使ってテーマや教え方をしっかり試せるスタートプランです。',
                        'starter' => 'もっと速く広げたい講師向けの成長プランです。',
                        'pro' => '本気で育てたい講師のための、最も自由度の高いプランです。',
                    ],
                ],
                'faq' => [
                    'title' => 'よくある質問',
                    'items' => [
                        ['question' => 'オンライン講師の経験がなくても大丈夫ですか？', 'answer' => 'はい。Free プランは、実際に試しながら始められるように作られています。'],
                        ['question' => 'なぜ承認が必要なのですか？', 'answer' => '学習者の信頼とチャンネル品質を守るためです。'],
                        ['question' => '後からアップグレードできますか？', 'answer' => 'もちろん可能です。成長に合わせて変更できます。'],
                        ['question' => '無料プランは厳しすぎませんか？', 'answer' => '2講座あれば方向性や反応を確かめるには十分です。'],
                    ],
                ],
                'final' => [
                    'title' => '知識はあなたが持っている。舞台は一緒に整えます。',
                    'description' => '本気の講師チャンネルを作りたいなら、ここから始めるのがいいスタートです。',
                    'primary' => '講師プロフィールを作成',
                    'secondary' => 'プランを見る',
                ],
            ],
            'zh' => [
                'page_title' => '成为讲师',
                'hero' => [
                    'eyebrow' => 'Teacher Program',
                    'title' => '把你的知识变成一个真正能成长、能运营、也能赚钱的讲师频道。',
                    'description' => '如果你曾经想过“这个我其实讲得不错”，那现在就是认真开始的时候。BigK Udemy 为你准备了完整路径：讲师资料、套餐选择、审核流程，以及审核通过后的专属讲师门户。',
                    'primary' => '立即开始',
                    'secondary' => '查看套餐',
                    'chips' => [
                        '清晰的申请流程',
                        '免费套餐也能发布 2 门课程',
                        '频道有起色后可随时升级',
                    ],
                    'panel_title' => '可以轻松起步，也能认真做大',
                    'panel_items' => [
                        '先创建一个有说服力的讲师资料，不需要夸张，但要让人信任。',
                        '根据你当前阶段选择合适的套餐。',
                        '管理员审核通过后，讲师频道就会正式开启。',
                    ],
                ],
                'stats' => [
                    ['value' => '2 门课程', 'label' => '免费套餐也足够你真实测试内容方向。'],
                    ['value' => '70%', 'label' => 'Pro 套餐当前可获得的最高分成比例。'],
                    ['value' => '独立门户', 'label' => '资料、收益、提现申请都能在一个空间里管理。'],
                ],
                'highlights' => [
                    ['title' => '先小步开始，不必一上来就压太满', 'description' => '免费套餐可发布 2 门课程，先验证你的内容是否真的有人买单。'],
                    ['title' => '有增长再升级，更合理', 'description' => '当课程开始有学员、有反馈时，再升级到更高套餐也完全来得及。'],
                    ['title' => '不是“随便发了等运气”', 'description' => '审核流程是为了保护学员体验，也让你的频道起步更稳。'],
                ],
                'journey' => [
                    'title' => '成为讲师的流程',
                    'description' => '每一步都清楚，让你知道现在到哪一步、下一步该做什么。',
                    'steps' => [
                        ['title' => '填写资料', 'description' => '说明你是谁、你教什么、为什么值得被学员信任。'],
                        ['title' => '选择套餐', 'description' => 'Free 用来试水，Starter 和 Pro 适合想认真经营的人。'],
                        ['title' => '等待审核', 'description' => '管理员会查看资料和套餐，并在需要时给出修改建议。'],
                        ['title' => '开启讲师门户', 'description' => '通过后，你将拥有自己的课程、收益和提现管理空间。'],
                    ],
                ],
                'earnings' => [
                    'title' => '收益逻辑清楚，不玩模糊',
                    'description' => '不同套餐有不同分成，但数据展示清晰，让你知道频道到底表现如何。',
                    'points' => [
                        '你可以看到总收入、折扣分摊、预计实收和可提现余额。',
                        '套餐越高，分成越好，可扩展空间也越大。',
                        '不画大饼，但会给你足够清楚的数据去做判断。',
                    ],
                ],
                'packages' => [
                    'title' => '选择适合你当前阶段的套餐',
                    'description' => '先选够用的，等频道有了起色再升级，会更稳也更聪明。',
                    'cta' => '选择此套餐',
                    'most_popular' => '热门选择',
                    'meta_free' => '适合轻松起步',
                    'meta_paid' => '适合认真扩张',
                    'features' => [
                        'commission' => '分成比例 :rate%',
                        'course_limit' => '最多发布 :count 门课程',
                        'unlimited' => '课程发布不限数量',
                        'priority_yes' => '优先审核',
                        'priority_no' => '标准审核队列',
                        'support' => ':level 支持',
                    ],
                    'descriptions' => [
                        'free' => '免费起步，但不只是看看而已。你可以先发布 2 门课程，测试方向和学员反馈。',
                        'starter' => '适合已经准备认真运营讲师频道的人，空间更大，分成更好。',
                        'pro' => '适合不想半途而废的人，拥有目前最完整的权限和最优分成。'],
                ],
                'faq' => [
                    'title' => '常见问题',
                    'items' => [
                        ['question' => '没有线上授课经验也可以开始吗？', 'answer' => '可以。Free 套餐就是为了让你先真实测试，而不是一直停留在“我应该可以吧”。'],
                        ['question' => '为什么需要管理员审核？', 'answer' => '为了保证学员体验和讲师质量。说白了，就是少一点混乱，多一点信任。'],
                        ['question' => '之后可以升级套餐吗？', 'answer' => '当然可以。先从适合现在的开始，后续再升级。'],
                        ['question' => '免费套餐会不会太限制？', 'answer' => '不会。2 门课程已经足够你验证方向和市场反馈。'],
                    ],
                ],
                'final' => [
                    'title' => '你负责内容，我们帮你把舞台搭起来。',
                    'description' => '如果你准备认真做讲师频道，这里会是一个很好的起点。',
                    'primary' => '创建讲师资料',
                    'secondary' => '查看套餐',
                ],
            ],
        ];

        return $content[$locale] ?? $content['en'];
    }
}
