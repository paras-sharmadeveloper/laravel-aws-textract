<?php

namespace Tests\Unit;

use App\Support\LandingVideo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LandingVideoTest extends TestCase
{
    public static function embeds(): array
    {
        $youtube = 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ';

        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s', $youtube],
            'youtube mobile, no scheme' => ['m.youtube.com/watch?v=dQw4w9WgXcQ', $youtube],
            'youtube short link' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', $youtube],
            'youtube shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', $youtube],
            'youtube live' => ['https://www.youtube.com/live/dQw4w9WgXcQ', $youtube],
            'youtube embed code' => ['<iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ?si=x" allowfullscreen></iframe>', $youtube],
            'youtube playlist' => ['https://www.youtube.com/playlist?list=PL123', 'https://www.youtube-nocookie.com/embed/videoseries?list=PL123'],
            'vimeo' => ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871'],
            'vimeo unlisted' => ['https://vimeo.com/76979871/abc123', 'https://player.vimeo.com/video/76979871?h=abc123'],
            'vimeo embed code' => ['<div style="padding:56.25% 0 0 0"><iframe src="https://player.vimeo.com/video/76979871?h=abc123&amp;title=0" frameborder="0"></iframe></div>', 'https://player.vimeo.com/video/76979871?h=abc123'],
            'wistia' => ['https://acme.wistia.com/medias/abc123xyz', 'https://fast.wistia.net/embed/iframe/abc123xyz'],
            'wistia script embed' => ['<script src="https://fast.wistia.com/embed/medias/abc123xyz.jsonp" async></script>', 'https://fast.wistia.net/embed/iframe/abc123xyz'],
            'loom' => ['https://www.loom.com/share/abc123', 'https://www.loom.com/embed/abc123'],
            'dailymotion' => ['https://www.dailymotion.com/video/x8abc', 'https://www.dailymotion.com/embed/video/x8abc'],
            'dailymotion short link' => ['https://dai.ly/x8abc', 'https://www.dailymotion.com/embed/video/x8abc'],
            'streamable' => ['https://streamable.com/abc12', 'https://streamable.com/e/abc12'],
            'vidyard' => ['https://share.vidyard.com/watch/abc123', 'https://play.vidyard.com/abc123'],
            'google drive' => ['https://drive.google.com/file/d/1a2B_c-3/view?usp=sharing', 'https://drive.google.com/file/d/1a2B_c-3/preview'],
            'tiktok' => ['https://www.tiktok.com/@dps/video/7123456789', 'https://www.tiktok.com/embed/v2/7123456789'],
            'instagram' => ['https://www.instagram.com/reel/Cabc123/', 'https://www.instagram.com/p/Cabc123/embed'],
            'facebook' => ['https://www.facebook.com/dps/videos/12345/', 'https://www.facebook.com/plugins/video.php?show_text=false&href=https%3A%2F%2Fwww.facebook.com%2Fdps%2Fvideos%2F12345%2F'],
            'other platform embed code' => ['<iframe src="//player.example.tv/v/9"></iframe>', 'https://player.example.tv/v/9'],
        ];
    }

    #[DataProvider('embeds')]
    public function test_platform_links_and_embed_codes_become_players(string $input, string $src): void
    {
        $this->assertSame(['type' => 'embed', 'src' => $src], LandingVideo::parse($input));
    }

    public function test_direct_video_files_play_natively(): void
    {
        $this->assertSame(
            ['type' => 'file', 'src' => 'https://cdn.example.com/hero.webm?v=2'],
            LandingVideo::parse('https://cdn.example.com/hero.webm?v=2')
        );
    }

    public function test_anything_else_is_rejected(): void
    {
        foreach ([null, '', 'not a video', 'https://example.com/about', 'javascript:alert(1)', '<iframe src="javascript:alert(1)"></iframe>', '<iframe src="http://insecure.example.com/x"></iframe>', '<script>alert(1)</script>'] as $input) {
            $this->assertNull(LandingVideo::parse($input), (string) $input);
        }
    }
}
