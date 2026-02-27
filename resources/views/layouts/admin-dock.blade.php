{{-- Admin Bottom Dock Bar — iPad OS Style --}}
@php
    $isDashboard = request()->routeIs('dashboard');
    $dockItems = [
        ['id' => 'home', 'label' => __('navigation.Dashboard'), 'icon' => 'fas fa-home'],
        ['id' => 'academic', 'label' => __('navigation.Academic'), 'icon' => 'fas fa-graduation-cap', 'permission' => 'view academic management'],
        ['id' => 'schedule', 'label' => __('navigation.Schedule'), 'icon' => 'fas fa-calendar-alt', 'permission' => 'view time-table and attendance'],
        ['id' => 'people', 'label' => __('navigation.People'), 'icon' => 'fas fa-users', 'permission' => 'view departments and profiles'],
        ['id' => 'finance', 'label' => __('navigation.Finance'), 'icon' => 'fas fa-dollar-sign', 'permission' => 'view finance management'],
        ['id' => 'news', 'label' => __('navigation.News'), 'icon' => 'fas fa-bullhorn', 'permission' => 'view events and announcements'],
        ['id' => 'more', 'label' => __('navigation.More'), 'icon' => 'fas fa-ellipsis-h'],
    ];
@endphp

<nav class="admin-dock">
    <div class="admin-dock-inner">
        @foreach($dockItems as $item)
            @if(isset($item['permission']))
                @can($item['permission'])
                    <a href="{{ route('dashboard') }}{{ $item['id'] !== 'home' ? '#' . $item['id'] : '' }}" class="admin-dock-item"
                        :class="activeSection === '{{ $item['id'] }}' && '{{ $isDashboard ? 'true' : 'false' }}' === 'true' ? 'active' : ''"
                        @if($isDashboard)
                        @click.prevent="activeSection = '{{ $item['id'] }}'; window.scrollTo({top: 0, behavior: 'smooth'})" @endif>
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endcan
            @else
                <a href="{{ route('dashboard') }}{{ $item['id'] !== 'home' ? '#' . $item['id'] : '' }}" class="admin-dock-item"
                    :class="activeSection === '{{ $item['id'] }}' && '{{ $isDashboard ? 'true' : 'false' }}' === 'true' ? 'active' : ''"
                    @if($isDashboard)
                    @click.prevent="activeSection = '{{ $item['id'] }}'; window.scrollTo({top: 0, behavior: 'smooth'})" @endif>
                    <i class="{{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>

@if($isDashboard)
    <script>
        // Handle hash-based section switching on page load
        document.addEventListener('alpine:init', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash && ['academic', 'schedule', 'people', 'finance', 'news', 'more'].includes(hash)) {
                setTimeout(() => {
                    const body = document.querySelector('body');
                    if (typeof Alpine !== 'undefined') {
                        Alpine.$data(body).activeSection = hash;
                    }
                }, 100);
            }
        });
    </script>
@endif