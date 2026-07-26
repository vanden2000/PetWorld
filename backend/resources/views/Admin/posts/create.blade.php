@extends('admin.layouts.app')

@section('title', 'Thêm Bài viết Mới')

@section('styles') @include('admin.posts._styles') @endsection

@section('content')
<div class="pe-page">
    <div class="pe-header">
        <div>
            <div class="pe-kicker">
                <i class="fa-solid fa-newspaper"></i>
                <a href="{{ route('admin.posts') }}">Bài viết</a> <span>/</span> <span>Thêm mới</span>
            </div>
            <h1>Viết bài viết mới</h1>
            <p>Tạo tin tức, cẩm nang chăm sóc và khuyến mãi cho PetWorld.</p>
        </div>
        <div class="pe-header-actions">
            <a href="{{ route('admin.posts') }}" class="pe-btn">
                <i class="fa-solid fa-arrow-left-long"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    @include('admin.posts._form')
</div>
@endsection

@section('scripts')
    @include('admin.posts._scripts', ['peConfig' => ['isEdit' => false, 'draftKey' => 'petworld.admin.post.draft.new']])
@endsection
