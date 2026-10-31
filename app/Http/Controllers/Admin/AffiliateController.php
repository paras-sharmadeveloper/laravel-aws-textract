<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Support\LandingContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $affiliates = Affiliate::withCount('leads')
            ->when(in_array($request->status, ['active', 'disabled']), fn($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->q, function ($q, $term) {
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->fragment('all-affiliates');

        // Deleting or filtering away the last row of a page leaves it empty: step back to the real last page
        if ($affiliates->isEmpty() && $affiliates->currentPage() > 1) {
            $request->session()->reflash();

            return redirect($affiliates->url($affiliates->lastPage()));
        }

        return view('admin.affiliates.index', compact('affiliates'));
    }

    public function store(Request $request)
    {
        Affiliate::create($this->validated($request));

        return redirect()->route('admin.affiliates.index')->with('success', 'Affiliate created — the landing page and application links are ready to share.');
    }

    public function edit(Affiliate $affiliate)
    {
        return view('admin.affiliates.edit', compact('affiliate'));
    }

    public function update(Request $request, Affiliate $affiliate)
    {
        $affiliate->update($this->validated($request, $affiliate));

        return redirect()->route('admin.affiliates.index')->with('success', 'Affiliate updated.');
    }

    public function toggle(Affiliate $affiliate)
    {
        $affiliate->update(['is_active' => !$affiliate->is_active]);

        return back()->with('success', $affiliate->is_active
            ? "{$affiliate->name}'s link is active again."
            : "{$affiliate->name}'s link is disabled and will now return 404.");
    }

    public function destroy(Affiliate $affiliate)
    {
        $affiliate->delete();

        return back()->with('success', 'Affiliate deleted. Their past leads are kept.');
    }

    public function editLanding(Affiliate $affiliate)
    {
        return view('admin.affiliates.landing', [
            'affiliate' => $affiliate,
            'sections' => LandingContent::SECTIONS,
            'content' => $affiliate->landingContent(),
        ]);
    }

    public function updateLanding(Request $request, Affiliate $affiliate)
    {
        $data = $request->validate(LandingContent::rules((array) $request->input('content')) + [
            'apply_to_all' => ['nullable', Rule::in(array_keys(LandingContent::SECTIONS))],
        ]);
        $content = $data['content'] ?? [];
        $section = $data['apply_to_all'] ?? null;

        if ($section) {
            LandingContent::applyToAll($section, $content);
        }

        $affiliate->update(['landing_content' => LandingContent::overrides($content)]);

        return back()->with('success', $section
            ? "The {$section} section is now live on every affiliate's page."
            : "{$affiliate->name}'s landing page is updated.");
    }

    public function resetLanding(Affiliate $affiliate)
    {
        $affiliate->update(['landing_content' => null]);

        return back()->with('success', "{$affiliate->name}'s landing page is back to the default.");
    }

    private function validated(Request $request, ?Affiliate $affiliate = null): array
    {
        // Slug defaults to the name, e.g. "John Smith" -> "johnsmith"
        $request->merge([
            'slug' => Str::lower(str_replace(' ', '', trim($request->input('slug') ?: Str::slug($request->input('name', ''), '')))),
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(Affiliate::RESERVED_SLUGS),
                Rule::unique('affiliates', 'slug')->ignore($affiliate?->id),
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
        ], [
            'slug.regex' => 'The link may only contain lowercase letters, numbers and dashes.',
            'slug.not_in' => 'That link name is reserved by the system.',
            'slug.unique' => 'That link is already used by another affiliate.',
        ]);

        $data['is_active'] = $request->boolean('is_active', $affiliate?->is_active ?? true);

        return $data;
    }
}
