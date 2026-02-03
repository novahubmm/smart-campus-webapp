@php
    $title = __('errors.Page not found');
    $message = __("errors.We couldn't find the page you were looking for. It may have been moved or deleted.");
@endphp

@include('errors.base', ['title' => $title, 'message' => $message])
