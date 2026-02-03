@props(['tabs', 'activeTab' => 'activeTab'])

<div class="academic-structure-section">
    <div class="academic-tabs">
        @foreach($tabs as $tabKey => $tabLabel)
            <button 
                type="button" 
                @click="{{ $activeTab }}='{{ $tabKey }}'" 
                :class="{{ $activeTab }}==='{{ $tabKey }}' ? 'academic-tab active' : 'academic-tab'" 
                data-tab="{{ $tabKey }}"
            >
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>
</div>
