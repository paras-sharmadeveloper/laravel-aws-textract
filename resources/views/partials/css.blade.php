@foreach ($files as $file)
    <link rel="stylesheet" href="{{ asset("css/{$file}.css") }}?v={{ filemtime(public_path("css/{$file}.css")) }}">
@endforeach
