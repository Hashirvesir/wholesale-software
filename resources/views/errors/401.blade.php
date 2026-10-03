@extends('errors.layout')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized Access'))
@section('description', __('It looks like you are not authorized to access this resource. Please log in or verify your
    credentials.'))
