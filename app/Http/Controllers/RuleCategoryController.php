<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleCategory\StoreRuleCategoryRequest;
use App\Http\Requests\RuleCategory\UpdateRuleCategoryRequest;
use App\Models\RuleCategory;
use App\Models\SchoolRule;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RuleCategoryController extends Controller
{
    use LogsActivity;
    public function index(): View
    {
        $categories = RuleCategory::withCount('rules')
            ->with(['rules' => fn($query) => $query->orderBy('sort_order')])
            ->orderBy('title')
            ->get();

        $stats = [
            'total_categories' => $categories->count(),
            'total_rules' => SchoolRule::count(),
            'categories_with_rules' => $categories->where('rules_count', '>', 0)->count(),
        ];

        return view('rules.index', compact('categories', 'stats'));
    }

    public function show(RuleCategory $ruleCategory): View
    {
        $ruleCategory->load(['rules' => fn($query) => $query->orderBy('sort_order')]);

        return view('rules.show', [
            'category' => $ruleCategory,
        ]);
    }

    public function store(StoreRuleCategoryRequest $request): RedirectResponse
    {
        $category = RuleCategory::create($request->validated());

        $this->logCreate('RuleCategory', $category->id, $category->title);

        return redirect()->route('rules.index')->with('status', __('Rule category created.'));
    }

    public function update(UpdateRuleCategoryRequest $request, RuleCategory $ruleCategory): RedirectResponse
    {
        $ruleCategory->update($request->validated());

        $this->logUpdate('RuleCategory', $ruleCategory->id, $ruleCategory->title);

        return redirect()->route('rules.index')->with('status', __('Rule category updated.'));
    }

    public function destroy(RuleCategory $ruleCategory): RedirectResponse
    {
        if ($ruleCategory->rules()->exists()) {
            return redirect()->route('rules.index')->with('error', __('Cannot delete category with existing rules.'));
        }

        $categoryTitle = $ruleCategory->title;
        $categoryId = $ruleCategory->id;
        $ruleCategory->delete();

        $this->logDelete('RuleCategory', $categoryId, $categoryTitle);

        return redirect()->route('rules.index')->with('status', __('Rule category removed.'));
    }
}
