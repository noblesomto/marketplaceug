@if (!$ads->isEmpty())
    @foreach ($ads as $row)
    @php
        $isSold     = $row->sold === 'Yes';
        $isFeatured = $row->featured === 'Yes';
        $adStatus   = $row->ad_status;          // active | disabled | banned
        $imgCount   = $row->getMedia('images')->count();
        $adLink     = url($row->state_slug . '/' . $row->title_slug . '/' . $row->ad_id);
        $hasShipping = !in_array($row->category, [1, 3, 11, 18]);
    @endphp

    <div class="myad-card {{ $isSold ? 'is-sold' : '' }} {{ $adStatus === 'disabled' ? 'is-disabled' : '' }}">

        {{-- ── Body: image + details + three-dots ── --}}
        <div class="myad-body">

            {{-- Image --}}
            <a href="{{ $adLink }}" class="myad-img">
                @if($row->hasMedia('images'))
                    <img src="{{ $row->getFirstMediaUrl('images', 'thumbnail') }}"
                         alt="{{ $row->ad_title ?? 'Advert' }}"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img src="{{ asset('frontend/images/default.png') }}" alt="Advert image">
                @endif
                <span class="myad-img-count"><i class="bi bi-images"></i> {{ $imgCount }}</span>
            </a>

            {{-- Details --}}
            <div class="myad-details">
                <div class="myad-meta">
                    @if($row->state)
                        <span class="myad-meta-item"><i class="bi bi-geo-alt-fill"></i> {{ $row->state }}</span>
                    @endif
                    <span class="myad-meta-item"><i class="bi bi-calendar3"></i> {{ date('d M Y', strtotime($row->created_at)) }}</span>
                </div>

                <a href="{{ $adLink }}" class="myad-title">{{ Str::limit($row->ad_title, 80) }}</a>

                <div class="myad-desc">
                    {{ Str::limit(strip_tags($row->description ?? ''), 100) }}
                </div>

                {{-- Price --}}
                <div class="myad-price">
                    @if($row->category == 3)
                        {{ $row->salary }}
                    @elseif($row->category == 18)
                        {{ $row->expected_salary }}
                    @elseif($row->contact_price === 'yes')
                        Contact for Price
                    @else
                        ₦{{ number_format($row->price, 0, '.', ',') }}
                        @if($row->price_type)
                            <span style="font-size:0.72rem;font-weight:500;color:#9ca3af;margin-left:4px;">{{ $row->price_type }}</span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Three-dots dropdown --}}
            <div class="relative dropdown inline-block" style="flex-shrink:0;">
                <button type="button" class="myad-dots-btn dropdown-button" aria-haspopup="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="dropdown-menu hidden absolute right-0 z-50 mt-1 w-40 rounded-lg bg-white shadow-lg"
                     style="border:1px solid #e5e7eb;top:100%;">
                    <a href="javascript:void(0)"
                       data-delete-url="/user/delete-ad/{{ $row->id }}"
                       class="delete-ad-btn"
                       style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:0.8rem;color:#dc2626;text-decoration:none;"
                       onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background=''">
                        <i class="bi bi-trash"></i> Delete Ad
                    </a>
                </div>
            </div>

        </div>{{-- .myad-body --}}

        {{-- ── Footer: action buttons ── --}}
        <div class="myad-footer {{ $isSold ? 'myad-footer--sold' : '' }}">

            {{-- Mark Sold / Sold badge --}}
            @if($isSold)
                <span class="myad-btn myad-btn-sold">
                    <i class="bi bi-check-circle-fill"></i> Sold
                </span>
            @else
                <a href="javascript:void(0)"
                   class="myad-btn myad-btn-sell mark-sold-btn"
                   data-mark-sold-url="/user/mark-sold/{{ $row->id }}"
                   title="Mark this advert as sold">
                    <i class="bi bi-check-circle"></i> Mark Sold
                </a>
            @endif

            {{-- Edit Ad --}}
            @if($isSold)
                <span class="myad-btn myad-btn-edit disabled">
                    <i class="bi bi-pencil"></i> Edit Ad
                </span>
            @else
                <a href="/user/edit-ad/{{ $row->id }}" class="myad-btn myad-btn-edit">
                    <i class="bi bi-pencil"></i> Edit Ad
                </a>
            @endif

            {{-- Boost Ad --}}
            @if($isSold)
                <span class="myad-btn myad-btn-boost-off">
                    <i class="bi bi-rocket-takeoff"></i> Boost Ad
                </span>
            @elseif($isFeatured)
                <a href="/user/boosted-ad/{{ $row->id }}" class="myad-btn myad-btn-boosted">
                    <i class="bi bi-rocket-takeoff-fill"></i> Boosted
                </a>
            @else
                <a href="/user/boost-ad/{{ $row->id }}" class="myad-btn myad-btn-boost">
                    <i class="bi bi-rocket-takeoff"></i> Boost Ad
                </a>
            @endif

            {{-- Ad status toggle --}}
            @if($isSold)
                {{-- status not actionable when sold --}}
            @elseif($adStatus === 'active')
                <a href="javascript:void(0)"
                   class="myad-btn myad-status-active ad-status-btn"
                   data-status-url="/user/ad-status/disabled/{{ $row->id }}"
                   data-current-status="active"
                   data-new-status="disabled"
                   title="Click to disable this ad">
                    <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Active
                </a>
            @elseif($adStatus === 'disabled')
                <a href="javascript:void(0)"
                   class="myad-btn myad-status-disabled ad-status-btn"
                   data-status-url="/user/ad-status/active/{{ $row->id }}"
                   data-current-status="disabled"
                   data-new-status="active"
                   title="Click to reactivate this ad">
                    <i class="bi bi-slash-circle"></i> Disabled
                </a>
            @else
                <span class="myad-btn myad-status-banned" title="This ad has been banned">
                    <i class="bi bi-x-circle"></i> Banned
                </span>
            @endif

            {{-- Shipping Status (sold ads only, not services/jobs/wanted) --}}
            @if($isSold && $hasShipping)
                <a href="/user/ad-shipping/{{ $row->id }}" class="myad-btn myad-btn-shipping myad-footer--shipping">
                    <i class="bi bi-truck"></i> Shipping Status
                </a>
            @endif

        </div>{{-- .myad-footer --}}

    </div>{{-- .myad-card --}}
    @endforeach

@else
    <div class="myad-empty">
        <i class="bi bi-badge-ad"></i>
        <div style="font-size:1rem;font-weight:600;color:#374151;margin-bottom:6px;">No adverts yet</div>
        <p style="font-size:0.85rem;margin-bottom:16px;">Your listings will appear here once you post an ad.</p>
        <a href="/user/post-ad" style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#326916;color:#fff;border-radius:8px;font-size:0.82rem;font-weight:600;text-decoration:none;">
            <i class="bi bi-plus-lg"></i> Post Your First Ad
        </a>
    </div>
@endif

<script>
(function () {
    /* ── Dropdown toggle ── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.dropdown-button');
        const anyDropdown = e.target.closest('.dropdown');
        if (btn && anyDropdown) {
            const menu = anyDropdown.querySelector('.dropdown-menu');
            const willOpen = menu.classList.contains('hidden');
            document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
            if (willOpen) {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            } else {
                btn.setAttribute('aria-expanded', 'false');
            }
            return;
        }
        if (!anyDropdown) {
            document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
    });

    /* ── Delete Ad ── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-ad-btn');
        if (!btn) return;
        e.preventDefault();
        const url = btn.getAttribute('data-delete-url');
        Swal.fire({
            title: 'Delete this advert?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">
                              <input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        });
    });

    /* ── Mark Sold ── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.mark-sold-btn');
        if (!btn) return;
        e.preventDefault();
        Swal.fire({
            title: 'Mark as Sold?',
            text: 'This will mark the advert as sold and hide it from search results.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#326916',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, mark sold',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) window.location.href = btn.getAttribute('data-mark-sold-url');
        });
    });

    /* ── Ad Status Toggle ── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.ad-status-btn');
        if (!btn) return;
        e.preventDefault();
        const newStatus = btn.getAttribute('data-new-status');
        const isDisabling = newStatus === 'disabled';
        Swal.fire({
            title: isDisabling ? 'Disable Ad?' : 'Activate Ad?',
            text: isDisabling
                ? 'Your ad will be hidden from public view. You can reactivate it anytime.'
                : 'Your ad will be visible to the public again.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isDisabling ? '#ef4444' : '#326916',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isDisabling ? 'Yes, disable' : 'Yes, activate',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) window.location.href = btn.getAttribute('data-status-url');
        });
    });
})();
</script>
