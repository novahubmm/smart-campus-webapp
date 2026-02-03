@php
    $title = __('errors.Unexpected error');
    $message = __('errors.Something went wrong on our side. The team has been notified.');
@endphp

@include('errors.base', ['title' => $title, 'message' => $message])
