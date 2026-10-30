<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Editable text of the affiliate landing page. An affiliate only stores the values
 * that differ from these defaults, so anything they never changed follows the defaults.
 */
class LandingContent
{
    /** Section title => [field key => [admin label, default value]] */
    public const SECTIONS = [
        'Top menu' => [
            'nav_watch' => ['Link 1', 'Watch'],
            'nav_how' => ['Link 2', 'How it works'],
            'nav_funding' => ['Link 3', 'Funding'],
            'nav_faq' => ['Link 4', 'FAQ'],
            'nav_button' => ['Button', 'Apply now →'],
        ],
        'Hero' => [
            'hero_eyebrow' => ['Small heading', 'GROWTH CAPITAL FOR RESTAURANTS AND BARS'],
            'hero_title' => ['Headline', 'Get *up to $1.5 million+* in cash for your restaurant or bar.'],
            'hero_text' => ['Intro text', 'Funding comes in *two rounds*: an initial round when you go live, and you can qualify for more within 90 days. You pay it back in food and drinks, not cash.'],
            'hero_button' => ['Button', 'Apply in 5 minutes →'],
        ],
        'Video' => [
            'video_use_embed' => ['Use embed code', ''],
            'video_url' => ['Video link', ''],
            'video_embed' => ['Embed code', ''],
            'video_poster' => ['Thumbnail image link', ''],
        ],
        'Checkmarks' => [
            'perk_1' => ['Item 1', 'No interest, ever'],
            'perk_2' => ['Item 2', 'No daily, weekly or monthly payments'],
            'perk_3' => ['Item 3', 'No personal collateral'],
            'perk_4' => ['Item 4', 'Not a loan, not debt'],
            'perk_5' => ['Item 5', 'No credit check to apply'],
        ],
        'Stats' => [
            'stat_1_value' => ['Stat 1 number', '249M+'],
            'stat_1_label' => ['Stat 1 label', 'funded across our partner network'],
            'stat_2_value' => ['Stat 2 number', '2M+'],
            'stat_2_label' => ['Stat 2 label', 'members across our partner network'],
            'stat_3_value' => ['Stat 3 number', 'Up to 22%'],
            'stat_3_label' => ['Stat 3 label', 'more spent per visit by network members'],
        ],
        'How it works' => [
            'steps_eyebrow' => ['Small heading', 'HOW IT WORKS'],
            'steps_title' => ['Heading', 'Three steps. No debt on your books.'],
            'step_1_title' => ['Step 1 title', 'Send your documents'],
            'step_1_text' => ['Step 1 text', "Apply below with a few documents. You'll see what you qualify for in 1–2 business days."],
            'step_2_title' => ['Step 2 title', 'We wire the cash'],
            'step_2_text' => ['Step 2 text', 'We set up your payments, then wire the cash when you go live. Use it for anything: a new location, build-outs, equipment, payroll, marketing.'],
            'step_3_title' => ['Step 3 title', 'Pay it back in food and drinks'],
            'step_3_text' => ['Step 3 text', "Members of our partner network buy credit at your restaurant and spend it with you. That's how the funding is paid back."],
            'benefit_1_title' => ['Benefit 1 title', 'Fast money'],
            'benefit_1_text' => ['Benefit 1 text', 'Your initial round is typically funded 2–4 weeks after you apply, with more possible within 90 days.'],
            'benefit_2_title' => ['Benefit 2 title', 'Keep 100% of your business'],
            'benefit_2_text' => ['Benefit 2 text', 'No equity. No personal-loan-style debt on your books.'],
            'benefit_3_title' => ['Benefit 3 title', 'New customers on demand'],
            'benefit_3_text' => ['Benefit 3 text', 'Fill slow nights with guests from a 3M+ member network.'],
            'benefit_4_title' => ['Benefit 4 title', 'Bigger tickets'],
            'benefit_4_text' => ['Benefit 4 text', 'Network members spend up to 40% more per visit.'],
        ],
        'Funding and who qualifies' => [
            'funding_eyebrow' => ['Small heading', 'FUNDING AND WHO QUALIFIES'],
            'funding_title' => ['Heading', 'Built to scale with you.'],
            'tier_1_title' => ['Tier 1 title', 'Initial Funding'],
            'tier_1_text' => ['Tier 1 text', 'Wired at go-live · typically 2–4 weeks'],
            'tier_1_amount' => ['Tier 1 amount', 'Up to $50K per location'],
            'tier_2_title' => ['Tier 2 title', 'Growth Funding'],
            'tier_2_text' => ['Tier 2 text', 'Within 90 days, based on your results'],
            'tier_2_amount' => ['Tier 2 amount', 'Up to $1.5M+'],
            'tier_3_title' => ['Tier 3 title', 'Enterprise'],
            'tier_3_text' => ['Tier 3 text', 'Groups of 15+ locations · structured case by case'],
            'tier_3_amount' => ['Tier 3 amount', 'Custom'],
            'qualify_1_title' => ['Requirement 1 title', 'Restaurants and bars'],
            'qualify_1_text' => ['Requirement 1 text', 'Full-service, fine dining, bars, nightclubs, cafés and pizzerias with seating'],
            'qualify_2_title' => ['Requirement 2 title', '4★+ on Google'],
            'qualify_2_text' => ['Requirement 2 text', 'Required'],
            'qualify_3_title' => ['Requirement 3 title', '$500K+ / year'],
            'qualify_3_text' => ['Requirement 3 text', 'In sales, per location.'],
            'funding_note' => ['Note', 'Single locations and multi-location groups both welcome. More seats, more funding.'],
        ],
        'Apply card' => [
            'apply_eyebrow' => ['Small heading', 'APPLY · ABOUT 5 MINUTES'],
            'apply_title' => ['Heading', "See your number\nin 1–2 *business days.*"],
            'apply_text' => ['Text', "Upload a few documents through our secure application. No cost, no obligation, and you'll hear from us the same day."],
            'apply_button' => ['Button', 'Start your application →'],
            'apply_secure' => ['Text under button', 'Opens our secure application'],
            'ready_title' => ['Checklist heading', 'HAVE THESE READY'],
            'ready_1' => ['Checklist item 1', "Driver's license or passport"],
            'ready_2' => ['Checklist item 2', 'Voided check or bank letter'],
            'ready_3' => ['Checklist item 3', 'Business certificate or EIN letter'],
            'ready_4' => ['Checklist item 4', '12 months of bank statements *(or first-year Projections for a new location)*'],
            'ready_note' => ['Checklist note', '+ Your last 3 months of processing statements speed up approval'],
        ],
        'FAQ' => [
            'faq_eyebrow' => ['Small heading', 'FAQ'],
            'faq_title' => ['Heading', 'Questions, answered.'],
            'faq_1_question' => ['Question 1', 'Is this a loan?'],
            'faq_1_answer' => ['Answer 1', "No. There's no interest and no daily, weekly or monthly payments. The funding is paid back in food and drinks as members of our partner network dine with you."],
            'faq_2_question' => ['Question 2', 'Who qualifies?'],
            'faq_2_answer' => ['Answer 2', 'Restaurants, bars, nightclubs, cafés and pizzerias with seating and a 4★+ rating on Google, doing at least $500K a year in sales per location. Single locations and groups are both welcome.'],
            'faq_3_question' => ['Question 3', 'How much can I get?'],
            'faq_3_answer' => ['Answer 3', "It's based on your yearly sales and number of locations, and it comes in two rounds: an initial round when you go live, and you can qualify for more within 90 days. We'll give you your exact numbers in 1–2 business days."],
            'faq_4_question' => ['Question 4', 'How fast can I get funded?'],
            'faq_4_answer' => ['Answer 4', "We respond the same day and show you your number in 1–2 business days. You're funded when you go live, typically 2–4 weeks with a second round possible within 90 days."],
            'faq_5_question' => ['Question 5', 'Do I have to switch my processing?'],
            'faq_5_answer' => ['Answer 5', 'Switching your card processing to DPS comes with the funding, and we handle all of it.'],
            'faq_6_question' => ['Question 6', 'Will switching disrupt my business?'],
            'faq_6_answer' => ['Answer 6', "No. We install outside your business hours, so there's no downtime. Before you go live, we demo the system, train your staff hands-on and stay with you 24/7/365 after."],
            'faq_7_question' => ['Question 7', 'Am I locked into a contract?'],
            'faq_7_answer' => ['Answer 7', "Only if you take the funding. Funded agreements typically run 3 to 5 years. If you don't move forward, there's no obligation."],
            'faq_8_question' => ['Question 8', 'Does applying affect my credit?'],
            'faq_8_answer' => ['Answer 8', "No. There's no credit check to apply."],
            'faq_9_question' => ['Question 9', 'What happens to my current equipment?'],
            'faq_9_answer' => ['Answer 9', 'Once your new system is up and running, we help you return your old equipment.'],
        ],
    ];

    public static function defaults(): array
    {
        return array_map(fn($field) => $field[1], array_merge(...array_values(self::SECTIONS)));
    }

    /** Full page text: the affiliate's own values on top of the defaults. */
    public static function merge(?array $overrides): array
    {
        $defaults = self::defaults();

        return array_merge($defaults, array_intersect_key($overrides ?? [], $defaults));
    }

    /** What to store for an affiliate: only the known fields that differ from the defaults. */
    public static function overrides(array $input): array
    {
        $overrides = [];

        foreach (self::defaults() as $key => $default) {
            $value = trim(str_replace("\r\n", "\n", (string) ($input[$key] ?? '')));

            if ($value !== '' && $value !== $default) {
                $overrides[$key] = $value;
            }
        }

        return $overrides;
    }

    public static function rules(array $input = []): array
    {
        $rules = [];

        foreach (array_keys(self::defaults()) as $key) {
            $rules["content.{$key}"] = 'nullable|string|max:2000';
        }

        $rules['content.video_poster'] = 'nullable|url:http,https|max:2000';
        $rules['content.video_use_embed'] = 'nullable|in:1';

        // Only the source in use is checked; the other one is kept for switching back
        $useEmbed = !empty($input['video_use_embed']);
        $playable = function ($attribute, $value, $fail) use ($useEmbed) {
            if (!LandingVideo::parse($value)) {
                $fail($useEmbed
                    ? "We can't play this embed code. Paste the full iframe code from the video platform."
                    : "We can't play this video. Paste a link from YouTube, Vimeo, Wistia, Loom or a similar platform, or a direct link to a video file such as an MP4.");
            }
        };

        $rules['content.video_url'] = ['nullable', 'string', 'max:5000', ...($useEmbed ? [] : [$playable])];
        $rules['content.video_embed'] = $useEmbed
            ? ['required', 'string', 'max:5000', $playable]
            : ['nullable', 'string', 'max:5000'];

        return $rules;
    }

    /** The video source chosen in the admin portal, or the site-wide default. */
    public static function video(array $content): ?array
    {
        $source = $content['video_use_embed'] ? $content['video_embed'] : $content['video_url'];

        return LandingVideo::parse($source ?: config('services.landing.video_url'));
    }

    /** Escapes the text, then turns *words* into the given tag and line breaks into <br>. */
    public static function highlight(string $text, string $tag = 'em'): HtmlString
    {
        $html = preg_replace('/\*(.+?)\*/s', "<{$tag}>$1</{$tag}>", e($text));

        return new HtmlString(nl2br($html, false));
    }
}
