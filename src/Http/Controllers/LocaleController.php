<?php

namespace Johannhsdev\OctoLang\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Johannhsdev\OctoLang\LocaleManager;

class LocaleController extends Controller
{
    public function __construct(protected LocaleManager $manager) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($this->manager->supported())],
        ]);

        $this->manager->set($validated['locale']);

        return redirect()->back();
    }
}
