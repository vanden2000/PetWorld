@extends('admin.layouts.app')
@section('title', 'Thêm loài thú cưng')
@section('styles') @include('admin.pet-species._styles') @endsection
@section('content')
    <div class="species-page">
        <header class="species-header">
            <div>
                <div class="species-kicker"><i class="fa-solid fa-paw"></i> Loài thú cưng</div>
                <h1>Thêm loài mới</h1>
                <p>Tạo loài để gán trực tiếp cho sản phẩm và dùng làm bộ lọc tại cửa hàng.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.pet-species.store') }}" enctype="multipart/form-data">
            @include('admin.pet-species._form')</form>
    </div>
@endsection
@section('scripts')
    <script>document.getElementById('name')?.addEventListener('input', e => { document.getElementById('slug').value = e.target.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g, 'd').toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/[\s-]+/g, '-') })</script>
@endsection