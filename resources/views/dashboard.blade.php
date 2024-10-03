<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white border-red-600 border-2 p-5 rounded-lg border-solid">
        <a href="{{ route('auth.google') }}" class="bg-black p-2 text-white">Đăng nhập bằng Google để Upload Video</a>
        <form action="{{ route('upvideoyt.store') }}" method="post" enctype="multipart/form-data" class="w-full h-full flex flex-col gap-2 mt-3">
            @csrf
            <input type="text" name="title" placeholder="title" class="" required>
            <input type="text" name="description" placeholder="description" class="" required>
            <input type="file" accept="video/mp4,video/mov,video/avi" name="video" id="video" class="" required>
            <button type="submit" class="bg-blue-500 leading-9 hover:bg-blue-700 text-white">Upload</button>
        </form>
    </div>
</x-app-layout>
