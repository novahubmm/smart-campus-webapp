<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolRule\StoreSchoolRuleRequest;
use App\Http\Requests\SchoolRule\UpdateSchoolRuleRequest;
use App\Models\RuleCategory;
use App\Models\SchoolRule;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;

class SchoolRuleController extends Controller
{
    use LogsActivity;
    public function store(RuleCategory $ruleCategory, StoreSchoolRuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $order = $data['order'] ?? null;

        if (!$order) {
            $order = (int) $ruleCategory->rules()->max('sort_order') + 1;
        }

        $rule = $ruleCategory->rules()->create([
            'sort_order' => $order,
            'text' => $data['text'],
            'severity' => $data['severity'] ?? 'medium',
            'consequence' => $data['consequence'] ?? null,
        ]);

        $this->logCreate('SchoolRule', $rule->id, substr($rule->text, 0, 50));

        return redirect()
            ->route('rules.show', $ruleCategory)
            ->with('status', __('Rule added.'));
    }

    public function update(
        RuleCategory $ruleCategory,
        SchoolRule $schoolRule,
        UpdateSchoolRuleRequest $request
    ): RedirectResponse {
        if ($schoolRule->rule_category_id !== $ruleCategory->id) {
            abort(404);
        }

        $data = $request->validated();
        $order = $data['order'] ?? $schoolRule->sort_order;

        $schoolRule->update([
            'sort_order' => $order,
            'text' => $data['text'],
            'severity' => $data['severity'],
            'consequence' => $data['consequence'] ?? null,
        ]);

        $this->logUpdate('SchoolRule', $schoolRule->id, substr($schoolRule->text, 0, 50));

        return redirect()
            ->route('rules.show', $ruleCategory)
            ->with('status', __('Rule updated.'));
    }

    public function destroy(RuleCategory $ruleCategory, SchoolRule $schoolRule): RedirectResponse
    {
        if ($schoolRule->rule_category_id !== $ruleCategory->id) {
            abort(404);
        }

        $ruleText = substr($schoolRule->text, 0, 50);
        $ruleId = $schoolRule->id;
        $schoolRule->delete();

        $this->logDelete('SchoolRule', $ruleId, $ruleText);

        return redirect()
            ->route('rules.show', $ruleCategory)
            ->with('status', __('Rule removed.'));
    }
}
