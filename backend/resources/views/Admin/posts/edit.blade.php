@extends('admin.layouts.app')

@section('title', 'Sửa Bài viết')

@section('styles') @include('admin.posts._styles') @endsection

@section('content')
<div class="pe-page">
    <div class="pe-header">
        <div>
            <div class="pe-kicker">
                <i class="fa-solid fa-newspaper"></i>
                <a href="{{ route('admin.posts') }}">Bài viết</a> <span>/</span> <span>Chỉnh sửa</span>
            </div>
            <h1>Chỉnh sửa bài viết</h1>
            <p>Cập nhật nội dung của "{{ \Illuminate\Support\Str::limit($post->title, 70) }}".</p>
        </div>
        <div class="pe-header-actions">
            @if($post->status === 'active')
                <a href="{{ rtrim(config('app.frontend_url'), '/') }}/news/{{ $post->slug }}"
                   target="_blank" rel="noopener" class="pe-btn">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trên website
                </a>
            @endif
            <a href="{{ route('admin.posts') }}" class="pe-btn">
                <i class="fa-solid fa-arrow-left-long"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    @include('admin.posts._form')
</div>
@endsection

@section('scripts')
    @include('admin.posts._scripts', ['peConfig' => ['isEdit' => true, 'draftKey' => 'petworld.admin.post.draft.edit.' . $post->id]])
@endsection
