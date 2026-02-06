@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Announcement Detail',
    'headerTitle' => $headerTitle ?? 'Announcement',
    'showBack' => $showBack ?? true,
    'hideBottomNav' => $hideBottomNav ?? true,
    'themeColor' => '#26BFFF',
    'role' => 'guardian'
])

@section('content')
<div class="pwa-section">
    {{-- Announcement Header --}}
    <div class="pwa-card">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
            <span class="pwa-badge pwa-badge-{{ $announcement->priority }}">
                {{ ucfirst($announcement->priority) }}
            </span>
            <span class="pwa-badge pwa-badge-outline">
                {{ ucfirst($announcement->category) }}
            </span>
        </div>
        
        <h1 style="margin: 0 0 12px 0; font-size: 24px; font-weight: 700; color: var(--text-primary); line-height: 1.3;">
            {{ $announcement->title }}
        </h1>
        
        <div style="display: flex; align-items: center; gap: 16px; padding: 12px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 6px; color: var(--text-secondary); font-size: 14px;">
                <i class="fas fa-user"></i>
                <span>{{ $announcement->author->name }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; color: var(--text-secondary); font-size: 14px;">
                <i class="fas fa-calendar"></i>
                <span>{{ $announcement->date }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; color: var(--text-secondary); font-size: 14px;">
                <i class="fas fa-clock"></i>
                <span>{{ $announcement->time }}</span>
            </div>
        </div>
        
        {{-- Content --}}
        <div style="font-size: 16px; line-height: 1.7; color: var(--text-primary);">
            {!! nl2br(e($announcement->content)) !!}
        </div>
        
        {{-- Attachments --}}
        @if(isset($announcement->attachments) && count($announcement->attachments) > 0)
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
                <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: var(--text-primary);">
                    <i class="fas fa-paperclip"></i> Attachments
                </h3>
                
                @foreach($announcement->attachments as $attachment)
                    <a href="{{ $attachment->url }}" target="_blank" class="pwa-list-item" style="text-decoration: none;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--info-bg); display: flex; align-items: center; justify-content: center; color: var(--info);">
                                <i class="fas fa-file"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; color: var(--text-primary);">{{ $attachment->name }}</div>
                                <div style="font-size: 12px; color: var(--text-secondary);">{{ $attachment->size }}</div>
                            </div>
                            <i class="fas fa-download" style="color: var(--text-muted);"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
