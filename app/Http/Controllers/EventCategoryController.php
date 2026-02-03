<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventCategory\StoreEventCategoryRequest;
use App\Http\Requests\EventCategory\UpdateEventCategoryRequest;
use App\Models\EventCategory;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventCategoryController extends Controller
{
    use LogsActivity;
    public function index(): View
    {
        $categories = EventCategory::withCount('events')->orderBy('name')->get();

        return view('event-categories.index', compact('categories'));
    }

    public function store(StoreEventCategoryRequest $request): RedirectResponse
    {
        $category = EventCategory::create($request->validated());

        $this->logCreate('EventCategory', $category->id, $category->name);

        return redirect()->route('event-categories.index')->with('status', __('Category created.'));
    }

    public function update(UpdateEventCategoryRequest $request, EventCategory $eventCategory): RedirectResponse
    {
        $eventCategory->update($request->validated());

        $this->logUpdate('EventCategory', $eventCategory->id, $eventCategory->name);

        return redirect()->route('event-categories.index')->with('status', __('Category updated.'));
    }

    public function destroy(EventCategory $eventCategory): RedirectResponse
    {
        // Check if category has events
        if ($eventCategory->events()->exists()) {
            return redirect()->route('event-categories.index')->with('error', __('Cannot delete category with existing events.'));
        }

        $categoryName = $eventCategory->name;
        $categoryId = $eventCategory->id;
        $eventCategory->delete();

        $this->logDelete('EventCategory', $categoryId, $categoryName);

        return redirect()->route('event-categories.index')->with('status', __('Category removed.'));
    }
}
