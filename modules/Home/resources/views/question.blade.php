@php
    $cards = [
        [
            'icon' => 'fas fa-chart-line',
            'title' => __('home::common.experience_title'),
            'items' => [
                __('home::common.experience_item_1'),
                __('home::common.experience_item_2'),
                __('home::common.experience_item_3'),
            ],
        ],
        [
            'icon' => 'fas fa-users',
            'title' => __('home::common.students_title'),
            'items' => [
                __('home::common.students_item_1'),
                __('home::common.students_item_2'),
                __('home::common.students_item_3'),
            ],
        ],
        [
            'icon' => 'fas fa-briefcase',
            'title' => __('home::common.business_title'),
            'items' => [
                __('home::common.business_item_1'),
                __('home::common.business_item_2'),
                __('home::common.business_item_3'),
            ],
        ],
        [
            'icon' => 'fas fa-headset',
            'title' => __('home::common.support_title'),
            'items' => [
                __('home::common.support_item_1'),
                __('home::common.support_item_2'),
                __('home::common.support_item_3'),
            ],
        ],
    ];

    $extractMetric = static function (string $title): ?array {
        if (! preg_match('/^\s*([\d][\d.,]*)(\+?)(.*)$/u', trim($title), $matches)) {
            return null;
        }

        $value = (int) preg_replace('/\D+/', '', $matches[1]);

        if ($value <= 0) {
            return null;
        }

        return [
            'value' => $value,
            'suffix' => $matches[2] ?? '',
            'label' => trim($matches[3] ?? ''),
        ];
    };
@endphp

<section class="question">
    <div class="container padding">
        <h3 class="text-warning">{{ __('home::common.why_choose_us') }}</h3>
        <div class="row">
            @foreach ($cards as $card)
                @php
                    $metric = $extractMetric($card['title']);
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="group">
                        <div class="group-icon">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="group-title">
                            @if ($metric)
                                <div class="group-metric">
                                    <span class="group-metric__value js-count-value"
                                        data-target="{{ $metric['value'] }}"
                                        data-delay="{{ $loop->index * 120 }}">0</span>
                                    @if ($metric['suffix'] !== '')
                                        <span class="group-metric__suffix">{{ $metric['suffix'] }}</span>
                                    @endif
                                </div>
                            @endif
                            <p>{{ $metric && $metric['label'] !== '' ? $metric['label'] : $card['title'] }}</p>
                            <ul>
                                @foreach ($card['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
