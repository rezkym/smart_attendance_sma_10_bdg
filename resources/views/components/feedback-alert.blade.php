@foreach ($alerts as $alert)
    @php
        $wrapperClasses = trim($alert['classes'] . ($alert['hasIcon'] ? ' d-flex align-items-center' : ''));
    @endphp
    <div class="{{ $wrapperClasses }}" role="alert">
        @if ($alert['hasIcon'])
            <span class="alert-icon rounded me-3">
                <i class="icon-base {{ $alert['icon'] }} icon-md"></i>
            </span>
        @endif

        <div class="{{ $alert['hasIcon'] ? 'flex-grow-1' : '' }}">
            @if ($alert['slot'])
                {!! $alert['slot'] !!}
            @else
                @if ($alert['heading'])
                    <h4 class="alert-heading mb-3">{{ $alert['heading'] }}</h4>
                @endif

                @if (count($alert['messages']) > 1)
                    <ul class="mb-0 ps-3">
                        @foreach ($alert['messages'] as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @elseif (!empty($alert['messages']))
                    <p class="mb-0">
                        {{ $alert['messages'][0] }}
                        @if ($alert['link'])
                            <a href="{{ $alert['link']['href'] }}" class="alert-link ms-1">{{ $alert['link']['text'] }}</a>
                        @endif
                    </p>
                @elseif ($alert['link'])
                    <p class="mb-0">
                        <a href="{{ $alert['link']['href'] }}" class="alert-link">{{ $alert['link']['text'] }}</a>
                    </p>
                @endif
            @endif
        </div>

        @if ($alert['dismissible'])
            <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
@endforeach
