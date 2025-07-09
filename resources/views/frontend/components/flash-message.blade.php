@if ($message = Session::get('success'))
<div class="bg-green-100 flex justify-center items-center w-full h-16 text-base my-2 rounded-lg p-3">
    <h4 class="font-semibold text-green-900">{{ $message }}</h4>
</div>
@endif

@if ($message = Session::get('error'))
<div class="bg-red-100 flex justify-center items-center w-full h-16 text-base my-2 rounded-lg p-3">
    <h4 class="font-semibold text-red-900">{{ $message }}</h4>
</div>
@endif

@if ($message = Session::get('warning'))
<div class="bg-yellow-100 flex justify-center items-center w-full h-16 text-base my-2 rounded-lg p-3">
    <h4 class="font-semibold text-yellow-900">{{ $message }}</h4>
</div>
@endif

@if ($message = Session::get('info'))
<div class="bg-blue-100 flex justify-center items-center w-full h-16 text-base my-2 rounded-lg p-3">
    <h4 class="font-semibold text-blue-900">{{ $message }}</h4>
</div>
@endif

@if ($errors->any())
<div class="bg-red-100 w-full text-base my-2 rounded-lg p-4">
    <h4 class="font-semibold text-red-900 mb-2">Please fix the following errors:</h4>
    <ul class="list-disc list-inside space-y-1">
        @foreach ($errors->all() as $error)
            <li class="text-red-700">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif