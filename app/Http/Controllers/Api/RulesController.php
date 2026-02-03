<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RuleCategory;
use Illuminate\Http\JsonResponse;

class RulesController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = RuleCategory::withCount('rules')
            ->with(['rules' => fn($query) => $query->orderBy('sort_order')])
            ->orderBy('title')
            ->get();

        $totalRules = $categories->sum('rules_count');

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories->map(function (RuleCategory $category) {
                    return [
                        'id' => $category->id,
                        'title' => $category->title,
                        'rules_count' => $category->rules_count,
                        'icon' => $category->icon,
                        'icon_color' => $category->icon_color,
                        'icon_bg_color' => $category->icon_bg_color,
                        'preview_rules' => $category->rules->pluck('text')->take(2)->values(),
                    ];
                })->values(),
                'total_categories' => $categories->count(),
                'total_rules' => $totalRules,
            ],
        ]);
    }

    public function show(RuleCategory $ruleCategory): JsonResponse
    {
        $ruleCategory->load(['rules' => fn($query) => $query->orderBy('sort_order')]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ruleCategory->id,
                'title' => $ruleCategory->title,
                'rules_count' => $ruleCategory->rules->count(),
                'icon' => $ruleCategory->icon,
                'icon_color' => $ruleCategory->icon_color,
                'icon_bg_color' => $ruleCategory->icon_bg_color,
                'description' => $ruleCategory->description,
                'rules' => $ruleCategory->rules->map(function ($rule) {
                    return [
                        'id' => $rule->id,
                        'order' => $rule->sort_order,
                        'text' => $rule->text,
                        'severity' => $rule->severity,
                        'consequence' => $rule->consequence,
                    ];
                })->values(),
            ],
        ]);
    }
}
