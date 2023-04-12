<?php

/**
 * @package PaystackController
 * @author TechVillage <support@techvill.org>
 * @contributor Muhammad AR Zihad <[zihad.techvill@gmail.com]>
 * @created 14-2-22
 */

namespace Modules\CoopSavings\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Addons\Entities\Addon;
use Modules\CoopSavings\Entities\CoopSavings;
use Modules\CoopSavings\Entities\CoopSavingsBody;
use Modules\CoopSavings\Http\Requests\CoopSavingsRequest;

class CoopSavingsController extends Controller
{

    public function store(CoopSavingsRequest $request)
    {
        $paystackBody = new CoopSavingsBody($request);

        CoopSavings::updateOrCreate(
            ['alias' => config('coopsavings.alias')],
            [
                'name' => config('coopsavings.name'),
                'instruction' => $request->instruction,
                'status' => $request->status,
                'sandbox' => $request->sandbox,
                'image' => 'thumbnail.png',
                'data' => json_encode($paystackBody)
            ]
        );

        return back()->with(['AddonStatus' => 'success', 'AddonMessage' => __('Coop Savings settings updated.')]);
    }

    public function edit(Request $request)
    {
        try {
            $module = CoopSavings::first()->data;
        } catch (\Exception $e) {
            $module = null;
        }
        $addon = Addon::findOrFail('coopsavings');

        return response()->json(
            [
                'html' => view('gateway::partial.form', compact('module', 'addon'))->render(),
                'status' => true
            ],
            200
        );
    }
}
