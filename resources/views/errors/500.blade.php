@extends('errors.layout')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Internal Server Error'))
@section('description', __('Whoops! Something went wrong on our servers. We are already working to fix it. Please try
    again later.'))
