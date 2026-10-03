@extends('errors.layout')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __('Access Denied'))
@section('description', __('Sorry, you do not have permission to access this page. Please contact your administrator if
    you think this is a mistake.'))
