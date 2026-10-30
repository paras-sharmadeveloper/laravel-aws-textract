<?php

namespace App\Support;

/**
 * Works out how to play what an admin pasted as a landing page video: a share link
 * from a video platform, an embed code, or a direct link to a video file. Script
 * embeds from other platforms only ever run inside the sandboxed frame from
 * document(), never on the page itself.
 */
class LandingVideo
{
    private const FILE_EXTENSIONS = ['mp4', 'm4v', 'mov', 'webm', 'ogv', 'ogg'];

    /** Regex on "host/path" => embed URL, $1 being the video id */
    private const PLATFORMS = [
        '#^(?:music\.)?youtube(?:-nocookie)?\.com/(?:embed|shorts|live|v)/([\w-]{6,})#' => 'https://www.youtube-nocookie.com/embed/$1',
        '#^youtu\.be/([\w-]{6,})#' => 'https://www.youtube-nocookie.com/embed/$1',
        '#^(?:[\w-]+\.)?wistia\.(?:com|net)/(?:medias|embed/iframe|embed/medias|embed)/(\w+)#' => 'https://fast.wistia.net/embed/iframe/$1',
        '#^loom\.com/(?:share|embed)/(\w+)#' => 'https://www.loom.com/embed/$1',
        '#^(?:geo\.)?dailymotion\.com/(?:embed/)?video/(\w+)#' => 'https://www.dailymotion.com/embed/video/$1',
        '#^dai\.ly/(\w+)#' => 'https://www.dailymotion.com/embed/video/$1',
        '#^streamable\.com/(?:e/)?(\w+)#' => 'https://streamable.com/e/$1',
        '#^(?:share|play|video)\.vidyard\.com/(?:watch/)?(\w+)#' => 'https://play.vidyard.com/$1',
        '#^drive\.google\.com/file/d/([\w-]+)#' => 'https://drive.google.com/file/d/$1/preview',
        '#^tiktok\.com/(?:@[^/]+/video|embed/v2|embed)/(\d+)#' => 'https://www.tiktok.com/embed/v2/$1',
        '#^instagram\.com/(?:p|reel|tv)/([\w-]+)#' => 'https://www.instagram.com/p/$1/embed',
        '#^rumble\.com/embed/(\w+)#' => 'https://rumble.com/embed/$1/',
    ];

    /** @return array{type: 'file'|'embed', src: string}|array{type: 'code', html: string}|null */
    public static function parse(?string $input): ?array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (!str_contains($input, '<')) {
            return self::fromUrl($input);
        }

        // Embed code: an iframe from any https site, or a script that loads its player from the web
        if (preg_match('/<iframe\b[^>]*\bsrc\s*=\s*["\']?([^"\'\s>]+)/i', $input, $iframe)) {
            return self::fromUrl($iframe[1], true);
        }

        preg_match_all('#(?:https?:)?//[^\s"\'<>]+#i', $input, $urls);

        foreach ($urls[0] as $url) {
            if ($video = self::fromUrl($url)) {
                return $video;
            }
        }

        return $urls[0] && stripos($input, '<script') !== false ? ['type' => 'code', 'html' => $input] : null;
    }

    /** Standalone page for the sandboxed frame that runs a script embed. */
    public static function document(string $code): string
    {
        return '<!DOCTYPE html><html><head><meta charset="utf-8">'
            . '<style>html,body{margin:0;height:100%;overflow:hidden;background:#000}</style>'
            . "</head><body>{$code}</body></html>";
    }

    private static function fromUrl(string $url, bool $fromIframe = false): ?array
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5);

        if (str_starts_with($url, '//')) {
            $url = "https:{$url}";
        } elseif (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            $url = "https://{$url}";
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');

        if (empty($parts['host']) || !in_array($scheme, ['http', 'https'])) {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);
        $host = preg_replace('/^(?:www|m)\./', '', strtolower($parts['host']));
        $path = $parts['path'] ?? '/';

        if ($embed = self::platformEmbed($host, $path, $query, $url)) {
            return ['type' => 'embed', 'src' => $embed];
        }

        if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::FILE_EXTENSIONS)) {
            return ['type' => 'file', 'src' => $url];
        }

        return $fromIframe && $scheme === 'https' ? ['type' => 'embed', 'src' => $url] : null;
    }

    private static function platformEmbed(string $host, string $path, array $query, string $url): ?string
    {
        $target = $host . $path;

        foreach (self::PLATFORMS as $pattern => $embed) {
            if (preg_match($pattern, $target, $match)) {
                return str_replace('$1', $match[1], $embed);
            }
        }

        if (preg_match('/(?:^|\.)youtube(?:-nocookie)?\.com$/', $host)) {
            $id = (string) ($query['v'] ?? '');
            $list = (string) ($query['list'] ?? '');

            if ($path === '/watch' && preg_match('/^[\w-]{6,}$/', $id)) {
                return "https://www.youtube-nocookie.com/embed/{$id}";
            }

            if ($path === '/playlist' && preg_match('/^[\w-]+$/', $list)) {
                return "https://www.youtube-nocookie.com/embed/videoseries?list={$list}";
            }
        }

        // Unlisted Vimeo videos need their private hash as well as the id
        if (preg_match('#^(?:player\.)?vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/|showcase/\d+/video/)?(\d+)(?:/(\w+))?#', $target, $match)) {
            $hash = $match[2] ?? (string) ($query['h'] ?? '');

            return "https://player.vimeo.com/video/{$match[1]}" . (preg_match('/^\w+$/', $hash) ? "?h={$hash}" : '');
        }

        if ($host === 'fb.watch' || ($host === 'facebook.com' && preg_match('#/videos/|^/watch|^/reel/#', $path))) {
            return 'https://www.facebook.com/plugins/video.php?show_text=false&href=' . urlencode($url);
        }

        return null;
    }
}
