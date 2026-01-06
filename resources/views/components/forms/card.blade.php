@props([
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'full', // sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, full
    'action' => null,
    'method' => 'POST',
    'hasFiles' => false,
    'columns' => 1, // 1 = single column, 2 = two columns layout
])

@php
$widthClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    '5xl' => 'max-w-5xl',
    '6xl' => 'max-w-6xl',
    'full' => 'w-full max-w-none',
];
$widthClass = $widthClasses[$maxWidth] ?? 'w-full max-w-none';
@endphp

<div class="form-page-container {{ $widthClass }}">
    <div class="form-card">
        @if($title)
        <div class="form-card-header">
            <div class="form-card-header-content">
                <h2 class="form-card-title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="form-card-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @endif
        
        <div class="form-card-body">
            @if($action)
            <form 
                action="{{ $action }}" 
                method="{{ $method === 'GET' ? 'GET' : 'POST' }}" 
                @if($hasFiles) enctype="multipart/form-data" @endif
                {{ $attributes->merge(['class' => 'space-y-6']) }}
            >
                @csrf
                @if(!in_array($method, ['GET', 'POST']))
                    @method($method)
                @endif
                
                @if($columns == 2)
                <div class="form-two-columns">
                    {{ $slot }}
                </div>
                @else
                    {{ $slot }}
                @endif
            </form>
            @else
                <div {{ $attributes->merge(['class' => 'space-y-6']) }}>
                    @if($columns == 2)
                    <div class="form-two-columns">
                        {{ $slot }}
                    </div>
                    @else
                        {{ $slot }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
