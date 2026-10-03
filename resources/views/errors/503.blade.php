@extends('errors.layout')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __('System Maintenance'))
@section('description', __('We are currently performing scheduled maintenance to improve our service. We will be back
    online shortly.'))
