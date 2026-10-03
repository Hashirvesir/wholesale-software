@extends('errors.layout')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Session Expired'))
@section('description', __('Your session has expired due to inactivity. Please refresh the page and try again.'))
