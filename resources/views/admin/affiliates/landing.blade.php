@extends('admin.layout')

@section('title', 'Edit landing page')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow"><a href="{{ route('admin.affiliates.index') }}">AFFILIATES</a> · LANDING PAGE</div>
            <h1>{{ $affiliate->name }}</h1>
        </div>
        <div class="actions">
            <a href="{{ $affiliate->landingLink() }}" class="btn" target="_blank" rel="noopener">View page</a>
            <form method="POST" action="{{ route('admin.affiliates.landing.reset', $affiliate) }}"
                data-confirm="Reset {{ $affiliate->name }}'s landing page? All their custom text goes back to the default.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Reset to default</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.affiliates.landing.update', $affiliate) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <p class="muted" style="margin: 0">Changes here only affect {{ $affiliate->name }}'s page. Clear a field to
                go back to the default text. Wrap words in *stars* in the headline, intro, apply heading or checklist
                to highlight them.</p>
        </div>

        @foreach ($sections as $section => $fields)
            <div class="card">
                <h2>{{ $section }}</h2>
                <div class="form-grid">
                    @foreach ($fields as $key => [$label, $default])
                        @php
                            $value = old("content.{$key}", $content[$key]);
                            $long = mb_strlen($default) > 60 || str_contains($default, "\n") || $key === 'video_embed';
                            $wide = $long || in_array($key, ['video_use_embed', 'video_url']);
                            $videoMode = ['video_url' => 'link', 'video_poster' => 'link', 'video_embed' => 'embed'][$key] ?? null;
                        @endphp
                        <div class="field" @if ($wide) style="grid-column: 1 / -1" @endif
                            @if ($videoMode) data-video-mode="{{ $videoMode }}" @endif>
                            @if ($key === 'video_use_embed')
                                <label class="check">
                                    <input type="checkbox" id="{{ $key }}" name="content[{{ $key }}]" value="1"
                                        @checked(session()->hasOldInput() ? old("content.{$key}") : $value)>
                                    {{ $label }}
                                </label>
                            @else
                                <label for="{{ $key }}">{{ $label }}</label>
                            @endif
                            @if ($key === 'video_use_embed')
                            @elseif ($long)
                                <textarea id="{{ $key }}" name="content[{{ $key }}]" class="input" rows="3"
                                    style="height: auto; padding-top: 10px; padding-bottom: 10px; resize: vertical; font: inherit">{{ $value }}</textarea>
                            @else
                                <input id="{{ $key }}" name="content[{{ $key }}]" class="input"
                                    value="{{ $value }}">
                            @endif
                            @if ($key === 'video_url')
                                <span class="help">A share link from YouTube, Vimeo, Wistia, Loom, Dailymotion, Facebook, Instagram,
                                    TikTok, Google Drive and others, or a direct link to a video file. Leave blank to use the
                                    default video.</span>
                            @endif
                            @if ($key === 'video_embed')
                                <span class="help">Paste the iframe code from the platform's Share or Embed option. Any https
                                    platform works, and the video always fits the page's video space.</span>
                            @endif
                            @if ($key === 'video_poster')
                                <span class="help">Only used for direct video files. Platforms show their own thumbnail.</span>
                            @endif
                            @error("content.{$key}")
                                <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px">
            <a href="{{ route('admin.affiliates.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Save landing page</button>
        </div>
    </form>

    <script>
        (function() {
            const toggle = document.getElementById('video_use_embed');

            function show() {
                document.querySelectorAll('[data-video-mode]').forEach(function(field) {
                    field.style.display = (field.dataset.videoMode === 'embed') === toggle.checked ? '' : 'none';
                });
            }

            toggle.addEventListener('change', show);
            show();
        })();
    </script>
@endsection
