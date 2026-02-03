@php
    $title = __('errors.Access Forbidden');
    $message = __("errors.You don't have permission to access this resource. Please contact your administrator if you believe this is an error.");
@endphp

@include('errors.base', ['title' => $title, 'message' => $message])