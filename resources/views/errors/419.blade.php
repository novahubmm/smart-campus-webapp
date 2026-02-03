@php
    $title = __('errors.Page expired');
    $message = __('errors.Your session expired. Please refresh and try again.');
@endphp

@include('errors.base', ['title' => $title, 'message' => $message])
