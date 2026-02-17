<?php

namespace App\Http\Controllers;

use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeatureFlagController extends Controller
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Display feature flag management page
     */
    public function index(): View
    {
        $availableFeatures = $this->featureService->getAvailableFeatures();
        $enabledFeatures = $this->featureService->getEnabledFeatures();

        return view('system-admin.features.index', compact('availableFeatures', 'enabledFeatures'));
    }

    /**
     * Update feature flags
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'features' => 'nullable|array',
            'features.*' => 'string',
        ]);

        $features = $request->input('features', []);
        $this->featureService->setFeatures($features);

        return redirect()->route('system-admin.features.index')
            ->with('success', 'Feature flags updated successfully');
    }
}
